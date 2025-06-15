<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil data dari POST
$required_fields = ['field_id', 'field_name', 'date', 'start_time', 'duration', 'total_price'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        header('Location: booking.php?id=' . $_POST['field_id']);
        exit();
    }
}

// Simpan data dalam variabel
$field_id = (int)$_POST['field_id'];
$field_name = $_POST['field_name'];
$date = $_POST['date'];
$start_time = $_POST['start_time'];
$duration = (int)$_POST['duration'];
$total_price = (float)$_POST['total_price'];

// Hitung waktu selesai
$start_time_obj = DateTime::createFromFormat('H:i', $start_time);
$end_time_obj = clone $start_time_obj;
$end_time_obj->modify("+{$duration} hours");
$end_time = $end_time_obj->format('H:i');

// Dapatkan data user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Perbaikan: Pastikan data user ditemukan
if (!$user) {
    die("User tidak ditemukan.");
}

// Dapatkan saldo pengguna
$stmt = $conn->prepare("SELECT balance FROM user_balances WHERE user_id = ?");
$stmt->execute([$user_id]);
$balance = $stmt->fetchColumn();
$balance = $balance ? (float)$balance : 0.00;

// Jika form pembayaran dikirim (memilih metode)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['topup_amount'])) {
        // Proses top up saldo
        $topup_amount = (float)$_POST['topup_amount'];
        if ($topup_amount > 0) {
            $new_balance = $balance + $topup_amount;
            $conn->beginTransaction();
            try {
                // Update saldo
                $stmt = $conn->prepare("UPDATE user_balances SET balance = ? WHERE user_id = ?");
                $stmt->execute([$new_balance, $user_id]);
                
                // Insert transaksi top up
                $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'topup', 'Top up saldo')");
                $stmt->execute([$user_id, $topup_amount]);
                
                $conn->commit();
                $balance = $new_balance;
                $success_message = "Saldo berhasil ditambahkan: Rp " . number_format($topup_amount, 0, ',', '.');
            } catch (Exception $e) {
                $conn->rollBack();
                $error = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['payment_method'])) {
        $payment_method = $_POST['payment_method'];
        
        // Proses pembayaran berdasarkan metode
        if ($payment_method === 'saldo') {
            // Cek saldo cukup
            if ($balance < $total_price) {
                $error = "Saldo tidak cukup. Silakan top up atau pilih metode lain.";
            } else {
                // Kurangi saldo
                $new_balance = $balance - $total_price;
                $conn->beginTransaction();
                try {
                    // Update saldo
                    $stmt = $conn->prepare("UPDATE user_balances SET balance = ? WHERE user_id = ?");
                    $stmt->execute([$new_balance, $user_id]);
                    
                    // Insert transaksi (pengurangan)
                    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'payment', 'Pembayaran booking lapangan')");
                    $stmt->execute([$user_id, -$total_price]);
                    
                    // Buat booking
                    $stmt = $conn->prepare("INSERT INTO booking (user_id, lapangan_id, tanggal, jam_mulai, jam_selesai, durasi, total_harga, status, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed', ?)");
                    $stmt->execute([$user_id, $field_id, $date, $start_time, $end_time, $duration, $total_price, 'saldo']);
                    $booking_id = $conn->lastInsertId();
                    
                    // Commit
                    $conn->commit();
                    
                    // Redirect ke halaman sukses
                    header('Location: booking_success.php?id=' . $booking_id);
                    exit();
                } catch (Exception $e) {
                    $conn->rollBack();
                    $error = "Terjadi kesalahan: " . $e->getMessage();
                }
            }
        } elseif ($payment_method === 'qris') {
            // Simpan booking dengan status confirmed dan metode qris
            $stmt = $conn->prepare("INSERT INTO booking (user_id, lapangan_id, tanggal, jam_mulai, jam_selesai, durasi, total_harga, status, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed', ?)");
            $stmt->execute([$user_id, $field_id, $date, $start_time, $end_time, $duration, $total_price, 'qris']);
            $booking_id = $conn->lastInsertId();
            header('Location: booking_success.php?id=' . $booking_id);
            exit();
        } elseif ($payment_method === 'transfer') {
            // Handle upload bukti transfer
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                $target_dir = "assets/img/payment_proofs/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $file_extension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
                $file_name = 'proof_' . time() . '_' . uniqid() . '.' . $file_extension;
                $target_file = $target_dir . $file_name;
                
                if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target_file)) {
                    // Simpan booking dengan status pending dan metode transfer, simpan bukti
                    $stmt = $conn->prepare("INSERT INTO booking (user_id, lapangan_id, tanggal, jam_mulai, jam_selesai, durasi, total_harga, status, payment_method, payment_proof) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
                    $stmt->execute([$user_id, $field_id, $date, $start_time, $end_time, $duration, $total_price, 'transfer', $file_name]);
                    $booking_id = $conn->lastInsertId();
                    header('Location: booking_pending.php?id=' . $booking_id);
                    exit();
                } else {
                    $error = "Gagal mengunggah bukti pembayaran.";
                }
            } else {
                $error = "Silakan unggah bukti transfer.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pembayaran - <?= htmlspecialchars($field_name) ?> | Paresports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .header {
      background-color: white;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      padding: 15px 0;
    }
    
    .logo {
      height: 40px;
    }
    
    .container-main {
      max-width: 1200px;
      margin: 30px auto;
      padding: 0 15px;
    }
    
    .card {
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      border: none;
      margin-bottom: 25px;
    }
    
    .card-header {
      background-color: white;
      border-bottom: 1px solid #eee;
      padding: 20px;
      font-weight: 600;
      font-size: 1.25rem;
    }
    
    .card-body {
      padding: 25px;
    }
    
    .venue-title {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 5px;
    }
    
    .venue-subtitle {
      color: #6c757d;
      margin-bottom: 15px;
    }
    
    .booking-info {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
    }
    
    .booking-detail {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: 1px solid #eee;
    }
    
    .booking-detail:last-child {
      border-bottom: none;
    }
    
    .price-tag {
      font-weight: 700;
      color: #dc3545;
    }
    
    .section-title {
      font-size: 1.1rem;
      font-weight: 600;
      margin: 20px 0 15px 0;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }
    
    .cost-summary {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 20px;
    }
    
    .cost-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
    }
    
    .cost-total {
      display: flex;
      justify-content: space-between;
      font-size: 1.1rem;
      font-weight: 700;
      padding-top: 15px;
      margin-top: 15px;
      border-top: 1px solid #ddd;
    }
    
    .payment-method {
      display: flex;
      align-items: center;
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      margin-bottom: 15px;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .payment-method:hover {
      border-color: #dc3545;
      background-color: rgba(220, 53, 69, 0.05);
    }
    
    .payment-method.selected {
      border-color: #dc3545;
      border-width: 2px;
      background-color: rgba(220, 53, 69, 0.05);
    }
    
    .payment-icon {
      width: 50px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      color: #dc3545;
      font-size: 1.5rem;
    }
    
    .payment-title {
      font-weight: 600;
      margin-bottom: 3px;
    }
    
    .payment-desc {
      color: #6c757d;
      font-size: 0.9rem;
    }
    
    .terms {
      font-size: 0.85rem;
      color: #6c757d;
      margin-top: 20px;
    }
    
    .terms a {
      color: #dc3545;
      text-decoration: none;
    }
    
    .terms a:hover {
      text-decoration: underline;
    }
    
    .btn-pay {
      background-color: #dc3545;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 12px 0;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s;
      width: 100%;
      margin-top: 10px;
    }
    
    .btn-pay:hover {
      background-color: #bb2d3b;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
    }
    
    .btn-pay:active {
      transform: translateY(0);
    }
    
    .cancel-policy {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 15px;
      font-size: 0.9rem;
    }
    
    .policy-title {
      font-weight: 600;
      margin-bottom: 10px;
    }
    
    .policy-content {
      color: #6c757d;
    }
    
    .divider {
      height: 1px;
      background-color: #eee;
      margin: 25px 0;
    }
    
    /* Header styles */
    .navbar-brand img {
      height: 40px;
    }
    
    .navbar {
      background-color: #ffffff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .user-dropdown .dropdown-toggle {
      background-color: #f8f9fa;
      border-color: #dee2e6;
      color: #495057;
      transition: all 0.3s ease;
    }
    
    .user-dropdown .dropdown-toggle:hover {
      background-color: #e9ecef;
      border-color: #ced4da;
      color: #212529;
      transform: translateY(-1px);
    }
    
    .user-dropdown .dropdown-item {
      color: #495057;
      transition: all 0.2s;
      padding: 8px 16px;
    }
    
    .user-dropdown .dropdown-item:hover,
    .user-dropdown .dropdown-item:focus {
      background-color: #dc3545;
      color: white !important;
    }
    
    .user-dropdown .dropdown-item.text-danger:hover {
      background-color: #dc3545;
      color: white !important;
    }
    
    .btn-danger {
      background-color: #dc3545;
      border-color: #dc3545;
      transition: all 0.3s ease;
    }
    
    .btn-danger:hover {
      background-color: #bb2d3b;
      border-color: #b02a37;
      transform: translateY(-1px);
    }
    
    .topup-section {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 20px;
      margin-top: 20px;
    }
    
    .topup-options {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 15px;
    }
    
    .topup-option {
      flex: 1;
      min-width: 100px;
      text-align: center;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .topup-option:hover, .topup-option.selected {
      border-color: #28a745;
      background-color: rgba(40, 167, 69, 0.1);
    }
    
    .topup-option.selected {
      border-width: 2px;
    }
    
    @media (max-width: 768px) {
      .container-main {
        padding: 0 15px;
      }
      
      .card-body {
        padding: 20px 15px;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <nav class="navbar navbar-expand-lg navbar-light p-3">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center fw-bold" href="index.php">
        <img src="assets/img/Logo pare sports.png" alt="Logo" class="logo">
      </a>
      <div class="d-flex align-items-center">
        <!-- Tombol Kembali -->
        <a href="javascript:history.back()" class="btn btn-outline-danger me-2">
          <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
        <!-- Tombol Bantuan -->
        <a href="#" class="btn btn-outline-secondary">Bantuan</a>
      </div>
    </div>
  </nav>

  <div class="container container-main">
    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if (isset($success_message)): ?>
      <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>
    
    <div class="row">
      <!-- Kolom Kiri -->
      <div class="col-lg-8 mb-4">
        <div class="card">
          <div class="card-header">Penyewaan Lapangan</div>
          <div class="card-body">
            <h2 class="venue-title"><?= htmlspecialchars($field_name) ?></h2>
            <div class="booking-info">
              <div class="booking-detail">
                <div>
                  <strong>Tanggal & Waktu</strong>
                </div>
              </div>
              <div class="booking-detail">
                <div>
                  <i class="far fa-calendar-alt me-2"></i> <?= date('l, d M Y', strtotime($date)) ?>
                </div>
                <div>
                  <i class="far fa-clock me-2"></i> <?= $start_time ?> - <?= $end_time ?>
                </div>
              </div>
              <div class="booking-detail">
                <div>Durasi</div>
                <div><?= $duration ?> Jam</div>
              </div>
              <div class="booking-detail">
                <div>Harga</div>
                <div class="price-tag">Rp <?= number_format($total_price, 0, ',', '.') ?></div>
              </div>
            </div>
            
            <h5 class="section-title">Data Penyewa</h5>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Nama Lengkap *</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" readonly>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Nomor Ponsel *</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" readonly>
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label">E-mail *</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
              </div>
            </div>
            
            <div class="divider"></div>
            
            <h5 class="section-title">Kebijakan Penjadwalan Ulang & Pembatalan</h5>
            
            <div class="cancel-policy">
              <div class="policy-title">Ketentuan Pembatalan</div>
              <div class="policy-content">
                <p>Pembatalan lebih dari 24 jam sebelum waktu booking: Biaya pembatalan 10% dari total biaya.</p>
                <p>Pembatalan dalam 24 jam sebelum waktu booking: Biaya pembatalan 50% dari total biaya.</p>
                <p>Pembatalan dalam 12 jam sebelum waktu booking: Tidak dapat dilakukan pembatalan.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Kolom Kanan -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header">Rincian Biaya</div>
          <div class="card-body">
            <div class="cost-summary">
              <div class="cost-item">
                <div>Biaya Sewa</div>
                <div>Rp <?= number_format($total_price, 0, ',', '.') ?></div>
              </div>
              <div class="cost-total">
                <div>Total Bayar</div>
                <div class="price-tag">Rp <?= number_format($total_price, 0, ',', '.') ?></div>
              </div>
            </div>
            
            <h5 class="section-title">Metode Pembayaran</h5>
            
            <form method="post" enctype="multipart/form-data" id="paymentForm">
              <input type="hidden" name="field_id" value="<?= $field_id ?>">
              <input type="hidden" name="field_name" value="<?= htmlspecialchars($field_name) ?>">
              <input type="hidden" name="date" value="<?= $date ?>">
              <input type="hidden" name="start_time" value="<?= $start_time ?>">
              <input type="hidden" name="duration" value="<?= $duration ?>">
              <input type="hidden" name="total_price" value="<?= $total_price ?>">
              
              <div class="payment-method <?= $balance >= $total_price ? 'selected' : '' ?>" data-method="saldo">
                <div class="payment-icon">
                  <i class="fas fa-wallet"></i>
                </div>
                <div class="payment-info">
                  <div class="payment-title">Saldo Paresports</div>
                  <div class="payment-desc">Saldo tersedia: Rp <?= number_format($balance, 0, ',', '.') ?></div>
                  <?php if ($balance < $total_price): ?>
                    <div class="text-danger small mt-1">Saldo tidak cukup</div>
                  <?php endif; ?>
                </div>
                <input type="radio" name="payment_method" value="saldo" class="d-none" <?= $balance >= $total_price ? 'checked' : '' ?>>
              </div>
              
              <div class="payment-method" data-method="qris">
                <div class="payment-icon">
                  <i class="fas fa-qrcode"></i>
                </div>
                <div class="payment-info">
                  <div class="payment-title">QRIS</div>
                  <div class="payment-desc">Bayar dengan e-wallet</div>
                </div>
                <input type="radio" name="payment_method" value="qris" class="d-none">
              </div>
              
              <div class="payment-method" data-method="transfer">
                <div class="payment-icon">
                  <i class="fas fa-university"></i>
                </div>
                <div class="payment-info">
                  <div class="payment-title">Transfer Bank</div>
                  <div class="payment-desc">BCA, Mandiri, BRI</div>
                </div>
                <input type="radio" name="payment_method" value="transfer" class="d-none">
              </div>
              
              <div id="transferDetails" class="mt-3" style="display: none;">
                <div class="mb-3">
                  <label for="payment_proof" class="form-label">Upload Bukti Transfer</label>
                  <input type="file" class="form-control" id="payment_proof" name="payment_proof" accept="image/*">
                </div>
              </div>
              
              <div class="terms">
                <p>Dengan mengklik tombol berikut, Anda menyetujui <a href="#">Syarat dan Ketentuan</a> serta <a href="#">Kebijakan Privasi</a> yang berlaku.</p>
              </div>
              
              <button type="submit" class="btn-pay">
                Bayar Rp <?= number_format($total_price, 0, ',', '.') ?>
              </button>
            </form>
            
            <!-- Top Up Saldo -->
            <div class="topup-section">
              <h5 class="section-title">Top Up Saldo Paresports</h5>
              <form method="post">
                <div class="topup-options">
                  <div class="topup-option" data-amount="50000">Rp 50.000</div>
                  <div class="topup-option" data-amount="100000">Rp 100.000</div>
                  <div class="topup-option" data-amount="200000">Rp 200.000</div>
                  <div class="topup-option" data-amount="500000">Rp 500.000</div>
                </div>
                
                <div class="mb-3">
                  <label for="topup_amount" class="form-label">Nominal Top Up</label>
                  <input type="number" class="form-control" id="topup_amount" name="topup_amount" min="10000" step="10000" placeholder="Minimal Rp 10.000" required>
                </div>
                
                <button type="submit" class="btn btn-success w-100">
                  <i class="fas fa-plus-circle me-1"></i> Top Up Sekarang
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Fungsi untuk memilih metode pembayaran
      const paymentMethods = document.querySelectorAll('.payment-method');
      const transferDetails = document.getElementById('transferDetails');
      const topupOptions = document.querySelectorAll('.topup-option');
      const topupInput = document.getElementById('topup_amount');
      
      paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
          // Hapus kelas 'selected' dari semua metode
          paymentMethods.forEach(m => m.classList.remove('selected'));
          
          // Tambahkan kelas 'selected' ke metode yang diklik
          this.classList.add('selected');
          
          // Pilih radio button di dalamnya
          const radio = this.querySelector('input[type="radio"]');
          if (radio) {
            radio.checked = true;
          }
          
          // Tampilkan/sembunyikan detail transfer
          const methodType = this.getAttribute('data-method');
          if (methodType === 'transfer') {
            transferDetails.style.display = 'block';
          } else {
            transferDetails.style.display = 'none';
          }
        });
      });
      
      // Pilih opsi top up
      topupOptions.forEach(option => {
        option.addEventListener('click', function() {
          // Hapus selected dari semua opsi
          topupOptions.forEach(opt => opt.classList.remove('selected'));
          
          // Tandai yang dipilih
          this.classList.add('selected');
          
          // Set nilai input
          topupInput.value = this.getAttribute('data-amount');
        });
      });
      
      // Validasi form sebelum submit: jika transfer, wajib upload bukti
      document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const selectedMethod = document.querySelector('.payment-method.selected')?.getAttribute('data-method');
        if (selectedMethod === 'transfer') {
          const fileInput = document.getElementById('payment_proof');
          if (!fileInput.files.length) {
            e.preventDefault();
            alert('Silakan unggah bukti transfer.');
          }
        }
      });
    });
  </script>
</body>
</html>