<?php
ob_start();
define('APP_RUNNING', true);
require_once "../includes/config.php";
require_once "../includes/auth.php";
require_role('admin');

$flash_success = $_SESSION['admin_success'] ?? null;
$flash_error = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_success'], $_SESSION['admin_error']);

// ############ Fetch all categories with breed count ############
$categories = [];
$q = $conn->query("
    SELECT c.category_id, c.category_name, COUNT(b.breed_id) AS breed_count
    FROM categories c
    LEFT JOIN breeds b ON b.category_id = c.category_id
    GROUP BY c.category_id
    ORDER BY c.category_name ASC
");
if ($q) $categories = $q->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories — AdoptME Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="shortcut icon" href="../assets/images/logo.png" type="image/x-icon">
</head>
<body>
<div class="admin-layout">
    <?php include "includes/sidebar.php"; ?>
    <div class="admin-main">
        <!-- ############ Topbar ############ -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle"><span></span><span></span><span></span></button>
                <div>
                    <h1>Categories</h1>
                    <p>Manage pet categories</p>
                </div>
            </div>
            <div class="topbar-right">
                <button class="theme-toggle" id="themeToggle">🌙 Dark</button>
                <a href="../actions/logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </div>
        <div class="page-content">
            <?php if ($flash_success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
            <?php endif; ?>
            <?php if ($flash_error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($flash_error) ?></div>
            <?php endif; ?>
            <div class="two-col-layout">
                <!-- ############ Add Category Form ############ -->
                <div class="panel">
                    <div class="panel-header">
                        <h3>🏷️ Add New Category</h3>
                    </div>
                    <div class="panel-body">
                        <form action="../actions/admin/category_action.php" method="POST" class="admin-form">
                            <input type="hidden" name="action" value="add">
                            <div class="form-group">
                                <label for="category_name">Category Name <span class="req">*</span></label>
                                <input type="text" id="category_name" name="category_name"
                                       placeholder="e.g. Dog, Cat, Rabbit" required maxlength="50">
                            </div>
                            <button type="submit" class="btn-admin-primary btn-full">➕ Add Category</button>
                        </form>
                    </div>
                </div>
                <!-- ############ Categories List ############ -->
                <div class="panel">
                    <div class="panel-header">
                        <h3>All Categories (<?= count($categories) ?>)</h3>
                    </div>
                    <?php if (empty($categories)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">🏷️</div>
                            <p>No categories yet. Add one!</p>
                        </div>
                    <?php else: ?>
                        <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Breeds</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td style="color:var(--text-muted);font-size:.8rem;"><?= $cat['category_id'] ?></td>
                                    <td><strong><?= htmlspecialchars($cat['category_name']) ?></strong></td>
                                    <td>
                                        <span class="badge badge-available"><?= $cat['breed_count'] ?> breed<?= $cat['breed_count'] != 1 ? 's' : '' ?></span>
                                    </td>
                                    <td>
                                        <form action="../actions/admin/category_action.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                            <button type="submit" class="action-btn action-btn-danger"
                                                    onclick="return confirm('Delete \'<?= htmlspecialchars(addslashes($cat['category_name'])) ?>\'? Breeds linked to it will lose their category.')">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "includes/admin_footer.php"; ?>