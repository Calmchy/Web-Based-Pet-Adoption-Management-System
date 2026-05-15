<?php
ob_start();
define('APP_RUNNING', true);
require_once "../includes/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=register");
    exit();
}

// ── Collect & sanitize inputs ─────────────────────────────────────────────
$first_name = trim($_POST['first_name'] ?? '');
$middle_name = trim($_POST['middle_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$brgy_or_street = trim($_POST['brgy_or_street'] ?? '');
$municipality = trim($_POST['municipality'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_pw = $_POST['confirm_password'] ?? '';

$old = compact('first_name','middle_name','last_name','email','phone_number','brgy_or_street','municipality');

// ── Validation ────────────────────────────────────────────────────────────
$errors = [];

if (empty($first_name)) $errors[] = "First name is required.";
if (empty($last_name)) $errors[] = "Last name is required.";

if (empty($email)) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

if (!empty($phone_number) && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone_number)) {
    $errors[] = "Phone number format is invalid.";
}

if (empty($brgy_or_street)) $errors[] = "Barangay / Street is required.";
if (empty($municipality)) $errors[] = "Municipality / City is required.";

if (empty($password)) {
    $errors[] = "Password is required.";
} elseif (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters.";
}

if ($password !== $confirm_pw) {
    $errors[] = "Passwords do not match.";
}

// Check email uniqueness
if (empty($errors)) {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "That email address is already registered.";
    }
    $stmt->close();
}

if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_old'] = $old;
    header("Location: ../index.php?page=register");
    exit();
}

// ── Handle profile image upload ───────────────────────────────────────────
$profile_image = null;

if (!empty($_FILES['profile_image']['name'])) {
    $file = $_FILES['profile_image'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Image upload failed. Please try again.";
    } elseif (!in_array($mime, $allowed)) {
        $errors[] = "Profile image must be JPG, PNG, or WEBP.";
    } elseif ($file['size'] > $max_size) {
        $errors[] = "Profile image must be 2MB or less.";
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'profile_' . uniqid() . '.' . $ext;
        $upload_dir = realpath(__DIR__ . '/../assets') . '/uploads/profiles/';

        // Auto-create folder if missing (handles fresh Docker containers)
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }

        $profile_image = 'assets/uploads/profiles/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            $errors[] = "Could not save the image. Check folder permissions.";
            $profile_image = null;
        }
    }

    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_old'] = $old;
        header("Location: ../index.php?page=register");
        exit();
    }
}

// ── Insert into users ─────────────────────────────────────────────────────
$hashed_pw = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("
    INSERT INTO users (first_name, middle_name, last_name, email, password, phone_number, profile_image, role)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'adopter')
");
$stmt->bind_param("sssssss",
    $first_name, $middle_name, $last_name,
    $email, $hashed_pw, $phone_number, $profile_image
);

if (!$stmt->execute()) {
    $_SESSION['register_errors'] = ["Registration failed. Please try again."];
    $_SESSION['register_old'] = $old;
    header("Location: ../index.php?page=register");
    exit();
}

$user_id = $conn->insert_id;
$stmt->close();

// ── Insert into address ───────────────────────────────────────────────────
$stmt = $conn->prepare("
    INSERT INTO address (user_id, brgy_or_street, municipality)
    VALUES (?, ?, ?)
");
$stmt->bind_param("iss", $user_id, $brgy_or_street, $municipality);
$stmt->execute();
$stmt->close();

// ── Success ───────────────────────────────────────────────────────────────
$_SESSION['register_success'] = "Account created! You can now log in.";
header("Location: ../index.php?page=login");
exit();