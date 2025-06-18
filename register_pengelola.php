<?php
session_start();
$hide_auth_buttons = true;
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pengelola - Pare Sports</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="assets/css/register_pengelola.css" />
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main class="register-container">
        <div class="register-box">
            <h2 class="text-center">Registrasi Pengelola</h2>
            <p class="text-center text-muted">Daftarkan diri untuk mulai mengelola venue olahraga Anda di Pare Sports.</p>
            
            <?php
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger mt-3">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                unset($_SESSION['error_message']);
            }
            ?>

            <form id="registerForm" action="proses/proses_register_pengelola.php" method="POST" onsubmit="return validatePassword()" class="mt-4">
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama" name="nama" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="no_telepon" class="form-label">Nomor Telepon</label>
                    <input type="tel" class="form-control" id="no_telepon" name="no_telepon" required>
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <div id="passwordHelp" class="form-text text-danger" style="display:none;">Password tidak cocok!</div>
                </div>
                <button type="submit" class="btn btn-register w-100 mt-3">Daftar</button>
            </form>
            <div class="text-center mt-4">
                <p class="text-muted">Sudah punya akun? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login di sini</a></p>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function validatePassword() {
        var password = document.getElementById("password").value;
        var confirm_password = document.getElementById("confirm_password").value;
        var helpText = document.getElementById("passwordHelp");
        
        // Reset tampilan error
        helpText.style.display = 'none';
        
        // Validasi panjang password
        if (password.length < 8) {
            helpText.textContent = "Password minimal 8 karakter!";
            helpText.style.display = 'block';
            return false;
        }
        
        // Validasi kesesuaian password
        if (password !== confirm_password) {
            helpText.textContent = "Password tidak cocok!";
            helpText.style.display = 'block';
            return false;
        }
        
        return true;
    }
    </script>
</body>
</html>