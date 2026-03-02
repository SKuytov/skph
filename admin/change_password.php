<?php
require_once '../includes/functions.php';
requireAdmin();
$db = Database::getInstance();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $user = $db->fetch("SELECT * FROM admin_users WHERE id = ?", [$_SESSION['admin_id']]);

    if (!password_verify($current, $user['password_hash'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $db->execute("UPDATE admin_users SET password_hash = ? WHERE id = ?", [$hash, $_SESSION['admin_id']]);
        $success = 'Password updated successfully.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?= SITE_NAME ?> Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <nav class="admin-nav">
        <div class="nav-brand"><?= SITE_NAME ?> <span>Admin</span></div>
        <div class="nav-links">
            <a href="index.php">Dashboard</a>
            <a href="session_create.php">+ New Session</a>
            <a href="change_password.php" class="active">Settings</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <main class="admin-main">
        <h1>Change Password</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitize($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= sanitize($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-card" style="max-width: 500px;">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </main>
</body>
</html>
