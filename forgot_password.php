<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors',1);
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['sendReset'])){
    $email=trim($_POST['email'] ?? '');
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $msg='Please enter a valid email';
    } else {
        $conn=mysqli_connect('localhost','root','','intecgib_db');
        if(!$conn){ $msg='Database connection failed.'; }
        else {
            mysqli_set_charset($conn,'utf8');
            $stmt=mysqli_prepare($conn,'SELECT id FROM users WHERE email=?');
            mysqli_stmt_bind_param($stmt,'s',$email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt)==0){
                $msg='Email not found.';
            } else {
                mysqli_stmt_bind_result($stmt,$userId);
                mysqli_stmt_fetch($stmt);
                $token=bin2hex(random_bytes(20));
                $expiry=date('Y-m-d H:i:s',strtotime('+1 hour'));
                $upd=mysqli_prepare($conn,'UPDATE users SET password_reset_token=?, password_reset_expiry=? WHERE id=?');
                mysqli_stmt_bind_param($upd,'sss',$token,$expiry,$userId);
                mysqli_stmt_execute($upd);
                // En modo local, mostramos link en pantalla. En prod enviar email.
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $resetLink = "$scheme://$host$path/reset_password.php?token=$token";
                $msg = 'Password reset link: <a href="' . htmlspecialchars($resetLink) . '">' . htmlspecialchars($resetLink) . '</a> (valid for 1h)';
                $msg = 'Password reset link: <a href="' . htmlspecialchars($resetUrl) . '">' . htmlspecialchars($resetUrl) . '</a> (valid for 1h)';
            }
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><title>Forgot Password - IntecGIB</title><link rel="stylesheet" href="css/style.css"></head><body style="background:#eef2fb;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;"><div style="background:white;padding:25px;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.15);width:360px;"><h2>Forgot Password</h2><p>Enter your email to receive a password reset link.</p><?php if($msg): ?> <div style="padding:10px;border-radius:8px;background:#f9f9f9;margin-bottom:10px;line-height:1.5;"><?php echo $msg; ?></div> <?php endif; ?><form method="post"><label>Email</label><input type="email" name="email" style="width:100%;padding:10px;margin:8px 0;border:1px solid #ccc;border-radius:6px;" required><button type="submit" name="sendReset" style="width:100%;padding:12px;background:#acd90c;border:none;border-radius:8px;color:#fff;font-weight:700;cursor:pointer;">Send Reset Link</button></form><p style="margin-top:12px;text-align:center;"><a href="login.php">Back to login</a></p></div></body></html>