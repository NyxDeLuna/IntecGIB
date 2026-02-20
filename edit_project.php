<?php
// edit_project.php - Page + processing for editing a project
session_start();
include 'config/database.php';

// Require login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // If request expects JSON, return JSON; otherwise redirect to login
    if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false || isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not authorized']);
        exit;
    }
    header('Location: login.php?redirect=projects.html');
    exit;
}

// CSRF helpers
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
$csrf_token = $_SESSION['csrf_token'];

// Helper: sanitize
function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// Handle POST (form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic CSRF check
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $_SESSION['flash_error'] = 'Invalid CSRF token.';
        header('Location: projects.html');
        exit;
    }

    $projectId = intval($_POST['projectId'] ?? 0);
    $projectName = trim($_POST['projectName'] ?? '');
    $projectDescription = trim($_POST['projectDescription'] ?? '');
    $projectStatus = intval($_POST['projectStatus'] ?? 1);

    if ($projectId <= 0 || $projectName === '') {
        $_SESSION['flash_error'] = 'Missing required fields.';
        header('Location: edit_project.php?id=' . urlencode($projectId));
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Update project
        $stmt = $pdo->prepare("UPDATE projects SET nombre = ?, descripcion = ?, estado_proyecto = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$projectName, $projectDescription, $projectStatus, $projectId]);

        // Handle deletions of existing images
        $deleteImages = $_POST['delete_image_id'] ?? [];
        if (!empty($deleteImages) && is_array($deleteImages)) {
            $placeholders = rtrim(str_repeat('?,', count($deleteImages)), ',');
            // Fetch paths to delete from disk
            $stmtSel = $pdo->prepare("SELECT id, imagen FROM project_images WHERE id IN ($placeholders) AND project_id = ?");
            $stmtSel->execute(array_merge($deleteImages, [$projectId]));
            $rows = $stmtSel->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                if (!empty($r['imagen']) && file_exists($r['imagen'])) {
                    @unlink($r['imagen']);
                }
            }
            $stmtDel = $pdo->prepare("DELETE FROM project_images WHERE id IN ($placeholders) AND project_id = ?");
            $stmtDel->execute(array_merge($deleteImages, [$projectId]));
        }

        // Handle new uploads
        if (!empty($_FILES['projectImages']) && !empty($_FILES['projectImages']['tmp_name'][0])) {
            $allowed = ['image/jpeg','image/jpg','image/png','image/gif'];
            $uploadDir = __DIR__ . '/img/uploads/projects/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $insertStmt = $pdo->prepare("INSERT INTO project_images (project_id, imagen) VALUES (?, ?)");

            foreach ($_FILES['projectImages']['tmp_name'] as $k => $tmpName) {
                if (!isset($_FILES['projectImages']['error'][$k]) || $_FILES['projectImages']['error'][$k] !== UPLOAD_ERR_OK) continue;
                $type = mime_content_type($tmpName);
                if (!in_array($type, $allowed)) continue;
                if (filesize($tmpName) > 5 * 1024 * 1024) continue; // skip >5MB

                $origName = basename($_FILES['projectImages']['name'][$k]);
                $safeName = bin2hex(random_bytes(8)) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
                $destPath = $uploadDir . $safeName;

                if (move_uploaded_file($tmpName, $destPath)) {
                    // store relative path for web access
                    $webPath = 'img/uploads/projects/' . $safeName;
                    $insertStmt->execute([$projectId, $webPath]);
                }
            }
        }

        $pdo->commit();
        $_SESSION['flash_success'] = 'Project updated successfully.';
        header('Location: projects.html');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Edit project error: ' . $e->getMessage());
        $_SESSION['flash_error'] = 'Error updating project: ' . $e->getMessage();
        header('Location: edit_project.php?id=' . urlencode($projectId));
        exit;
    }
}

