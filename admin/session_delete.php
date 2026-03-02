<?php
require_once '../includes/functions.php';
requireAdmin();
$db = Database::getInstance();

$id = (int)($_GET['id'] ?? 0);
$session = $db->fetch("SELECT * FROM sessions WHERE id = ?", [$id]);

if ($session) {
    // Delete photo files
    $photos = $db->fetchAll("SELECT filename FROM photos WHERE session_id = ?", [$id]);
    foreach ($photos as $photo) {
        @unlink(UPLOAD_DIR . $photo['filename']);
        @unlink(THUMB_DIR . $photo['filename']);
    }
    $db->execute("DELETE FROM sessions WHERE id = ?", [$id]);
    flashMessage('Session deleted successfully.');
}

header('Location: index.php');
exit;
