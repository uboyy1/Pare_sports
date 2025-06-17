<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../dashboard_admin.php');
    exit();
}

$page_title = "PARESPORT Admin";

// Panggil fungsi-fungsi untuk mengambil data statistik admin
$total_users = countTotalUsers($conn);
$total_managers = countTotalManagers($conn);
$total_bookings_system_wide = countTotalBookings($conn); 

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Paresports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style-manager.css"> </head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1"><?= $page_title ?></span>
                </div>
            </nav>

            <main class="container-fluid p-4">
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card card-stat">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-users fa-3x text-info"></i>
                                    <div class="ms-3">
                                        <h5 class="card-title">Total Pengguna</h5>
                                        <p class="card-text fs-4 fw-bold"><?= $total_users ?></p> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-stat">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-tie fa-3x text-primary"></i>
                                    <div class="ms-3">
                                        <h5 class="card-title">Total Pengelola</h5>
                                        <p class="card-text fs-4 fw-bold"><?= $total_managers ?></p> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                         <div class="card card-stat">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-check fa-3x text-success"></i>
                                    <div class="ms-3">
                                        <h5 class="card-title">Total Booking</h5>
                                        <p class="card-text fs-4 fw-bold"><?= $total_bookings_system_wide ?></p> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5>Verifikasi Akun & Data</h5>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <p>Kelola verifikasi akun pengguna dan pengelola di sini.</p>
                                <a href="verifikasi-data.php" class="btn btn-outline-primary mt-auto">Menuju Halaman Verifikasi</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5>Manajemen Akun</h5>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <p>Kelola semua akun pengguna dan pengelola di sini.</p>
                                <a href="data_akun.php" class="btn btn-outline-success mt-auto">Menuju Data Akun</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Laporan Keuangan Global</h5>
                    </div>
                    <div class="card-body">
                        <p>Lihat laporan keuangan komprehensif untuk seluruh sistem.</p>
                        <a href="laporan-keuangan.php" class="btn btn-outline-success">Menuju Laporan Keuangan</a>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>