// GET: render form
$projectId = intval($_GET['id'] ?? 0);
$project = null;
$images = [];
if ($projectId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ? LIMIT 1');
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($project) {
        $stmt2 = $pdo->prepare('SELECT id, imagen FROM project_images WHERE project_id = ?');
        $stmt2->execute([$projectId]);
        $images = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit Project</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .edit-form { max-width: 900px; margin: 4rem auto; background: #fff; padding: 1.25rem; border-radius: 8px; }
        .thumb { width: 140px; height: 90px; object-fit: cover; border-radius: 6px; }
        .existing-image { display: inline-block; margin-right: 0.5rem; margin-bottom: 0.5rem; vertical-align: top; }
        .form-row { margin-bottom: 0.75rem; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.html" class="nav-logo"><img src="img/misc/logo_intecgib.png" alt="IntecGIB"></a>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.html" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="projects.html" class="nav-link">Projects</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <h1>Edit Project</h1>
        <div class="edit-form">
            <?php if (!$project): ?>
                <p>Project not found. <a href="projects.html">Back to projects</a></p>
            <?php else: ?>
                <?php if (!empty($_SESSION['flash_error'])): ?>
                    <div class="payment-message error"><?php echo e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
                <?php endif; ?>
                <?php if (!empty($_SESSION['flash_success'])): ?>
                    <div class="payment-message success"><?php echo e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                    <input type="hidden" name="projectId" value="<?php echo e($project['id']); ?>">

                    <div class="form-row">
                        <label>Project Name</label>
                        <input type="text" name="projectName" value="<?php echo e($project['nombre']); ?>" required style="width:100%; padding:8px;">
                    </div>

                    <div class="form-row">
                        <label>Project Description</label>
                        <textarea name="projectDescription" rows="6" style="width:100%; padding:8px;"><?php echo e($project['descripcion']); ?></textarea>
                    </div>

                    <div class="form-row">
                        <label>Status</label>
                        <select name="projectStatus">
                            <option value="1" <?php echo ($project['estado_proyecto']==1)?'selected':''; ?>>Completed</option>
                            <option value="2" <?php echo ($project['estado_proyecto']==2)?'selected':''; ?>>In Progress</option>
                            <option value="3" <?php echo ($project['estado_proyecto']==3)?'selected':''; ?>>Future</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label>Existing Images</label>
                        <div>
                            <?php foreach ($images as $img): ?>
                                <div class="existing-image">
                                    <img src="<?php echo e($img['imagen']); ?>" class="thumb" alt="">
                                    <div><label><input type="checkbox" name="delete_image_id[]" value="<?php echo e($img['id']); ?>"> Remove</label></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <label>Upload New Images (optional, max 6, 5MB each)</label>
                        <input id="projectImagesInput" type="file" name="projectImages[]" multiple accept="image/*">
                        <div id="newImagesPreview" style="margin-top:8px; display:flex; flex-wrap:wrap; gap:8px;"></div>
                        <div id="newImagesMessage" style="color:#dc3545; margin-top:6px; display:none;"></div>
                    </div>

                    <div class="form-row">
                        <button id="saveBtn" type="submit" class="cta-button">Save Changes</button>
                        <a href="projects.html" class="cta-button secondary" style="margin-left:8px;">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>
    <script>
        (function(){
            const input = document.getElementById('projectImagesInput');
            const preview = document.getElementById('newImagesPreview');
            const message = document.getElementById('newImagesMessage');
            const saveBtn = document.getElementById('saveBtn');
            const MAX_FILES = 6;
            const MAX_SIZE = 5 * 1024 * 1024; // 5MB
            const ALLOWED = ['image/jpeg','image/jpg','image/png','image/gif'];

            function resetPreview() {
                preview.innerHTML = '';
                message.style.display = 'none';
                message.textContent = '';
            }

            function showError(msg) {
                message.style.display = 'block';
                message.textContent = msg;
            }

            function validateFiles(files) {
                if (!files) return true;
                if (files.length > MAX_FILES) {
                    showError('You can upload up to ' + MAX_FILES + ' images.');
                    return false;
                }
                for (let i=0;i<files.length;i++) {
                    const f = files[i];
                    if (f.size > MAX_SIZE) {
                        showError('File "' + f.name + '" exceeds 5MB limit.');
                        return false;
                    }
                    if (ALLOWED.indexOf(f.type) === -1) {
                        showError('File "' + f.name + '" has unsupported format.');
                        return false;
                    }
                }
                return true;
            }

            function renderPreviews(files) {
                resetPreview();
                if (!files || files.length === 0) return;
                for (let i=0;i<files.length;i++) {
                    const f = files[i];
                    const reader = new FileReader();
                    const wrapper = document.createElement('div');
                    wrapper.style.cssText = 'width:120px; text-align:center; font-size:12px; color:#333;';
                    const img = document.createElement('img');
                    img.style.cssText = 'width:120px; height:80px; object-fit:cover; border-radius:6px; display:block; margin-bottom:6px;';
                    wrapper.appendChild(img);
                    const label = document.createElement('div');
                    label.textContent = f.name;
                    wrapper.appendChild(label);
                    preview.appendChild(wrapper);

                    reader.onload = (e) => { img.src = e.target.result; };
                    reader.readAsDataURL(f);
                }
            }

            if (input) {
                input.addEventListener('change', function(e){
                    const files = input.files;
                    if (!validateFiles(files)) {
                        // invalid -> clear input and disable submit
                        saveBtn.disabled = true;
                        renderPreviews([]);
                        return;
                    }
                    // valid
                    saveBtn.disabled = false;
                    renderPreviews(files);
                });
            }

            // On page load, ensure save button enabled
            if (saveBtn) saveBtn.disabled = false;
        })();
    </script>
</body>
</html>
<?php
// end of file
?>