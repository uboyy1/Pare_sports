<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$bookings = getBookingsByUserId($conn, $user_id);

// Set header variables
$is_logged_in = true;
$user_role = $_SESSION['role'];
$username = $_SESSION['username'];
$currentPage = 'booking-saya.php';
$is_profile_page = false;
$is_dashboard_page = false;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Booking Saya - Paresports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/halaman pemesanan.css">
    <style>
        /* Tambahan gaya khusus */
        .empty-state {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
        }
        .empty-state-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include 'includes/header.php'; ?>

    <main class="container my-5 flex-grow-1">
        <h1 class="mb-4">Riwayat Booking Saya</h1>

        <div class="filter-container mb-4 d-flex flex-wrap">
            <button class="btn filter-btn active" data-filter="all">Semua</button>
            <button class="btn filter-btn" data-filter="aktif">Aktif</button>
            <button class="btn filter-btn" data-filter="selesai">Selesai</button>
            <button class="btn filter-btn" data-filter="dibatalkan">Dibatalkan</button>
        </div>

        <div class="booking-list">
            <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="far fa-calendar-times"></i>
                    </div>
                    <h4 class="mb-3">Anda belum memiliki riwayat booking</h4>
                    <p class="text-muted mb-4">Mulai cari lapangan sekarang dan lakukan booking pertama Anda!</p>
                    <a href="index.php" class="btn btn-danger btn-lg">Cari Lapangan Sekarang</a>
                </div>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                <div class="booking-card mb-4" data-status="<?= strtolower($booking['status']) ?>">
                    <div class="row g-0">
                        <div class="col-md-3">
                            <img src="assets/img/<?= htmlspecialchars($booking['venue_gambar']) ?>" 
                                 class="img-fluid rounded-start h-100 w-100" 
                                 alt="<?= htmlspecialchars($booking['nama_venue']) ?>"
                                 style="object-fit: cover;">
                        </div>
                        <div class="col-md-9">
                            <div class="card-body h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="card-title mb-1"><?= htmlspecialchars($booking['nama_venue']) ?></h5>
                                        <p class="card-subtitle text-muted small"><?= htmlspecialchars($booking['nama_lapangan']) ?></p>
                                    </div>
                                    <span class="badge status-badge status-<?= strtolower($booking['status']) ?>">
                                        <?= htmlspecialchars($booking['status']) ?>
                                    </span>
                                </div>
                                
                                <hr class="my-2">
                                
                                <div class="booking-details mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-alt text-danger me-2"></i>
                                        <span><?= date('d M Y', strtotime($booking['booking_date'])) ?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock text-danger me-2"></i>
                                        <span><?= date('H:i', strtotime($booking['start_time'])) ?> - <?= date('H:i', strtotime($booking['end_time'])) ?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-money-bill-wave text-danger me-2"></i>
                                        <strong>Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></strong>
                                    </div>
                                </div>
                                
                                <div class="mt-auto d-flex justify-content-end">
                                    <a href="#" class="btn btn-sm btn-outline-danger me-2">Lihat Detail</a>
                                    <?php if ($booking['status'] === 'Aktif'): ?>
                                    <a href="#" class="btn btn-sm btn-danger">Batalkan</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const bookingCards = document.querySelectorAll('.booking-card');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    const filter = this.getAttribute('data-filter');
                    
                    bookingCards.forEach(card => {
                        const status = card.getAttribute('data-status');
                        
                        if (filter === 'all' || filter === status) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>