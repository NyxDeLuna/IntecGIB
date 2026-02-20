<?php
include 'config/database.php';
header('Content-Type: application/json');

try {
    // Consulta para obtener proyectos con sus imágenes - CORREGIDA
    $stmt = $pdo->query("
        SELECT p.*, 
               GROUP_CONCAT(CAST(pi.imagen AS CHAR) ORDER BY pi.id) as images_paths
        FROM projects p
        LEFT JOIN project_images pi ON p.id = pi.project_id
        GROUP BY p.id
        ORDER BY p.id DESC, p.estado_proyecto, p.created_at DESC
    ");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar imágenes para cada proyecto
    foreach ($projects as &$project) {
        // Si hay imágenes en la base de datos
        if (!empty($project['images_paths'])) {
            // Los BLOB se almacenan como rutas hex (0x...), necesitamos limpiarlas
            $paths = explode(',', $project['images_paths']);
            $cleanPaths = [];
            
            foreach ($paths as $path) {
                // Si comienza con 0x, es hexadecimal, lo convertimos
                if (strpos($path, '0x') === 0) {
                    // Convertir hex a string
                    $hex = substr($path, 2);
                    $cleanPath = hex2bin($hex);
                    if ($cleanPath !== false) {
                        $cleanPaths[] = $cleanPath;
                    }
                } else {
                    $cleanPaths[] = $path;
                }
            }
            
            $project['images'] = $cleanPaths;
        } else {
            // Buscar imágenes en sistema de archivos (ruta correcta)
            $projectFolder = 'img/projects/project_' . $project['id'] . '/';
            if (is_dir($projectFolder)) {
                $imageFiles = glob($projectFolder . "*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}", GLOB_BRACE);
                $project['images'] = $imageFiles ?: [];
            } else {
                $project['images'] = [];
            }
        }
        
        // Asegurar que images sea un array
        if (!is_array($project['images'])) {
            $project['images'] = [];
        }
        
        // Si no hay imágenes, usar placeholder
        if (empty($project['images'])) {
            $project['images'] = ['img/projects/placeholder.jpg'];
        }
        
        // Eliminar campo temporal
        unset($project['images_paths']);
    }
    
    echo json_encode(['success' => true, 'projects' => $projects]);
    
} catch (PDOException $e) {
    error_log("Error en get_projects.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading projects: ' . $e->getMessage(), 'projects' => []]);
}
?>