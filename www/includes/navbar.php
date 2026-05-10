<nav class="navbar">
    <!-- LEFT -->
    <div class="nav-left">

        <!-- Logo + Brand -->
        <div class="brand-container">
            <img class="logo" src="../assets/images/logo.png" alt="AdoptME Logo">

            <div class="brand">
                <h1>AdoptME</h1>
                <p>Pet Adoption System</p>
            </div>
        </div>

        <!-- Nav Links -->
        <div class="nav-links">
            <a href="index.php?page=home">Home</a>
            <a href="index.php?page=pets">Pets</a>
            <a href="index.php?page=apply">Apply</a>
            <a href="index.php?page=about">About</a>
        </div>

    </div>

    <!-- RIGHT -->
    <div class="nav-right">

        <!-- Dark Mode Button -->
        <button id="darkModeBtn">🌙</button>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="admin/dashboard.php" class="register-btn" style="display:flex;align-items:center;gap:5px;">🛡️ Admin</a>
            <?php else: ?>
                <a href="#">My Account</a>
            <?php endif; ?>
            <a href="actions/logout.php">Logout</a>
        <?php else: ?>
            <a href="index.php?page=login">Login</a>
            <a href="index.php?page=register" class="register-btn">Register</a>
        <?php endif; ?>

    </div>
</nav>