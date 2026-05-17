<header>
<nav class="navbar">

    <!-- LEFT: Logo + Desktop Links -->
    <div class="nav-left">
        <div class="brand-container">
            <img class="logo" src="assets/images/logo.png" alt="AdoptME Logo">
            <div class="brand">
                <h1>AdoptME</h1>
                <p>Pet Adoption System</p>
            </div>
        </div>

        <div class="nav-links">
            <a href="index.php?page=home">Home</a>
            <a href="index.php?page=pets">Pets</a>
            <a href="index.php?page=apply">Apply</a>
            <a href="index.php?page=about">About</a>
        </div>
    </div>

    <!-- RIGHT: Dark mode + Auth (desktop) -->
    <div class="nav-right">
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="nav-notif" style="position:relative;">
            <button class="nav-notif-btn" id="navNotifBtn" title="Notifications">🔔<?php
                $nq = $conn->prepare("SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0");
                $nq->bind_param("i", $_SESSION['user_id']);
                $nq->execute();
                $nc = $nq->get_result()->fetch_assoc()['c'];
                $nq->close();
                if ($nc > 0): ?><span class="nav-notif-badge"><?= $nc ?></span><?php endif; ?>
            </button>
            <div class="nav-notif-dropdown" id="navNotifDropdown">
                <div class="notif-header">
                    <h4>🔔 Notifications</h4>
                    <button class="notif-mark-read" id="navMarkAllRead">Mark all read</button>
                </div>
                <div class="notif-list">
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
        <?php endif; ?>
                <button id="darkModeBtn" title="Toggle dark mode">🌙</button>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="admin/dashboard.php" class="register-btn">🛡️ Admin</a>
            <?php else: ?>
                <a href="index.php?page=account">My Account</a>
            <?php endif; ?>
            <a href="actions/logout.php">Logout</a>
        <?php else: ?>
            <a href="index.php?page=login">Login</a>
            <a href="index.php?page=register" class="register-btn">Register</a>
        <?php endif; ?>
    </div>

    <!-- Hamburger (mobile only) -->
    <button class="nav-hamburger" id="navHamburger" aria-label="Open menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

</nav>

<!-- Mobile Drawer -->
<div class="nav-drawer" id="navDrawer">
    <a href="index.php?page=home">🏠 Home</a>
    <a href="index.php?page=pets">🐾 Pets</a>
    <a href="index.php?page=apply">📋 Apply</a>
    <a href="index.php?page=about">ℹ️ About</a>
    <!-- Dark mode toggle in drawer -->
    <button id="darkModeBtnDrawer" style="
        background:none; border:none; color:#dcdde1; font-size:.95rem;
        font-weight:600; padding:10px 14px; border-radius:8px; cursor:pointer;
        display:flex; align-items:center; gap:10px; width:100%;
        font-family:inherit; transition:background .2s;
    ">🌙 Dark Mode</button>
    <div class="drawer-divider"></div>
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <a href="admin/dashboard.php">🛡️ Admin Panel</a>
        <?php else: ?>
            <a href="index.php?page=account">👤 My Account</a>
        <?php endif; ?>
        <a href="actions/logout.php">🚪 Logout</a>
    <?php else: ?>
        <a href="index.php?page=login">🔑 Login</a>
        <a href="index.php?page=register" class="register-btn">✨ Register</a>
    <?php endif; ?>
</div>
</header>

<script>
(function () {
    const btn    = document.getElementById('navHamburger');
    const drawer = document.getElementById('navDrawer');
    if (!btn || !drawer) return;

    btn.addEventListener('click', () => {
        const open = drawer.classList.toggle('open');
        btn.classList.toggle('open', open);
        btn.setAttribute('aria-expanded', open);
    });

    // Close drawer when a link is tapped
    drawer.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => {
            drawer.classList.remove('open');
            btn.classList.remove('open');
        });
    });

    // Close on outside click
    document.addEventListener('click', e => {
        if (!btn.contains(e.target) && !drawer.contains(e.target)) {
            drawer.classList.remove('open');
            btn.classList.remove('open');
        }
    });

    // Dark mode — sync both buttons (topbar + drawer)
    const dmMain   = document.getElementById('darkModeBtn');
    const dmDrawer = document.getElementById('darkModeBtnDrawer');

    function applyDark(dark) {
        document.body.classList.toggle('dark-mode', dark);
        localStorage.setItem('theme', dark ? 'dark' : 'light');
        const icon = dark ? '☀️' : '🌙';
        if (dmMain)   dmMain.textContent   = icon;
        if (dmDrawer) dmDrawer.textContent = icon + ' Dark Mode';
    }

    // Load saved preference
    applyDark(localStorage.getItem('theme') === 'dark');

    if (dmMain)   dmMain.addEventListener('click',   () => applyDark(!document.body.classList.contains('dark-mode')));
    if (dmDrawer) dmDrawer.addEventListener('click', () => applyDark(!document.body.classList.contains('dark-mode')));

    // Navbar notification bell
    const navNotifBtn      = document.getElementById('navNotifBtn');
    const navNotifDropdown = document.getElementById('navNotifDropdown');
    const navMarkAll       = document.getElementById('navMarkAllRead');

    if (navNotifBtn && navNotifDropdown) {
        navNotifBtn.addEventListener('click', e => {
            e.stopPropagation();
            navNotifDropdown.classList.toggle('open');
        });
        document.addEventListener('click', e => {
            if (!navNotifBtn.contains(e.target) && !navNotifDropdown.contains(e.target)) {
                navNotifDropdown.classList.remove('open');
            }
        });
    }

    if (navMarkAll) {
        navMarkAll.addEventListener('click', () => {
            fetch('actions/mark_notifications_read.php', { method: 'POST' })
                .then(() => {
                    document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
                    const badge = document.querySelector('.nav-notif-badge');
                    if (badge) badge.remove();
                    if (navNotifDropdown) navNotifDropdown.classList.remove('open');
                });
        });
    }
})();
</script>