<?php
session_start();
// Langkah 2.1: Koneksi database diperlukan di sini, dan path ini sudah benar.
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan bersihkan
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['no_telepon']); 
    $user_address = trim($_POST['alamat']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi dasar
    if (empty($nama) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Nama, Email, dan Password wajib diisi.";
        header("Location: /Pare_sports/register_pengelola.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Password dan konfirmasi password tidak cocok.";
        header("Location: /Pare_sports/register_pengelola.php");
        exit();
    }
    
    try {
        // Cek apakah email sudah ada
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = "Email sudah terdaftar. Silakan gunakan email lain.";
            header("Location: /Pare_sports/register_pengelola.php");
            exit();
        }

        // Cek apakah username (dari nama) sudah ada
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->bindParam(':username', $nama);
        $stmt->execute();
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = "Nama ini sudah digunakan sebagai username. Mohon gunakan nama lain.";
            header("Location: /Pare_sports/register_pengelola.php");
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'pengelola';

        // Query INSERT yang benar
        $sql = "INSERT INTO users (nama, username, email, password, phone, address, role) 
                VALUES (:nama, :username, :email, :password, :phone, :address, :role)";
        $stmt = $conn->prepare($sql);

        // Memasukkan variabel yang benar dari form, bukan teks biasa
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':username', $nama);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':phone', $phone_number);
        $stmt->bindParam(':address', $user_address);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            $_SESSION['register_success'] = "Registrasi sebagai pengelola berhasil! Silakan login.";
            header("Location: /Pare_sports/index.php"); 
            exit();
        } else {
            $_SESSION['error_message'] = "Gagal menyimpan data. Silakan coba lagi.";
            header("Location: /Pare_sports/register_pengelola.php");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Manager Registration Error: " . $e->getMessage());
        $_SESSION['error_message'] = "Terjadi kesalahan pada server database.";
        header("Location: /Pare_sports/register_pengelola.php");
        exit();
    }
} else {
    header("Location: /Pare_sports/index.php");
    exit();
}
?>