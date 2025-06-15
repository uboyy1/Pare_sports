<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


header('Content-Type: application/json');


require_once 'config/database.php'; 


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan. Hanya POST yang diterima.']);
    exit();
}


$input = file_get_contents('php://input');
$data = json_decode($input, true);


$required_fields = ['user_id', 'lapangan_id', 'rating', 'komentar'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400); 
        echo json_encode(['success' => false, 'message' => "Field '$field' diperlukan."]);
        exit();
    }
}


$user_id = (int)$data['user_id'];
$lapangan_id = (int)$data['lapangan_id'];
$rating = (int)$data['rating'];
$komentar = htmlspecialchars(trim($data['komentar'] ?? '')); 
if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Rating harus antara 1 dan 5.']);
    exit();
}


$conn->beginTransaction();

try {
    


    $current_time = date('Y-m-d H:i:s');
    $sql_check_booking = "SELECT id FROM booking WHERE user_id = ? AND lapangan_id = ? AND status = 'completed' AND CONCAT(tanggal, ' ', jam_selesai) < ? LIMIT 1";
    $stmt_check_booking = $conn->prepare($sql_check_booking);
    if (!$stmt_check_booking) {
        throw new Exception('Error preparing booking check statement: ' . implode(" - ", $conn->errorInfo()));
    }
    $stmt_check_booking->execute([$user_id, $lapangan_id, $current_time]);
    $booking_record = $stmt_check_booking->fetch(PDO::FETCH_ASSOC);

    if (!$booking_record) {
        http_response_code(403); 
        throw new Exception('Anda tidak berhak memberikan review untuk lapangan ini atau booking belum selesai/berstatus completed.');
    }

    
    $sql_check_review = "SELECT id FROM reviews WHERE user_id = ? AND lapangan_id = ? LIMIT 1";
    $stmt_check_review = $conn->prepare($sql_check_review);
    if (!$stmt_check_review) {
        throw new Exception('Error preparing review check statement: ' . implode(" - ", $conn->errorInfo()));
    }
    $stmt_check_review->execute([$user_id, $lapangan_id]);
    $existing_review = $stmt_check_review->fetch(PDO::FETCH_ASSOC);

    if ($existing_review) {
        http_response_code(409); 
        throw new Exception('Anda sudah pernah memberikan review untuk lapangan ini.');
    }


    $sql_insert_review = "INSERT INTO reviews (user_id, lapangan_id, rating, komentar) VALUES (?, ?, ?, ?)";
    $stmt_insert_review = $conn->prepare($sql_insert_review);
    if (!$stmt_insert_review) {
        throw new Exception('Error preparing insert review statement: ' . implode(" - ", $conn->errorInfo()));
    }
    $stmt_insert_review->execute([$user_id, $lapangan_id, $rating, $komentar]);

    $sql_get_lapangan = "SELECT rating, jumlah_review FROM lapangan WHERE id = ? FOR UPDATE";
    $stmt_get_lapangan = $conn->prepare($sql_get_lapangan);
    if (!$stmt_get_lapangan) {
        throw new Exception('Error preparing get lapangan statement: ' . implode(" - ", $conn->errorInfo()));
    }
    $stmt_get_lapangan->execute([$lapangan_id]);
    $lapangan_data = $stmt_get_lapangan->fetch(PDO::FETCH_ASSOC);

    $current_rating = (float)$lapangan_data['rating'];
    $current_jumlah_review = (int)$lapangan_data['jumlah_review'];

    $new_jumlah_review = $current_jumlah_review + 1;

    $new_rating = $new_jumlah_review > 0 ? (($current_rating * $current_jumlah_review) + $rating) / $new_jumlah_review : $rating;


    $sql_update_lapangan = "UPDATE lapangan SET rating = ?, jumlah_review = ? WHERE id = ?";
    $stmt_update_lapangan = $conn->prepare($sql_update_lapangan);
    if (!$stmt_update_lapangan) {
        throw new Exception('Error preparing update lapangan statement: ' . implode(" - ", $conn->errorInfo()));
    }
    $stmt_update_lapangan->execute([$new_rating, $new_jumlah_review, $lapangan_id]);

    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Review berhasil disimpan.', 'new_rating' => round($new_rating, 1)]);

} catch (Exception $e) {

    $conn->rollback();

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan review: ' . $e->getMessage()]);
}
?>