<?php
require_once '../includes/functions.php';
requireAdmin();
$db = Database::getInstance();

$stats = [
    'total_sessions' => $db->fetch("SELECT COUNT(*) as c FROM sessions")['c'],
    'active_sessions' => $db->fetch("SELECT COUNT(*) as c FROM sessions WHERE is_active = 1")['c'],
    'total_photos' => $db->fetch("SELECT COUNT(*) as c FROM photos")['c'],
    'total_views' => $db->fetch("SELECT COUNT(*) as c FROM access_logs")['c'],
];

$sessions = $db->fetchAll(
    "SELECT s.*, COUNT(p.id) as photo_count, 
     (SELECT COUNT(*) FROM access_logs WHERE session_id = s.id) as view_count
     FROM sessions s 
     LEFT JOIN photos p ON p.session_id = s.id 
     GROUP BY s.id 
     ORDER BY s.created_at DESC"
);

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= SITE_NAME ?> Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <nav class="admin-nav">
        <div class="nav-brand"><?= SITE_NAME ?> <span>Admin</span></div>
        <div class="nav-links">
            <a href="index.php" class="active">Dashboard</a>
            <a href="session_create.php">+ New Session</a>
            <a href="change_password.php">Settings</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <main class="admin-main">
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['message'] ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_sessions'] ?></div>
                <div class="stat-label">Total Sessions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['active_sessions'] ?></div>
                <div class="stat-label">Active Sessions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_photos'] ?></div>
                <div class="stat-label">Total Photos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_views'] ?></div>
                <div class="stat-label">Gallery Views</div>
            </div>
        </div>

        <div class="section-header">
            <h2>Photo Sessions</h2>
            <a href="session_create.php" class="btn btn-primary">+ New Session</a>
        </div>

        <?php if (empty($sessions)): ?>
            <div class="empty-state">
                <p>No sessions yet. Create your first photo session!</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Session</th>
                            <th>Client</th>
                            <th>Access Code</th>
                            <th>Photos</th>
                            <th>Views</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sessions as $s): ?>
                        <tr>
                            <td>
                                <strong><?= sanitize($s['title']) ?></strong>
                                <?php if ($s['session_date']): ?>
                                    <br><small><?= date('M j, Y', strtotime($s['session_date'])) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= sanitize($s['client_name']) ?></td>
                            <td><code class="access-code"><?= sanitize($s['access_code']) ?></code></td>
                            <td><?= $s['photo_count'] ?></td>
                            <td><?= $s['view_count'] ?></td>
                            <td>
                                <span class="badge <?= $s['is_active'] ? 'badge-success' : 'badge-muted' ?>">
                                    <?= $s['is_active'] ? 'Active' : 'Disabled' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="session_edit.php?id=<?= $s['id'] ?>" class="btn btn-sm">Edit</a>
                                <a href="session_photos.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline">Photos</a>
                                <a href="session_delete.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Delete this session and all photos?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
