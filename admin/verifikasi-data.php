<?php
session_start();
require_once '../config/database.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$page_title = "Verifikasi Akun Pengelola";

// --- Handle Aksi Verifikasi/Tolak ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $user_id = $_POST['user_id'] ?? 0;
    $action = $_POST['action'] ?? ''; // 'verify' atau 'reject'

    if ($user_id > 0) {
        $new_status = ($action == 'verify') ? 'verified' : 'rejected';
        
        // Update status verifikasi langsung di database
        $sql = "UPDATE users SET status = :status WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':status', $new_status);
        $stmt->bindParam(':id', $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Status akun berhasil diperbarui.";
        } else {
            $_SESSION['error_message'] = "Gagal memperbarui status akun.";
        }
    } else {
        $_SESSION['error_message'] = "ID pengguna tidak valid.";
    }
    header('Location: verifikasi-data.php'); // Redirect untuk mencegah resubmission form
    exit();
}

// --- Ambil data pengelola yang perlu diverifikasi ---
$status = 'pending';
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'pengelola' AND status = :status");
$stmt->bindParam(':status', $status);
$stmt->execute();
$pending_managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

                <div class="card">
                    <div class="card-header">
                        <h5>Verifikasi Akun Pengelola Lapangan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pending_managers)): ?>
                                        <tr><td colspan="6" class="text-center">Tidak ada pengelola yang perlu diverifikasi.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($pending_managers as $manager): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($manager['id']) ?></td>
                                            <td><?= htmlspecialchars($manager['nama']) ?></td>
                                            <td><?= htmlspecialchars($manager['username']) ?></td>
                                            <td><?= htmlspecialchars($manager['email']) ?></td>
                                            <td><span class="badge bg-warning"><?= htmlspecialchars(ucfirst($manager['status'])) ?></span></td>
                                            <td class="table-action-buttons">
                                                <form method="POST">
                                                    <input type="hidden" name="user_id" value="<?= $manager['id'] ?>">
                                                    <button type="submit" name="action" value="verify" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Verifikasi</button>
                                                </form>
                                                <form method="POST">
                                                    <input type="hidden" name="user_id" value="<?= $manager['id'] ?>">
                                                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Tolak</button>
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
</body>
</html>