<?php
    if (isset($_SESSION['user_id'])) {
        header("Location: index.php?page=home");
        exit();
    }

    $error     = $_SESSION['login_error'] ?? null;
    $old_email = $_SESSION['login_old_email'] ?? '';
    unset($_SESSION['login_error'], $_SESSION['login_old_email']);

    $success = $_SESSION['register_success'] ?? null;
    unset($_SESSION['register_success']);
?>

<main class="auth-wrapper">
    <!-- ############ Decorative background blobs ############ -->
    <div class="auth-bg-blob auth-bg-blob--1"></div>
    <div class="auth-bg-blob auth-bg-blob--2"></div>
    <div class="auth-card auth-card--sm">
        <!-- ############ Paw icon header ############ -->
        <div class="auth-brand-icon">🐾</div>
        <div class="auth-header">
            <h2>Welcome Back</h2>
            <p>Sign in to your AdoptME account</p>
        </div>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="actions/login_action.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-icon-wrap">
                    <span class="input-icon">✉️</span>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($old_email) ?>"
                           placeholder="juan@email.com" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-password input-icon-wrap">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="password" name="password"
                           placeholder="Your password" required>
                    <button type="button" class="toggle-pw" data-target="password">👁</button>
                </div>
            </div>
            <button type="submit" class="btn-primary btn-full">
                Sign In &rarr;
            </button>
        </form>
        <div class="auth-divider"><span>or</span></div>
        <p class="auth-switch">
            No account yet? <a href="index.php?page=register">Create one here</a>
        </p>
    </div>
</main>

<link rel="stylesheet" href="../assets/css/register.css">
<script src="../assets/js/login.js"></script>