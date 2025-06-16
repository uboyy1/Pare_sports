<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Handle file upload
    $profile_picture = $_SESSION['profile_picture']; // default to existing picture
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
        $target_dir = '../assets/img/profiles/';
        
        // Buat folder jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Validasi ekstensi file
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $allowed_ext)) {
            $_SESSION['profile_error'] = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
            header('Location: ../profil.php');
            exit();
        }
        
        // Validasi ukuran file (maks 2MB)
        $max_size = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $max_size) {
            $_SESSION['profile_error'] = "Ukuran file terlalu besar. Maksimal 2MB.";
            header('Location: ../profil.php');
            exit();
        }
        
        // Upload file
        if (move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
            // Hapus foto lama jika bukan default
            if ($profile_picture !== 'default.png' && file_exists($target_dir . $profile_picture)) {
                @unlink($target_dir . $profile_picture);
            }
            $profile_picture = $filename;
        } else {
            $_SESSION['profile_error'] = "Gagal mengunggah foto profil.";
            header('Location: ../profil.php');
            exit();
        }
    }
    
    // Validasi format username
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $_SESSION['profile_error'] = "Username hanya boleh mengandung huruf, angka, dan underscore (_).";
        header('Location: ../profil.php');
        exit();
    }
    
    // Validasi username unik untuk user lain
    $query = "SELECT id FROM users WHERE username = :username AND id != :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['profile_error'] = "Username sudah digunakan oleh pengguna lain!";
        header('Location: ../profil.php');
        exit();
    }
    
    // Update data di database
    $query = "UPDATE users SET nama = :nama, username = :username, phone = :phone, 
              address = :address, profile_picture = :profile_picture WHERE id = :id";
    
    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':profile_picture', $profile_picture);
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Update session data
            $_SESSION['username'] = $username;
            $_SESSION['nama'] = $nama;
            $_SESSION['profile_picture'] = $profile_picture;
            
            $_SESSION['profile_success'] = "Profil berhasil diperbarui!";
        } else {
            $errorInfo = $stmt->errorInfo();
            $_SESSION['profile_error'] = "Gagal memperbarui profil: " . $errorInfo[2];
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $_SESSION['profile_error'] = "Username sudah digunakan!";
        } else {
            $_SESSION['profile_error'] = "Error sistem: " . $e->getMessage();
        }
    }
    
    header('Location: ../profil.php');
    exit();
} else {
    header('Location: ../profil.php');
    exit();
}
?>