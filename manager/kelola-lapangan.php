<?php
session_start();
include '../config/database.php';
include '../config/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengelola') {
    header("Location: ../index.php");
    exit();
}

$manager_id = $_SESSION['user_id'];
$lapangan_list = getManagedFields($conn, $manager_id);

$page_title = "Kelola Lapangan";
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><?= $page_title ?></h1>
                    <a href="form-lapangan.php" class="btn btn-danger"><i class="fas fa-plus me-2"></i>Tambah Lapangan</a>
                </div>

                <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                <?php endif; ?>
                 <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Gambar</th>
                                        <th>Nama Venue</th>
                                        <th>Jenis Olahraga</th>
                                        <th>Harga/Jam</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($lapangan_list)): ?>
                                        <tr><td colspan="5" class="text-center">Anda belum menambahkan lapangan.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($lapangan_list as $lapangan): ?>
                                            <tr>
                                                <td><img src="../assets/img/<?= htmlspecialchars($lapangan['gambar']) ?>" alt="Gambar" width="100"></td>
                                                <td><?= htmlspecialchars($lapangan['nama_venue']) ?></td>
                                                <td><?= ucfirst(htmlspecialchars($lapangan['jenis_olahraga'])) ?></td>
                                                <td>Rp <?= number_format($lapangan['harga'], 0, ',', '.') ?></td>
                                                <td>
                                                    <a href="form-lapangan.php?id=<?= $lapangan['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                                    <a href="../proses/proses-lapangan.php?action=delete&id=<?= $lapangan['id'] ?>" class="btn btn-sm btn-dark" onclick="return confirm('Apakah Anda yakin ingin menghapus lapangan ini?')"><i class="fas fa-trash"></i></a>
                                                </td>
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