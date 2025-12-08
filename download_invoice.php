<?php
// download_invoice.php?token=abcdef
$token = $_GET['token'] ?? '';
if (!$token) {
    http_response_code(400);
    exit('Token requerido');
}

$cfg = require __DIR__ . '/config/invoice_config.php';
$mappingFile = $cfg['mapping_file'];
if (!file_exists($mappingFile)) {
    http_response_code(404);
    exit('No invoices');
}
$mappings = json_decode(file_get_contents($mappingFile), true);
if (!isset($mappings[$token])) {
    http_response_code(404);
    exit('Token no válido');
}
$relative = $mappings[$token]['file'];
$path = __DIR__ . '/' . ltrim($relative, '/\\');
if (!file_exists($path)) {
    http_response_code(404);
    exit('Archivo no encontrado');
}

// Forzar nombre personalizado para el PDF descargado y soportar vista inline
$orderId = isset($mappings[$token]['order_id']) ? preg_replace('/[^a-zA-Z0-9_-]/','', $mappings[$token]['order_id']) : $token;
$filename = 'invoice_' . $orderId . '.pdf';

// Decide si se debe forzar descarga (attachment) o mostrar inline (view)
$view = isset($_GET['view']) && ($_GET['view'] === '1' || strtolower($_GET['view']) === 'true');

header('Content-Type: application/pdf');
if ($view) {
    header('Content-Disposition: inline; filename="' . $filename . '"');
} else {
    header('Content-Disposition: attachment; filename="' . $filename . '"');
}
// Optional headers for cache and length
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
if (function_exists('filesize')) {
    header('Content-Length: ' . filesize($path));
}
readfile($path);
exit;
