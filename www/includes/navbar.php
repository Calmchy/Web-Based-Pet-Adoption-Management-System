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
})();
</script>