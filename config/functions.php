<?php
// FILE: config/functions.php
// Direvisi total agar sesuai dengan skema database yang Anda berikan.

/**
 * Mengambil data detail sebuah lapangan berdasarkan ID-nya dari tabel 'lapangan'.
 * Fungsi ini sudah benar dan tidak diubah.
 */
function getLapanganById($conn, $id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM lapangan WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $lapangan = $stmt->fetch(PDO::FETCH_ASSOC);
        return $lapangan ? $lapangan : null;
    } catch (PDOException $e) {
        // Log error untuk debugging, jangan tampilkan ke user
        error_log("Database Error in getLapanganById: " . $e->getMessage());
        return null;
    }
}

/**
 * Mengganti nama fungsi lama agar kode di file lain tidak error.
 * Ini sekarang hanya memanggil getLapanganById.
 */
function getVenueById($conn, $id) {
    return getLapanganById($conn, $id);
}

/**
 * Mengambil semua data lapangan untuk ditampilkan di halaman utama (index.php).
 * @param PDO $conn Koneksi database
 * @return array Daftar semua lapangan
 */
function getAllVenues($conn) {
     try {
        // Mengambil semua data dari tabel lapangan
        $stmt = $conn->prepare("SELECT * FROM lapangan ORDER BY nama_venue");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error in getAllVenues: " . $e->getMessage());
        return [];
    }
}

/**
 * Mengambil daftar semua lapangan untuk halaman utama (index.php).
 * Fungsi ini sudah benar dan tidak diubah.
 */
function getLapangan($conn, $sport_filter, $search_query, $per_page, $offset) {
    $sql = "SELECT * FROM lapangan WHERE 1=1";
    $params = [];
    
    if ($sport_filter != 'all') {
        $sql .= " AND jenis_olahraga = ?";
        $params[] = $sport_filter;
    }
    
    if (!empty($search_query)) {
        $sql .= " AND nama_venue LIKE ?";
        $params[] = '%' . $search_query . '%';
    }
    
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    
    $stmt = $conn->prepare($sql);
    
    foreach ($params as $key => $value) {
        $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($key + 1, $value, $param_type);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Menghitung total lapangan untuk pagination.
 * Fungsi ini sudah benar dan tidak diubah.
 */
function countLapangan($conn, $sport_filter, $search_query) {
    $sql = "SELECT COUNT(*) as total FROM lapangan WHERE 1=1";
    $params = [];
    
    if ($sport_filter != 'all') {
        $sql .= " AND jenis_olahraga = ?";
        $params[] = $sport_filter;
    }
    
    if (!empty($search_query)) {
        $sql .= " AND nama_venue LIKE ?";
        $params[] = '%' . $search_query . '%';
    }
    
    $stmt = $conn->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key + 1, $value);
    }
    
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['total'];
}

/**
 * Mengambil riwayat booking pengguna.
 * DIPERBAIKI: Nama tabel, nama kolom, dan relasi disesuaikan dengan skema database Anda.
 */
function getBookingsByUserId($conn, $user_id) {
    // Kueri diperbaiki untuk menggunakan tabel 'booking' dan kolom yang benar ('lapangan_id', 'tanggal', dll.)
    $sql = "SELECT 
                b.id as booking_id, 
                b.tanggal as booking_date,          /* Menggunakan kolom 'tanggal' dari DB */
                b.jam_mulai as start_time,         /* Menggunakan kolom 'jam_mulai' dari DB */
                b.jam_selesai as end_time,         /* Menggunakan kolom 'jam_selesai' dari DB */
                b.total_harga as total_price,      /* Menggunakan kolom 'total_harga' dari DB */
                b.status,
                l.nama_venue, 
                l.gambar as venue_gambar,
                l.nama_venue as nama_lapangan      /* Tidak ada kolom 'nama_lapangan', jadi kita pakai 'nama_venue' */
            FROM booking b                         /* Menggunakan tabel 'booking' (bukan 'bookings') */
            JOIN lapangan l ON b.lapangan_id = l.id /* Join menggunakan 'lapangan_id' */
            WHERE b.user_id = :user_id
            ORDER BY b.tanggal DESC, b.jam_mulai DESC";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error untuk debugging jika diperlukan
        // error_log("Get Bookings Error: " . $e->getMessage());
        return [];
    }
}
?>