<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$page_title = "Data Akun Pengguna & Pengelola";

// Tangani aksi hapus
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'] ?? 0;
    if ($user_id > 0) {
        if (deleteUserAccount($conn, $user_id)) {
            $_SESSION['success_message'] = "Akun berhasil dihapus beserta semua data terkait.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus akun.";
        }
    } else {
        $_SESSION['error_message'] = "ID pengguna tidak valid.";
    }
    header('Location: data_akun.php');
    exit();
}

// Ambil semua pengguna
$all_users = getAllUsers($conn);
// Ambil semua pengelola
$all_managers = getAllManagers($conn);

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
    <style>
        .table-action-buttons form {
            display: inline-block;
            margin-right: 5px;
        }
        .table-action-buttons button {
            padding: 5px 10px;
            font-size: 0.85rem;
        }
        .badge-pending { background-color: #ffc107; color: #212529; }
        .badge-verified { background-color: #198754; }
        .badge-rejected { background-color: #dc3545; }
    </style>
</head>
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
                <h1 class="mb-4"><?= $page_title ?></h1>

                <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['success_message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    unset($_SESSION['success_message']);
                }
                if (isset($_SESSION['error_message'])) {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['error_message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    unset($_SESSION['error_message']);
                }
                ?>

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Data Pelanggan</h5>
                        <span class="badge bg-primary">Total: <?= count($all_users) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($all_users)): ?>
                                        <tr><td colspan="7" class="text-center py-4">Tidak ada data pengguna.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($all_users as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['id']) ?></td>
                                            <td><?= htmlspecialchars($user['nama']) ?></td>
                                            <td><?= htmlspecialchars($user['username']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $user['status'] ?>">
                                                    <?= htmlspecialchars(ucfirst($user['status'])) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                            <td class="table-action-buttons">
                                                <form method="POST" onsubmit="return confirm('Apakah Anda yakin menghapus akun ini? Semua data terkait akan dihapus permanen.');">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt me-1"></i> Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Data Pengelola Lapangan</h5>
                        <span class="badge bg-primary">Total: <?= count($all_managers) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($all_managers)): ?>
                                        <tr><td colspan="7" class="text-center py-4">Tidak ada data pengelola.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($all_managers as $manager): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($manager['id']) ?></td>
                                            <td><?= htmlspecialchars($manager['nama']) ?></td>
                                            <td><?= htmlspecialchars($manager['username']) ?></td>
                                            <td><?= htmlspecialchars($manager['email']) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $manager['status'] ?>">
                                                    <?= htmlspecialchars(ucfirst($manager['status'])) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M Y', strtotime($manager['created_at'])) ?></td>
                                            <td class="table-action-buttons">
                                                <form method="POST" onsubmit="return confirm('Apakah Anda yakin menghapus akun pengelola ini? Semua lapangan dan data terkait akan dihapus permanen.');">
                                                    <input type="hidden" name="user_id" value="<?= $manager['id'] ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt me-1"></i> Hapus</button>
                                                </form>
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
    <script>
        // Konfirmasi sebelum menghapus
        document.querySelectorAll('form[onsubmit]').forEach(form => {
            form.onsubmit = function() {
                return confirm(this.getAttribute('onsubmit').replace('return ', ''));
            };
        });
    </script>
</body>
</html>