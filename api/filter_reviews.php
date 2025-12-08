<?php
session_start();
header('Content-Type: application/json');

// Require auth
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get filter parameters
$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : null;
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : null;
$minRating = isset($_GET['minRating']) ? (int)$_GET['minRating'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : null;

// Build query
$query = "SELECT id, name, email, rating, comment, page, approved, created_at FROM reviews WHERE 1=1";
$params = [];
$types = '';

if ($dateFrom) {
    $query .= " AND DATE(created_at) >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}

if ($dateTo) {
    $query .= " AND DATE(created_at) <= ?";
    $params[] = $dateTo;
    $types .= 's';
}

if ($minRating > 0) {
    $query .= " AND rating >= ?";
    $params[] = $minRating;
    $types .= 'i';
}

if ($status === 'pending') {
    $query .= " AND approved = 0";
} elseif ($status === 'approved') {
    $query .= " AND approved = 1";
}

$query .= " ORDER BY created_at DESC";

try {
    if (!empty($params)) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        $result = $conn->query($query);
    }
    
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'),
            'email' => $row['email'],
            'rating' => (int)$row['rating'],
            'comment' => htmlspecialchars($row['comment'], ENT_QUOTES, 'UTF-8'),
            'page' => $row['page'],
            'approved' => (bool)$row['approved'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode(['success' => true, 'reviews' => $reviews]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'reviews' => []]);
}
$conn->close();
?>
