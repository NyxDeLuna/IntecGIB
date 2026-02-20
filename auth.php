<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo json_encode([
        'loggedIn' => true,
        'user' => $_SESSION['user_id'] ?? '',
        'name' => $_SESSION['user_name'] ?? '',
        'role' => $_SESSION['user_role'] ?? '',
        'loginTime' => $_SESSION['login_time'] ?? ''
    ]);
} else {
    echo json_encode([
        'loggedIn' => false,
        'message' => 'Not authenticated'
    ]);
}
?>