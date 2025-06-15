<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengelola') {
    header('Location: ../index.php');
    exit();
}

$is_edit = false;
$lapangan_data = [
    'id' => '', 'nama_venue' => '', 'jenis_olahraga' => '', 'deskripsi' => '', 
    'alamat' => '', 'fasilitas' => '', 'aturan' => '', 'harga' => '', 'gambar' => ''
];

if (isset($_GET['id'])) {
    $is_edit = true;
    $lapangan_id = $_GET['id'];
    $lapangan_data = getManagedFieldById($conn, $lapangan_id, $_SESSION['user_id']);
    if (!$lapangan_data) {
        $_SESSION['error_message'] = "Akses tidak diizinkan.";
        header('Location: kelola-lapangan.php');
        exit();
    }
}

$page_title = $is_edit ? "Edit Lapangan" : "Tambah Lapangan Baru";
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
                <a href="kelola-lapangan.php" class="btn btn-outline-dark btn-sm mb-3"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
                <h1 class="mb-4"><?= $page_title ?></h1>

                <div class="card">
                    <div class="card-body">
                        <form action="../proses/proses-lapangan.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?= $is_edit ? 'edit' : 'add' ?>">
                            <input type="hidden" name="id" value="<?= $lapangan_data['id'] ?>">

                            <div class="mb-3">
                                <label for="nama_venue" class="form-label">Nama Venue/Lapangan *</label>
                                <input type="text" class="form-control" id="nama_venue" name="nama_venue" value="<?= htmlspecialchars($lapangan_data['nama_venue']) ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="jenis_olahraga" class="form-label">Jenis Olahraga *</label>
                                    <select class="form-select" id="jenis_olahraga" name="jenis_olahraga" required>
                                        <option value="">Pilih Olahraga</option>
                                        <option value="futsal" <?= $lapangan_data['jenis_olahraga'] == 'futsal' ? 'selected' : '' ?>>Futsal</option>
                                        <option value="badminton" <?= $lapangan_data['jenis_olahraga'] == 'badminton' ? 'selected' : '' ?>>Badminton</option>
                                        <option value="basket" <?= $lapangan_data['jenis_olahraga'] == 'basket' ? 'selected' : '' ?>>Basket</option>
                                        <option value="tenis" <?= $lapangan_data['jenis_olahraga'] == 'tenis' ? 'selected' : '' ?>>Tenis</option>
                                        <option value="minisoccer" <?= $lapangan_data['jenis_olahraga'] == 'minisoccer' ? 'selected' : '' ?>>Mini Soccer</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="harga" class="form-label">Harga per Jam *</label>
                                    <input type="number" class="form-control" id="harga" name="harga" value="<?= htmlspecialchars($lapangan_data['harga']) ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= htmlspecialchars($lapangan_data['deskripsi']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <input type="text" class="form-control" id="alamat" name="alamat" value="<?= htmlspecialchars($lapangan_data['alamat']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="fasilitas" class="form-label">Fasilitas (pisahkan dengan koma)</label>
                                <input type="text" class="form-control" id="fasilitas" name="fasilitas" value="<?= htmlspecialchars($lapangan_data['fasilitas']) ?>">
                            </div>
                             <div class="mb-3">
                                <label for="aturan" class="form-label">Aturan</label>
                                <textarea class="form-control" id="aturan" name="aturan" rows="3"><?= htmlspecialchars($lapangan_data['aturan']) ?></textarea>
                            </div>
                             <div class="mb-3">
                                <label for="gambar" class="form-label">Gambar Lapangan <?= $is_edit ? '(kosongkan jika tidak ingin ganti)' : '' ?></label>
                                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" <?= !$is_edit ? 'required' : '' ?>>
                                <?php if ($is_edit && $lapangan_data['gambar']): ?>
                                    <small class="d-block mt-2">Gambar saat ini: <img src="../assets/img/<?= htmlspecialchars($lapangan_data['gambar']) ?>" width="150"></small>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-danger">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>