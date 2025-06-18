<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Query dengan pengecualian status untuk pengelola
    $query = "SELECT * FROM users WHERE email = :email AND role = :role";
    
    // Tambahkan kondisi status khusus untuk pengelola
    if ($role === 'pengelola') {
        $query .= " AND status = 'verified'";
    }

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':role', $role);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Periksa status untuk pengelola
        if ($role === 'pengelola' && $user['status'] !== 'verified') {
            $_SESSION['login_error'] = "Akun pengelola belum diverifikasi. Silakan hubungi admin.";
            header('Location: ../index.php');
            exit();
        }
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            if ($role == 'admin') {
                header('Location: ../admin/dashboard_admin.php');
            } elseif ($role == 'pengelola') {
                header('Location: ../manager/dashboard-pengelola.php');
            } else {
                header('Location: ../index.php');
            }
            exit();
        } else {
            $_SESSION['login_error'] = "Password salah!";
            header('Location: ../index.php');
            exit();
        }
    } else {
        $_SESSION['login_error'] = "Email tidak ditemukan atau status akun belum sesuai.";
        header('Location: ../index.php');
        exit();
    }
} else {
    header('Location: ../index.php');
    exit();
}
?>