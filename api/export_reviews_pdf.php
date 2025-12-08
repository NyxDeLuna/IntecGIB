<?php
session_start();
// Require auth
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 403 Unauthorized');
    echo 'Unauthorized';
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
        $reviews[] = $row;
    }
    $conn->close();
    
    // Generate HTML for PDF
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reviews Report</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { color: #333; border-bottom: 3px solid #6aaa64; padding-bottom: 10px; }
            .filter-info { background: #f5f7fa; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background: #6aaa64; color: white; padding: 12px; text-align: left; font-weight: bold; }
            td { padding: 10px; border-bottom: 1px solid #ddd; }
            tr:nth-child(even) { background: #f9f9f9; }
            .rating { color: #f6b042; font-weight: bold; }
            .approved { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 3px; font-weight: bold; }
            .pending { background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 3px; font-weight: bold; }
            .comment { max-width: 300px; word-wrap: break-word; }
            .page { color: #666; font-size: 12px; }
            .date { color: #999; font-size: 12px; }
            .total { margin-top: 20px; padding-top: 20px; border-top: 2px solid #6aaa64; font-weight: bold; }
        </style>
    </head>
    <body>
        <h1>📊 Reviews Report - IntecGIB</h1>';
    
    $html .= '<div class="filter-info">';
    $html .= '<strong>Report Generated:</strong> ' . date('Y-m-d H:i:s') . '<br>';
    if ($dateFrom) $html .= '<strong>From Date:</strong> ' . htmlspecialchars($dateFrom) . '<br>';
    if ($dateTo) $html .= '<strong>To Date:</strong> ' . htmlspecialchars($dateTo) . '<br>';
    if ($minRating > 0) $html .= '<strong>Min Rating:</strong> ' . $minRating . '+ stars<br>';
    if ($status) $html .= '<strong>Status:</strong> ' . ucfirst($status) . '<br>';
    $html .= '<strong>Total Reviews:</strong> ' . count($reviews) . '<br>';
    $html .= '</div>';
    
    $html .= '<table>
        <thead>
            <tr>
                <th style="width: 15%;">Name</th>
                <th style="width: 10%;">Rating</th>
                <th style="width: 35%;">Comment</th>
                <th style="width: 12%;">Page</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 18%;">Date</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($reviews as $r) {
        $stars = str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']);
        $status_badge = $r['approved'] ? '<span class="approved">✓ Approved</span>' : '<span class="pending">⏳ Pending</span>';
        $date = date('Y-m-d H:i', strtotime($r['created_at']));
        
        $html .= '<tr>
            <td>' . htmlspecialchars($r['name']) . '</td>
            <td class="rating">' . $stars . '</td>
            <td class="comment">' . htmlspecialchars($r['comment']) . '</td>
            <td class="page">' . htmlspecialchars($r['page']) . '</td>
            <td>' . $status_badge . '</td>
            <td class="date">' . $date . '</td>
        </tr>';
    }
    
    $html .= '</tbody></table>';
    $html .= '<div class="total">Total Reviews: ' . count($reviews) . ' | Approved: ' . count(array_filter($reviews, fn($r) => $r['approved'])) . ' | Pending: ' . count(array_filter($reviews, fn($r) => !$r['approved'])) . '</div>';
    $html .= '</body></html>';
    
    // Generate PDF using DOMPDF
    require_once __DIR__ . '/../vendor/autoload.php';
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    
    // Output PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="reviews_report_' . date('Y-m-d_H-i-s') . '.pdf"');
    echo $dompdf->output();
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Server Error');
    echo 'Error generating PDF: ' . $e->getMessage();
}
?>
