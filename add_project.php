<?php
// add_project.php - VERSIÓN CON TIMEOUT CONTROLADO
session_start();

// 1. Configurar timeout MANUALMENTE
set_time_limit(30); // 30 segundos máximo

// 2. Deshabilitar buffering
while (ob_get_level()) ob_end_clean();

// 3. Configurar headers inmediatamente
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// 4. Iniciar log de depuración
$logFile = 'debug_upload.log';
function logDebug($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    error_log($logMessage, 3, $logFile);
    
    // También a error_log normal
    error_log($message);
}

// Limpiar log anterior
file_put_contents($logFile, "=== START ADD_PROJECT ===\n");

logDebug("Step 1: Script started");

try {
    // PASO 1: Verificar sesión RÁPIDO
    logDebug("Step 2: Checking session");
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Not authorized');
    }
    
    // PASO 2: Verificar método RÁPIDO
    logDebug("Step 3: Checking method");
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid method');
    }
    
    // PASO 3: Obtener datos básicos
    logDebug("Step 4: Getting POST data");
    $projectName = trim($_POST['projectName'] ?? '');
    $projectDescription = trim($_POST['projectDescription'] ?? '');
    $projectStatus = intval($_POST['projectStatus'] ?? 1);
    
    logDebug("Data: Name='$projectName', Status=$projectStatus");
    
    if (empty($projectName)) {
        throw new Exception('Project name required');
    }
    
    // PASO 4: Verificar archivos RÁPIDO
    logDebug("Step 5: Checking files");
    if (!isset($_FILES['projectImages']) || empty($_FILES['projectImages']['tmp_name'][0])) {
        throw new Exception('Please upload at least 1 image');
    }
    
    $fileCount = count($_FILES['projectImages']['tmp_name']);
    logDebug("File count: $fileCount");
    
    // PASO 5: CONEXIÓN A BD CON TIMEOUT
    logDebug("Step 6: Connecting to database");
    
    // Verificar si el archivo de configuración existe
    $configFile = __DIR__ . '/config/database.php';
    logDebug("Config file path: $configFile");
    
    if (!file_exists($configFile)) {
        throw new Exception("Database config file not found: $configFile");
    }
    
    // Incluir con verificación
    include $configFile;
    logDebug("Config file included");
    
    // Verificar que $pdo existe
    if (!isset($pdo)) {
        throw new Exception('PDO connection not established after including config');
    }
    
    // Test rápido de conexión con timeout
    logDebug("Step 7: Testing DB connection");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->query("SELECT 1")->fetch();
    logDebug("Database connection OK");
    
    // PASO 6: INSERTAR PROYECTO
    logDebug("Step 8: Inserting project");
    $stmt = $pdo->prepare("INSERT INTO projects (nombre, descripcion, estado_proyecto) VALUES (?, ?, ?)");
    $stmt->execute([$projectName, $projectDescription, $projectStatus]);
    
    $projectId = $pdo->lastInsertId();
    logDebug("Project inserted with ID: $projectId");
    
    // PASO 7: CREAR CARPETA
    logDebug("Step 9: Creating folder");
    $projectFolder = 'img/projects/project_' . $projectId . '/';
    
    if (!is_dir($projectFolder)) {
        if (!@mkdir($projectFolder, 0755, true)) {
            logDebug("Warning: Could not create folder $projectFolder");
            // Continuar igual
        } else {
            logDebug("Folder created: $projectFolder");
        }
    }
    
    // PASO 8: SUBIR IMÁGENES
    logDebug("Step 10: Uploading images");
    $uploadedCount = 0;
    
    for ($i = 0; $i < $fileCount; $i++) {
        logDebug("Processing image $i");
        
        if ($_FILES['projectImages']['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['projectImages']['tmp_name'][$i];
            $originalName = $_FILES['projectImages']['name'][$i];
            
            // Validar tipo de archivo RÁPIDO
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tmpName);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                logDebug("Skipping invalid file type: $mimeType");
                continue;
            }
            
            // Generar nombre
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $newFileName = 'image_' . ($uploadedCount + 1) . '.' . $extension;
            $filePath = $projectFolder . $newFileName;
            
            logDebug("Moving $originalName to $filePath");
            
            // Mover archivo
            if (@move_uploaded_file($tmpName, $filePath)) {
                logDebug("File moved successfully");
                
                // Insertar en BD
                $imgStmt = $pdo->prepare("INSERT INTO project_images (project_id, imagen) VALUES (?, ?)");
				$imgStmt->execute([$projectId, $filePath]);  // Guarda la ruta del archivo
                
                $uploadedCount++;
                logDebug("Image record inserted. Total: $uploadedCount");
            } else {
                logDebug("Failed to move file. Error: " . $_FILES['projectImages']['error'][$i]);
                
                // Intentar ruta alternativa
                $altPath = 'img/uploads/project_' . $projectId . '_' . $newFileName;
                if (@move_uploaded_file($tmpName, $altPath)) {
                    $imgStmt = $pdo->prepare("INSERT INTO project_images (project_id, imagen) VALUES (?, ?)");
                    $imgStmt->execute([$projectId, $altPath]);
                    $uploadedCount++;
                    logDebug("Image saved to alternative path: $altPath");
                }
            }
        }
    }
    
    logDebug("Step 11: Process completed. Uploaded $uploadedCount images");
    
    // RESPUESTA DE ÉXITO
    $response = [
        'success' => true,
        'message' => 'Project created successfully!',
        'projectId' => $projectId,
        'imagesUploaded' => $uploadedCount
    ];
    
    echo json_encode($response);
    logDebug("Response sent successfully");
    
} catch (Exception $e) {
    logDebug("ERROR: " . $e->getMessage());
    logDebug("Stack trace: " . $e->getTraceAsString());
    
    $response = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'errorType' => get_class($e)
    ];
    
    echo json_encode($response);
}

logDebug("=== END ADD_PROJECT ===");
?>