<?php
require_once 'includes/functions.php';
$db = Database::getInstance();

if (!isset($_SESSION['client_session_id'])) {
    header('Location: index.php');
    exit;
}

$sessionId = $_SESSION['client_session_id'];
$session = $db->fetch("SELECT * FROM sessions WHERE id = ? AND is_active = 1", [$sessionId]);

if (!$session) {
    unset($_SESSION['client_session_id'], $_SESSION['client_access_code']);
    header('Location: index.php');
    exit;
}

$photos = $db->fetchAll(
    "SELECT * FROM photos WHERE session_id = ? ORDER BY sort_order ASC, id ASC",
    [$sessionId]
);

$downloadAllUrl = defined('DOWNLOAD_ALL_URL') ? trim(DOWNLOAD_ALL_URL) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($session['title']) ?> - <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="gallery-page">
    <header class="gallery-header">
        <div class="container">
            <div class="header-content">
                <div>
                    <h1 class="gallery-title"><?= sanitize($session['title']) ?></h1>
                    <p class="gallery-meta">
                        <span><?= sanitize($session['client_name']) ?></span>
                        <?php if ($session['session_date']): ?>
                            <span class="separator">&bull;</span>
                            <span><?= date('F j, Y', strtotime($session['session_date'])) ?></span>
                        <?php endif; ?>
                        <span class="separator">&bull;</span>
                        <span><?= count($photos) ?> photos</span>
                    </p>
                </div>
                <div class="header-actions">
                    <?php if ($session['downloads_enabled'] && count($photos) > 0 && $downloadAllUrl !== ''): ?>
                        <a href="<?= htmlspecialchars($downloadAllUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline" target="_blank" rel="noopener noreferrer">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Download All
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-ghost">Exit Gallery</a>
                </div>
            </div>
        </div>
    </header>

    <?php if ($session['description']): ?>
        <div class="container">
            <p class="gallery-description"><?= nl2br(sanitize($session['description'])) ?></p>
        </div>
    <?php endif; ?>

    <main class="container">
        <?php if (count($photos) === 0): ?>
            <div class="empty-state">
                <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                <h2>Photos are being prepared</h2>
                <p>Your photos will appear here soon. Please check back later.</p>
            </div>
        <?php else: ?>
            <div class="photo-grid">
                <?php foreach ($photos as $photo): ?>
                    <div class="photo-card" data-id="<?= $photo['id'] ?>">
                        <div class="photo-wrapper">
                            <img src="uploads/thumbnails/<?= sanitize($photo['filename']) ?>" 
                                 alt="Photo" loading="lazy"
                                 data-full="uploads/<?= sanitize($photo['filename']) ?>">
                            <div class="photo-overlay">
                                <button class="btn-icon" onclick="openLightbox(<?= $photo['id'] ?>)" title="View full size">
                                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                                </button>
                                <?php if ($session['downloads_enabled']): ?>
                                    <a href="download.php?id=<?= $photo['id'] ?>" class="btn-icon" title="Download">
                                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Lightbox -->
    <div id="lightbox" class="lightbox" style="display:none;">
        <div class="lightbox-backdrop" onclick="closeLightbox()"></div>
        <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
        <button class="lightbox-nav lightbox-prev" onclick="navigateLightbox(-1)">&#8249;</button>
        <button class="lightbox-nav lightbox-next" onclick="navigateLightbox(1)">&#8250;</button>
        <div class="lightbox-content">
            <img id="lightbox-img" src="" alt="">
        </div>
        <div class="lightbox-counter"><span id="lightbox-current">1</span> / <span id="lightbox-total"><?= count($photos) ?></span></div>
    </div>

    <footer class="gallery-footer">
        <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
    </footer>

    <script>
    const photos = <?= json_encode(array_values(array_map(function($p) {
        return ['id' => $p['id'], 'src' => 'uploads/' . $p['filename']];
    }, $photos))) ?>;
    let currentIndex = 0;

    function openLightbox(photoId) {
        currentIndex = photos.findIndex(p => p.id === photoId);
        if (currentIndex === -1) currentIndex = 0;
        updateLightbox();
        document.getElementById('lightbox').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = '';
    }

    function navigateLightbox(dir) {
        currentIndex = (currentIndex + dir + photos.length) % photos.length;
        updateLightbox();
    }

    function updateLightbox() {
        document.getElementById('lightbox-img').src = photos[currentIndex].src;
        document.getElementById('lightbox-current').textContent = currentIndex + 1;
    }

    document.addEventListener('keydown', function(e) {
        const lb = document.getElementById('lightbox');
        if (lb.style.display === 'none') return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') navigateLightbox(-1);
        if (e.key === 'ArrowRight') navigateLightbox(1);
    });
    </script>
</body>
</html>
