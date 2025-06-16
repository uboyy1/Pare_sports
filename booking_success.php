<?php
session_start();
require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$booking_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Dapatkan data booking
$stmt = $conn->prepare("SELECT b.*, l.nama_venue FROM booking b JOIN lapangan l ON b.lapangan_id = l.id WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Booking tidak ditemukan.");
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Berhasil | Paresports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .success-container {
      max-width: 600px;
      margin: 50px auto;
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
      text-align: center;
    }
    
    .success-icon {
      font-size: 5rem;
      color: #28a745;
      margin-bottom: 20px;
    }
    
    .booking-details {
      text-align: left;
      background: #f8f9fa;
      padding: 20px;
      border-radius: 10px;
      margin-top: 20px;
    }
    
    .detail-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }
    
    .detail-item:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }
  </style>
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <div class="container">
    <div class="success-container">
      <div class="success-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <h2>Pembayaran Berhasil!</h2>
      <p>Booking lapangan Anda telah berhasil dikonfirmasi.</p>
      
      <div class="booking-details">
        <h5>Detail Booking</h5>
        <div class="detail-item">
          <span>Venue:</span>
          <strong><?= htmlspecialchars($booking['nama_venue']) ?></strong>
        </div>
        <div class="detail-item">
          <span>Tanggal:</span>
          <strong><?= date('d M Y', strtotime($booking['tanggal'])) ?></strong>
        </div>
        <div class="detail-item">
          <span>Waktu:</span>
          <strong><?= date('H:i', strtotime($booking['jam_mulai'])) ?> - <?= date('H:i', strtotime($booking['jam_selesai'])) ?></strong>
        </div>
        <div class="detail-item">
          <span>Total Pembayaran:</span>
          <strong>Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?></strong>
        </div>
        <div class="detail-item">
          <span>Metode Pembayaran:</span>
          <strong><?= 
            $booking['payment_method'] === 'saldo' ? 'Saldo Paresports' : 
            ($booking['payment_method'] === 'qris' ? 'QRIS' : 'Transfer Bank')
          ?></strong>
        </div>
      </div>
      
      <div class="mt-4">
        <a href="Riwayat-pemesanan.php" class="btn btn-outline-danger me-2">Lihat Riwayat Booking</a>
        <a href="index.php" class="btn btn-danger">Kembali ke Beranda</a>
      </div>
    </div>
  </div>

</body>
</html>