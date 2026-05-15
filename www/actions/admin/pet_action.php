<?php
ob_start();
define('APP_RUNNING', true);
require_once "../../includes/config.php";
require_once "../../includes/auth.php";
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/add_pet.php");
    exit();
}

$action = $_POST['action'] ?? '';

// ── ADD ───────────────────────────────────────────────────────────────────
if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $breed_id = (int)($_POST['breed_id']   ?? 0);
    $age = $_POST['age'] !== '' ? (int)$_POST['age'] : null;
    $status = $_POST['status'] ?? 'available';
    $description = trim($_POST['description'] ?? '');
    $created_by = $_SESSION['user_id'];

    $old = compact('name','gender','breed_id','age','status','description');
    $old['category_id'] = (int)($_POST['category_id'] ?? 0);

    // Validate
    $errors = [];
    if (empty($name)) $errors[] = "Pet name is required.";
    if (!in_array($gender, ['male','female'])) $errors[] = "Gender is required.";
    if ($breed_id === 0) $errors[] = "Please select a breed.";
    if (!in_array($status, ['available','pending','adopted'])) $errors[] = "Invalid status.";

    if (!empty($errors)) {
        $_SESSION['admin_error'] = implode(' ', $errors);
        $_SESSION['admin_old'] = $old;
        header("Location: ../../admin/add_pet.php");
        exit();
    }

    // Insert pet
    $stmt = $conn->prepare("
        INSERT INTO pets (name, age, gender, description, status, breed_id, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("siissii", $name, $age, $gender, $description, $status, $breed_id, $created_by);

    if (!$stmt->execute()) {
        $_SESSION['admin_error'] = "Failed to add pet. Please try again.";
        $_SESSION['admin_old'] = $old;
        header("Location: ../../admin/add_pet.php");
        exit();
    }

    $pet_id = $conn->insert_id;
    $stmt->close();

    // Handle image uploads
    $upload_dir = realpath(__DIR__ . '/../../assets') . '/uploads/pets/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

    $allowed_mime = ['image/jpeg','image/png','image/webp'];
    $max_size = 2 * 1024 * 1024;
    $upload_errors = [];

    if (!empty($_FILES['pet_images']['name'][0])) {
        $files = $_FILES['pet_images'];
        $count = count($files['name']);
        $count = min($count, 5); // cap at 5

        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            if ($files['size'][$i] > $max_size) { $upload_errors[] = "{$files['name'][$i]} exceeds 2MB."; continue; }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($files['tmp_name'][$i]);
            if (!in_array($mime, $allowed_mime)) { $upload_errors[] = "{$files['name'][$i]} is not a valid image."; continue; }

            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            $filename = 'pet_' . $pet_id . '_' . uniqid() . '.' . $ext;
            $dest = $upload_dir . $filename;

            if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
                $path = 'assets/uploads/pets/' . $filename;
                $stmt = $conn->prepare("INSERT INTO pet_images (pet_id, image_path) VALUES (?, ?)");
                $stmt->bind_param("is", $pet_id, $path);
                $stmt->execute();
                $stmt->close();
            } else {
                $upload_errors[] = "Failed to save {$files['name'][$i]}.";
            }
        }
    }

    if (!empty($upload_errors)) {
        $_SESSION['admin_success'] = "Pet '{$name}' added, but some images had issues: " . implode(', ', $upload_errors);
    } else {
        $_SESSION['admin_success'] = "Pet '{$name}' added successfully! 🐾";
    }

    header("Location: ../../admin/pets.php");
    exit();
}

header("Location: ../../admin/add_pet.php");
exit();