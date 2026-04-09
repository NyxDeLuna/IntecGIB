<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mensaje = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $id = trim($_POST['userId'] ?? '');
    $name = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirmPassword'] ?? '';
    $countryCode = trim($_POST['countryCode'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($id) || empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $mensaje = 'Please fill all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'Please provide a valid email address.';
    } elseif ($password !== $confirm) {
        $mensaje = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $mensaje = 'Password must be at least 6 characters.';
    } else {
        $conn = mysqli_connect('localhost', 'root', '', 'intecgib_db');
        if (!$conn) {
            $mensaje = 'Database connection failed.';
        } else {
            mysqli_set_charset($conn, 'utf8');
            $sql = 'SELECT id FROM users WHERE id = ? OR email = ?';
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ss', $id, $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $mensaje = 'User ID or email already exists.';
            } else {
                $hash = md5($password);
                $insert = 'INSERT INTO users (id, pwd, nombre_completo, country_code, phone, address, email, rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?)' ;
                $stmt2 = mysqli_prepare($conn, $insert);
                $role = 'user';
                mysqli_stmt_bind_param($stmt2, 'ssssssss', $id, $hash, $name, $countryCode, $phone, $address, $email, $role);
                if (mysqli_stmt_execute($stmt2)) {
                    $success = true;
                    $mensaje = 'Registration successful!';
                } else {
                    $mensaje = 'Registration failed. Please try again.';
                }
                mysqli_stmt_close($stmt2);
            }
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register - IntecGIB</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; margin:0; }
    .auth-container {background:#fff; padding:30px; border-radius:15px; width:360px; box-shadow:0 10px 30px rgba(0,0,0,0.2); color:#333;}
    .auth-container h1 {margin-bottom:20px; font-size:1.5rem;}
    .form-group {margin-bottom:12px;}
    .form-group label{display:block;margin-bottom:6px;font-weight:600;}
    .form-group input, .form-group select{width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;}
    .button{width:100%;padding:12px;background:#acd90c;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:700;margin-top:10px;}
    .button:hover{background:#95b908;}
    .message{padding:10px;border-radius:8px;margin-bottom:10px;}
    .error{background:#ffe6e6;color:#d63031;border:1px solid #d63031;}
    .success{background:#e6f7e6;color:#27ae60;border:1px solid #27ae60;}
    .link{display:block;text-align:center;margin-top:12px;color:#667eea;text-decoration:none;}
    .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center;}
    .modal.show{display:flex;}
    .modal-content{background:#fff;padding:40px;border-radius:15px;text-align:center;max-width:400px;box-shadow:0 10px 30px rgba(0,0,0,0.3);}
    .modal-content h2{margin:0 0 10px;color:#27ae60;}
    .modal-content p{margin:0;color:#666;}
    .success-icon{font-size:48px;margin-bottom:10px;}
    </style>
</head>
<body>
    <div class="auth-container">
        <h1>Create an Account</h1>
        <?php if ($mensaje): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        <?php if (!$success): ?>
        <form method="post" action="register.php">
            <div class="form-group"><label for="userId">Username</label><input type="text" id="userId" name="userId" required></div>
            <div class="form-group"><label for="fullName">Full Name</label><input type="text" id="fullName" name="fullName" required></div>
            <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
            <div class="form-group"><label for="password">Password</label><input type="password" id="password" name="password" required minlength="6"></div>
            <div class="form-group"><label for="confirmPassword">Confirm Password</label><input type="password" id="confirmPassword" name="confirmPassword" required minlength="6"></div>
            <div class="form-group"><label for="countryCode">Country Code</label><select id="countryCode" name="countryCode"><option value="+44">🇬🇧 +44</option><option value="+34">🇪🇸 +34</option><option value="+1">🇺🇸 +1</option></select></div>
            <div class="form-group"><label for="phone">Phone</label><input type="tel" id="phone" name="phone" required></div>
            <div class="form-group"><label for="address">Address</label><input type="text" id="address" name="address"></div>
            <button type="submit" name="register" class="button">Register</button>
        </form>
        <?php endif; ?>
        <a class="link" href="login.php">Already have an account? Login</a>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal <?php echo $success ? 'show' : ''; ?>">
        <div class="modal-content">
            <div class="success-icon">✅</div>
            <h2>Registration Successful!</h2>
            <p>Your account has been created. You will be redirected to login in 2 seconds.</p>
        </div>
    </div>

    <script>
        if (document.getElementById('successModal').classList.contains('show')) {
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 2000);
        }
    </script>
</body>
</html>
