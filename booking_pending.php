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

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Pending | Paresports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <main class="container my-5 text-center">
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
      <div class="card-body py-5">
        <div class="mb-4">
          <i class="fas fa-clock fa-5x text-warning"></i>
        </div>
        <h2 class="card-title mb-3">Pembayaran Sedang Diproses</h2>
        <p class="card-text">Booking Anda akan dikonfirmasi setelah bukti pembayaran diverifikasi oleh admin.</p>
        
        <div class="booking-details mt-4 text-start mx-auto" style="max-width: 400px;">
          <h5 class="mb-3">Detail Booking</h5>
          <p><strong>Venue:</strong> <?= htmlspecialchars($booking['nama_venue']) ?></p>
          <p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($booking['tanggal'])) ?></p>
          <p><strong>Waktu:</strong> <?= date('H:i', strtotime($booking['jam_mulai'])) ?> - <?= date('H:i', strtotime($booking['jam_selesai'])) ?></p>
          <p><strong>Total Pembayaran:</strong> Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?></p>
          <p><strong>Metode Pembayaran:</strong> Transfer Bank</p>
        </div>
        
        <div class="mt-5">
          <a href="Riwayat-pemesanan.php" class="btn btn-outline-danger me-2">Lihat Riwayat Booking</a>
          <a href="index.php" class="btn btn-danger">Kembali ke Beranda</a>
        </div>
      </div>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>
</body>
</html>