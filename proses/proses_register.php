<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi kesesuaian password
    if ($password !== $confirm_password) {
        $_SESSION['register_error'] = "Password dan konfirmasi password tidak cocok!";
        header('Location: ../index.php');
        exit();
    }
    
    // Validasi panjang password
    if (strlen($password) < 6) {
        $_SESSION['register_error'] = "Password harus minimal 6 karakter!";
        header('Location: ../index.php');
        exit();
    }
    
    // Validasi panjang username
    if (strlen($username) < 3) {
        $_SESSION['register_error'] = "Username harus minimal 3 karakter!";
        header('Location: ../index.php');
        exit();
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Cek duplikat email
    $query = "SELECT id FROM users WHERE email = :email";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['register_error'] = "Email sudah terdaftar!";
        header('Location: ../index.php');
        exit();
    }
    
    // Cek duplikat username
    $query = "SELECT id FROM users WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['register_error'] = "Username sudah digunakan!";
        header('Location: ../index.php');
        exit();
    }
    
    // Insert user baru
    $query = "INSERT INTO users (nama, username, email, password, role, status) 
              VALUES (:nama, :username, :email, :password, 'user', 'pending')";
    
    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        
        if ($stmt->execute()) {
            $_SESSION['register_success'] ;
        } else {
            $errorInfo = $stmt->errorInfo();
            $_SESSION['register_error'] = "Pendaftaran gagal: " . $errorInfo[2];
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $_SESSION['register_error'] = "Username atau email sudah digunakan!";
        } else {
            $_SESSION['register_error'] = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
    
    header('Location: ../index.php');
    exit();
} else {
    header('Location: ../index.php');
    exit();
}
?>