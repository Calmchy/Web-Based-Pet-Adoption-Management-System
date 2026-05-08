<nav>
    <a href="index.php?page=home">Home</a>
    <a href="index.php?page=pets">Pets</a>
    <a href="index.php?page=apply">Apply</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="#">Dashboard</a>
        <a href="actions/logout.php">Logout</a>
    <?php else: ?>
        <a href="index.php?page=login">Login</a>
        <a href="index.php?page=register">Register</a>
    <?php endif; ?>
</nav>