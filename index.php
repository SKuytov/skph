<?php
require_once 'includes/functions.php';
$error = '';
$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['access_code'])) {
    $code = strtoupper(trim($_POST['access_code']));
    $session = $db->fetch(
        "SELECT * FROM sessions WHERE access_code = ? AND is_active = 1",
        [$code]
    );
    if ($session) {
        // Log access
        $db->insert(
            "INSERT INTO access_logs (session_id, ip_address, user_agent) VALUES (?, ?, ?)",
            [$session['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '']
        );
        $_SESSION['client_session_id'] = $session['id'];
        $_SESSION['client_access_code'] = $code;
        header('Location: gallery.php');
        exit;
    }
    $error = 'Invalid access code. Please check and try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Client Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="client-access-page">
    <div class="access-container">
        <div class="access-card">
            <div class="logo-section">
                <h1 class="brand-name"><?= SITE_NAME ?></h1>
                <p class="brand-tagline">Client Photo Portal</p>
            </div>
            <div class="divider"></div>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= sanitize($error) ?></div>
            <?php endif; ?>
            <form method="POST" class="access-form">
                <div class="form-group">
                    <label for="access_code">Enter Your Access Code</label>
                    <input type="text" id="access_code" name="access_code" 
                           placeholder="e.g. AB3XK7YZ" required
                           autocomplete="off" maxlength="20"
                           style="text-transform: uppercase; letter-spacing: 3px; text-align: center; font-size: 1.3rem;">
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    View My Photos
                </button>
            </form>
            <p class="access-hint">Your photographer provided you with a unique access code to view your photos.</p>
        </div>
    </div>
</body>
</html>
