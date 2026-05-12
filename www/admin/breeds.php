<?php
ob_start();
define('APP_RUNNING', true);
require_once "../includes/config.php";
require_once "../includes/auth.php";
require_role('admin');

$flash_success = $_SESSION['admin_success'] ?? null;
$flash_error   = $_SESSION['admin_error']   ?? null;
unset($_SESSION['admin_success'], $_SESSION['admin_error']);

// Fetch breeds with category name
$breeds = [];
$q = $conn->query("
    SELECT b.breed_id, b.breed_name, c.category_name, c.category_id
    FROM breeds b
    LEFT JOIN categories c ON c.category_id = b.category_id
    ORDER BY c.category_name ASC, b.breed_name ASC
");
if ($q) $breeds = $q->fetch_all(MYSQLI_ASSOC);

// Fetch categories for dropdown
$categories = [];
$q = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
if ($q) $categories = $q->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Breeds — AdoptME Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="shortcut icon" href="../assets/images/logo.png" type="image/x-icon">
</head>
<body>
<div class="admin-layout">

    <?php include "includes/sidebar.php"; ?>

    <div class="admin-main">

        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle"><span></span><span></span><span></span></button>
                <div>
                    <h1>Breeds</h1>
                    <p>Manage pet breeds per category</p>
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

            <?php if (empty($categories)): ?>
                <div class="alert alert-error">
                    ⚠️ No categories found. <a href="categories.php" style="color:inherit;font-weight:700;">Add a category first</a> before adding breeds.
                </div>
            <?php endif; ?>

            <div class="two-col-layout">

                <!-- Add Breed Form -->
                <div class="panel">
                    <div class="panel-header">
                        <h3>🐶 Add New Breed</h3>
                    </div>
                    <div class="panel-body">
                        <form action="../actions/admin/breed_action.php" method="POST" class="admin-form">
                            <input type="hidden" name="action" value="add">
                            <div class="form-group">
                                <label for="category_id">Category <span class="req">*</span></label>
                                <select id="category_id" name="category_id" required <?= empty($categories) ? 'disabled' : '' ?>>
                                    <option value="">— Select Category —</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="breed_name">Breed Name <span class="req">*</span></label>
                                <input type="text" id="breed_name" name="breed_name"
                                       placeholder="e.g. Aspin, Labrador, Persian" required maxlength="50"
                                       <?= empty($categories) ? 'disabled' : '' ?>>
                            </div>
                            <button type="submit" class="btn-admin-primary btn-full" <?= empty($categories) ? 'disabled' : '' ?>>➕ Add Breed</button>
                        </form>
                    </div>
                </div>

                <!-- Breeds List -->
                <div class="panel">
                    <div class="panel-header">
                        <h3>All Breeds (<?= count($breeds) ?>)</h3>
                    </div>
                    <?php if (empty($breeds)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">🐶</div>
                            <p>No breeds yet. Add one!</p>
                        </div>
                    <?php else: ?>
                        <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Breed</th>
                                    <th>Category</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($breeds as $breed): ?>
                                <tr>
                                    <td style="color:var(--text-muted);font-size:.8rem;"><?= $breed['breed_id'] ?></td>
                                    <td><strong><?= htmlspecialchars($breed['breed_name']) ?></strong></td>
                                    <td><span class="badge badge-available"><?= htmlspecialchars($breed['category_name'] ?? '—') ?></span></td>
                                    <td>
                                        <form action="../actions/admin/breed_action.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="breed_id" value="<?= $breed['breed_id'] ?>">
                                            <button type="submit" class="action-btn action-btn-danger"
                                                    onclick="return confirm('Delete \'<?= htmlspecialchars(addslashes($breed['breed_name'])) ?>\'?')">
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