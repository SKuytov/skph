<?php
require_once __DIR__ . '/Database.php';

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function generateAccessCode($length = 8) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function createThumbnail($source, $destination, $maxWidth = THUMB_WIDTH, $maxHeight = THUMB_HEIGHT) {
    $info = getimagesize($source);
    if (!$info) return false;

    $mime = $info['mime'];
    switch ($mime) {
        case 'image/jpeg': $img = imagecreatefromjpeg($source); break;
        case 'image/png':  $img = imagecreatefrompng($source); break;
        case 'image/webp': $img = imagecreatefromwebp($source); break;
        default: return false;
    }

    $origW = imagesx($img);
    $origH = imagesy($img);
    $ratio = min($maxWidth / $origW, $maxHeight / $origH);
    $newW = (int)($origW * $ratio);
    $newH = (int)($origH * $ratio);

    $thumb = imagecreatetruecolor($newW, $newH);

    if ($mime === 'image/png') {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }

    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

    switch ($mime) {
        case 'image/jpeg': imagejpeg($thumb, $destination, 85); break;
        case 'image/png':  imagepng($thumb, $destination, 8); break;
        case 'image/webp': imagewebp($thumb, $destination, 85); break;
    }

    imagedestroy($img);
    imagedestroy($thumb);
    return true;
}

function formatFileSize($bytes) {
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

function flashMessage($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
