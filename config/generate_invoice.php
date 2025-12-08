<?php
// Helper para generar facturas PDF usando Dompdf.
// Requiere instalar: composer require dompdf/dompdf

// Intentar cargar el autoload de Composer si está disponible
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

function generate_invoice_pdf(array $invoiceData) {
    $logFile = __DIR__ . '/../logs/invoice_generation.log';
    $log = function($msg) use ($logFile) {
        file_put_contents($logFile, date('c') . ' ' . $msg . "\n", FILE_APPEND | LOCK_EX);
    };
    $log('--- Generación de factura iniciada ---');
    $cfg = require __DIR__ . '/invoice_config.php';
    $invoiceDir = $cfg['invoice_dir'];
    $mappingFile = $cfg['mapping_file'];

    if (!is_dir($invoiceDir)) {
        $log('Directorio de facturas no existe, creando: ' . $invoiceDir);
        mkdir($invoiceDir, 0755, true);
    }
    if (!is_dir(dirname($mappingFile))) {
        $log('Directorio de mapping no existe, creando: ' . dirname($mappingFile));
        mkdir(dirname($mappingFile), 0755, true);
    }

    $token = bin2hex(random_bytes(8));
    $orderId = $invoiceData['order_id'] ?? time();
    $filename = sprintf('invoice_%s_%s.pdf', preg_replace('/[^a-zA-Z0-9_-]/','', $orderId), $token);
    $filepath = realpath($invoiceDir) . DIRECTORY_SEPARATOR . $filename;
    $log('Archivo destino: ' . $filepath);

    // Render plantilla a HTML
    $invoice = $invoiceData;
    ob_start();
    include __DIR__ . '/../templates/invoice_template.php';
    $html = ob_get_clean();
    $log('HTML generado, longitud: ' . strlen($html));

    if (!class_exists('Dompdf\\Dompdf')) {
        $log('ERROR: Dompdf no encontrado.');
        error_log('Dompdf no encontrado. Instala dompdf/dompdf via Composer.');
        return false;
    }
    try {
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf = $dompdf->output();
        file_put_contents($filepath, $pdf);
        $log('PDF generado y guardado correctamente.');
    } catch (Exception $e) {
        $log('ERROR al generar PDF: ' . $e->getMessage());
        return false;
    }

    // Guardar mapeo token -> archivo
    $mappings = [];
    if (file_exists($mappingFile)) {
        $content = file_get_contents($mappingFile);
        $mappings = json_decode($content, true) ?? [];
    }
    $mappings[$token] = [
        'file' => 'img/uploads/invoices/' . $filename,
        'order_id' => $orderId,
        'created' => date('c'),
        'email' => $invoiceData['customer']['email'] ?? null
    ];
    file_put_contents($mappingFile, json_encode($mappings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $log('Mapping actualizado.');

    $log('--- Generación de factura finalizada ---');
    return ['token' => $token, 'path' => $mappings[$token]['file']];
}

// Si se llama directamente (útil para pruebas):
if (php_sapi_name() === 'cli' && isset($argv) && basename(__FILE__) === basename($argv[0])) {
    // Prueba rápida
    $sample = [
        'order_id' => 'TEST-' . time(),
        'date' => date('Y-m-d'),
        'customer' => ['name' => 'Cliente Prueba', 'email' => 'cliente@example.com'],
        'items' => [['desc' => 'Servicio Ejemplo', 'qty' => 1, 'price' => 50]],
        'total' => 50
    ];
    $res = generate_invoice_pdf($sample);
    // CLI test invocation — avoid printing debug output in production
    // Use exit code or logs for automation; printing removed.
}
