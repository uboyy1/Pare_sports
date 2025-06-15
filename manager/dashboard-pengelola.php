<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Pastikan hanya manajer yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengelola') {
    header('Location: ../index.php');
    exit();
}

$manager_id = $_SESSION['user_id'];
$page_title = "Dashboard Manager";

// Data untuk Statistik
$total_lapangan = countManagedFields($conn, $manager_id);
$laporan_bulan_ini = getFinancialReportForManager($conn, $manager_id, date('Y-m-01'), date('Y-m-t'));
$total_pendapatan = $laporan_bulan_ini['total_bersih'];
$bookings_terbaru = getRecentBookingsForManager($conn, $manager_id, 5);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Paresports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-manager.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1"><?= $page_title ?></span>
                    <div class="collapse navbar-collapse">
                        <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user me-2"></i><?= htmlspecialchars($_SESSION['username']) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="../profil.php">Profil</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <main class="container-fluid p-4">
                <h1 class="mb-4"><?= $page_title ?></h1>
                
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card card-stat">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-futbol fa-3x text-danger"></i>
                                    <div class="ms-3">
                                        <h5 class="card-title">Total Lapangan</h5>
                                        <p class="card-text fs-4 fw-bold"><?= $total_lapangan ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-stat">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-wallet fa-3x text-success"></i>
                                    <div class="ms-3">
                                        <h5 class="card-title">Pendapatan (Bulan Ini)</h5>
                                        <p class="card-text fs-4 fw-bold">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                         <div class="card card-stat">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-check fa-3x text-warning"></i>
                                    <div class="ms-3">
                                        <h5 class="card-title">Booking (Bulan Ini)</h5>
                                        <p class="card-text fs-4 fw-bold"><?= count($laporan_bulan_ini['transactions']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Booking Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Pemesan</th>
                                        <th>Lapangan</th>
                                        <th>Tanggal</th>
                                        <th>Jam</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($bookings_terbaru)): ?>
                                        <tr><td colspan="5" class="text-center">Belum ada booking.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($bookings_terbaru as $booking): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($booking['nama_user']) ?></td>
                                                <td><?= htmlspecialchars($booking['nama_venue']) ?></td>
                                                <td><?= date('d M Y', strtotime($booking['tanggal'])) ?></td>
                                                <td><?= date('H:i', strtotime($booking['jam_mulai'])) ?> - <?= date('H:i', strtotime($booking['jam_selesai'])) ?></td>
                                                <td><span class="badge status-<?= strtolower($booking['status']) ?>"><?= htmlspecialchars($booking['status']) ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../includes/footer.php'; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>