<?php
require_once '../includes/functions.php';
requireAdmin();
$db = Database::getInstance();

$id = (int)($_GET['id'] ?? 0);
$session = $db->fetch("SELECT * FROM sessions WHERE id = ?", [$id]);
if (!$session) { header('Location: index.php'); exit; }

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos'])) {
    // Create directories if needed
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    if (!is_dir(THUMB_DIR)) mkdir(THUMB_DIR, 0755, true);

    $uploaded = 0;
    $errors = [];
    $files = $_FILES['photos'];

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

        $originalName = $files['name'][$i];
        $tmpName = $files['tmp_name'][$i];
        $size = $files['size'][$i];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($ext, ALLOWED_EXTENSIONS)) {
            $errors[] = "$originalName: unsupported format.";
            continue;
        }
        if ($size > MAX_UPLOAD_SIZE) {
            $errors[] = "$originalName: file too large.";
            continue;
        }

        $filename = uniqid('photo_') . '_' . time() . '.' . $ext;
        $destination = UPLOAD_DIR . $filename;

        if (move_uploaded_file($tmpName, $destination)) {
            // Get dimensions
            $imgInfo = getimagesize($destination);
            $width = $imgInfo[0] ?? 0;
            $height = $imgInfo[1] ?? 0;

            // Create thumbnail
            createThumbnail($destination, THUMB_DIR . $filename);

            $maxOrder = $db->fetch("SELECT MAX(sort_order) as m FROM photos WHERE session_id = ?", [$id])['m'] ?? 0;

            $db->insert(
                "INSERT INTO photos (session_id, filename, original_name, file_size, width, height, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$id, $filename, $originalName, $size, $width, $height, $maxOrder + 1]
            );
            $uploaded++;
        }
    }

    $msg = "$uploaded photo(s) uploaded successfully.";
    if (!empty($errors)) $msg .= ' Errors: ' . implode(', ', $errors);
    flashMessage($msg, empty($errors) ? 'success' : 'warning');
    header("Location: session_photos.php?id=$id");
    exit;
}

// Handle photo deletion
if (isset($_GET['delete_photo'])) {
    $photoId = (int)$_GET['delete_photo'];
    $photo = $db->fetch("SELECT * FROM photos WHERE id = ? AND session_id = ?", [$photoId, $id]);
    if ($photo) {
        @unlink(UPLOAD_DIR . $photo['filename']);
        @unlink(THUMB_DIR . $photo['filename']);
        $db->execute("DELETE FROM photos WHERE id = ?", [$photoId]);
        flashMessage('Photo deleted.');
    }
    header("Location: session_photos.php?id=$id");
    exit;
}

$photos = $db->fetchAll("SELECT * FROM photos WHERE session_id = ? ORDER BY sort_order ASC, id ASC", [$id]);
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photos - <?= sanitize($session['title']) ?> - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <nav class="admin-nav">
        <div class="nav-brand"><?= SITE_NAME ?> <span>Admin</span></div>
        <div class="nav-links">
            <a href="index.php">Dashboard</a>
            <a href="session_create.php">+ New Session</a>
            <a href="change_password.php">Settings</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <main class="admin-main">
        <div class="section-header">
            <div>
                <h1>Photos: <?= sanitize($session['title']) ?></h1>
                <p class="text-muted"><?= sanitize($session['client_name']) ?> &bull; Access Code: <code class="access-code"><?= sanitize($session['access_code']) ?></code></p>
            </div>
            <a href="session_edit.php?id=<?= $id ?>" class="btn btn-outline">Edit Session</a>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="upload-zone" id="uploadZone">
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="upload-content">
                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <p><strong>Drag &amp; drop photos here</strong> or click to browse</p>
                    <p class="text-muted">JPG, PNG, WebP &bull; Max 20MB per file</p>
                    <input type="file" name="photos[]" id="photoInput" multiple accept=".jpg,.jpeg,.png,.webp" style="display:none">
                </div>
            </form>
            <div id="uploadProgress" style="display:none;">
                <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
                <p id="uploadStatus">Uploading...</p>
            </div>
        </div>

        <!-- Photo Grid -->
        <?php if (!empty($photos)): ?>
            <div class="admin-photo-grid">
                <?php foreach ($photos as $photo): ?>
                    <div class="admin-photo-card">
                        <img src="../uploads/thumbnails/<?= sanitize($photo['filename']) ?>" alt="">
                        <div class="photo-info">
                            <small><?= sanitize($photo['original_name']) ?></small>
                            <small><?= formatFileSize($photo['file_size']) ?> &bull; <?= $photo['width'] ?>x<?= $photo['height'] ?></small>
                        </div>
                        <a href="?id=<?= $id ?>&delete_photo=<?= $photo['id'] ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Delete this photo?')">Delete</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No photos yet. Upload some photos above!</p>
            </div>
        <?php endif; ?>
    </main>

    <script>
    const zone = document.getElementById('uploadZone');
    const form = document.getElementById('uploadForm');
    const input = document.getElementById('photoInput');

    zone.addEventListener('click', () => input.click());
    zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', (e) => {
        e.preventDefault();
        zone.classList.remove('drag-over');
        input.files = e.dataTransfer.files;
        form.submit();
    });
    input.addEventListener('change', () => { if (input.files.length > 0) form.submit(); });
    </script>
</body>
</html>
