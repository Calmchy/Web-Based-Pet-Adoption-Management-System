<?php
define('APP_RUNNING', true);
require_once "../includes/config.php";

session_destroy();
header("Location: ../index.php?page=login");
exit();