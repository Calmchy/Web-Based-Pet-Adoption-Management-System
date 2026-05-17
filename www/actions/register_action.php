<?php
ob_start();
define('APP_RUNNING', true);
require_once "../includes/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=register");
    exit();
}

// ── Collect & sanitize inputs ─────────────────────────────────────────────
$first_name       = trim($_POST['first_name']       ?? '');
$middle_name      = trim($_POST['middle_name']      ?? '');
$last_name        = trim($_POST['last_name']        ?? '');
$email            = trim($_POST['email']            ?? '');
$phone_number     = trim($_POST['phone_number']     ?? '');
$sitio_purok      = trim($_POST['sitio_purok']      ?? '');
$subdivision_name = trim($_POST['subdivision_name'] ?? '');
$barangay_name    = trim($_POST['barangay_name']    ?? '');
$city_town        = trim($_POST['city_town']        ?? '');
$province         = trim($_POST['province']         ?? '');
$region           = trim($_POST['region']           ?? '');
$zip_code         = trim($_POST['zip_code']         ?? '');
$password         = $_POST['password']              ?? '';
$confirm_pw       = $_POST['confirm_password']      ?? '';

$old = compact(
    'first_name','middle_name','last_name','email','phone_number',
    'sitio_purok','subdivision_name','barangay_name','city_town',
    'province','region','zip_code'
);

// ── Validation ────────────────────────────────────────────────────────────
$errors = [];

if (empty($first_name)) $errors[] = "First name is required.";
if (empty($last_name))  $errors[] = "Last name is required.";

if (empty($email)) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

if (!empty($phone_number) && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone_number)) {
    $errors[] = "Phone number format is invalid.";
}

if (empty($barangay_name)) $errors[] = "Barangay is required.";
if (empty($city_town))     $errors[] = "City / Town is required.";
if (empty($province))      $errors[] = "Province is required.";
if (empty($region))        $errors[] = "Region is required.";
if (empty($zip_code))      $errors[] = "ZIP code is required.";

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
    $_SESSION['register_old']    = $old;
    header("Location: ../index.php?page=register");
    exit();
}

// ── Handle profile image upload ───────────────────────────────────────────
$profile_image = null;

if (!empty($_FILES['profile_image']['name'])) {
    $file     = $_FILES['profile_image'];
    $allowed  = ['image/jpeg','image/png','image/webp'];
    $max_size = 2 * 1024 * 1024;

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Image upload failed. Please try again.";
    } elseif (!in_array($mime, $allowed)) {
        $errors[] = "Profile image must be JPG, PNG, or WEBP.";
    } elseif ($file['size'] > $max_size) {
        $errors[] = "Profile image must be 2MB or less.";
    } else {
        $ext        = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename   = 'profile_' . uniqid() . '.' . $ext;
        $upload_dir = realpath(__DIR__ . '/../assets') . '/uploads/profiles/';

        if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

        $profile_image = 'assets/uploads/profiles/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            $errors[] = "Could not save the image. Check folder permissions.";
            $profile_image = null;
        }
    }

    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_old']    = $old;
        header("Location: ../index.php?page=register");
        exit();
    }
}

// ── Insert into address first ─────────────────────────────────────────────
$stmt = $conn->prepare("
    INSERT INTO address (sitio_purok, subdivision_name, barangay_name, city_town, province, region, zip_code)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("sssssss",
    $sitio_purok, $subdivision_name, $barangay_name,
    $city_town, $province, $region, $zip_code
);

if (!$stmt->execute()) {
    $_SESSION['register_errors'] = ["Registration failed. Please try again."];
    $_SESSION['register_old']    = $old;
    header("Location: ../index.php?page=register");
    exit();
}
$address_id = $conn->insert_id;
$stmt->close();

// ── Insert into users with address_id ────────────────────────────────────
$hashed_pw = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("
    INSERT INTO users (first_name, middle_name, last_name, email, password, phone_number, profile_image, address_id, role)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'adopter')
");
$stmt->bind_param("sssssssi",
    $first_name, $middle_name, $last_name,
    $email, $hashed_pw, $phone_number, $profile_image, $address_id
);

if (!$stmt->execute()) {
    // Clean up orphan address row
    $conn->query("DELETE FROM address WHERE address_id = $address_id");
    $_SESSION['register_errors'] = ["Registration failed. Please try again."];
    $_SESSION['register_old']    = $old;
    header("Location: ../index.php?page=register");
    exit();
}
$stmt->close();

// ── Success ───────────────────────────────────────────────────────────────
$_SESSION['register_success'] = "Account created! You can now log in.";
header("Location: ../index.php?page=login");
exit();