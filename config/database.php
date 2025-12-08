<?php
// config/database.php - Database connections (PDO and MySQLi)

// ============================================
// MYSQL CONFIGURATION
// ============================================

$host = 'localhost';
$dbname = 'intecgib_db';
$username = 'root';
$password = '';
$port = 3306;

// ============================================
// APACHE CONFIGURATION (SSL)
// ============================================

$apache_port = 4433;
$base_url = "https://localhost:{$apache_port}/intecgib/";

// ============================================
// PDO CONNECTION
// ============================================

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username, 
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5
        ]
    );
    
    error_log("PDO MySQL connected on port $port");
    
} catch (PDOException $e) {
    error_log("PDO MySQL connection failed on port $port: " . $e->getMessage());
    
    // Try alternative ports
    $alternative_ports = [3307, 3308, 3309];
    
    foreach ($alternative_ports as $alt_port) {
        try {
            $pdo = new PDO(
                "mysql:host=$host;port=$alt_port;dbname=$dbname",
                $username, 
                $password,
                [PDO::ATTR_TIMEOUT => 2]
            );
            error_log("Connected to MySQL on alternative port $alt_port");
            break;
        } catch (PDOException $e2) {
            error_log("Also failed on port $alt_port");
        }
    }
    
    if (!isset($pdo)) {
        throw new Exception("Could not connect to MySQL on any port. Check if MySQL is running.");
    }
}

// ============================================
// MYSQLI CONNECTION (for legacy code compatibility)
// ============================================

$conn = new mysqli($host, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    error_log("MySQLi connection failed: " . $conn->connect_error);
    // Try to create a dummy connection object to prevent fatal errors
    // (some code may still attempt to use $conn)
    throw new Exception("MySQLi connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
error_log("MySQLi MySQL connected on port $port");

?>