<?php
session_start();
require_once '../config/database.php';

// Pastikan hanya pengelola yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengelola') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $lapangan_id = $_POST['lapangan_id'];
    $tanggal = $_POST['tanggal'];
    $jam_mulai_str = $_POST['jam_mulai'];
    $customer_name = trim($_POST['customer_name']);
    $durasi = 1; // Asumsi booking manual selalu 1 jam

    // Hitung jam selesai
    $jam_selesai = date('H:i:s', strtotime($jam_mulai_str . ' + ' . $durasi . ' hour'));
    $jam_mulai = date('H:i:s', strtotime($jam_mulai_str));

    // Dapatkan harga dari data lapangan
    $stmt_harga = $conn->prepare("SELECT harga FROM lapangan WHERE id = ?");
    $stmt_harga->execute([$lapangan_id]);
    $harga_per_jam = $stmt_harga->fetchColumn();

    $total_harga = $harga_per_jam * $durasi;

    try {
        // Masukkan data booking baru
        $sql = "INSERT INTO booking (user_id, lapangan_id, tanggal, jam_mulai, jam_selesai, durasi, total_harga, status, payment_method, offline_customer_name) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed', 'offline', ?)";
        
        $stmt = $conn->prepare($sql);
        
        // user_id bisa diisi dengan ID pengelola yang melakukan booking
        $stmt->execute([
            $_SESSION['user_id'], 
            $lapangan_id, 
            $tanggal, 
            $jam_mulai, 
            $jam_selesai, 
            $durasi, 
            $total_harga, 
            $customer_name
        ]);

        $_SESSION['success_message'] = "Booking manual untuk " . htmlspecialchars($customer_name) . " berhasil ditambahkan.";

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Gagal menambahkan booking manual: " . $e->getMessage();
    }

    // Redirect kembali ke halaman jadwal dengan parameter yang sama
    header("Location: ../manager/jadwal-lapangan.php?lapangan_id=$lapangan_id&tanggal=$tanggal");
    exit();

} else {
    header('Location: ../manager/jadwal-lapangan.php');
    exit();
}