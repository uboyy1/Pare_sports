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
    
    // Bind parameter secara manual
    foreach ($params as $key => $value) {
        $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($key + 1, $value, $param_type);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameter secara manual
    foreach ($params as $key => $value) {
        $stmt->bindValue($key + 1, $value);
    }
    
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['total'];
    
}
?>

