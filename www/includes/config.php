<?php
    session_start();

    $host = "mariadb";
    $dbname = "adoptme";
    $username = "root";
    $password = "canwepass";

    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if (!defined('APP_RUNNING')) {
        die("Direct access not allowed.");
    }

    $conn->set_charset("utf8");
?>

