<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengelola') {
    header('Location: ../index.php');
    exit();
}

define('ADMIN_FEE_PERCENTAGE', 0.03); // Potongan admin 10%
$manager_id = $_SESSION['user_id'];

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

$report = getFinancialReportForManager($conn, $manager_id, $start_date, $end_date);
$page_title = "Laporan Keuangan";
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
                                <h5 class="card-title">Total Pemasukan (Kotor)</h5>
                                <p class="card-text fs-4 fw-bold">Rp <?= number_format($report['total_kotor'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                     <div class="col-md-4">
                        <div class="card card-stat bg-light-danger">
                            <div class="card-body">
                                <h5 class="card-title">Total Potongan Admin (3%)</h5>
                                <p class="card-text fs-4 fw-bold">Rp <?= number_format($report['total_potongan'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-stat bg-light-success">
                            <div class="card-body">
                                <h5 class="card-title">Total Pendapatan (Bersih)</h5>
                                <p class="card-text fs-4 fw-bold">Rp <?= number_format($report['total_bersih'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Rincian Transaksi (<?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?>)</h5>
                    </div>
                    <div class="card-body">
                         <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID Booking</th>
                                        <th>Tanggal</th>
                                        <th>Lapangan</th>
                                        <th>Total Harga</th>
                                        <th>Potongan Admin</th>
                                        <th>Pendapatan Bersih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                     <?php if (empty($report['transactions'])): ?>
                                        <tr><td colspan="6" class="text-center">Tidak ada transaksi pada periode ini.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($report['transactions'] as $trx): ?>
                                            <?php 
                                                $potongan = $trx['total_harga'] * ADMIN_FEE_PERCENTAGE;
                                                $bersih = $trx['total_harga'] - $potongan;
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($trx['id']) ?></td>
                                                <td><?= date('d M Y', strtotime($trx['tanggal'])) ?></td>
                                                <td><?= htmlspecialchars($trx['nama_venue']) ?></td>
                                                <td>Rp <?= number_format($trx['total_harga'], 0, ',', '.') ?></td>
                                                <td class="text-danger">- Rp <?= number_format($potongan, 0, ',', '.') ?></td>
                                                <td class="text-success fw-bold">Rp <?= number_format($bersih, 0, ',', '.') ?></td>
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