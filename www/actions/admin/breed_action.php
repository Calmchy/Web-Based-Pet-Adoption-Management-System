<?php
ob_start();
define('APP_RUNNING', true);
require_once "../../includes/config.php";
require_once "../../includes/auth.php";
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/breeds.php");
    exit();
}

$action = $_POST['action'] ?? '';

// ############ ADD ############
if ($action === 'add') {
    $name = trim($_POST['breed_name']   ?? '');
    $cat_id = (int)($_POST['category_id'] ?? 0);

    if (empty($name) || $cat_id === 0) {
        $_SESSION['admin_error'] = "Breed name and category are required.";
        header("Location: ../../admin/breeds.php");
        exit();
    }

    // ############ Check duplicate within same category ############
    $stmt = $conn->prepare("SELECT breed_id FROM breeds WHERE LOWER(breed_name) = LOWER(?) AND category_id = ?");
    $stmt->bind_param("si", $name, $cat_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['admin_error'] = "Breed '$name' already exists in this category.";
        $stmt->close();
        header("Location: ../../admin/breeds.php");
        exit();
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO breeds (breed_name, category_id) VALUES (?, ?)");
    $stmt->bind_param("si", $name, $cat_id);
    if ($stmt->execute()) {
        $_SESSION['admin_success'] = "Breed '$name' added successfully.";
    } else {
        $_SESSION['admin_error'] = "Failed to add breed.";
    }
    $stmt->close();
}

// ############ DELETE ############
if ($action === 'delete') {
    $id = (int)($_POST['breed_id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM breeds WHERE breed_id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = "Breed deleted.";
        } else {
            $_SESSION['admin_error'] = "Failed to delete breed.";
        }
        $stmt->close();
    }
}

header("Location: ../../admin/breeds.php");
exit();