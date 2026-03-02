<?php
require_once '../includes/functions.php';
requireAdmin();
$db = Database::getInstance();

$id = (int)($_GET['id'] ?? 0);
$session = $db->fetch("SELECT * FROM sessions WHERE id = ?", [$id]);
if (!$session) { header('Location: index.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $clientName = trim($_POST['client_name'] ?? '');
    $clientEmail = trim($_POST['client_email'] ?? '');
    $sessionDate = $_POST['session_date'] ?? null;
    $description = trim($_POST['description'] ?? '');
    $downloadsEnabled = isset($_POST['downloads_enabled']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (empty($title) || empty($clientName)) {
        $error = 'Title and client name are required.';
    } else {
        $db->execute(
            "UPDATE sessions SET title=?, client_name=?, client_email=?, session_date=?, description=?, downloads_enabled=?, is_active=? WHERE id=?",
            [$title, $clientName, $clientEmail ?: null, $sessionDate ?: null, $description ?: null, $downloadsEnabled, $isActive, $id]
        );
        flashMessage('Session updated successfully.');
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
    <title>Edit Session - <?= SITE_NAME ?> Admin</title>
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
        <h1>Edit: <?= sanitize($session['title']) ?></h1>
        <p class="text-muted">Access Code: <code class="access-code"><?= sanitize($session['access_code']) ?></code></p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitize($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-card">
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Session Title *</label>
                    <input type="text" id="title" name="title" required value="<?= sanitize($session['title']) ?>">
                </div>
                <div class="form-group">
                    <label for="session_date">Session Date</label>
                    <input type="date" id="session_date" name="session_date" value="<?= $session['session_date'] ?? '' ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="client_name">Client Name *</label>
                    <input type="text" id="client_name" name="client_name" required value="<?= sanitize($session['client_name']) ?>">
                </div>
                <div class="form-group">
                    <label for="client_email">Client Email</label>
                    <input type="email" id="client_email" name="client_email" value="<?= sanitize($session['client_email'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"><?= sanitize($session['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="downloads_enabled" <?= $session['downloads_enabled'] ? 'checked' : '' ?>>
                    Allow downloads
                </label>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" <?= $session['is_active'] ? 'checked' : '' ?>>
                    Gallery is active (visible to client)
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="session_photos.php?id=<?= $id ?>" class="btn btn-outline">Manage Photos</a>
                <a href="index.php" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </main>
</body>
</html>
