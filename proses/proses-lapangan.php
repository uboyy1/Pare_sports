<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengelola') {
    http_response_code(403);
    die("Akses ditolak.");
}

$manager_id = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    
    $nama_venue = $_POST['nama_venue'];
    $jenis_olahraga = $_POST['jenis_olahraga'];
    $harga = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];
    $alamat = $_POST['alamat'];
    $fasilitas = $_POST['fasilitas'];
    $aturan = $_POST['aturan'];
    $lapangan_id = $_POST['id'] ?? null;
    
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['gambar'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'venue_' . time() . '.' . $ext;
        $target_dir = '../assets/img/';
        
        if (move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
            $gambar = $filename;
        } else {
            $_SESSION['error_message'] = "Gagal mengunggah gambar.";
            header('Location: ../Manager/kelola-lapangan.php');
            exit();
        }
    }

    if ($action === 'add') {
        $sql = "INSERT INTO lapangan (nama_venue, jenis_olahraga, harga, deskripsi, alamat, fasilitas, aturan, gambar, pengelola_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$nama_venue, $jenis_olahraga, $harga, $deskripsi, $alamat, $fasilitas, $aturan, $gambar, $manager_id])) {
            $_SESSION['success_message'] = "Lapangan baru berhasil ditambahkan!";
        } else {
            $_SESSION['error_message'] = "Gagal menambahkan lapangan.";
        }
    } elseif ($action === 'edit' && $lapangan_id) {
        if ($gambar) {
            $sql = "UPDATE lapangan SET nama_venue=?, jenis_olahraga=?, harga=?, deskripsi=?, alamat=?, fasilitas=?, aturan=?, gambar=? WHERE id=? AND pengelola_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nama_venue, $jenis_olahraga, $harga, $deskripsi, $alamat, $fasilitas, $aturan, $gambar, $lapangan_id, $manager_id]);
        } else {
            $sql = "UPDATE lapangan SET nama_venue=?, jenis_olahraga=?, harga=?, deskripsi=?, alamat=?, fasilitas=?, aturan=? WHERE id=? AND pengelola_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nama_venue, $jenis_olahraga, $harga, $deskripsi, $alamat, $fasilitas, $aturan, $lapangan_id, $manager_id]);
        }
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Data lapangan berhasil diperbarui!";
        }
    }

    header('Location: ../Manager/kelola-lapangan.php');
    exit();
}

// Handle Delete
if ($action === 'delete' && isset($_GET['id'])) {
    $lapangan_id = $_GET['id'];
    $sql = "DELETE FROM lapangan WHERE id = ? AND pengelola_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$lapangan_id, $manager_id])) {
        $_SESSION['success_message'] = "Lapangan berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus lapangan.";
    }
    header('Location: ../Manager/kelola-lapangan.php');
    exit();
}

header('Location: ../Manager/kelola-lapangan.php');
exit();