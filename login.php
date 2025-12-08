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

    // Si el cliente no ha enviado el MD5 (por ejemplo JS deshabilitado),
    // convertimos el texto a MD5 en servidor para comparar con la BD.
    if (!preg_match('/^[a-f0-9]{32}$/i', $password)) {
        $password = md5($password);
    }
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
                // MD5 implementation (minimal) - used to hash password client-side
                // Note: MD5 is NOT recommended for password storage. See README notes.
                function md5cycle(x, k) {
                    var a = x[0], b = x[1], c = x[2], d = x[3];
                    a = ff(a, b, c, d, k[0], 7, -680876936);
                    d = ff(d, a, b, c, k[1], 12, -389564586);
                    c = ff(c, d, a, b, k[2], 17, 606105819);
                    b = ff(b, c, d, a, k[3], 22, -1044525330);
                    a = ff(a, b, c, d, k[4], 7, -176418897);
                    d = ff(d, a, b, c, k[5], 12, 1200080426);
                    c = ff(c, d, a, b, k[6], 17, -1473231341);
                    b = ff(b, c, d, a, k[7], 22, -45705983);
                    a = ff(a, b, c, d, k[8], 7, 1770035416);
                    d = ff(d, a, b, c, k[9], 12, -1958414417);
                    c = ff(c, d, a, b, k[10], 17, -42063);
                    b = ff(b, c, d, a, k[11], 22, -1990404162);
                    a = ff(a, b, c, d, k[12], 7, 1804603682);
                    d = ff(d, a, b, c, k[13], 12, -40341101);
                    c = ff(c, d, a, b, k[14], 17, -1502002290);
                    b = ff(b, c, d, a, k[15], 22, 1236535329);
                    a = gg(a, b, c, d, k[1], 5, -165796510);
                    d = gg(d, a, b, c, k[6], 9, -1069501632);
                    c = gg(c, d, a, b, k[11], 14, 643717713);
                    b = gg(b, c, d, a, k[0], 20, -373897302);
                    a = gg(a, b, c, d, k[5], 5, -701558691);
                    d = gg(d, a, b, c, k[10], 9, 38016083);
                    c = gg(c, d, a, b, k[15], 14, -660478335);
                    b = gg(b, c, d, a, k[4], 20, -405537848);
                    a = gg(a, b, c, d, k[9], 5, 568446438);
                    d = gg(d, a, b, c, k[14], 9, -1019803690);
                    c = gg(c, d, a, b, k[3], 14, -187363961);
                    b = gg(b, c, d, a, k[8], 20, 1163531501);
                    a = gg(a, b, c, d, k[13], 5, -1444681467);
                    d = gg(d, a, b, c, k[2], 9, -51403784);
                    c = gg(c, d, a, b, k[7], 14, 1735328473);
                    b = gg(b, c, d, a, k[12], 20, -1926607734);
                    a = hh(a, b, c, d, k[5], 4, -378558);
                    d = hh(d, a, b, c, k[8], 11, -2022574463);
                    c = hh(c, d, a, b, k[11], 16, 1839030562);
                    b = hh(b, c, d, a, k[14], 23, -35309556);
                    a = hh(a, b, c, d, k[1], 4, -1530992060);
                    d = hh(d, a, b, c, k[4], 11, 1272893353);
                    c = hh(c, d, a, b, k[7], 16, -155497632);
                    b = hh(b, c, d, a, k[10], 23, -1094730640);
                    a = hh(a, b, c, d, k[13], 4, 681279174);
                    d = hh(d, a, b, c, k[0], 11, -358537222);
                    c = hh(c, d, a, b, k[3], 16, -722521979);
                    b = hh(b, c, d, a, k[6], 23, 76029189);
                    a = hh(a, b, c, d, k[9], 4, -640364487);
                    d = hh(d, a, b, c, k[12], 11, -421815835);
                    c = hh(c, d, a, b, k[15], 16, 530742520);
                    b = hh(b, c, d, a, k[2], 23, -995338651);
                    a = ii(a, b, c, d, k[0], 6, -198630844);
                    d = ii(d, a, b, c, k[7], 10, 1126891415);
                    c = ii(c, d, a, b, k[14], 15, -1416354905);
                    b = ii(b, c, d, a, k[5], 21, -57434055);
                    a = ii(a, b, c, d, k[12], 6, 1700485571);
                    d = ii(d, a, b, c, k[3], 10, -1894986606);
                    c = ii(c, d, a, b, k[10], 15, -1051523);
                    b = ii(b, c, d, a, k[1], 21, -2054922799);
                    a = ii(a, b, c, d, k[8], 6, 1873313359);
                    d = ii(d, a, b, c, k[15], 10, -30611744);
                    c = ii(c, d, a, b, k[6], 15, -1560198380);
                    b = ii(b, c, d, a, k[13], 21, 1309151649);
                    a = ii(a, b, c, d, k[4], 6, -145523070);
                    d = ii(d, a, b, c, k[11], 10, -1120210379);
                    c = ii(c, d, a, b, k[2], 15, 718787259);
                    b = ii(b, c, d, a, k[9], 21, -343485551);
                    x[0] = add32(a, x[0]);
                    x[1] = add32(b, x[1]);
                    x[2] = add32(c, x[2]);
                    x[3] = add32(d, x[3]);
                }

                function cmn(q, a, b, x, s, t) {
                    a = add32(add32(a, q), add32(x, t));
                    return add32((a << s) | (a >>> (32 - s)), b);
                }
                function ff(a, b, c, d, x, s, t) { return cmn((b & c) | ((~b) & d), a, b, x, s, t); }
                function gg(a, b, c, d, x, s, t) { return cmn((b & d) | (c & (~d)), a, b, x, s, t); }
                function hh(a, b, c, d, x, s, t) { return cmn(b ^ c ^ d, a, b, x, s, t); }
                function ii(a, b, c, d, x, s, t) { return cmn(c ^ (b | (~d)), a, b, x, s, t); }

                function md51(s) {
                    txt = '';
                    var n = s.length,
                            state = [1732584193, -271733879, -1732584194, 271733878],
                            i;
                    for (i = 64; i <= s.length; i += 64) {
                        md5cycle(state, md5blk(s.substring(i - 64, i)));
                    }
                    s = s.substring(i - 64);
                    var tail = new Array(16).fill(0);
                    for (i = 0; i < s.length; i++) tail[i >> 2] |= s.charCodeAt(i) << ((i % 4) << 3);
                    tail[i >> 2] |= 0x80 << ((i % 4) << 3);
                    if (i > 55) {
                        md5cycle(state, tail);
                        tail = new Array(16).fill(0);
                    }
                    tail[14] = n * 8;
                    md5cycle(state, tail);
                    return state;
                }

                function md5blk(s) {
                    var md5blks = [], i;
                    for (i = 0; i < 64; i += 4) {
                        md5blks[i >> 2] = s.charCodeAt(i) + (s.charCodeAt(i + 1) << 8) +
                            (s.charCodeAt(i + 2) << 16) + (s.charCodeAt(i + 3) << 24);
                    }
                    return md5blks;
                }

                var hex_chr = '0123456789abcdef'.split('');
                function rhex(n) {
                    var s = '', j = 0;
                    for (; j < 4; j++) s += hex_chr[(n >> (j * 8 + 4)) & 0x0F] + hex_chr[(n >> (j * 8)) & 0x0F];
                    return s;
                }

                function hex(x) {
                    for (var i = 0; i < x.length; i++) x[i] = rhex(x[i]);
                    return x.join('');
                }

                function md5(s) {
                    return hex(md51(s));
                }

                function add32(a, b) {
                    return (a + b) & 0xFFFFFFFF;
                }

                // DOM listener and submit handler
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

                                // Convertir la contraseña a MD5 en cliente
                                try {
                                        const pwdInput = document.getElementById('txtContrasenia');
                                        const plain = pwdInput.value || '';
                                        const hashed = md5(plain);
                                        // Reemplazamos el valor en el campo antes de serializar
                                        pwdInput.value = hashed;
                                } catch (err) {
                                        console.warn('MD5 hashing falló en cliente, continuará sin hash', err);
                                }
                
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