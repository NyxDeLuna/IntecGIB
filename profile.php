<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors',1);
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    header('Location: login.php');
    exit;
}

$conn = mysqli_connect('localhost','root','','intecgib_db');
if(!$conn) die('DB connection failed');
mysqli_set_charset($conn,'utf8');
$userId = $_SESSION['user_id'];
$message='';

function resizeAvatarImage($sourcePath, $destinationPath, $mimeType, $maxWidth = 256, $maxHeight = 256, $jpegQuality = 85, $pngCompression = 6)
{
    $imageInfo = getimagesize($sourcePath);
    if($imageInfo === false){
        return false;
    }

    [$width, $height] = $imageInfo;
    $scale = min(1, $maxWidth / $width, $maxHeight / $height);
    $newWidth = max(1, (int) round($width * $scale));
    $newHeight = max(1, (int) round($height * $scale));

    if($mimeType === 'image/jpeg'){
        $src = imagecreatefromjpeg($sourcePath);
    } elseif($mimeType === 'image/png'){
        $src = imagecreatefrompng($sourcePath);
    } elseif($mimeType === 'image/gif'){
        $src = imagecreatefromgif($sourcePath);
    } else {
        return false;
    }

    if(!$src){
        return false;
    }

    if($scale === 1){
        if($mimeType === 'image/gif'){
            $result = copy($sourcePath, $destinationPath);
            imagedestroy($src);
            return $result;
        }
        if($mimeType === 'image/jpeg'){
            $result = imagejpeg($src, $destinationPath, $jpegQuality);
        } else {
            imagesavealpha($src, true);
            $result = imagepng($src, $destinationPath, $pngCompression);
        }
        imagedestroy($src);
        return $result;
    }

    $dst = imagecreatetruecolor($newWidth, $newHeight);
    if($mimeType === 'image/png' || $mimeType === 'image/gif'){
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagedestroy($src);

    if($mimeType === 'image/jpeg'){
        $result = imagejpeg($dst, $destinationPath, $jpegQuality);
    } elseif($mimeType === 'image/png'){
        $result = imagepng($dst, $destinationPath, $pngCompression);
    } else {
        $result = imagegif($dst, $destinationPath);
    }
    imagedestroy($dst);
    return $result;
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveProfile'])){
    $nombre = trim($_POST['nombre_completo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $country_code = trim($_POST['country_code'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if($nombre === '' || $email === ''){
        $message = 'Full name and email are required.';
    } else {
        // Handle avatar upload
        $avatarPath = null;
        if(isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK){
            $file = $_FILES['avatar'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            $maxDimensions = 5000;

            if(!in_array($file['type'], $allowedTypes)){
                $message = 'Only JPG, PNG and GIF files are allowed.';
            } elseif($file['size'] > $maxSize){
                $message = 'File size must be less than 2MB.';
            } else {
                // Validate image dimensions
                $imageInfo = getimagesize($file['tmp_name']);
                if($imageInfo === false){
                    $message = 'Invalid image file.';
                } elseif($imageInfo[0] > $maxDimensions || $imageInfo[1] > $maxDimensions){
                    $message = 'Image dimensions must be 5000x5000 pixels or smaller.';
                } else {
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $newFileName = $userId . '_' . time() . '.' . $extension;
                    $uploadPath = __DIR__ . '/img/avatars/' . $newFileName;
                    if(resizeAvatarImage($file['tmp_name'], $uploadPath, $imageInfo['mime'])){
                        $avatarPath = 'img/avatars/' . $newFileName;

                        // Delete old avatar if exists
                        if(!empty($currentAvatar) && file_exists(__DIR__ . '/' . $currentAvatar)){
                            unlink(__DIR__ . '/' . $currentAvatar);
                        }
                    } else {
                        $message = 'Error uploading avatar.';
                    }
                }
            }
        }

        if(empty($message)){
            $stmt = mysqli_prepare($conn, "UPDATE users SET nombre_completo=?, email=?, country_code=?, phone=?, address=?, avatar=? WHERE id=?");
            $avatarValue = $avatarPath ?? $currentAvatar;
            mysqli_stmt_bind_param($stmt, 'sssssss', $nombre, $email, $country_code, $phone, $address, $avatarValue, $userId);
            if(mysqli_stmt_execute($stmt)){
                $message = 'Profile updated successfully.';
                $_SESSION['user_name'] = $nombre;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_country_code'] = $country_code;
                $_SESSION['user_phone'] = $phone;
                $_SESSION['user_address'] = $address;
                if($avatarPath) $_SESSION['user_avatar'] = $avatarPath;
            } else {
                $message = 'Error updating profile.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

$stmt = mysqli_prepare($conn, "SELECT nombre_completo, email, country_code, phone, address, rol, avatar FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt,'s',$userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt,$nombre,$email,$country_code,$phone,$address,$rol,$currentAvatar);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Profile - IntecGIB</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body{background:#f0f4fb;margin:0;padding:0;font-family:Arial, sans-serif;}
        .profile-wrap{max-width:700px;margin:40px auto;background:#fff;padding:30px;border-radius:12px;box-shadow:0 12px 24px rgba(0,0,0,.1);}
        .profile-wrap h1{margin:0 0 1rem;color:#333;}
        .profile-wrap label{font-weight:600;margin-top:12px;display:block;color:#444;}
        .profile-wrap input, .profile-wrap select{width:100%;padding:10px;margin-top:6px;border:1px solid #ccc;border-radius:8px;}
        .profile-wrap button{margin-top:16px;background:#0a74da;color:#fff;border:none;padding:12px 20px;border-radius:8px;cursor:pointer;}
        .profile-wrap .links{margin-top:20px;display:flex;gap:12px;}
        .profile-wrap .links a{color:#0a74da;text-decoration:none;font-weight:600;}
        .message{margin:12px 0;padding:10px;border-radius:8px;background:#eaf5ff;color:#084f99;}
        .avatar-section{display:flex;align-items:center;gap:20px;margin-bottom:20px;}
        .avatar-preview{width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid #ddd;}
        .avatar-upload{flex:1;}
        .avatar-upload input[type="file"]{margin-top:6px;}
        .current-avatar{margin-top:10px;font-size:14px;color:#666;}
    </style>
</head>
<body>
    <div class="profile-wrap">
        <h1>My Profile</h1>
        <?php if($message): ?><div class="message"><?php echo htmlspecialchars($message ?? ''); ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="avatar-section">
                <div>
                    <?php if($currentAvatar && file_exists(__DIR__ . '/' . $currentAvatar)): ?>
                        <img src="<?php echo htmlspecialchars($currentAvatar ?? ''); ?>" alt="Current avatar" class="avatar-preview">
                    <?php else: ?>
                        <div class="avatar-preview" style="background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#999;font-size:24px;">👤</div>
                    <?php endif; ?>
                </div>
                <div class="avatar-upload">
                    <label>Profile Picture</label>
                    <input type="file" name="avatar" accept="image/*">
                    <div class="current-avatar">
                        <?php if($currentAvatar): ?>
                            Current: <?php echo htmlspecialchars(basename($currentAvatar) ?? ''); ?>
                        <?php else: ?>
                            No avatar uploaded
                        <?php endif; ?>
                        <br><small>Max 256x256px, 2MB, JPG/PNG/GIF</small>
                    </div>
                </div>
            </div>

            <label>Username</label>
            <input type="text" value="<?php echo htmlspecialchars($userId ?? ''); ?>" disabled>
            <label>Role</label>
            <input type="text" value="<?php echo htmlspecialchars($rol ?? ''); ?>" disabled>
            <label>Full Name</label>
            <input type="text" name="nombre_completo" value="<?php echo htmlspecialchars($nombre ?? ''); ?>" placeholder="Insert your data here..." required>
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="Insert your data here..." required>
            <label>Country Code</label>
            <input type="text" name="country_code" value="<?php echo htmlspecialchars($country_code ?? ''); ?>" placeholder="e.g. +34">
            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" placeholder="540 12345">
            <label>Address</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($address ?? ''); ?>" placeholder="Street, city, country">
            <button type="submit" name="saveProfile">Save Changes</button>
        </form>
        <div class="links">
            <a href="admin.php">Admin Panel</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>