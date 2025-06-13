<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Gunakan prepared statement dengan PDO
    $query = "SELECT * FROM users WHERE email = :email AND role = :role";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':role', $role);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect berdasarkan role
            if ($role == 'admin') {
                header('Location: ../admin/dashboard-admin.php');
            } elseif ($role == 'pengelola') {
                header('Location: ../pengelola/dashboard-pengelola.php');
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
        $_SESSION['login_error'] = "Email tidak ditemukan!";
        header('Location: ../index.php');
        exit();
    }
} else {
    header('Location: ../index.php');
    exit();
}
?>