<?php
session_start();
if (ob_get_length()) ob_clean();

session_destroy();

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Sesión cerrada correctamente'
]);
?>