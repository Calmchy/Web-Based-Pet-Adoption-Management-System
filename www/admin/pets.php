<?php
ob_start();
define('APP_RUNNING', true);
require_once "../includes/config.php";
require_once "../includes/auth.php";

require_role('admin');

// ############ Handle status flash messages ############
$flash_success = $_SESSION['admin_success'] ?? null;
$flash_error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_success'], $_SESSION['admin_error']);

// ############ Fetch all pets ############
$pets = [];
$q = $conn->query("
    SELECT p.pet_id, p.name, p.age, p.gender, p.status, p.created_at, b.breed_name, c.category_name
    FROM pets p
    LEFT JOIN breeds b ON b.breed_id = p.breed_id
    LEFT JOIN categories c ON c.category_id = b.category_id
    ORDER BY p.created_at DESC
");
if ($q) $pets = $q->fetch_all(MYSQLI_ASSOC);

$admin_name = ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '');
$admin_init = strtoupper(substr($_SESSION['first_name'] ?? 'A', 0, 1));

// ############ Pending count for sidebar badge ############
$pending_count = 0;
$q = $conn->query("SELECT COUNT(*) as c FROM applications WHERE status = 'pending'");
if ($q) $pending_count = $q->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pets — AdoptME Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="shortcut icon" href="../assets/images/logo2.png" type="image/x-icon">
</head>
<body>
<div class="admin-layout">
    <?php include "includes/sidebar.php"; ?>
    <div class="admin-main">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar"><span></span><span></span><span></span></button>
                <h1>Pets</h1>
                <p>Manage all listed pets</p>
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
        <div class="page-content">
            <?php if ($flash_success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
            <?php endif; ?>
            <?php if ($flash_error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($flash_error) ?></div>
            <?php endif; ?>

            <div class="panel">
                <div class="panel-header">
                    <h3>🐾 All Pets (<?= count($pets) ?>)</h3>
                </div>
                <?php if (empty($pets)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🐾</div>
                        <p>No pets have been added yet.</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Breed</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <th>Listed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pets as $pet): ?>
                            <tr>
                                <td style="color:var(--text-muted);font-size:.8rem;">#<?= $pet['pet_id'] ?></td>
                                <td><strong><?= htmlspecialchars($pet['name']) ?></strong></td>
                                <td style="color:var(--text-muted);"><?= htmlspecialchars($pet['category_name'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($pet['breed_name'] ?? '—') ?></td>
                                <td><?= $pet['age'] ? $pet['age'] . ' yr' . ($pet['age'] != 1 ? 's' : '') : '—' ?></td>
                                <td style="text-transform:capitalize;"><?= htmlspecialchars($pet['gender'] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge-<?= $pet['status'] ?>">
                                        <?= ucfirst($pet['status']) ?>
                                    </span>
                                </td>
                                <td style="color:var(--text-muted);font-size:.8rem;">
                                    <?= date('M j, Y', strtotime($pet['created_at'])) ?>
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

<?php include "includes/admin_footer.php"; ?>