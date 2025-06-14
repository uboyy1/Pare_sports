<?php
// proses/get_jadwal.php
header('Content-Type: application/json');
require_once '../config/database.php';

// Validasi input dari client
if (!isset($_GET['lapangan_id']) || !is_numeric($_GET['lapangan_id']) || !isset($_GET['date'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Parameter tidak lengkap atau tidak valid.']);
    exit;
}

$lapangan_id = (int)$_GET['lapangan_id'];
$date = $_GET['date'];

// Validasi format tanggal (YYYY-MM-DD)
if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Format tanggal tidak valid.']);
    exit;
}

// Asumsi jam operasional venue
$jam_buka = 8;
$jam_tutup = 23; // Buka sampai jam 11 malam (slot terakhir jam 10 malam)

// Buat semua kemungkinan slot waktu per 1 jam
$all_slots = [];
for ($i = $jam_buka; $i < $jam_tutup; $i++) {
    $all_slots[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
}

$booked_slots = [];
try {
    // --- PERBAIKAN UTAMA DI SINI ---
    // Mengambil semua booking untuk lapangan dan tanggal yang statusnya BUKAN 'cancelled'
    $query = "SELECT jam_mulai, jam_selesai FROM booking 
              WHERE lapangan_id = :lapangan_id 
              AND tanggal = :tanggal 
              AND status != 'cancelled'"; // Diubah dari 'Dibatalkan' ke 'cancelled'
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':lapangan_id', $lapangan_id, PDO::PARAM_INT);
    $stmt->bindParam(':tanggal', $date);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tandai semua jam dalam rentang waktu booking sebagai sudah dipesan
    foreach ($bookings as $booking) {
        $start_hour = (int)substr($booking['jam_mulai'], 0, 2);
        $end_hour = (int)substr($booking['jam_selesai'], 0, 2);
        for ($h = $start_hour; $h < $end_hour; $h++) {
            $booked_slots[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
        }
    }
    // Menghapus duplikat jam yang sudah dibooking
    $booked_slots = array_unique($booked_slots);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    error_log("Database Error in get_jadwal.php: " . $e->getMessage());
    echo json_encode(['error' => 'Terjadi kesalahan pada server.']);
    exit;
}

// Kirim hasil dalam format JSON
$result = [
    'all_slots' => $all_slots,
    'booked_slots' => array_values($booked_slots) // Mengatur ulang index array
];

echo json_encode($result);
?>