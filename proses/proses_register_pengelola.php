<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['no_telepon']); 
    $user_address = trim($_POST['alamat']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($nama) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Nama, Email, dan Password wajib diisi.";
        header("Location: ../register_pengelola.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Password dan konfirmasi password tidak cocok.";
        header("Location: ../register_pengelola.php");
        exit();
    }
    
    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = "Email sudah terdaftar. Silakan gunakan email lain.";
            header("Location: ../register_pengelola.php");
            exit();
        }

        // Buat username unik dari nama
        $base_username = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($nama));
        $username = $base_username;
        $counter = 1;

        while (true) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if (!$stmt->fetch()) break;
            
            $username = $base_username . $counter;
            $counter++;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'pengelola';

        $sql = "INSERT INTO users (nama, username, email, password, phone, address, role) 
                VALUES (:nama, :username, :email, :password, :phone, :address, :role)";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':phone', $phone_number);
        $stmt->bindParam(':address', $user_address);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Registrasi sebagai pengelola berhasil! Silakan login.";
            header("Location: ../index.php"); 
            exit();
        } else {
            $_SESSION['error_message'] = "Gagal menyimpan data. Silakan coba lagi.";
            header("Location: ../register_pengelola.php");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Manager Registration Error: " . $e->getMessage());
        $_SESSION['error_message'] = "Terjadi kesalahan pada server database: " . $e->getMessage();
        header("Location: ../register_pengelola.php");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}