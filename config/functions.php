<?php
function getVenueById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM venues WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getFieldsByVenueId($conn, $venue_id) {
    $stmt = $conn->prepare("SELECT * FROM fields WHERE venue_id = ?");
    $stmt->bind_param("i", $venue_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fields = [];
    while ($row = $result->fetch_assoc()) {
        $fields[] = $row;
    }
    return $fields;
}
function getLapangan($conn, $sport = 'all', $search = '', $limit = 6, $offset = 0) {
    $query = "SELECT * FROM lapangan";
    
    $conditions = [];
    $params = [];
    $types = '';
    
    if ($sport != 'all') {
        $conditions[] = "jenis_olahraga = ?";
        $params[] = $sport;
        $types .= 's';
    }
    
    if (!empty($search)) {
        $conditions[] = "nama_venue LIKE ?";
        $params[] = "%$search%";
        $types .= 's';
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $query .= " ORDER BY rating DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        die("Error in query: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function countLapangan($conn, $sport = 'all', $search = '') {
    $query = "SELECT COUNT(*) as total FROM lapangan";
    
    $conditions = [];
    $params = [];
    $types = '';
    
    if ($sport != 'all') {
        $conditions[] = "jenis_olahraga = ?";
        $params[] = $sport;
        $types .= 's';
    }
    
    if (!empty($search)) {
        $conditions[] = "nama_venue LIKE ?";
        $params[] = "%$search%";
        $types .= 's';
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        die("Error in query: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'];
    
}
?>

