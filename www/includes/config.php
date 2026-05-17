<?php
    session_start();
    /*
    // Xampp Config
    $host = 'localhost';
    $dbname = 'adoptme';
    $username = 'root';
    $password = '';
    */

    // Docker Config
    $host     = getenv('DB_HOST')     ?: 'mariadb';
    $dbname   = getenv('DB_NAME')     ?: 'adoptme';
    $username = getenv('DB_USER')     ?: 'adoptme';
    $password = getenv('DB_PASSWORD') ?: '';

    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if (!defined('APP_RUNNING')) {
        die("Direct access not allowed.");
    }

    $conn->set_charset("utf8mb4");

    // CSRF token — generated once per session
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }