<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $_SESSION['register_error'] = "Password dan konfirmasi password tidak cocok!";
        header('Location: ../index.php');
        exit();
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Cek apakah email sudah terdaftar
    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['register_error'] = "Email sudah terdaftar!";
        header('Location: ../index.php');
        exit();
    }
    
    // Insert user baru
    $query = "INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, 'user')";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':nama', $nama);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    
    if ($stmt->execute()) {
        $_SESSION['register_success'] = "Pendaftaran berhasil! Silakan login.";
        header('Location: ../index.php');
        exit();
    } else {
        $_SESSION['register_error'] = "Pendaftaran gagal. Silakan coba lagi!";
        header('Location: ../index.php');
        exit();
    }
} else {
    header('Location: ../index.php');
    exit();
}
?>