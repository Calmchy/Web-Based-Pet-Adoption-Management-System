<?php
/**
 * Auth helpers — include this at the top of any page that needs protection.
 *
 * Usage:
 *   require_once "includes/auth.php";
 *   require_login();          // any logged-in user
 *   require_role('admin');    // admin only
 */

function require_login(): void {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['login_error'] = "Please log in to continue.";
        header("Location: ../index.php?page=login");
        exit();
    }
}

function require_role(string $role): void {
    require_login();
    if ($_SESSION['role'] !== $role) {
        header("Location: ../index.php?page=home");
        exit();
    }
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function current_user_role(): string {
    return $_SESSION['role'] ?? '';
}