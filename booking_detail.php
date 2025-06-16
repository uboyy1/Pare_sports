<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: Riwayat-pemesanan.php');
    exit();
}

$booking_id = (int)$_GET['id'];

// Get booking data
$sql = "SELECT 
            b.id AS booking_id, 
            b.tanggal AS booking_date,
            b.jam_mulai AS start_time,
            b.jam_selesai AS end_time,
            b.durasi,
            b.total_harga AS total_price,
            b.status,
            b.payment_method,
            b.payment_proof,
            l.nama_venue, 
            l.gambar AS venue_gambar,
            l.alamat,
            l.maps_link,
            u.nama AS user_name,
            u.email,
            u.phone
        FROM booking b
        JOIN lapangan l ON b.lapangan_id = l.id
        JOIN users u ON b.user_id = u.id
        WHERE b.id = :booking_id AND b.user_id = :user_id";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $booking = null;
}

if (!$booking) {
    header('Location: Riwayat-pemesanan.php');
    exit();
}

$status_map = [
    'pending' => 'Menunggu Konfirmasi',
    'confirmed' => 'Aktif',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Booking - Paresports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .status-pending { background-color: #ffc107; color: #000; }
        .status-confirmed { background-color: #198754; color: #fff; }
        .status-completed { background-color: #0dcaf0; color: #000; }
        .status-cancelled { background-color: #dc3545; color: #fff; }
        .payment-badge {
            background-color: #e9ecef;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
        }
        .payment-saldo { background-color: #d4edda; color: #155724; }
        .payment-qris { background-color: #cce5ff; color: #004085; }
        .payment-transfer { background-color: #fff3cd; color: #856404; }
        .venue-img {
            border-radius: 12px;
            object-fit: cover;
            height: 100%;
            width: 100%;
        }
        .cancelled-banner {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Detail Booking</h1>
            <a href="Riwayat-pemesanan.php" class="btn btn-outline-danger">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <?php if ($booking['status'] === 'cancelled'): ?>
            <div class="cancelled-banner">
                <i class="fas fa-times-circle me-2"></i>
                Booking ini telah dibatalkan
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Booking</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>ID Booking</h6>
                                <p>#<?= $booking['booking_id'] ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Status</h6>
                                <span class="badge status-badge status-<?= $booking['status'] ?>">
                                    <?= $status_map[$booking['status']] ?>
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Tanggal</h6>
                                <p><?= date('d M Y', strtotime($booking['booking_date'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Waktu</h6>
                                <p><?= $booking['start_time'] ?> - <?= $booking['end_time'] ?> (<?= $booking['durasi'] ?> jam)</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Metode Pembayaran</h6>
                                <span class="payment-badge payment-<?= $booking['payment_method'] ?>">
                                    <?= 
                                        $booking['payment_method'] === 'saldo' ? 'Saldo Paresports' : 
                                        ($booking['payment_method'] === 'qris' ? 'QRIS' : 'Transfer Bank') 
                                    ?>
                                </span>
                            </div>
                            <div class="col-md-6">
                                <h6>Total Pembayaran</h6>
                                <p class="fw-bold">Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                        <?php if ($booking['payment_method'] == 'transfer' && $booking['payment_proof']): ?>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <h6>Bukti Transfer</h6>
                                    <img src="assets/img/payment_proofs/<?= htmlspecialchars($booking['payment_proof']) ?>" 
                                         alt="Bukti Transfer" class="img-fluid rounded" style="max-height: 300px;">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Data Penyewa</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6>Nama Lengkap</h6>
                                <p><?= htmlspecialchars($booking['user_name']) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6>Email</h6>
                                <p><?= htmlspecialchars($booking['email']) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6>Nomor Telepon</h6>
                                <p><?= htmlspecialchars($booking['phone']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Lapangan</h5>
                    </div>
                    <div class="card-body">
                        <img src="assets/img/<?= htmlspecialchars($booking['venue_gambar']) ?>" 
                             alt="<?= htmlspecialchars($booking['nama_venue']) ?>" 
                             class="venue-img mb-3">
                        <h5 class="card-title"><?= htmlspecialchars($booking['nama_venue']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($booking['alamat']) ?></p>
                        <?php if (!empty($booking['maps_link'])): ?>
                            <a href="<?= htmlspecialchars($booking['maps_link']) ?>" 
                               target="_blank" 
                               class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i> Lihat di Peta
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>