<?php
// FILE: config/functions.php

/**
 * Mengambil data detail sebuah lapangan berdasarkan ID-nya
 */
function getLapanganById($conn, $id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM lapangan WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log("Database Error in getLapanganById: " . $e->getMessage());
        return null;
    }
}

/**
 * Alias untuk getLapanganById (kompatibilitas)
 */
function getVenueById($conn, $id) {
    return getLapanganById($conn, $id);
}

/**
 * Mengambil semua data lapangan
 */
function getAllVenues($conn) {
     try {
        $stmt = $conn->prepare("SELECT * FROM lapangan ORDER BY nama_venue");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error in getAllVenues: " . $e->getMessage());
        return [];
    }
}

/**
 * Mengambil daftar lapangan dengan filter dan pagination
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
    
    try {
        $stmt = $conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key + 1, $value, $param_type);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error in getLapangan: " . $e->getMessage());
        return [];
    }
}

/**
 * Menghitung total lapangan untuk pagination
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
    
    try {
        $stmt = $conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key + 1, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Database Error in countLapangan: " . $e->getMessage());
        return 0;
    }
}

/**
 * Mengambil riwayat booking pengguna
 */
function getBookingsByUserId($conn, $user_id) {
    $sql = "SELECT 
                b.id AS booking_id, 
                b.tanggal AS booking_date,
                b.jam_mulai AS start_time,
                b.jam_selesai AS end_time,
                b.total_harga AS total_price,
                b.status,
                l.nama_venue, 
                l.gambar AS venue_gambar
            FROM booking b
            JOIN lapangan l ON b.lapangan_id = l.id
            WHERE b.user_id = :user_id
            ORDER BY b.tanggal DESC, b.jam_mulai DESC";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error in getBookingsByUserId: " . $e->getMessage());
        return [];
    }
}