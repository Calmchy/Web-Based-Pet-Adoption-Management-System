<?php
ob_start();
define('APP_RUNNING', true);
require_once "../includes/config.php";
require_once "../includes/auth.php";

require_role('admin');

// ── Stats ──────────────────────────────────────────────────────────────────
$stats = [
    'total_pets' => 0,
    'available_pets' => 0,
    'total_users' => 0,
    'total_apps' => 0,
    'pending_apps' => 0,
    'approved_apps'  => 0,
];

$q = $conn->query("SELECT COUNT(*) as c FROM pets");
if ($q) $stats['total_pets'] = $q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) as c FROM pets WHERE status = 'available'");
if ($q) $stats['available_pets'] = $q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'adopter'");
if ($q) $stats['total_users'] = $q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) as c FROM applications");
if ($q) $stats['total_apps'] = $q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) as c FROM applications WHERE status = 'pending'");
if ($q) $stats['pending_apps'] = $q->fetch_assoc()['c'];

$q = $conn->query("SELECT COUNT(*) as c FROM applications WHERE status = 'approved'");
if ($q) $stats['approved_apps'] = $q->fetch_assoc()['c'];

// ── Recent Applications ────────────────────────────────────────────────────
$recent_apps = [];
$q = $conn->query("
    SELECT a.application_id, a.status, a.applied_at, CONCAT(u.first_name, ' ',
    u.last_name) AS applicant, p.name AS pet_name
    FROM applications a
    JOIN users u ON u.user_id = a.user_id
    JOIN pets p ON p.pet_id  = a.pet_id
    ORDER BY a.applied_at DESC
    LIMIT 8
");
if ($q) $recent_apps = $q->fetch_all(MYSQLI_ASSOC);

// ── Recent Pets ────────────────────────────────────────────────────────────
$recent_pets = [];
$q = $conn->query("
    SELECT p.pet_id, p.name, p.gender, p.status, p.created_at, b.breed_name, c.category_name
    FROM pets p
    LEFT JOIN breeds b ON b.breed_id = p.breed_id
    LEFT JOIN categories c ON c.category_id = b.category_id
    ORDER BY p.created_at DESC
    LIMIT 6
");
if ($q) $recent_pets = $q->fetch_all(MYSQLI_ASSOC);

$admin_name = ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '');
$admin_init = strtoupper(substr($_SESSION['first_name'] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — AdoptME 🐾</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="shortcut icon" href="../assets/images/logo.png" type="image/x-icon">
</head>
<body>
<div class="admin-layout">
    <?php include "includes/sidebar.php"; ?>
    <!-- Main -->
    <div class="admin-main">
        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar"><span></span><span></span><span></span></button>
                <h1>Dashboard</h1>
                <p>Welcome back, <?= htmlspecialchars($_SESSION['first_name'] ?? 'Admin') ?> 👋</p>
            </div>
            <div class="topbar-right">
                <div class="notif-wrapper" style="position:relative;">
                    <button class="notif-bell" id="adminNotifBtn" title="Notifications">🔔
                        <?php
                        $nq = $conn->prepare("SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0");
                        $nq->bind_param("i", $_SESSION['user_id']);
                        $nq->execute();
                        $nc = $nq->get_result()->fetch_assoc()['c'];
                        $nq->close();
                        if ($nc > 0): ?>
                            <span class="notif-badge"><?= $nc ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notif-dropdown" id="adminNotifDropdown">
                        <div class="notif-header">
                            <h4>🔔 Notifications</h4>
                            <button class="notif-mark-read" id="adminMarkAllRead">Mark all read</button>
                        </div>
                        <div class="notif-list" id="adminNotifList">
                        <?php
                        $nq2 = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
                        $nq2->bind_param("i", $_SESSION['user_id']);
                        $nq2->execute();
                        $notifs = $nq2->get_result()->fetch_all(MYSQLI_ASSOC);
                        $nq2->close();
                        if (empty($notifs)): ?>
                            <div class="notif-empty">No notifications yet</div>
                        <?php else: foreach ($notifs as $n): ?>
                            <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                                <span class="notif-icon">🐾</span>
                                <div class="notif-text">
                                    <p><?= htmlspecialchars($n['message']) ?></p>
                                    <span><?= date('M d, g:i A', strtotime($n['created_at'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>
                <button class="theme-toggle" id="themeToggle">🌙 Dark</button>
                <a href="../actions/logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </div>
        <!-- Page Content -->
        <div class="page-content">
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon orange">🐕</div>
                    <div class="stat-info">
                        <h3><?= $stats['total_pets'] ?></h3>
                        <p>Total Pets</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">✅</div>
                    <div class="stat-info">
                        <h3><?= $stats['available_pets'] ?></h3>
                        <p>Available Pets</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">👥</div>
                    <div class="stat-info">
                        <h3><?= $stats['total_users'] ?></h3>
                        <p>Registered Adopters</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">⏳</div>
                    <div class="stat-info">
                        <h3><?= $stats['pending_apps'] ?></h3>
                        <p>Pending Applications</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">🎉</div>
                    <div class="stat-info">
                        <h3><?= $stats['approved_apps'] ?></h3>
                        <p>Approved Applications</p>
                    </div>
                </div>
            </div>
            <!-- Tables Grid -->
            <div class="content-grid">
                <!-- Recent Applications -->
                <div class="panel">
                    <div class="panel-header">
                        <h3>📋 Recent Applications</h3>
                        <a href="applications.php">View all →</a>
                    </div>
                    <?php if (empty($recent_apps)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📭</div>
                            <p>No applications yet.</p>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Applicant</th>
                                    <th>Pet</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_apps as $app): ?>
                                <tr>
                                    <td><?= htmlspecialchars($app['applicant']) ?></td>
                                    <td><?= htmlspecialchars($app['pet_name']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $app['status'] ?>">
                                            <?= ucfirst($app['status']) ?>
                                        </span>
                                    </td>
                                    <td style="color:var(--text-muted);font-size:.8rem;">
                                        <?= date('M j, Y', strtotime($app['applied_at'])) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                <!-- Recent Pets -->
                <div class="panel">
                    <div class="panel-header">
                        <h3>🐾 Recent Pets</h3>
                        <a href="pets.php">Manage →</a>
                    </div>
                    <?php if (empty($recent_pets)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">🐾</div>
                            <p>No pets listed yet.</p>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Breed</th>
                                    <th>Gender</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_pets as $pet): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($pet['name']) ?></strong></td>
                                    <td style="color:var(--text-muted);font-size:.85rem;">
                                        <?= htmlspecialchars($pet['breed_name'] ?? 'Unknown') ?>
                                    </td>
                                    <td style="text-transform:capitalize;">
                                        <?= htmlspecialchars($pet['gender'] ?? '—') ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $pet['status'] ?>">
                                            <?= ucfirst($pet['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/admin_footer.php"; ?>