<?php
ob_start();
define('APP_RUNNING', true);
require_once "../includes/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=login");
    exit();
}

$email = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';

// ############ Basic validation ############
if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = "Email and password are required.";
    $_SESSION['login_old_email'] = $email;
    header("Location: ../index.php?page=login");
    exit();
}

// ############ Fetch user by email ############
$stmt = $conn->prepare("SELECT user_id, first_name, last_name, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// ############ Verify password ############
if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['login_error'] = "Invalid email or password.";
    $_SESSION['login_old_email'] = $email;
    header("Location: ../index.php?page=login");
    exit();
}

// ############ Set session ############
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['last_name'] = $user['last_name'];
$_SESSION['role'] = $user['role'];

// ############ Redirect based on role ############
if ($user['role'] === 'admin') {
    header("Location: ../admin/dashboard.php");
} else {
    header("Location: ../index.php?page=home");
}
exit();