<?php
$host = 'localhost';
$dbname = 'pare_sports';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Define upload paths
    define('PROFILE_PICTURE_DIR', 'assets/img/profiles/');
    define('LAPANGAN_IMAGE_DIR', 'assets/img/');
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}