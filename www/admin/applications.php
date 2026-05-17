<?php
ob_start();
define('APP_RUNNING', true);
require_once "../includes/config.php";
require_once "../includes/auth.php";

require_role('admin');

// ── Handle approve/reject actions ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['application_id'])) {
    $app_id = (int) $_POST['application_id'];
    $action = $_POST['action'];

    if (in_array($action, ['approved', 'rejected'])) {
        $stmt = $conn->prepare("
            UPDATE applications
            SET status = ?, reviewed_at = NOW(), reviewed_by = ?
            WHERE application_id = ?
        ");
        $stmt->bind_param("sii", $action, $_SESSION['user_id'], $app_id);

        if ($stmt->execute()) {
            // If approved, mark pet as adopted
            if ($action === 'approved') {
                $pet_q = $conn->query("SELECT pet_id FROM applications WHERE application_id = $app_id");
                if ($pet_q) {
                    $pet_row = $pet_q->fetch_assoc();
                    $conn->query("UPDATE pets SET status = 'adopted' WHERE pet_id = " . (int)$pet_row['pet_id']);
                }
            }
            $_SESSION['admin_success'] = "Application #$app_id has been " . $action . ".";
        } else {
            $_SESSION['admin_error'] = "Failed to update application.";
        }
        $stmt->close();
    }

    header("Location: applications.php");
    exit();
}

$flash_success = $_SESSION['admin_success'] ?? null;
$flash_error   = $_SESSION['admin_error']   ?? null;
unset($_SESSION['admin_success'], $_SESSION['admin_error']);

// ── Filter ────────────────────────────────────────────────────────────────
$filter = in_array($_GET['status'] ?? '', ['pending','approved','rejected']) ? $_GET['status'] : '';
$where  = $filter ? "WHERE a.status = '$filter'" : '';

// ── Fetch applications ────────────────────────────────────────────────────
$applications = [];
$q = $conn->query("
    SELECT a.application_id, a.status, a.applied_at, a.message, a.reviewed_at,
    CONCAT(u.first_name, ' ', u.last_name) AS applicant, u.email AS applicant_email,
    p.name AS pet_name, p.pet_id, CONCAT(r.first_name, ' ', r.last_name) AS reviewer
    FROM applications a
    JOIN users u ON u.user_id = a.user_id
    JOIN pets p  ON p.pet_id  = a.pet_id
    LEFT JOIN users r ON r.user_id = a.reviewed_by
    $where
    ORDER BY
        CASE a.status WHEN 'pending' THEN 0 ELSE 1 END,
        a.applied_at DESC
");
if ($q) $applications = $q->fetch_all(MYSQLI_ASSOC);

// Count by status
$counts = ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
$qc = $conn->query("SELECT status, COUNT(*) as c FROM applications GROUP BY status");
if ($qc) {
    while ($row = $qc->fetch_assoc()) {
        $counts[$row['status']] = (int)$row['c'];
        $counts['all'] += (int)$row['c'];
    }
}

$admin_name = ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '');
$admin_init = strtoupper(substr($_SESSION['first_name'] ?? 'A', 0, 1));
$pending_count = $counts['pending'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications — AdoptME Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="shortcut icon" href="../assets/images/logo2.png" type="image/x-icon">
    <style>
        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-tab {
            padding: 7px 16px;
            border-radius: 20px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-muted);
            text-decoration: none;
            font-size: .82rem;
            font-weight: 600;
            transition: all .2s;
        }
        .filter-tab:hover { border-color: var(--primary); color: var(--primary); }
        .filter-tab.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .filter-tab .count {
            display: inline-block;
            background: rgba(255,255,255,.25);
            border-radius: 10px;
            padding: 1px 7px;
            font-size: .72rem;
            margin-left: 4px;
        }
        .filter-tab:not(.active) .count {
            background: var(--surface-2);
            color: var(--text-muted);
        }
        .action-form { display: inline; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include "includes/sidebar.php"; ?>
    <div class="admin-main">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar"><span></span><span></span><span></span></button>
                <h1>Applications</h1>
                <p>Review and manage adoption applications</p>
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
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="applications.php" class="filter-tab <?= $filter === '' ? 'active' : '' ?>">
                    All <span class="count"><?= $counts['all'] ?></span>
                </a>
                <a href="applications.php?status=pending" class="filter-tab <?= $filter === 'pending' ? 'active' : '' ?>">
                    ⏳ Pending <span class="count"><?= $counts['pending'] ?></span>
                </a>
                <a href="applications.php?status=approved" class="filter-tab <?= $filter === 'approved' ? 'active' : '' ?>">
                    ✅ Approved <span class="count"><?= $counts['approved'] ?></span>
                </a>
                <a href="applications.php?status=rejected" class="filter-tab <?= $filter === 'rejected' ? 'active' : '' ?>">
                    ❌ Rejected <span class="count"><?= $counts['rejected'] ?></span>
                </a>
            </div>
            <div class="panel">
                <div class="panel-header">
                    <h3>Applications (<?= count($applications) ?>)</h3>
                </div>
                <?php if (empty($applications)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <p>No <?= $filter ?: '' ?> applications found.</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Applicant</th>
                                <th>Pet</th>
                                <th>Applied</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                            <tr>
                                <td style="color:var(--text-muted);font-size:.8rem;">#<?= $app['application_id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($app['applicant']) ?></strong>
                                    <br>
                                    <span style="font-size:.78rem;color:var(--text-muted);">
                                        <?= htmlspecialchars($app['applicant_email']) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($app['pet_name']) ?></strong>
                                </td>
                                <td style="color:var(--text-muted);font-size:.82rem;">
                                    <?= date('M j, Y', strtotime($app['applied_at'])) ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $app['status'] ?>">
                                        <?= ucfirst($app['status']) ?>
                                    </span>
                                    <?php if ($app['reviewer'] && $app['reviewed_at']): ?>
                                        <br>
                                        <span style="font-size:.72rem;color:var(--text-muted);">
                                            by <?= htmlspecialchars($app['reviewer']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($app['status'] === 'pending'): ?>
                                        <form class="action-form" method="POST">
                                            <input type="hidden" name="application_id" value="<?= $app['application_id'] ?>">
                                            <input type="hidden" name="action" value="approved">
                                            <button type="submit" class="action-btn action-btn-success"
                                                    onclick="return confirm('Approve this application?')">
                                                ✅ Approve
                                            </button>
                                        </form>
                                        <form class="action-form" method="POST" style="margin-left:6px;">
                                            <input type="hidden" name="application_id" value="<?= $app['application_id'] ?>">
                                            <input type="hidden" name="action" value="rejected">
                                            <button type="submit" class="action-btn action-btn-danger"
                                                    onclick="return confirm('Reject this application?')">
                                                ❌ Reject
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);font-size:.82rem;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if (!empty($app['message'])): ?>
                            <tr style="background:var(--surface-2);">
                                <td></td>
                                <td colspan="5" style="font-size:.82rem;color:var(--text-muted);padding-top:6px;padding-bottom:12px;">
                                    <em>Message: <?= htmlspecialchars($app['message']) ?></em>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include "includes/admin_footer.php"; ?>