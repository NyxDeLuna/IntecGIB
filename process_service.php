<?php
// process_service.php
header('Content-Type: application/json');
// Evitar que errores de PHP se muestren como HTML en la respuesta JSON
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

// Incluir la configuración de la base de datos
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Recoger datos del formulario
$serviceType = $_POST['serviceType'] ?? '';
$technicians = $_POST['technicians'] ?? '';
$hours = $_POST['hours'] ?? '';
$totalAmount = $_POST['totalAmount'] ?? '0';
$customerName = $_POST['customerName'] ?? '';
$customerEmail = $_POST['customerEmail'] ?? '';
$customerPhone = $_POST['customerPhone'] ?? '';
$serviceAddress = $_POST['serviceAddress'] ?? '';
$serviceDate = $_POST['serviceDate'] ?? '';
$serviceTime = $_POST['serviceTime'] ?? '';
$serviceDetails = $_POST['serviceDetails'] ?? '';
$paypalTransactionId = $_POST['paypalTransactionId'] ?? '';

// Procesar detalles de PayPal
$paypalDetails = [];
if (isset($_POST['paypalDetails'])) {
    $paypalDetails = json_decode($_POST['paypalDetails'], true);
}

// Validar datos básicos
if (empty($serviceType) || empty($customerName) || empty($customerEmail)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validar que los tipos de servicio sean correctos
if (!in_array($serviceType, ['maintenance', 'installation'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid service type']);
    exit;
}

// Generar número de referencia
$referenceNumber = 'SRV-' . date('Ymd') . '-' . rand(1000, 9999);

try {
    // Conectar a la base de datos
    // `config/database.php` crea un PDO en la variable $pdo
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('Database connection not available (missing $pdo from config/database.php)');
    }
    $conn = $pdo;
    // Si viene un paypalTransactionId, comprobar si ya existe (idempotencia)
    if (!empty($paypalTransactionId)) {
        $checkSql = "SELECT id, reference_number, status, total_amount FROM service_orders WHERE paypal_transaction_id = :pt LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(':pt', $paypalTransactionId);
        $checkStmt->execute();
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            // Ya existe un pedido con esa transacción: devolver éxito idempotente
            echo json_encode([
                'success' => true,
                'message' => 'Payment already processed',
                'reference' => $existing['reference_number'],
                'order_id' => $existing['id'],
                'amount' => $existing['total_amount'],
                'already_exists' => true
            ]);
            exit;
        }
    }

    // Funciones auxiliares para verificar PayPal
    function get_paypal_access_token() {
        $cfg = require __DIR__ . '/config/paypal.php';
        $mode = $cfg['mode'] ?? 'sandbox';
        $client = $cfg['client_id'] ?? '';
        $secret = $cfg['secret'] ?? '';
        $apiBase = is_callable($cfg['api_base']) ? $cfg['api_base']($mode) : ($cfg['api_base'] ?? 'https://api-m.sandbox.paypal.com');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiBase . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $client . ':' . $secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http >= 200 && $http < 300 && $resp) {
            $j = json_decode($resp, true);
            return $j['access_token'] ?? null;
        }
        error_log('PayPal token request failed: HTTP ' . $http . ' resp: ' . substr($resp,0,200));
        return null;
    }

    function verify_paypal_capture($captureId, $expectedAmount = null) {
        if (empty($captureId)) return false;
        $cfg = require __DIR__ . '/config/paypal.php';
        $mode = $cfg['mode'] ?? 'sandbox';
        $apiBase = is_callable($cfg['api_base']) ? $cfg['api_base']($mode) : ($cfg['api_base'] ?? 'https://api-m.sandbox.paypal.com');

        $token = get_paypal_access_token();
        if (!$token) return false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiBase . '/v2/payments/captures/' . urlencode($captureId));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http >= 200 && $http < 300 && $resp) {
            $j = json_decode($resp, true);
            $status = $j['status'] ?? null;
            if (strtoupper($status) === 'COMPLETED') {
                if ($expectedAmount !== null && isset($j['amount']['value'])) {
                    $paypalAmount = (float) $j['amount']['value'];
                    $expected = (float) $expectedAmount;
                    if (abs($paypalAmount - $expected) > 0.01) {
                        error_log('PayPal capture amount mismatch: expected ' . $expected . ' got ' . $paypalAmount);
                        return false;
                    }
                }
                return true;
            }
        }
        error_log('PayPal capture verify failed: HTTP ' . $http . ' resp: ' . substr($resp ?? '',0,200));
        return false;
    }

    function verify_paypal_order($orderId, $expectedAmount = null) {
        if (empty($orderId)) return false;
        $cfg = require __DIR__ . '/config/paypal.php';
        $mode = $cfg['mode'] ?? 'sandbox';
        $apiBase = is_callable($cfg['api_base']) ? $cfg['api_base']($mode) : ($cfg['api_base'] ?? 'https://api-m.sandbox.paypal.com');

        $token = get_paypal_access_token();
        if (!$token) return false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiBase . '/v2/checkout/orders/' . urlencode($orderId));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http >= 200 && $http < 300 && $resp) {
            $j = json_decode($resp, true);
            $status = $j['status'] ?? null;
            if (strtoupper($status) === 'COMPLETED') {
                if ($expectedAmount !== null && isset($j['purchase_units'][0]['amount']['value'])) {
                    $paypalAmount = (float) $j['purchase_units'][0]['amount']['value'];
                    $expected = (float) $expectedAmount;
                    if (abs($paypalAmount - $expected) > 0.01) {
                        error_log('PayPal order amount mismatch: expected ' . $expected . ' got ' . $paypalAmount);
                        return false;
                    }
                }
                return true;
            }
        }
        error_log('PayPal order verify failed: HTTP ' . $http . ' resp: ' . substr($resp ?? '',0,200));
        return false;
    }

    // Si hay una transacción de PayPal, verificarla con la API antes de aceptar como 'paid'
    $status = 'pending';
    if (!empty($paypalTransactionId)) {
        $verified = false;
        // Intentar verificar como capture (capture id)
        $verified = verify_paypal_capture($paypalTransactionId, $totalAmount);

        // Si no verificado, intentar buscar orderID en paypalDetails y verificar orden
        if (!$verified) {
            $orderId = $paypalDetails['orderID'] ?? $paypalDetails['id'] ?? null;
            if ($orderId) {
                $verified = verify_paypal_order($orderId, $totalAmount);
            }
        }

        if ($verified) {
            $status = 'paid';
        } else {
            throw new Exception('PayPal verification failed for transaction ' . $paypalTransactionId);
        }
    }
    
    // Preparar la consulta SQL para insertar en service_orders
    $sql = "INSERT INTO service_orders (
        reference_number,
        service_type,
        technicians,
        hours,
        service_date,
        service_time,
        customer_name,
        customer_email,
        customer_phone,
        service_address,
        service_details,
        total_amount,
        paypal_transaction_id,
        status,
        created_at,
        updated_at
    ) VALUES (
        :reference_number,
        :service_type,
        :technicians,
        :hours,
        :service_date,
        :service_time,
        :customer_name,
        :customer_email,
        :customer_phone,
        :service_address,
        :service_details,
        :total_amount,
        :paypal_transaction_id,
        :status,
        NOW(),
        NOW()
    )";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':reference_number', $referenceNumber);
    $stmt->bindParam(':service_type', $serviceType);
    $stmt->bindParam(':technicians', $technicians, PDO::PARAM_INT);
    $stmt->bindParam(':hours', $hours, PDO::PARAM_INT);
    $stmt->bindParam(':service_date', $serviceDate);
    $stmt->bindParam(':service_time', $serviceTime);
    $stmt->bindParam(':customer_name', $customerName);
    $stmt->bindParam(':customer_email', $customerEmail);
    $stmt->bindParam(':customer_phone', $customerPhone);
    $stmt->bindParam(':service_address', $serviceAddress);
    $stmt->bindParam(':service_details', $serviceDetails);
    $stmt->bindParam(':total_amount', $totalAmount);
    $stmt->bindParam(':paypal_transaction_id', $paypalTransactionId);
    
    // Establecer status como 'paid' si hay transacción de PayPal
    $status = !empty($paypalTransactionId) ? 'paid' : 'pending';
    $stmt->bindParam(':status', $status);
    
    // Ejecutar la inserción
    if ($stmt->execute()) {
        // Obtener el ID insertado
        $orderId = $conn->lastInsertId();
        
        // Aquí podrías también:
        // 1. Enviar email de confirmación al cliente
        // 2. Enviar notificación al equipo de IntecGIB
        // 3. Registrar en un log
        
        // Datos para la respuesta
        $bookingData = [
            'id' => $orderId,
            'reference' => $referenceNumber,
            'service_type' => $serviceType,
            'technicians' => $technicians,
            'hours' => $hours,
            'total_amount' => $totalAmount,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'service_address' => $serviceAddress,
            'service_date' => $serviceDate,
            'service_time' => $serviceTime,
            'service_details' => $serviceDetails,
            'paypal_transaction_id' => $paypalTransactionId,
            'status' => $status,
            'booking_date' => date('Y-m-d H:i:s')
        ];
        // Intentar generar factura PDF y obtener token/URL (opcional)
        $invoiceInfo = null;
        try {
            if (file_exists(__DIR__ . '/config/generate_invoice.php')) {
                require_once __DIR__ . '/config/generate_invoice.php';
                // Preparar datos básicos para la factura
                $invoiceData = [
                    'order_id' => $orderId,
                    'date' => date('Y-m-d'),
                    'customer' => [
                        'name' => $customerName,
                        'email' => $customerEmail
                    ],
                    'items' => [
                        [
                            'desc' => ucfirst($serviceType) . ' service',
                            'qty' => 1,
                            'price' => $totalAmount
                        ]
                    ],
                    'total' => $totalAmount
                ];

                $gen = generate_invoice_pdf($invoiceData);
                if ($gen && isset($gen['token'])) {
                    $invoiceUrl = 'download_invoice.php?token=' . urlencode($gen['token']);
                    $invoiceInfo = ['token' => $gen['token'], 'url' => $invoiceUrl, 'path' => $gen['path'] ?? null];
                }
            }
        } catch (Exception $ie) {
            error_log('Invoice generation error: ' . $ie->getMessage());
        }

        // Respuesta de éxito
        $response = [
            'success' => true,
            'message' => 'Service booked successfully',
            'reference' => $referenceNumber,
            'order_id' => $orderId,
            'amount' => $totalAmount,
            'booking_data' => $bookingData
        ];
        if ($invoiceInfo) {
            $response['invoice'] = $invoiceInfo;
        }

        echo json_encode($response);
        
    } else {
        throw new Exception('Failed to save order to database');
    }
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    
    // Respuesta de error
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your booking. Please try again.',
        'error' => $e->getMessage()
    ]);
}
?>