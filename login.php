<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Determinar a dónde redirigir después del login
$redirect_to = 'index.html'; // Por defecto

if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
    $redirect_to = $_GET['redirect'];
} elseif (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    if (strpos($referer, $_SERVER['HTTP_HOST']) !== false) {
        $redirect_to = basename($referer);
    }
}

// EVITAR BUCLE: No redirigir si ya estamos en login.php
if ($redirect_to === 'login.php') {
    $redirect_to = 'index.html';
}

// Si ya está logueado, redirigir directamente PERO evitar bucle
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Solo redirigir si no estamos ya en la página de destino
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page !== $redirect_to && $redirect_to !== 'login.php') {
        header('Location: ' . $redirect_to);
        exit;
    }
    // Si ya estamos en la página de destino, no hacer nada
}

// Procesamiento AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_login'])) {
    header('Content-Type: application/json');
    
    $username = $_POST["txtUsuario"] ?? '';
    $password = $_POST["txtContrasenia"] ?? '';
    $response = ['success' => false, 'message' => ''];
    
    if (empty($username) || empty($password)) {
        $response['message'] = "Por favor, completa todos los campos";
        echo json_encode($response);
        exit;
    }
    
    $conexion = mysqli_connect("localhost", "root", "", "intecgib_db");
    
    if (!$conexion) {
        $response['message'] = "Error de conexión a la base de datos";
        echo json_encode($response);
        exit;
    }
    
    mysqli_query($conexion, "SET NAMES 'utf8'");
    
    $sql = "SELECT id, pwd, nombre_completo, rol, email FROM users WHERE id = ? AND pwd = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $num_total_rows = mysqli_num_rows($result);
    
    if ($num_total_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $row["id"];
        $_SESSION['user_name'] = $row["nombre_completo"];
        $_SESSION['user_role'] = $row["rol"];
        $_SESSION['user_email'] = $row["email"];
        $_SESSION['login_time'] = time();
        
        $response['success'] = true;
        $response['message'] = "Login exitoso";
        $response['redirect'] = $redirect_to;
        
    } else {
        $response['message'] = "Usuario o contraseña incorrectos";
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
    
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Inicio de sesión - IntecGIB</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
            width: 350px;
        }
        .logo {
            margin-bottom: 20px;
        }
        .logo img {
            height: 60px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 300;
        }
        .redirect-info {
            background: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #1976d2;
        }
        table {
            width: 100%;
            margin: 20px 0;
        }
        td {
            padding: 10px;
            text-align: left;
        }
        label {
            font-weight: 500;
            color: #333;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .login-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: transform 0.3s ease;
            width: 100%;
        }
        .login-button:hover {
            transform: translateY(-2px);
        }
        .login-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        .error {
            color: #d63031;
            background: #ffe6e6;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #d63031;
            display: none;
        }
        .success {
            color: #27ae60;
            background: #e6f7e6;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #27ae60;
            display: none;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="img/misc/logo_intecgib.png" alt="IntecGIB Logo">
        </div>
        
        <h1>Inicio de Sesión</h1>
        
        <?php if ($redirect_to !== 'index.html' && $redirect_to !== 'login.php'): ?>
        <div class="redirect-info">
            🔄 Serás redirigido a: <strong><?php echo htmlspecialchars($redirect_to); ?></strong>
        </div>
        <?php endif; ?>
        
        <div class="error" id="errorMessage"></div>
        <div class="success" id="successMessage"></div>
        
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <div class="success" style="display: block;">
                ✅ Ya estás logueado como: <strong><?php echo $_SESSION['user_name']; ?></strong>
            </div>
            <p>Serás redirigido automáticamente...</p>
            <script>
                setTimeout(() => {
                    window.location.href = '<?php echo $redirect_to === "login.php" ? "index.html" : $redirect_to; ?>';
                }, 2000);
            </script>
        <?php else: ?>
        
        <form method="post" name="form1" id="form1">
            <input type="hidden" name="ajax_login" value="1">
            
            <table>
                <tr>
                    <td><label for="txtUsuario">Usuario:</label></td>
                    <td>
                        <input type="text" name="txtUsuario" id="txtUsuario" required 
                               placeholder="Ingresa tu usuario">
                    </td>
                </tr>
                <tr>
                    <td><label for="txtContrasenia">Contraseña:</label></td>
                    <td>
                        <input type="password" name="txtContrasenia" id="txtContrasenia" required 
                               placeholder="Ingresa tu contraseña">
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <button type="submit" class="login-button" id="btnEnviar">
                            Iniciar Sesión
                        </button>
                    </td>
                </tr>
            </table>
        </form>
        
        <a href="<?php echo $redirect_to === 'login.php' ? 'index.html' : $redirect_to; ?>" class="back-link">
            ← Volver a <?php echo htmlspecialchars($redirect_to === 'login.php' ? 'Inicio' : $redirect_to); ?>
        </a>
        
        <?php endif; ?>
    </div>

    <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById("form1");
            const button = document.getElementById("btnEnviar");
            const errorMsg = document.getElementById("errorMessage");
            const successMsg = document.getElementById("successMessage");
            
            // Auto-focus en el campo de usuario
            document.getElementById("txtUsuario").focus();
            
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevenir envío tradicional
                
                // Ocultar mensajes anteriores
                errorMsg.style.display = 'none';
                successMsg.style.display = 'none';
                
                // Cambiar estado del botón
                const originalText = button.innerHTML;
                button.innerHTML = 'Verificando...';
                button.disabled = true;
                
                // Preparar datos del formulario
                const formData = new FormData(form);
                
                // Enviar via AJAX
                fetch('login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Login exitoso
                        successMsg.textContent = data.message + ' - Redirigiendo...';
                        successMsg.style.display = 'block';
                        
                        // Redirigir después de 1 segundo
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                        
                    } else {
                        // Error en login
                        errorMsg.textContent = data.message;
                        errorMsg.style.display = 'block';
                        button.innerHTML = originalText;
                        button.disabled = false;
                        
                        // Mantener el foco en el campo de usuario
                        document.getElementById("txtUsuario").focus();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    errorMsg.textContent = 'Error de conexión. Intenta nuevamente.';
                    errorMsg.style.display = 'block';
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>