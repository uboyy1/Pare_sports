<?php
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

function getVenueById($conn, $id) {
    return getLapanganById($conn, $id);
}

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

function getBookingsByUserId($conn, $user_id) {
    $sql = "SELECT 
                b.id AS booking_id, 
                b.tanggal AS booking_date,
                b.jam_mulai AS start_time,
                b.jam_selesai AS end_time,
                b.durasi,
                b.total_harga AS total_price,
                b.status,
                b.payment_method,
                l.nama_venue, 
                l.nama_lapangan,
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

// Tambahkan fungsi ini di functions.php
function updateUserBalance($conn, $user_id, $amount, $type, $description) {
    $conn->beginTransaction();
    try {
        // Dapatkan saldo saat ini
        $stmt = $conn->prepare("SELECT balance FROM user_balances WHERE user_id = ? FOR UPDATE");
        $stmt->execute([$user_id]);
        $current_balance = $stmt->fetchColumn();
        
        // Jika belum ada record, buat baru
        if ($current_balance === false) {
            $current_balance = 0.00;
            $stmt = $conn->prepare("INSERT INTO user_balances (user_id, balance) VALUES (?, ?)");
            $stmt->execute([$user_id, $current_balance]);
        }
        
        // Hitung saldo baru
        $new_balance = $current_balance + $amount;
        
        // Update saldo
        $stmt = $conn->prepare("UPDATE user_balances SET balance = ? WHERE user_id = ?");
        $stmt->execute([$new_balance, $user_id]);
        
        // Catat transaksi
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $amount, $type, $description]);
        
        $conn->commit();
        return $new_balance;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error updating balance: " . $e->getMessage());
        return false;
    }
}
/**
 * Menghitung jumlah lapangan yang dikelola oleh seorang manajer.
 * @param PDO $conn Koneksi database.
 * @param int $manager_id ID manajer.
 * @return int Jumlah lapangan.
 */
function countManagedFields($conn, $manager_id) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM lapangan WHERE pengelola_id = :manager_id");
        $stmt->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Database Error in countManagedFields: " . $e->getMessage());
        return 0;
    }
}

/**
 * Mengambil semua lapangan yang dikelola oleh seorang manajer.
 * @param PDO $conn Koneksi database.
 * @param int $manager_id ID manajer.
 * @return array Daftar lapangan.
 */
function getManagedFields($conn, $manager_id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM lapangan WHERE pengelola_id = :manager_id ORDER BY nama_venue");
        $stmt->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error in getManagedFields: " . $e->getMessage());
        return [];
    }
}

/**
 * Mengambil data satu lapangan berdasarkan ID, memastikan itu milik manajer yang benar.
 * @param PDO $conn Koneksi database.
 * @param int $field_id ID lapangan.
 * @param int $manager_id ID manajer.
 * @return array|null Data lapangan atau null jika tidak ditemukan/tidak diizinkan.
 */
function getManagedFieldById($conn, $field_id, $manager_id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM lapangan WHERE id = :field_id AND pengelola_id = :manager_id");
        $stmt->bindParam(':field_id', $field_id, PDO::PARAM_INT);
        $stmt->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log("Database Error in getManagedFieldById: " . $e->getMessage());
        return null;
    }
}

/**
 * Mengambil laporan keuangan untuk manajer dalam rentang tanggal tertentu.
 * @param PDO $conn Koneksi database.
 * @param int $manager_id ID manajer.
 * @param string $start_date Tanggal mulai.
 * @param string $end_date Tanggal akhir.
 * @return array Laporan keuangan.
 */
function getFinancialReportForManager($conn, $manager_id, $start_date, $end_date) {
    $report = [
        'transactions' => [],
        'total_kotor' => 0,
        'total_potongan' => 0,
        'total_bersih' => 0
    ];
    
    $sql = "SELECT b.id, b.tanggal, b.total_harga, l.nama_venue
            FROM booking b
            JOIN lapangan l ON b.lapangan_id = l.id
            WHERE l.pengelola_id = :manager_id
            AND b.status IN ('confirmed', 'selesai')
            AND b.tanggal BETWEEN :start_date AND :end_date";
            
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_kotor = array_sum(array_column($transactions, 'total_harga'));
        $total_potongan = $total_kotor * 0.10; // Asumsi potongan admin 10%
        $total_bersih = $total_kotor - $total_potongan;

        $report['transactions'] = $transactions;
        $report['total_kotor'] = $total_kotor;
        $report['total_potongan'] = $total_potongan;
        $report['total_bersih'] = $total_bersih;
        
        return $report;
    } catch (PDOException $e) {
        error_log("Database Error in getFinancialReportForManager: " . $e->getMessage());
        return $report;
    }
}

