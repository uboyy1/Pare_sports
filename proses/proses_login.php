<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Use prepared statement with PDO
    // Add status check to the query
    $query = "SELECT * FROM users WHERE email = :email AND role = :role AND status = 'verified'"; // Added status check
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
        // Modified error message to be more generic for security
        $_SESSION['login_error'] = "Email atau status akun belum diverifikasi. Silakan hubungi admin.";
        header('Location: ../index.php');
        exit();
    }
} else {
    header('Location: ../index.php');
    exit();
}
?>