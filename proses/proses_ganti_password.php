<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi password baru
    if ($new_password !== $confirm_password) {
        $_SESSION['password_error'] = "Password baru dan konfirmasi password tidak cocok.";
        header('Location: ../profil.php');
        exit();
    }
    
    // Validasi panjang password
    if (strlen($new_password) < 6) {
        $_SESSION['password_error'] = "Password baru harus minimal 6 karakter.";
        header('Location: ../profil.php');
        exit();
    }
    
    // Ambil password saat ini dari database
    $query = "SELECT password FROM users WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['password_error'] = "Pengguna tidak ditemukan.";
        header('Location: ../profil.php');
        exit();
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verifikasi password saat ini
    if (!password_verify($current_password, $user['password'])) {
        $_SESSION['password_error'] = "Password saat ini salah.";
        header('Location: ../profil.php');
        exit();
    }
    
    // Hash password baru
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password di database
    $query = "UPDATE users SET password = :password WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $_SESSION['password_success'] = "Password berhasil diubah!";
    } else {
        $_SESSION['password_error'] = "Gagal mengubah password.";
    }
    
    header('Location: ../profil.php');
    exit();
} else {
    header('Location: ../profil.php');
    exit();
}