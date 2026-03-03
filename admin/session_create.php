<?php
require_once '../includes/functions.php';
requireAdmin();
$db = Database::getInstance();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $clientName = trim($_POST['client_name'] ?? '');
    $clientEmail = trim($_POST['client_email'] ?? '');
    $sessionDate = $_POST['session_date'] ?? null;
    $description = trim($_POST['description'] ?? '');
    $downloadsEnabled = isset($_POST['downloads_enabled']) ? 1 : 0;
    $externalDownloadUrl = trim($_POST['external_download_url'] ?? '');

    if (empty($title) || empty($clientName)) {
        $error = 'Title and client name are required.';
    } else {
        // Generate unique access code
        do {
            $accessCode = generateAccessCode();
            $exists = $db->fetch("SELECT id FROM sessions WHERE access_code = ?", [$accessCode]);
        } while ($exists);

        $db->insert(
            "INSERT INTO sessions (title, client_name, client_email, access_code, session_date, description, downloads_enabled, external_download_url) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$title, $clientName, $clientEmail ?: null, $accessCode, $sessionDate ?: null, $description ?: null, $downloadsEnabled, $externalDownloadUrl ?: null]
        );

        flashMessage("Session created! Access code: <strong>$accessCode</strong>");
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Session - <?= SITE_NAME ?> Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <nav class="admin-nav">
        <div class="nav-brand"><?= SITE_NAME ?> <span>Admin</span></div>
        <div class="nav-links">
            <a href="index.php">Dashboard</a>
            <a href="session_create.php" class="active">+ New Session</a>
            <a href="change_password.php">Settings</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <main class="admin-main">
        <h1>Create New Photo Session</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitize($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-card">
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Session Title *</label>
                    <input type="text" id="title" name="title" required 
                           placeholder="e.g. Wedding - Maria & Ivan" 
                           value="<?= sanitize($_POST['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="session_date">Session Date</label>
                    <input type="date" id="session_date" name="session_date" 
                           value="<?= sanitize($_POST['session_date'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="client_name">Client Name *</label>
                    <input type="text" id="client_name" name="client_name" required
                           placeholder="e.g. Maria Petrova"
                           value="<?= sanitize($_POST['client_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="client_email">Client Email</label>
                    <input type="email" id="client_email" name="client_email"
                           placeholder="e.g. maria@example.com"
                           value="<?= sanitize($_POST['client_email'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description (visible to client)</label>
                <textarea id="description" name="description" rows="3" 
                          placeholder="Optional note for the client..."><?= sanitize($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="external_download_url">Custom "Download All" URL (optional)</label>
                <input type="url" id="external_download_url" name="external_download_url"
                       placeholder="https://drive.google.com/... or https://onedrive.live.com/..."
                       value="<?= sanitize($_POST['external_download_url'] ?? '') ?>">
                <small class="text-muted" style="display: block; margin-top: 4px; color: #6b7280;">If set, the "Download All" button will open this link instead of generating a ZIP file.</small>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="downloads_enabled" checked>
                    Allow clients to download photos
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Session</button>
                <a href="index.php" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </main>
</body>
</html>
