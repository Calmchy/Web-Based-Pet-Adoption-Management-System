<?php
// ############ Pending count for badge ############
$pending_count = 0;
$_q = $conn->query("SELECT COUNT(*) as c FROM applications WHERE status = 'pending'");
if ($_q) $pending_count = (int)$_q->fetch_assoc()['c'];

$admin_name = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
$admin_init = strtoupper(substr($_SESSION['first_name'] ?? 'A', 0, 1));

// ############ Current page for active state ############
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="../assets/images/logo.png" alt="AdoptME">
        <div class="sidebar-brand-text">
            <h2>AdoptME</h2>
            <span>Admin Panel</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Main</div>
        <a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>"><span class="nav-icon">🏠</span> Dashboard</a>
        <a href="pets.php" class="<?= $current_page === 'pets.php' ? 'active' : '' ?>"><span class="nav-icon">🐾</span> Pets</a>
        <a href="add_pet.php" class="<?= $current_page === 'add_pet.php' ? 'active' : '' ?>"><span class="nav-icon">➕</span> Add Pet</a>
        <a href="categories.php" class="<?= $current_page === 'categories.php' ? 'active' : '' ?>"><span class="nav-icon">🏷️</span> Categories</a>
        <a href="breeds.php" class="<?= $current_page === 'breeds.php' ? 'active' : '' ?>"><span class="nav-icon">🐶</span> Breeds</a>
        <a href="applications.php" class="<?= $current_page === 'applications.php' ? 'active' : '' ?>">
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
                <strong><?= htmlspecialchars($admin_name) ?></strong>
                <span>Administrator</span>
            </div>
        </div>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>