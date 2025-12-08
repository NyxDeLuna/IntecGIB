<?php
header('Content-Type: application/json');

// Endpoint to save a review to database
// Expects POST JSON: { name, rating, comment, page, email (optional) }

require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$required = ['name', 'rating', 'comment', 'page'];
foreach ($required as $r) {
    if (!isset($input[$r])) {
        echo json_encode(['success' => false, 'error' => "Missing field: $r"]);
        exit;
    }
}

$name = trim($input['name']);
$email = isset($input['email']) ? trim($input['email']) : null;
$rating = (int)$input['rating'];
$comment = trim($input['comment']);
$page = preg_replace('/[^a-z0-9_\-\.]/i', '', $input['page']);

if (empty($name) || empty($comment)) {
    echo json_encode(['success' => false, 'error' => 'Name and comment are required']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Rating must be 1-5']);
    exit;
}

// Generate UUID v4
$id = sprintf(
    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);

try {
    $stmt = $conn->prepare("
        INSERT INTO reviews (id, name, email, rating, comment, page, approved, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
    ");
    
    $stmt->bind_param('sssiss', 
        $id, 
        $name, 
        $email, 
        $rating, 
        $comment, 
        $page
    );
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'review' => [
                'id' => $id,
                'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                'rating' => $rating,
                'comment' => htmlspecialchars($comment, ENT_QUOTES, 'UTF-8'),
                'page' => $page,
                'created_at' => date('c'),
                'approved' => false
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
$conn->close();
