<?php
    if (isset($_SESSION['user_id'])) {
        header("Location: index.php?page=home");
        exit();
    }

    $errors = $_SESSION['register_errors'] ?? [];
    $old    = $_SESSION['register_old']    ?? [];
    unset($_SESSION['register_errors'], $_SESSION['register_old']);
?>

<main class="auth-wrapper">
    <div class="auth-bg-blob auth-bg-blob--1"></div>
    <div class="auth-bg-blob auth-bg-blob--2"></div>
    <div class="auth-card">
        <div class="auth-brand-icon">🐾</div>
        <div class="auth-header">
            <h2>Create Account</h2>
            <p>Join AdoptME and find your perfect companion</p>
        </div>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form action="actions/register_action.php" method="POST" enctype="multipart/form-data" class="auth-form">
            <fieldset>
                <legend>🖼️ Profile Photo</legend>
                <div class="form-group">
                    <div class="avatar-upload">
                        <div class="avatar-preview">
                            <img id="avatarPreview" src="assets/images/default-avatar.png" alt="Preview">
                        </div>
                        <div class="avatar-info">
                            <label for="profile_image" class="btn-upload">📷 Choose Photo</label>
                            <input type="file" id="profile_image" name="profile_image"
                                   accept="image/jpeg,image/png,image/webp" style="display:none;">
                            <p class="upload-hint">JPG, PNG, or WEBP &middot; Max 2MB &middot; Optional</p>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend>👤 Personal Information</legend>
                <div class="form-row-3">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name"
                               value="<?= htmlspecialchars($old['first_name'] ?? '') ?>"
                               placeholder="Juan" required>
                    </div>
                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name"
                               value="<?= htmlspecialchars($old['middle_name'] ?? '') ?>"
                               placeholder="Santos (optional)">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name"
                               value="<?= htmlspecialchars($old['last_name'] ?? '') ?>"
                               placeholder="Dela Cruz" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                               placeholder="juan@email.com" required>
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number"
                               value="<?= htmlspecialchars($old['phone_number'] ?? '') ?>"
                               placeholder="09XXXXXXXXX">
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend>📍 Address</legend>
                <div class="form-row">
                    <div class="form-group">
                        <label for="sitio_purok">Sitio / Purok</label>
                        <input type="text" id="sitio_purok" name="sitio_purok"
                               value="<?= htmlspecialchars($old['sitio_purok'] ?? '') ?>"
                               placeholder="Sitio Mabini (optional)">
                    </div>
                    <div class="form-group">
                        <label for="subdivision_name">Subdivision</label>
                        <input type="text" id="subdivision_name" name="subdivision_name"
                               value="<?= htmlspecialchars($old['subdivision_name'] ?? '') ?>"
                               placeholder="Subdivision name (optional)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="barangay_name">Barangay <span class="required">*</span></label>
                        <input type="text" id="barangay_name" name="barangay_name"
                               value="<?= htmlspecialchars($old['barangay_name'] ?? '') ?>"
                               placeholder="Brgy. San Antonio" required>
                    </div>
                    <div class="form-group">
                        <label for="city_town">City / Town <span class="required">*</span></label>
                        <input type="text" id="city_town" name="city_town"
                               value="<?= htmlspecialchars($old['city_town'] ?? '') ?>"
                               placeholder="Abuyog" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="province">Province <span class="required">*</span></label>
                        <input type="text" id="province" name="province"
                               value="<?= htmlspecialchars($old['province'] ?? '') ?>"
                               placeholder="Leyte" required>
                    </div>
                    <div class="form-group">
                        <label for="region">Region <span class="required">*</span></label>
                        <input type="text" id="region" name="region"
                               value="<?= htmlspecialchars($old['region'] ?? '') ?>"
                               placeholder="Region VIII" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="zip_code">ZIP Code <span class="required">*</span></label>
                        <input type="text" id="zip_code" name="zip_code"
                               value="<?= htmlspecialchars($old['zip_code'] ?? '') ?>"
                               placeholder="6510" required maxlength="10">
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend>🔒 Password</legend>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <div class="input-password">
                            <input type="password" id="password" name="password"
                                   placeholder="Min. 8 characters" required>
                            <button type="button" class="toggle-pw" data-target="password">👁</button>
                        </div>
                        <div class="pw-strength" id="pwStrength"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                        <div class="input-password">
                            <input type="password" id="confirm_password" name="confirm_password"
                                   placeholder="Repeat password" required>
                            <button type="button" class="toggle-pw" data-target="confirm_password">👁</button>
                        </div>
                    </div>
                </div>
            </fieldset>
            <button type="submit" class="btn-primary btn-full">
                Create My Account &rarr;
            </button>
        </form>
        <p class="auth-switch">
            Already have an account? <a href="index.php?page=login">Sign in here</a>
        </p>
    </div>
</main>

<style>
.auth-wrapper {
    position: relative;
    overflow: hidden;
}

.auth-bg-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    pointer-events: none;
    z-index: 0;
}

.auth-bg-blob--1 {
    width: 340px; height: 340px;
    background: radial-gradient(circle, #f39c12 0%, transparent 70%);
    top: -80px; right: -80px;
    opacity: .15;
}

.auth-bg-blob--2 {
    width: 280px; height: 280px;
    background: radial-gradient(circle, #3498db 0%, transparent 70%);
    bottom: -60px; left: -60px;
    opacity: .12;
}

.auth-card {
    position: relative;
    z-index: 1;
}

.auth-brand-icon {
    text-align: center;
    font-size: 2.4rem;
    margin-bottom: .5rem;
    line-height: 1;
}

.auth-header h2 {
    font-size: 1.55rem;
    font-weight: 800;
    letter-spacing: -.4px;
}

.btn-primary.btn-full {
    margin-top: 1rem;
    letter-spacing: .02em;
}

/* Password strength bar */
.pw-strength {
    height: 4px;
    border-radius: 2px;
    margin-top: 6px;
    transition: width .3s, background .3s;
    width: 0;
}
</style>

<script>
    // Toggle password visibility
    document.querySelectorAll('.toggle-pw').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            input.type = input.type === 'password' ? 'text' : 'password';
            btn.textContent = input.type === 'password' ? '👁' : '🙈';
        });
    });

    // Profile image live preview
    document.getElementById('profile_image').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            alert('Image must be 2MB or less.');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(file);
    });

    // Password strength meter
    const pwInput    = document.getElementById('password');
    const strengthEl = document.getElementById('pwStrength');
    const colors     = ['#ef4444', '#f97316', '#eab308', '#22c55e'];
    const labels     = ['Weak', 'Fair', 'Good', 'Strong'];

    pwInput.addEventListener('input', () => {
        const v = pwInput.value;
        let score = 0;
        if (v.length >= 8)              score++;
        if (/[A-Z]/.test(v))            score++;
        if (/[0-9]/.test(v))            score++;
        if (/[^A-Za-z0-9]/.test(v))     score++;
        if (v.length === 0) {
            strengthEl.style.width = '0';
            strengthEl.title = '';
        } else {
            strengthEl.style.width  = ((score / 4) * 100) + '%';
            strengthEl.style.background = colors[score - 1] || '#ef4444';
            strengthEl.title = labels[score - 1] || '';
        }
    });
</script>