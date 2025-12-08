<?php
// webhook.php - receptor de Webhooks de PayPal con validación de firma

// Función para validar la firma del webhook (recomendado para seguridad)
function validate_paypal_webhook_signature($cert_url, $transmission_id, $transmission_time, $webhook_id, $event_body) {
    // Obtener el certificado de PayPal
    $cert = @file_get_contents($cert_url);
    if (!$cert) {
        error_log('No se pudo obtener el certificado de PayPal desde: ' . $cert_url);
        return false;
    }

    // Construir la cadena a verificar (SigBase)
    $algo = 'sha256';
    $sig_base = "$transmission_id|$transmission_time|$webhook_id|" . hash($algo, $event_body);

    // Obtener la firma del header
    $signature = $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ?? '';
    if (!$signature) {
        error_log('Falta header PAYPAL_TRANSMISSION_SIG');
        return false;
    }

    // Decodificar la firma
    $expected = base64_encode(hash_hmac($algo, $sig_base, $cert, true));

    // Comparar
    if (!hash_equals($expected, $signature)) {
        error_log('Firma de webhook inválida. Esperada: ' . $expected . ', Recibida: ' . $signature);
        return false;
    }

    return true;
}

// Leer raw POST
$raw = file_get_contents('php://input');
if (!$raw) {
    http_response_code(400);
    exit('No data');
}
$data = json_decode($raw, true);
if (!$data) {
    http_response_code(400);
    exit('Invalid JSON');
}

// Validar firma si está disponible (opcional pero recomendado)
// Descomenta la línea siguiente para activar validación:
// $cert_url = $_SERVER['HTTP_PAYPAL_CERT_URL'] ?? '';
// $transmission_id = $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ?? '';
// $transmission_time = $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] ?? '';
// $webhook_id = 'TU_WEBHOOK_ID'; // Obtén esto de tu panel de PayPal
// if (!validate_paypal_webhook_signature($cert_url, $transmission_id, $transmission_time, $webhook_id, $raw)) {
//     http_response_code(403);
//     exit('Invalid signature');
// }

// Función auxiliar para enviar correo (con PHPMailer si está disponible)
function send_invoice_email_inline(array $invoice, string $token, string $recipientEmail = null) {
    // Respetar la configuración: si está deshabilitado el envío por webhook, no enviar
    $invcfg = require __DIR__ . '/config/invoice_config.php';
    if (empty($invcfg['email_on_webhook'])) {
        error_log('send_invoice_email_inline: disabled by invoice_config.');
        return false;
    }

    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        return false;
    }
    require_once __DIR__ . '/vendor/autoload.php';
    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        // fallback: try unescaped form
    }

    $recipientEmail = $recipientEmail ?? ($invoice['customer']['email'] ?? null);
    if (!$recipientEmail) {
        return false;
    }

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Cambia según tu proveedor
        $mail->SMTPAuth = true;
        $mail->Username = 'tu_email@gmail.com'; // Configura tus credenciales
        $mail->Password = 'tu_app_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('tu_email@gmail.com', 'Tu Empresa');
        $mail->addAddress($recipientEmail, $invoice['customer']['name']);
        $mail->isHTML(true);
        $mail->Subject = 'Factura #' . $invoice['order_id'];
        $mail->Body = '<p>Hola ' . htmlspecialchars($invoice['customer']['name']) . ',</p>' .
                      '<p>Tu factura está lista.</p>' .
                      '<p><a href="https://tu-dominio.com/download_invoice.php?token=' . urlencode($token) . '">Descargar PDF</a></p>';
        $mail->send();
        error_log('Correo enviado a: ' . $recipientEmail);
        return true;
    } catch (Exception $e) {
        error_log('Error enviando correo: ' . $e->getMessage());
        return false;
    }
}

// Tipo de evento (varía según la versión de la API). Ejemplo: PAYMENT.CAPTURE.COMPLETED
$eventType = $data['event_type'] ?? $data['eventType'] ?? '';

// Soportamos 'PAYMENT.CAPTURE.COMPLETED' como ejemplo
if ($eventType === 'PAYMENT.CAPTURE.COMPLETED' || $eventType === 'PAYMENT.CAPTURE.REFUNDED') {
    $resource = $data['resource'] ?? [];
    $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? ($resource['id'] ?? null);
    $amount = $resource['amount'] ?? [];
    $value = $amount['value'] ?? null;
    $currency = $amount['currency_code'] ?? null;

    // Extraemos información del comprador si está disponible
    $payerEmail = $resource['payer']['email_address'] ?? ($resource['payer']['email'] ?? null);
    $payerName = $resource['payer']['name']['given_name'] ?? null;

    // Preparar datos para la factura
    $invoice = [
        'order_id' => $orderId ?? 'PAYPAL-' . time(),
        'date' => date('Y-m-d'),
        'customer' => ['name' => $payerName ?? 'Comprador', 'email' => $payerEmail],
        'items' => [[ 'desc' => 'Pago PayPal', 'qty' => 1, 'price' => (float)$value ]],
        'total' => (float)$value
    ];

    require __DIR__ . '/config/generate_invoice.php';
    $res = generate_invoice_pdf($invoice);

    if ($res) {
        // Comprobar configuración para envío automático por webhook
        $invcfg = require __DIR__ . '/config/invoice_config.php';
        $mailSent = false;
        if (!empty($invcfg['email_on_webhook'])) {
            // Intentar enviar correo con PHPMailer (si está disponible)
            $mailSent = send_invoice_email_inline($invoice, $res['token'], $payerEmail);
        }

        http_response_code(200);
        echo json_encode(['status' => 'ok', 'token' => $res['token'], 'path' => $res['path'], 'email_sent' => $mailSent]);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'No se pudo generar factura']);
        exit;
    }
}

// Responder 200 por defecto para otros eventos
http_response_code(200);
echo json_encode(['status' => 'ignored']);
