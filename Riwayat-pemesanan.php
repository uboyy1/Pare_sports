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

$status_map = [
    'pending' => 'Menunggu',
    'confirmed' => 'Aktif',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Riwayat Booking - Paresports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #dc3545;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-container {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .filter-btn {
            background-color: white;
            color: var(--secondary);
            border: 1px solid #dee2e6;
            margin-right: 8px;
            margin-bottom: 8px;
            border-radius: 20px;
            padding: 6px 15px;
            transition: all 0.3s;
        }
        
        .filter-btn.active,
        .filter-btn:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .booking-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
            background-color: white;
            overflow: hidden;
        }
        
        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .venue-img {
            height: 100%;
            width: 100%;
            object-fit: cover;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
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
            font-size: 0.8rem;
        }
        
        .payment-saldo { background-color: #d4edda; color: #155724; }
        .payment-qris { background-color: #cce5ff; color: #004085; }
        .payment-transfer { background-color: #fff3cd; color: #856404; }
        
        .empty-state {
            background-color: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .empty-state-icon {
            font-size: 5rem;
            color: var(--primary);
            margin-bottom: 20px;
            opacity: 0.8;
        }
        
        .btn-outline-danger {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .btn-outline-danger:hover {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
        }
        
        /* Style for cancelled booking */
        .booking-card.cancelled {
            opacity: 0.7;
            border-left: 4px solid #dc3545;
            position: relative;
        }
        
        .booking-card.cancelled::after {
            content: "DIBATALKAN";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
            opacity: 0.3;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Riwayat Booking</h1>
            <a href="index.php" class="btn btn-outline-danger">
                <i class="fas fa-plus me-1"></i> Booking Baru
            </a>
        </div>

        <div class="filter-container">
            <h5 class="mb-3">Filter Status</h5>
            <div class="d-flex flex-wrap">
                <button class="btn filter-btn active" data-filter="all">Semua</button>
                <button class="btn filter-btn" data-filter="pending">Menunggu</button>
                <button class="btn filter-btn" data-filter="confirmed">Aktif</button>
                <button class="btn filter-btn" data-filter="completed">Selesai</button>
            </div>
        </div>

        <div class="booking-list">
            <?php if (empty($bookings)) : ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="far fa-calendar-times"></i>
                    </div>
                    <h4 class="mb-3">Anda belum memiliki riwayat booking</h4>
                    <p class="text-muted mb-4">Mulai cari lapangan sekarang dan lakukan booking pertama Anda!</p>
                    <a href="index.php" class="btn btn-danger btn-lg">
                        <i class="fas fa-search me-2"></i> Cari Lapangan
                    </a>
                </div>
            <?php else : ?>
                <?php foreach ($bookings as $booking) : ?>
                    <?php if ($booking['status'] === 'cancelled') continue; ?>
                    <div class="booking-card" data-status="<?= $booking['status'] ?>">
                        <div class="row g-0">
                            <div class="col-md-3 position-relative">
                                <img src="assets/img/<?= htmlspecialchars($booking['venue_gambar']) ?>" 
                                     class="venue-img h-100" 
                                     alt="<?= htmlspecialchars($booking['nama_venue']) ?>"
                                     style="object-fit: cover; border-radius: 10px 0 0 10px;">
                                <div class="position-absolute top-0 start-0 p-2">
                                    <span class="badge bg-dark">#<?= $booking['booking_id'] ?></span>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="p-4 h-100 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="mb-1"><?= htmlspecialchars($booking['nama_venue']) ?></h5>
                                            <div class="d-flex align-items-center mt-2">
                                                <span class="badge status-badge status-<?= $booking['status'] ?> me-2">
                                                    <?= $status_map[$booking['status']] ?>
                                                </span>
                                                <span class="payment-badge payment-<?= 
                                                    $booking['payment_method'] === 'saldo' ? 'saldo' : 
                                                    ($booking['payment_method'] === 'qris' ? 'qris' : 'transfer') ?>">
                                                    <?= 
                                                        $booking['payment_method'] === 'saldo' ? 'Saldo' : 
                                                        ($booking['payment_method'] === 'qris' ? 'QRIS' : 'Transfer Bank') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <strong class="text-danger">Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></strong>
                                        </div>
                                    </div>

                                    <hr class="my-3">

                                    <div class="booking-details mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-calendar-alt text-danger me-2"></i>
                                            <span><?= date('d M Y', strtotime($booking['booking_date'])) ?></span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-clock text-danger me-2"></i>
                                            <span><?= date('H:i', strtotime($booking['start_time'])) ?> - <?= date('H:i', strtotime($booking['end_time'])) ?></span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-hourglass-half text-danger me-2"></i>
                                            <span><?= $booking['durasi'] ?> Jam</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-auto d-flex justify-content-end">
                                        <a href="booking_detail.php?id=<?= $booking['booking_id'] ?>" class="btn btn-sm btn-outline-danger me-2">
                                            <i class="fas fa-info-circle me-1"></i> Detail
                                        </a>
                                        <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                            <button class="btn btn-sm btn-danger btn-cancel-booking" 
                                                    data-booking-id="<?= $booking['booking_id'] ?>">
                                                <i class="fas fa-times me-1"></i> Batalkan
                                            </button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const bookingCards = document.querySelectorAll('.booking-card');

            filterButtons.forEach(button => {
                button.addEventListener('click', function () {
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

            document.querySelectorAll('.btn-cancel-booking').forEach(button => {
                button.addEventListener('click', function () {
                    const bookingId = this.dataset.bookingId;

                    Swal.fire({
                        title: 'Batalkan Booking?',
                        text: "Anda yakin ingin membatalkan booking ini?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Batalkan',
                        cancelButtonText: 'Kembali'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('proses/update_booking_status.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    booking_id: bookingId,
                                    status: 'cancelled'
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil Dibatalkan!',
                                        text: 'Booking berhasil dibatalkan.',
                                        timer: 2000,
                                        timerProgressBar: true,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Gagal!',
                                        data.message || 'Gagal membatalkan booking',
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>