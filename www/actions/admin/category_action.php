<?php
ob_start();
define('APP_RUNNING', true);
require_once "../../includes/config.php";
require_once "../../includes/auth.php";
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/categories.php");
    exit();
}

$action = $_POST['action'] ?? '';

// ############ ADD ############
if ($action === 'add') {
    $name = trim($_POST['category_name'] ?? '');

    if (empty($name)) {
        $_SESSION['admin_error'] = "Category name is required.";
        header("Location: ../../admin/categories.php");
        exit();
    }

    // ############ Check duplicate ############
    $stmt = $conn->prepare("SELECT category_id FROM categories WHERE LOWER(category_name) = LOWER(?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['admin_error'] = "Category '$name' already exists.";
        $stmt->close();
        header("Location: ../../admin/categories.php");
        exit();
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
    $stmt->bind_param("s", $name);
    if ($stmt->execute()) {
        $_SESSION['admin_success'] = "Category '$name' added successfully.";
    } else {
        $_SESSION['admin_error'] = "Failed to add category.";
    }
    $stmt->close();
}

// ############ DELETE ############
if ($action === 'delete') {
    $id = (int)($_POST['category_id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = "Category deleted.";
        } else {
            $_SESSION['admin_error'] = "Failed to delete category.";
        }
        $stmt->close();
    }
}

header("Location: ../../admin/categories.php");
exit();