<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $query = "SELECT * FROM users WHERE email = ? AND role = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
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