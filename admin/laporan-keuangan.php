<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

define('PLATFORM_FEE_PERCENTAGE', 0.03); // Asumsi potongan platform 3%

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// TODO: Implement getSystemFinancialReport($conn, $start_date, $end_date) in functions.php
// This function should fetch all confirmed/completed bookings across all venues.
$report = [
    'transactions' => [],
    'total_gross_revenue' => 0,
    'total_platform_fee' => 0,
    'total_net_revenue' => 0
];

// Example of how getSystemFinancialReport might work (needs to be in functions.php)
// $sql = "SELECT b.id, b.tanggal, b.total_harga, l.nama_venue, u.nama as nama_user
//         FROM booking b
//         JOIN lapangan l ON b.lapangan_id = l.id
//         JOIN users u ON b.user_id = u.id
//         WHERE b.status IN ('confirmed', 'selesai')
//         AND b.tanggal BETWEEN :start_date AND :end_date";
// ... (rest of the logic for calculating totals)


$page_title = "Laporan Keuangan Sistem";
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

                <div class="card mb-4">
                    <div class="card-body">
                         <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label for="start_date" class="form-label">Dari Tanggal</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="<?= $start_date ?>">
                            </div>
                            <div class="col-md-5">
                                <label for="end_date" class="form-label">Sampai Tanggal</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="<?= $end_date ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-danger w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                 <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card card-stat">
                            <div class="card-body">
                                <h5 class="card-title">Total Pemasukan Kotor Sistem</h5>
                                <p class="card-text fs-4 fw-bold">Rp <?= number_format($report['total_gross_revenue'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                     <div class="col-md-4">
                        <div class="card card-stat bg-light-info">
                            <div class="card-body">
                                <h5 class="card-title">Total Pendapatan Platform (<?= PLATFORM_FEE_PERCENTAGE * 100 ?>%)</h5>
                                <p class="card-text fs-4 fw-bold">Rp <?= number_format($report['total_platform_fee'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-stat bg-light-success">
                            <div class="card-body">
                                <h5 class="card-title">Total Pendapatan Bersih ke Pengelola</h5>
                                <p class="card-text fs-4 fw-bold">Rp <?= number_format($report['total_net_revenue'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Rincian Semua Transaksi (<?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?>)</h5>
                    </div>
                    <div class="card-body">
                         <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID Booking</th>
                                        <th>Tanggal</th>
                                        <th>Lapangan</th>
                                        <th>Pengelola</th>
                                        <th>Total Harga</th>
                                        <th>Pendapatan Platform</th>
                                        <th>Pendapatan Pengelola</th>
                                    </tr>
                                </thead>
                                <tbody>
                                     <?php if (empty($report['transactions'])): ?>
                                        <tr><td colspan="7" class="text-center">Tidak ada transaksi pada periode ini.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($report['transactions'] as $trx): ?>
                                            <?php
                                                $platform_fee = $trx['total_harga'] * PLATFORM_FEE_PERCENTAGE;
                                                $manager_revenue = $trx['total_harga'] - $platform_fee;
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($trx['id']) ?></td>
                                                <td><?= date('d M Y', strtotime($trx['tanggal'])) ?></td>
                                                <td><?= htmlspecialchars($trx['nama_venue']) ?></td>
                                                <td><?= htmlspecialchars($trx['nama_pengelola']) ?></td> <td>Rp <?= number_format($trx['total_harga'], 0, ',', '.') ?></td>
                                                <td class="text-info">Rp <?= number_format($platform_fee, 0, ',', '.') ?></td>
                                                <td class="text-success fw-bold">Rp <?= number_format($manager_revenue, 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>