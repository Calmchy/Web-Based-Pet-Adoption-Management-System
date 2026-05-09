<?php
    if (isset($_SESSION['user_id'])) {
        header("Location: index.php?page=home");
        exit();
    }

    $errors = $_SESSION['register_errors'] ?? [];
    $old     = $_SESSION['register_old']    ?? [];
    unset($_SESSION['register_errors'], $_SESSION['register_old']);
?>

<main class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-header">
            <h2>Create Account</h2>
            <p>Join AdoptMe and find your perfect companion 🐾</p>
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

            <!-- Personal Information -->
            <fieldset>
                <legend>Personal Information</legend>

                <div class="form-row">
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

            <!-- Address -->
            <fieldset>
                <legend>Address</legend>

                <div class="form-row">
                    <div class="form-group">
                        <label for="brgy_or_street">Barangay / Street <span class="required">*</span></label>
                        <input type="text" id="brgy_or_street" name="brgy_or_street"
                               value="<?= htmlspecialchars($old['brgy_or_street'] ?? '') ?>"
                               placeholder="Brgy. San Antonio" required>
                    </div>

                    <div class="form-group">
                        <label for="municipality">Municipality / City <span class="required">*</span></label>
                        <input type="text" id="municipality" name="municipality"
                               value="<?= htmlspecialchars($old['municipality'] ?? '') ?>"
                               placeholder="Abuyog, Leyte" required>
                    </div>
                </div>
            </fieldset>

            <!-- Profile Image -->
            <fieldset>
                <legend>Profile Image</legend>

                <div class="form-group">
                    <div class="avatar-upload">
                        <div class="avatar-preview">
                            <img id="avatarPreview" src="assets/images/default-avatar.png" alt="Preview">
                        </div>
                        <div class="avatar-info">
                            <label for="profile_image" class="btn-upload">Choose Photo</label>
                            <input type="file" id="profile_image" name="profile_image"
                                   accept="image/jpeg,image/png,image/webp" style="display:none;">
                            <p class="upload-hint">JPG, PNG, or WEBP · Max 2MB · Optional</p>
                        </div>
                    </div>
                </div>
            </fieldset>

            <!-- Password -->
            <fieldset>
                <legend>Password</legend>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <div class="input-password">
                            <input type="password" id="password" name="password"
                                   placeholder="Min. 8 characters" required>
                            <button type="button" class="toggle-pw" data-target="password">👁</button>
                        </div>
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

            <button type="submit" class="btn-primary btn-full">Create Account</button>

        </form>

        <p class="auth-switch">
            Already have an account? <a href="index.php?page=login">Login here</a>
        </p>

    </div>
</main>

<script>
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
</script>