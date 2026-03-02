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

// Download all photos as ZIP
if (isset($_GET['all'])) {
    $photos = $db->fetchAll("SELECT * FROM photos WHERE session_id = ?", [$sessionId]);
    if (empty($photos)) {
        header('Location: gallery.php');
        exit;
    }

    $zipName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $session['title']) . '.zip';
    $zipPath = sys_get_temp_dir() . '/' . uniqid('gallery_') . '.zip';

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
        foreach ($photos as $photo) {
            $filepath = UPLOAD_DIR . $photo['filename'];
            if (file_exists($filepath)) {
                $zip->addFile($filepath, $photo['original_name']);
            }
        }
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipName . '"');
        header('Content-Length: ' . filesize($zipPath));
        header('Cache-Control: no-cache');
        readfile($zipPath);
        unlink($zipPath);
        exit;
    }

    header('Location: gallery.php');
    exit;
}

header('Location: gallery.php');
