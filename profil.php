<?php
session_start();
require_once 'config/database.php';

// Pastikan hanya user yang login yang bisa mengakses
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$is_profile_page = true;
// Ambil data pengguna dari database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    die("User tidak ditemukan");
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Set variabel untuk header
$is_logged_in = true;
$user_role = $_SESSION['role'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Profil Pengguna - Pare Sports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/profil.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="d-flex justify-content-center">
                    <img src="assets/img/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" 
                         alt="Foto Profil" 
                         class="profile-picture">
                </div>
                <h1 class="profile-name"><?= htmlspecialchars($user['nama']) ?></h1>
                <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
                <!-- TAMBAHKAN USERNAME DI BAWAH EMAIL -->
                <div class="profile-username">@<?= htmlspecialchars($user['username']) ?></div>
                <div class="profile-role">
                    <i class="fas fa-user me-1"></i> <?= ucfirst(htmlspecialchars($user['role'])) ?>
                </div>
            </div>

            <div class="tab-container">
                <button class="tab-button active" data-target="data-diri">
                    <i class="fas fa-user-circle me-2"></i> Data Diri
                </button>
                <button class="tab-button" data-target="ubah-password">
                    <i class="fas fa-lock me-2"></i> Ubah Password
                </button>
            </div>

            <!-- Tab Data Diri -->
            <div class="tab-pane active" id="data-diri">
                <div class="tab-content">
                    <?php if (isset($_SESSION['profile_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['profile_success'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['profile_success']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['profile_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['profile_error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['profile_error']); ?>
                    <?php endif; ?>
                    
                    <form action="proses/proses_edit_profil.php" method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-col">
                                <label class="form-label">Nama Lengkap *</label>
                                <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                            </div>
                            
                            <div class="form-col">
                                <!-- UBAH INPUT USERNAME MENJADI DAPAT DIEDIT -->
                                <label class="form-label">Username *</label>
                                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <label class="form-label">Nomor Ponsel</label>
                                <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                            </div>
                            
                            <div class="form-col">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Foto Profil</label>
                            <div class="file-input">
                                <div class="file-input-label">
                                    <span class="file-input-text" id="file-name">Pilih file...</span>
                                    <span class="file-input-button">Pilih File</span>
                                </div>
                                <input type="file" name="profile_picture" accept="image/*" id="profile-picture-input">
                            </div>
                            <small class="text-muted">Format: JPG, PNG (Maks. 2MB)</small>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="address" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-danger">Simpan Perubahan</button>
                    </form>
                </div>
            </div>

            <!-- Tab Ubah Password -->
            <div class="tab-pane" id="ubah-password">
                <div class="tab-content">
                    <?php if (isset($_SESSION['password_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['password_success'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['password_success']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['password_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['password_error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['password_error']); ?>
                    <?php endif; ?>
                    
                    <form action="proses/proses_ganti_password.php" method="POST" id="password-form">
                        <div class="mb-4">
                            <label class="form-label">Masukkan Kata Sandi Lama</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="current_password" required>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Kata Sandi Baru</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="new_password" id="new_password" required>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Ketik Ulang Kata Sandi Baru</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="confirm_password" id="confirm_password" required>
                                </button>
                            </div>
                            <div class="password-feedback" id="password-feedback" style="display: none;"></div>
                        </div>
                        
                        <button type="submit" class="btn btn-danger">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/profil.js"></script>
</body>
</html>