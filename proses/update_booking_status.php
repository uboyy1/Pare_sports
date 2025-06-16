<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$booking_id = $data['booking_id'] ?? null;
$new_status = 'cancelled'; // Only cancellation is handled

if (!$booking_id) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

// Get booking data
$sql = "SELECT * FROM booking WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
    exit();
}

// Only allow cancellation for pending or confirmed bookings
if (!in_array($booking['status'], ['pending', 'confirmed'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Tidak dapat membatalkan booking dengan status saat ini'
    ]);
    exit();
}

// Start transaction
$conn->beginTransaction();
try {
    // If paid with balance, refund the amount
    if ($booking['payment_method'] === 'saldo') {
        $refundAmount = $booking['total_harga'];
        $stmt = $conn->prepare("UPDATE user_balances SET balance = balance + ? WHERE user_id = ?");
        $stmt->execute([$refundAmount, $booking['user_id']]);
        
        // Record refund transaction
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'refund', ?)");
        $description = 'Refund for cancelled booking #' . $booking_id;
        $stmt->execute([$booking['user_id'], $refundAmount, $description]);
    }
    
    // Delete the booking
    $stmt = $conn->prepare("DELETE FROM booking WHERE id = ?");
    $stmt->execute([$booking_id]);
    
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Gagal memproses pembatalan: ' . $e->getMessage()]);
}
exit();