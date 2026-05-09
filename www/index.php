<?php
    define('APP_RUNNING', true);
    require_once "includes/config.php";

    $page = isset($_GET['page']) ? $_GET['page'] : 'home';

    $allowed_pages = ['home', 'login', 'register', 'pets', 'apply', 'about'];

    if (!in_array($page, $allowed_pages)) {
        $page = 'home';
    }

    include "includes/header.php";

    include "pages/" . $page . ".php";

    include "includes/footer.php";
?>