/**
 * Mengambil booking terbaru untuk lapangan yang dikelola manajer.
 * @param PDO $conn Koneksi database.
 * @param int $manager_id ID manajer.
 * @param int $limit Batas jumlah data.
 * @return array Daftar booking.
 */
function getRecentBookingsForManager($conn, $manager_id, $limit = 5) {
    $sql = "SELECT b.*, u.nama as nama_user, l.nama_venue
            FROM booking b
            JOIN users u ON b.user_id = u.id
            JOIN lapangan l ON b.lapangan_id = l.id
            WHERE l.pengelola_id = :manager_id
            ORDER BY b.tanggal DESC, b.jam_mulai DESC
            LIMIT :limit";
            
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error in getRecentBookingsForManager: " . $e->getMessage());
        return [];
    }
}

/**
 * Mengambil semua booking untuk lapangan dan tanggal tertentu.
 * @param PDO $conn Koneksi database.
 * @param int $lapangan_id ID lapangan.
 * @param string $date Tanggal.
 * @return array Daftar booking.
 */
function getBookingsForFieldOnDate($conn, $lapangan_id, $date) {
    $sql = "SELECT b.jam_mulai, u.nama AS nama_user
            FROM booking b
            JOIN users u ON b.user_id = u.id
            WHERE b.lapangan_id = :lapangan_id 
            AND b.tanggal = :tanggal 
            AND b.status != 'cancelled'";
            
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':lapangan_id', $lapangan_id, PDO::PARAM_INT);
        $stmt->bindParam(':tanggal', $date);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error in getBookingsForFieldOnDate: " . $e->getMessage());
        return [];
    }
}

// New functions for Admin
function countTotalUsers($conn) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'user'");
        $stmt->execute();
        return (int)$stmt->fetchColumn(); // Mengambil jumlah total pengguna
    } catch (PDOException $e) {
        error_log("Database Error in countTotalUsers: " . $e->getMessage());
        return 0;
    }
}

function countTotalManagers($conn) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'pengelola'");
        $stmt->execute();
        return (int)$stmt->fetchColumn(); // Mengambil jumlah total pengelola
    } catch (PDOException $e) {
        error_log("Database Error in countTotalManagers: " . $e->getMessage());
        return 0;
    }
}

function countTotalBookings($conn) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM booking WHERE status IN ('confirmed', 'selesai')"); // Contoh status booking yang dihitung
        $stmt->execute();
        return (int)$stmt->fetchColumn(); // Mengambil jumlah total booking
    } catch (PDOException $e) {
        error_log("Database Error in countTotalBookings: " . $e->getMessage());
        return 0;
    }
}

/**
 * Mengambil daftar pengguna dengan status tertentu (misalnya 'pending').
 * @param PDO $conn Koneksi database.
 * @param string $status Status pengguna yang ingin diambil.
 * @return array Daftar pengguna.
 */
function getUsersbyStatus($conn, $status = 'pending') {
    try {
        $stmt = $conn->prepare("SELECT id, nama, email, username, status FROM users WHERE role = 'user' AND status = :status ORDER BY id DESC");
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error in getUsersbyStatus: " . $e->getMessage());
        return [];
    }
}

/**
 * Mengambil daftar pengelola dengan status tertentu (misalnya 'pending').
 * @param PDO $conn Koneksi database.
 * @param string $status Status pengelola yang ingin diambil.
 * @return array Daftar pengelola.
 */
function getManagersbyStatus($conn, $status = 'pending') {
    try {
        $stmt = $conn->prepare("SELECT id, nama, email, username, status FROM users WHERE role = 'pengelola' AND status = :status ORDER BY id DESC");
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error in getManagersbyStatus: " . $e->getMessage());
        return [];
    }
}

/**
 * Memperbarui status pengguna atau pengelola.
 * @param PDO $conn Koneksi database.
 * @param int $userId ID pengguna/pengelola.
 * @param string $newStatus Status baru ('verified', 'rejected').
 * @return bool True jika berhasil, false jika gagal.
 */
function updateVerificationStatus($conn, $userId, $newStatus) {
    try {
        $stmt = $conn->prepare("UPDATE users SET status = :new_status WHERE id = :id");
        $stmt->bindParam(':new_status', $newStatus);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database Error in updateVerificationStatus: " . $e->getMessage());
        return false;
    }
}