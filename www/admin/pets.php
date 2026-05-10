<?php
ob_start();
define('APP_RUNNING', true);
require_once "../includes/config.php";
require_once "../includes/auth.php";

require_role('admin');

// ── Handle status flash messages ──────────────────────────────────────────
$flash_success = $_SESSION['admin_success'] ?? null;
$flash_error   = $_SESSION['admin_error']   ?? null;
unset($_SESSION['admin_success'], $_SESSION['admin_error']);

// ── Fetch all pets ────────────────────────────────────────────────────────
$pets = [];
$q = $conn->query("
    SELECT p.pet_id, p.name, p.age, p.gender, p.status, p.created_at,
           b.breed_name, c.category_name
    FROM pets p
    LEFT JOIN breeds b     ON b.breed_id    = p.breed_id
    LEFT JOIN categories c ON c.category_id = b.category_id
    ORDER BY p.created_at DESC
");
if ($q) $pets = $q->fetch_all(MYSQLI_ASSOC);

$admin_name = ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '');
$admin_init = strtoupper(substr($_SESSION['first_name'] ?? 'A', 0, 1));

// ── Pending count for sidebar badge ───────────────────────────────────────
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

    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="../assets/images/logo.png" alt="AdoptME Logo">
            <div class="sidebar-brand-text">
                <h2>AdoptME</h2>
                <span>Admin Panel</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Main</div>
            <a href="dashboard.php"><span class="nav-icon">🏠</span> Dashboard</a>
            <a href="pets.php" class="active"><span class="nav-icon">🐾</span> Pets</a>
            <a href="applications.php">
                <span class="nav-icon">📋</span> Applications
                <?php if ($pending_count > 0): ?>
                    <span class="badge badge-pending" style="margin-left:auto;"><?= $pending_count ?></span>
                <?php endif; ?>
            </a>
            <div class="nav-section-label" style="margin-top:12px;">System</div>
            <a href="../index.php?page=home" target="_blank"><span class="nav-icon">🌐</span> View Site</a>
            <a href="../actions/logout.php" style="color:#f87171;"><span class="nav-icon">🚪</span> Logout</a>
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar"><?= htmlspecialchars($admin_init) ?></div>
                <div class="sidebar-user-info">
                    <strong><?= htmlspecialchars(trim($admin_name)) ?></strong>
                    <span>Administrator</span>
                </div>
            </div>
        </div>
    </aside>

    <div class="admin-main">

        <div class="topbar">
            <div class="topbar-left">
                <h1>Pets</h1>
                <p>Manage all listed pets</p>
            </div>
            <div class="topbar-right">
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

<script>
    const btn = document.getElementById('themeToggle');
    function applyTheme(dark) {
        document.body.classList.toggle('dark-mode', dark);
        btn.textContent = dark ? '☀️ Light' : '🌙 Dark';
        localStorage.setItem('theme', dark ? 'dark' : 'light');
    }
    const saved = localStorage.getItem('theme');
    applyTheme(saved === 'dark');
    btn.addEventListener('click', () => applyTheme(!document.body.classList.contains('dark-mode')));
</script>
</body>
</html>