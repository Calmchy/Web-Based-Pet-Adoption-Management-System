<?php
ob_start();
define('APP_RUNNING', true);
require_once "../includes/config.php";
require_once "../includes/auth.php";
require_role('admin');

$flash_error = $_SESSION['admin_error'] ?? null;
$old = $_SESSION['admin_old'] ?? [];
unset($_SESSION['admin_error'], $_SESSION['admin_old']);

// ############ Fetch categories for the category dropdown ############
$categories = [];
$q = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
if ($q) $categories = $q->fetch_all(MYSQLI_ASSOC);

// ############ Fetch ALL breeds — JS will filter by selected category ############
$breeds = [];
$q = $conn->query("SELECT breed_id, breed_name, category_id FROM breeds ORDER BY breed_name ASC");
if ($q) $breeds = $q->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Pet — AdoptME Admin</title>
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
                    <h1>Add Pet</h1>
                    <p>List a new pet for adoption</p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="notif-wrapper" style="position:relative;">
                    <button class="notif-bell" id="adminNotifBtn" title="Notifications">🔔
                        <?php
                        $nq = $conn->prepare("SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0");
                        $nq->bind_param("i", $_SESSION['user_id']);
                        $nq->execute();
                        $nc = $nq->get_result()->fetch_assoc()['c'];
                        $nq->close();
                        if ($nc > 0): ?>
                            <span class="notif-badge"><?= $nc ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notif-dropdown" id="adminNotifDropdown">
                        <div class="notif-header">
                            <h4>🔔 Notifications</h4>
                            <button class="notif-mark-read" id="adminMarkAllRead">Mark all read</button>
                        </div>
                        <div class="notif-list" id="adminNotifList">
                        <?php
                        $nq2 = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
                        $nq2->bind_param("i", $_SESSION['user_id']);
                        $nq2->execute();
                        $notifs = $nq2->get_result()->fetch_all(MYSQLI_ASSOC);
                        $nq2->close();
                        if (empty($notifs)): ?>
                            <div class="notif-empty">No notifications yet</div>
                        <?php else: foreach ($notifs as $n): ?>
                            <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                                <span class="notif-icon">🐾</span>
                                <div class="notif-text">
                                    <p><?= htmlspecialchars($n['message']) ?></p>
                                    <span><?= date('M d, g:i A', strtotime($n['created_at'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>
                <button class="theme-toggle" id="themeToggle">🌙 Dark</button>
                <a href="../actions/logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </div>
        <div class="page-content">
            <?php if ($flash_error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($flash_error) ?></div>
            <?php endif; ?>
            <div class="panel" style="max-width:760px;">
                <div class="panel-header">
                    <h3>🐾 New Pet Details</h3>
                    <a href="pets.php">← Back to Pets</a>
                </div>
                <div class="panel-body">
                    <form action="../actions/admin/pet_action.php" method="POST"
                          enctype="multipart/form-data" class="admin-form">
                        <input type="hidden" name="action" value="add">
                        <!-- ############ Name + Gender ############ -->
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="name">Pet Name <span class="req">*</span></label>
                                <input type="text" id="name" name="name"
                                       value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                                       placeholder="e.g. Brownie" required maxlength="50">
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender <span class="req">*</span></label>
                                <select id="gender" name="gender" required>
                                    <option value="">— Select —</option>
                                    <option value="male"   <?= ($old['gender'] ?? '') === 'male'   ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        <!-- ############ Category + Breed ############ -->
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="category_id">Category <span class="req">*</span></label>
                                <select id="category_id" name="category_id" required>
                                    <option value="">— Select Category —</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['category_id'] ?>"
                                            <?= ($old['category_id'] ?? '') == $cat['category_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="breed_id">Breed <span class="req">*</span></label>
                                <select id="breed_id" name="breed_id" required disabled>
                                    <option value="">— Select Category First —</option>
                                </select>
                            </div>
                        </div>
                        <!-- ############ Age + Status ############ -->
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="age">Age (years)</label>
                                <input type="number" id="age" name="age" min="0" max="30"
                                       value="<?= htmlspecialchars($old['age'] ?? '') ?>"
                                       placeholder="e.g. 2">
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="available" <?= ($old['status'] ?? 'available') === 'available' ? 'selected' : '' ?>>Available</option>
                                    <option value="pending"   <?= ($old['status'] ?? '') === 'pending'   ? 'selected' : '' ?>>Pending</option>
                                    <option value="adopted"   <?= ($old['status'] ?? '') === 'adopted'   ? 'selected' : '' ?>>Adopted</option>
                                </select>
                            </div>
                        </div>
                        <!-- ############ Description ############ -->
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"
                                      placeholder="Personality, health notes, special needs..."
                                      rows="4"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                        </div>
                        <!-- ############ Images ############ -->
                        <div class="form-group">
                            <label>Photos (up to 5 · JPG, PNG, WEBP · Max 2MB each)</label>
                            <div class="image-upload-area" id="imageUploadArea">
                                <div class="image-upload-placeholder">
                                    <span style="font-size:2rem;">📷</span>
                                    <p>Click or drag photos here</p>
                                    <span style="font-size:.78rem;color:var(--text-muted);">Up to 5 images</span>
                                </div>
                                <input type="file" id="pet_images" name="pet_images[]"
                                       accept="image/jpeg,image/png,image/webp"
                                       multiple style="display:none;">
                            </div>
                            <div class="image-preview-grid" id="imagePreviewGrid"></div>
                        </div>
                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <button type="submit" class="btn-admin-primary">🐾 Add Pet</button>
                            <a href="pets.php" class="btn-admin-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ############ Breed filter by category ############
const allBreeds = <?= json_encode($breeds) ?>;
const categorySel = document.getElementById('category_id');
const breedSel = document.getElementById('breed_id');
const savedBreed = <?= json_encode($old['breed_id'] ?? '') ?>;

function filterBreeds() {
    const catId = categorySel.value;
    const filtered = allBreeds.filter(b => String(b.category_id) === String(catId));

    breedSel.innerHTML = filtered.length
        ? '<option value="">— Select Breed —</option>'
        : '<option value="">— No breeds for this category —</option>';

    filtered.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b.breed_id;
        opt.textContent = b.breed_name;
        if (String(b.breed_id) === String(savedBreed)) opt.selected = true;
        breedSel.appendChild(opt);
    });

    breedSel.disabled = filtered.length === 0;
    if (filtered.length > 0) breedSel.required = true;
}

categorySel.addEventListener('change', filterBreeds);
if (categorySel.value) filterBreeds(); // ############ restore on validation fail ############

// ############ Image upload preview ############
const uploadArea = document.getElementById('imageUploadArea');
const fileInput = document.getElementById('pet_images');
const previewGrid = document.getElementById('imagePreviewGrid');
const MAX_FILES = 5;
const MAX_SIZE = 2 * 1024 * 1024;
let   selectedFiles = [];

uploadArea.addEventListener('click', () => fileInput.click());

uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.classList.add('drag-over'); });
uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('drag-over'));
uploadArea.addEventListener('drop', e => {
    e.preventDefault();
    uploadArea.classList.remove('drag-over');
    handleFiles(Array.from(e.dataTransfer.files));
});

fileInput.addEventListener('change', () => handleFiles(Array.from(fileInput.files)));

function handleFiles(files) {
    files.forEach(file => {
        if (selectedFiles.length >= MAX_FILES) return;
        if (!file.type.match(/image\/(jpeg|png|webp)/)) { alert(`${file.name} is not a valid image type.`); return; }
        if (file.size > MAX_SIZE) { alert(`${file.name} exceeds 2MB.`); return; }
        selectedFiles.push(file);
    });
    syncFileInput();
    renderPreviews();
}

function syncFileInput() {
    const dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    fileInput.files = dt.files;
}

function renderPreviews() {
    previewGrid.innerHTML = '';
    selectedFiles.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.className = 'preview-thumb';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="preview-remove" data-idx="${idx}">✕</button>
            `;
            previewGrid.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

previewGrid.addEventListener('click', e => {
    const btn = e.target.closest('.preview-remove');
    if (!btn) return;
    selectedFiles.splice(parseInt(btn.dataset.idx), 1);
    syncFileInput();
    renderPreviews();
});
</script>

<?php include "includes/admin_footer.php"; ?>