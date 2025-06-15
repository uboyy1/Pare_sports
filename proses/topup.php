<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

    // Validasi nominal
    if ($amount < 10000) {
        echo json_encode(['success' => false, 'message' => 'Minimal top up Rp 10.000.']);
        exit();
    }

    if ($amount > 5000000) {
        echo json_encode(['success' => false, 'message' => 'Maksimal top up Rp 5.000.000.']);
        exit();
    }

    
    // Gunakan fungsi updateUserBalance yang sudah diperbaiki
    $new_balance = updateUserBalance(
        $conn, 
        $user_id, 
        $amount, 
        'topup', 
        'Top up saldo'
    );

    if ($new_balance !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Top up berhasil. Saldo Anda telah ditambahkan.',
            'new_balance' => $new_balance
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan saat memproses top up. Silakan coba lagi.'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
}