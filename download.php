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

// Download all photos: redirect to external URL if configured
if (isset($_GET['all'])) {
    // Check if external download URL is configured
    if (!empty($session['external_download_url'])) {
        header('Location: ' . $session['external_download_url']);
        exit;
    }

    // Fallback: Generate ZIP file (old behavior)
    if (!class_exists('ZipArchive')) {
        header('Location: gallery.php?error=zip_not_supported');
        exit;
    }

    $photos = $db->fetchAll(
        "SELECT * FROM photos WHERE session_id = ?",
        [$sessionId]
    );

    if (empty($photos)) {
        header('Location: gallery.php');
        exit;
    }

    $zip = new ZipArchive();
    $zipName = tempnam(sys_get_temp_dir(), 'photos_') . '.zip';

    if ($zip->open($zipName, ZipArchive::CREATE) !== TRUE) {
        header('Location: gallery.php?error=zip_failed');
        exit;
    }

    foreach ($photos as $photo) {
        $filepath = UPLOAD_DIR . $photo['filename'];
        if (file_exists($filepath)) {
            $zip->addFile($filepath, $photo['original_name']);
        }
    }

    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . sanitize($session['title']) . '_photos.zip"');
    header('Content-Length: ' . filesize($zipName));
    header('Cache-Control: no-cache');
    readfile($zipName);
    unlink($zipName);
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

header('Location: gallery.php');
