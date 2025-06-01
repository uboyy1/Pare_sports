<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'config/functions.php';

// Check if venue ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$venue_id = (int)$_GET['id'];

// Get venue details from database
$venue = getVenueById($conn, $venue_id);
if (!$venue) {
    header("Location: index.php");
    exit();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : 'Nama User';
$isAdmin = $isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$isManager = $isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] === 'pengelola';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($venue['nama_venue']) ?> | Paresports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/booking.css">
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light shadow-sm p-3">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center fw-bold" href="index.php">
        <img src="Logo pare sports.png" alt="Logo" height="40" class="me-2">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <div class="ms-auto d-flex align-items-center">
          <?php if ($isLoggedIn): ?>
            <!-- Dropdown User (muncul setelah login) -->
            <div class="user-dropdown dropdown me-3">
              <a class="btn btn-outline-secondary dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle me-1"></i>
                <span id="navbarUsername"><?php echo htmlspecialchars($username); ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                <li><a class="dropdown-item" href="booking-saya.php"><i class="fas fa-calendar-alt me-2"></i>Booking Saya</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php" id="logoutButton"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
              </ul>
            </div>
            
            <!-- Tombol Admin/Pengelola -->
            <?php if ($isAdmin): ?>
              <a href="dashboard-admin.php" class="btn btn-danger me-2" id="adminButton">
                <i class="fas fa-user-shield me-1"></i>Admin
              </a>
            <?php endif; ?>
            
            <?php if ($isManager): ?>
              <a href="dashboard-pengelola.php" class="btn btn-warning me-2" id="managerButton">
                <i class="fas fa-user-tie me-1"></i>Pengelola
              </a>
            <?php endif; ?>
            
          <?php else: ?>
            <!-- Show login button if not logged in -->
            <a href="login.php" class="btn btn-outline-primary me-2">Login</a>
            <a href="register.php" class="btn btn-primary">Daftar</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <div class="header-nav">
    <nav>
      <a href="index.php">Home</a>
      <a href="#"><?= htmlspecialchars($venue['nama_venue']) ?></a>
    </nav>
  </div>

  <main class="container">

    <section class="venue-header">
      <!-- Gambar penuh di atas -->
      <div class="venue-image-container">
        <img src="assets/img/<?= htmlspecialchars($venue['gambar']) ?>" alt="<?= htmlspecialchars($venue['nama_venue']) ?>" class="venue-img">
      </div>

      <!-- Baris bawah: Deskripsi di kiri, Harga+cek ketersediaan di kanan -->
      <div class="venue-bottom d-flex">
        <div class="venue-info">
          <h1><?= htmlspecialchars($venue['nama_venue']) ?></h1>
          <div class="rating">
            <?php
            $rating = $venue['rating'];
            $fullStars = floor($rating);
            $halfStar = ($rating - $fullStars) >= 0.5;
            $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
            ?>
            
            <!-- Bintang penuh -->
            <?php for ($i = 0; $i < $fullStars; $i++): ?>
              <i class="fas fa-star text-warning"></i>
            <?php endfor; ?>
            
            <!-- Bintang setengah -->
            <?php if ($halfStar): ?>
              <i class="fas fa-star-half-alt text-warning"></i>
            <?php endif; ?>
            
            <!-- Bintang kosong -->
            <?php for ($i = 0; $i < $emptyStars; $i++): ?>
              <i class="far fa-star text-warning"></i>
            <?php endfor; ?>
            
            <span class="rating-value"><?= number_format($rating, 1) ?></span>
          </div>

          <div class="venue-desc">
            <h3>Deskripsi</h3>
            <p><?= htmlspecialchars($venue['deskripsi']) ?></p>

            <h3>Aturan Venue</h3>
            <ul class="rules">
              <?php 
              $rules = explode("\n", $venue['aturan']);
              foreach ($rules as $rule): 
                if (!empty(trim($rule))):
              ?>
                <li><?= htmlspecialchars(trim($rule)) ?></li>
              <?php 
                endif;
              endforeach; 
              ?>
            </ul>

            <h3>Lokasi Venue</h3>
            <a class="location" href="<?= htmlspecialchars($venue['maps_link']) ?>" target="_blank" rel="noopener noreferrer">
              <?= htmlspecialchars($venue['alamat']) ?>
            </a>     
          </div>
        </div>

        <!-- Harga dan tombol di sebelah kanan -->
        <div class="venue-price">
          <p>Harga mulai dari</p>
          <h2>Rp <?= number_format($venue['harga'], 0, ',', '.') ?></h2>
          <a href="#ketersediaan" class="check-availability">Cek Ketersediaan</a>
        </div>
      </div>
    </section>

    <!-- Kalender dan Filter -->
    <div class="pilih-lapangan">
      <h2>Pilih lapangan</h2>
    </div>
    <section id="ketersediaan" class="calendar-filter">
      <div class="calendar-row">
        <div class="dates">
          <?php
          // Generate dates for the next 7 days
          for ($i = 0; $i < 7; $i++):
            $date = strtotime("+$i days");
            $day = date('D', $date);
            $dateNum = date('j M', $date);
            
            // Translate day names to Indonesian
            $dayTranslations = [
              'Sun' => 'Min',
              'Mon' => 'Sen',
              'Tue' => 'Sel',
              'Wed' => 'Rab',
              'Thu' => 'Kam',
              'Fri' => 'Jum',
              'Sat' => 'Sab'
            ];
            $dayId = $dayTranslations[$day] ?? $day;
          ?>
          <button class="date-btn" data-date="<?= date('Y-m-d', $date) ?>">
            <span class="day"><?= $dayId ?></span>
            <span class="date"><?= $dateNum ?></span>
          </button>
          <?php endfor; ?>
        </div>

        <div class="filters">
          <select class="time-filter" id="timeFilter">
            <option value="all">Semua Waktu</option>
            <option value="morning">Pagi (06:00-12:00)</option>
            <option value="afternoon">Siang (12:00-17:00)</option>
            <option value="evening">Malam (17:00-22:00)</option>
          </select>

          <select class="sport-filter" id="sportFilter">
            <option value="all">Semua Cabang</option>
            <option value="futsal">Futsal</option>
            <option value="badminton">Badminton</option>
            <option value="basket">Basket</option>
            <option value="tenis">Tenis</option>
            <option value="minisoccer">Mini Soccer</option>
          </select>
        </div>
      </div>
    </section>

    <!-- Daftar Lapangan -->
    <section class="fields">
      <?php
      // Get fields for this venue
      $fields = getFieldsByVenueId($conn, $venue_id);
      
      if (empty($fields)): ?>
        <div class="alert alert-info">
          Tidak ada lapangan tersedia di venue ini.
        </div>
      <?php else: ?>
        <?php foreach ($fields as $field): ?>
        <div class="field-card" data-field="<?= $field['id'] ?>" data-price="<?= $field['harga'] ?>" data-sport="<?= $field['jenis_lapangan'] ?>">
          <img src="assets/img/<?= htmlspecialchars($field['gambar']) ?>" alt="<?= htmlspecialchars($field['nama_lapangan']) ?>" class="field-image">
          <div class="field-info">
            <h3><?= htmlspecialchars($field['nama_lapangan']) ?></h3>
            <p class="availability">Tersedia</p>
            <div class="price">Rp <?= number_format($field['harga'], 0, ',', '.') ?>/jam</div>
            <button class="schedule-btn" data-bs-toggle="modal" data-bs-target="#bookingModal" 
                    data-field-id="<?= $field['id'] ?>" 
                    data-field-name="<?= htmlspecialchars($field['nama_lapangan']) ?>"
                    data-field-price="<?= $field['harga'] ?>">
              Lihat Jadwal
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </main>

  <!-- Modal Booking -->
  <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="bookingModalLabel">Booking Lapangan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <h4 id="fieldName">Lapangan Futsal 1</h4>
              <p id="fieldDate">Selasa, 20 Mei 2024</p>
              
              <div class="time-slots-container">
                <h5>Pilih Waktu</h5>
                <div class="time-slots">
                  <!-- Time slots will be populated by JavaScript -->
                  <div class="text-center py-3">
                    <div class="spinner-border text-danger" role="status">
                      <span class="visually-hidden">Memuat jadwal...</span>
                    </div>
                    <p>Memuat jadwal ketersediaan</p>
                  </div>
                </div>
              </div>
              
              <div class="duration-selector mt-3">
                <h5>Durasi</h5>
                <select class="form-select" id="durationSelect">
                  <option value="1">1 Jam</option>
                  <option value="2">2 Jam</option>
                  <option value="3">3 Jam</option>
                </select>
              </div>
            </div>
            
            <div class="col-md-4">
              <div class="booking-summary">
                <h5>Ringkasan Booking</h5>
                <p><strong>Lapangan:</strong> <span id="summaryField">-</span></p>
                <p><strong>Tanggal:</strong> <span id="summaryDate">-</span></p>
                <p><strong>Waktu:</strong> <span id="summaryTime">-</span></p>
                <p><strong>Durasi:</strong> <span id="summaryDuration">-</span></p>
                <hr>
                <p><strong>Total Pembayaran:</strong> <span id="summaryTotal" class="fw-bold">Rp 0</span></p>
                
                <div class="payment-methods mt-3">
                  <h5>Metode Pembayaran</h5>
                  <div class="payment-method" data-method="qris">
                    <span>QRIS</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-danger" id="proceedPaymentBtn" disabled>Lanjutkan Pembayaran</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Pembayaran -->
  <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="paymentModalLabel">Pembayaran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="qrisPayment">
            <p>Silakan scan QR code berikut untuk melakukan pembayaran:</p>
            <div class="text-center my-4">
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://paresports.com/payment/123456" alt="QR Code Pembayaran" class="img-fluid">
            </div>
            <p class="text-center">Total: <strong id="qrisAmount">Rp 100,000</strong></p>
          </div>
          <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle me-2"></i>Booking akan otomatis dikonfirmasi setelah pembayaran berhasil.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="button" class="btn btn-success" id="confirmPaymentBtn">Saya Sudah Bayar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Booking -->
  <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="successModalLabel">Booking Berhasil</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <div class="mb-4">
            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
          </div>
          <h4>Terima kasih!</h4>
          <p>Booking lapangan Anda telah berhasil.</p>
          <p>Detail booking telah dikirim ke email Anda.</p>
          <div class="booking-details mt-4 p-3 bg-light rounded">
            <p><strong>Kode Booking:</strong> <span id="bookingCode">PRS-20240520-001</span></p>
            <p><strong>Lapangan:</strong> <span id="successField">Lapangan Futsal 1</span></p>
            <p><strong>Tanggal:</strong> <span id="successDate">20 Mei 2024</span></p>
            <p><strong>Waktu:</strong> <span id="successTime">16:00 - 18:00</span></p>
          </div>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Selesai</button>
          <a href="booking-saya.php" class="btn btn-outline-secondary">Lihat Booking Saya</a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/booking.js"></script>
  
  <script>
    // Add PHP-based JavaScript variables
    const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
    const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
    const isManager = <?php echo $isManager ? 'true' : 'false'; ?>;
    const venueId = <?php echo $venue_id; ?>;
    const baseUrl = window.location.origin;
    
    // Check if user is logged in when trying to book
    document.querySelectorAll('.schedule-btn').forEach(button => {
      button.addEventListener('click', function() {
        if (!isLoggedIn) {
          alert('Anda harus login terlebih dahulu untuk melakukan booking');
          window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
          return false;
        }
        
        // Set field data for modal
        const fieldId = this.getAttribute('data-field-id');
        const fieldName = this.getAttribute('data-field-name');
        const fieldPrice = this.getAttribute('data-field-price');
        
        document.getElementById('fieldName').textContent = fieldName;
        document.getElementById('summaryField').textContent = fieldName;
        
        // You can add more JavaScript logic here to handle the booking process
      });
    });
    
    // Handle date selection
    document.querySelectorAll('.date-btn').forEach(button => {
      button.addEventListener('click', function() {
        const selectedDate = this.getAttribute('data-date');
        const dateObj = new Date(selectedDate);
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const formattedDate = dateObj.toLocaleDateString('id-ID', options);
        
        document.getElementById('fieldDate').textContent = formattedDate;
        document.getElementById('summaryDate').textContent = formattedDate;
        document.getElementById('successDate').textContent = formattedDate;
        
        // Here you would typically fetch available time slots for the selected date
        // from your backend using AJAX
      });
    });
    
    // Handle duration change
    document.getElementById('durationSelect').addEventListener('change', function() {
      const duration = this.value;
      document.getElementById('summaryDuration').textContent = duration + ' Jam';
      
      // Calculate total price
      const pricePerHour = document.querySelector('.schedule-btn.active')?.getAttribute('data-field-price') || 0;
      const totalPrice = pricePerHour * duration;
      document.getElementById('summaryTotal').textContent = 'Rp ' + totalPrice.toLocaleString('id-ID');
      document.getElementById('qrisAmount').textContent = 'Rp ' + totalPrice.toLocaleString('id-ID');
    });
  </script>
</body>
</html>