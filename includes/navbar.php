<nav>
    <ul>
        <li><a href="">Home</a></li>
        <li><a href="">Pets</a></li>
        <li><a href="">About</a></li>
        <li><a href="">Login</a></li>
        <li><a href="">Register</a></li>
    </ul>
</nav>

<?php
/*

For future dynamic navigations

<nav>
    <ul>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <!-- GUEST NAV -->
        <li><a href="index.php?page=home">Home</a></li>
        <li><a href="index.php?page=pets">Pets</a></li>
        <li><a href="index.php?page=login">Login</a></li>
        <li><a href="index.php?page=register">Register</a></li>

    <?php elseif ($_SESSION['role'] == "adopter"): ?>
        <!-- ADOPTER NAV -->
        <li><a href="index.php?page=home">Home</a></li>
        <li><a href="index.php?page=pets">Pets</a></li>
        <li><a href="index.php?page=my_applications">My Applications</a></li>
        <li><a href="index.php?page=profile">Profile</a></li>
        <li><a href="actions/logout.php">Logout</a></li>

    <?php elseif ($_SESSION['role'] == "admin"): ?>
        <!-- ADMIN NAV -->
        <li><a href="admin/dashboard.php">Dashboard</a></li>
        <li><a href="admin/pets.php">Manage Pets</a></li>
        <li><a href="admin/applications.php">Applications</a></li>
        <li><a href="admin/users.php">Users</a></li>
        <li><a href="actions/logout.php">Logout</a></li>

    <?php endif; ?>

    </ul>
</nav>
*/
?>