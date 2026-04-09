<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors',1);
$message='';
$token = $_GET['token'] ?? '';
if(!$token){header('Location: login.php');exit;}
$conn = mysqli_connect('localhost','root','','intecgib_db');
if(!$conn){die('DB error');}
mysqli_set_charset($conn,'utf8');
$stmt = mysqli_prepare($conn,'SELECT id,password_reset_expiry FROM users WHERE password_reset_token=?');
mysqli_stmt_bind_param($stmt,'s',$token);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if(mysqli_stmt_num_rows($stmt)===0){$message='Invalid token.';}
else{
    mysqli_stmt_bind_result($stmt,$userId,$expiry);
    mysqli_stmt_fetch($stmt);
    if(strtotime($expiry) < time()){
        $message='Token expired.';
    }
}
mysqli_stmt_close($stmt);

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['resetPassword'])){
    $newpass=$_POST['newPassword']??'';
    $confirm=$_POST['confirmPassword']??'';
    if($newpass!==$confirm){$message='Passwords do not match.';}
    elseif(strlen($newpass)<6){$message='Password must be at least 6 characters.';}
    else{
        $hash=md5($newpass);
        $upd=mysqli_prepare($conn,'UPDATE users SET pwd=?, password_reset_token=NULL, password_reset_expiry=NULL WHERE id=?');
        mysqli_stmt_bind_param($upd,'ss',$hash,$userId);
        if(mysqli_stmt_execute($upd)){
            $message='Password updated successfully. <a href="login.php">Login here</a>.';
        } else {
            $message='Error updating password.';
        }
        mysqli_stmt_close($upd);
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><title>Reset Password - IntecGIB</title><link rel="stylesheet" href="css/style.css"></head><body style="background:#eef2fb;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;"><div style="background:white;padding:25px;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.15);width:360px;"><h2>Reset Password</h2><p>Set a new password for your account.</p><?php if($message): ?> <div style="padding:10px;border-radius:8px;background:#f9f9f9;margin-bottom:10px;line-height:1.5;"><?php echo $message; ?></div> <?php endif; if(strpos($message,'updated successfully')===false): ?><form method="post"><label>New Password</label><input type="password" name="newPassword" style="width:100%;padding:10px;margin:8px 0;border:1px solid #ccc;border-radius:6px;" required><label>Confirm Password</label><input type="password" name="confirmPassword" style="width:100%;padding:10px;margin:8px 0;border:1px solid #ccc;border-radius:6px;" required><button type="submit" name="resetPassword" style="width:100%;padding:12px;background:#acd90c;border:none;border-radius:8px;color:#fff;font-weight:700;cursor:pointer;">Reset Password</button></form><?php endif;?><p style="margin-top:12px;text-align:center;"><a href="login.php">Back to login</a></p></div></body></html>