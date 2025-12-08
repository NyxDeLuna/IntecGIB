<?php
session_start();
header('Content-Type: application/json');

// Endpoint to return reviews from database
// ?page=... filter by page
// ?all=1 return all reviews (admin only, requires auth)

require_once __DIR__ . '/../config/database.php';

$page = isset($_GET['page']) ? preg_replace('/[^a-z0-9_\-\.]/i', '', $_GET['page']) : null;
$all = isset($_GET['all']) ? true : false;

// If admin requesting all, check authentication
if ($all) {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized', 'reviews' => []]);
        exit;
    }
    
    $query = "SELECT id, name, email, rating, comment, page, approved, created_at FROM reviews ORDER BY created_at DESC";
} else if ($page) {
    // Return only approved reviews for specific page
    $query = "SELECT id, name, email, rating, comment, page, approved, created_at FROM reviews WHERE page = ? AND approved = 1 ORDER BY created_at DESC";
} else {
    // Return all approved reviews
    $query = "SELECT id, name, email, rating, comment, page, approved, created_at FROM reviews WHERE approved = 1 ORDER BY created_at DESC";
}

try {
    if ($page && !$all) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $page);
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
