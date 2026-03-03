<?php
require_once 'includes/functions.php';
$db = Database::getInstance();

if (!isset($_SESSION['client_session_id'])) {
    header('Location: index.php');
    exit;
}

$sessionId = $_SESSION['client_session_id'];
$session = $db->fetch("SELECT * FROM sessions WHERE id = ? AND is_active = 1 AND downloads_enabled = 1", [$sessionId]);

if (!$session) {
    header('Location: gallery.php');
    exit;
}

// Download single photo
if (isset($_GET['id'])) {
    $photo = $db->fetch(
        "SELECT * FROM photos WHERE id = ? AND session_id = ?",
        [(int)$_GET['id'], $sessionId]
    );
    if ($photo) {
        $filepath = UPLOAD_DIR . $photo['filename'];
        if (file_exists($filepath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $photo['original_name'] . '"');
            header('Content-Length: ' . filesize($filepath));
            header('Cache-Control: no-cache');
            readfile($filepath);
            exit;
        }
    }
    header('Location: gallery.php');
    exit;
}

// Download all photos: redirect to OneDrive/SharePoint URL (no ZIP creation)
if (isset($_GET['all'])) {
    $downloadAllUrl = defined('DOWNLOAD_ALL_URL') ? trim(DOWNLOAD_ALL_URL) : '';
    if ($downloadAllUrl !== '') {
        header('Location: ' . $downloadAllUrl);
        exit;
    }

    header('Location: gallery.php');
    exit;
}

header('Location: gallery.php');
