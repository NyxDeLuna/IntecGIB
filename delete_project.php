<?php
// delete_project.php - confirmation page + POST handler
session_start();
include 'config/database.php';

// Require login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: login.php?redirect=projects.html');
        exit;
}

// Ensure CSRF token exists
if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
$csrf = $_SESSION['csrf_token'];

// Helper to escape
function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
                $_SESSION['flash_error'] = 'Invalid CSRF token.';
                header('Location: projects.html');
                exit;
        }

        $projectId = intval($_POST['projectId'] ?? 0);
        if ($projectId <= 0) {
                $_SESSION['flash_error'] = 'Invalid project id.';
                header('Location: projects.html');
                exit;
        }

        try {
                $pdo->beginTransaction();

                // Get images to delete
                $stmt = $pdo->prepare('SELECT imagen FROM project_images WHERE project_id = ?');
                $stmt->execute([$projectId]);
                $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

                // Delete image files (paths may be relative)
                foreach ($images as $img) {
                        $path = __DIR__ . '/' . ltrim($img, '/');
                        if (file_exists($path)) {
                                @unlink($path);
                        }
                }

                // Delete image rows
                $stmt = $pdo->prepare('DELETE FROM project_images WHERE project_id = ?');
                $stmt->execute([$projectId]);

                // Delete project row
                $stmt = $pdo->prepare('DELETE FROM projects WHERE id = ?');
                $stmt->execute([$projectId]);

                $pdo->commit();
                $_SESSION['flash_success'] = 'Project deleted successfully.';
                header('Location: projects.html');
                exit;

        } catch (Exception $e) {
                $pdo->rollBack();
                error_log('Delete project error: ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Error deleting project: ' . $e->getMessage();
                header('Location: projects.html');
                exit;
        }

}

// GET: show confirmation
$projectId = intval($_GET['id'] ?? 0);
$project = null;
if ($projectId > 0) {
        $stmt = $pdo->prepare('SELECT id, nombre FROM projects WHERE id = ?');
        $stmt->execute([$projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Delete Project</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.html" class="nav-logo"><img src="img/misc/logo_intecgib.png" alt="IntecGIB"></a>
            <ul class="nav-menu">
                <li class="nav-item"><a href="projects.html" class="nav-link">Projects</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>
    <main class="container">
        <h1>Delete Project</h1>
        <div class="edit-form" style="padding:1rem;">
            <?php if (!$project): ?>
                <p>Project not found. <a href="projects.html">Back</a></p>
            <?php else: ?>
                <p>Are you sure you want to permanently delete the project:<br><strong><?php echo e($project['nombre']); ?></strong> ?</p>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo e($csrf); ?>">
                    <input type="hidden" name="projectId" value="<?php echo e($project['id']); ?>">
                    <button type="submit" class="cta-button" style="background:#dc3545;">Yes, delete project</button>
                    <a href="projects.html" class="cta-button secondary" style="margin-left:8px;">Cancel</a>
                </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
<?php
// EOF
?>