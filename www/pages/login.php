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
    <!-- Decorative background blobs -->
    <div class="auth-bg-blob auth-bg-blob--1"></div>
    <div class="auth-bg-blob auth-bg-blob--2"></div>
    <div class="auth-card auth-card--sm">
        <!-- Paw icon header -->
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

<style>
/* ── Extra login page polish ─── */
.auth-wrapper {
    position: relative;
    overflow: hidden;
}

.auth-bg-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: .5;
    pointer-events: none;
    z-index: 0;
}

.auth-bg-blob--1 {
    width: 320px; height: 320px;
    background: radial-gradient(circle, #f39c12 0%, transparent 70%);
    top: -80px; left: -80px;
    opacity: .18;
}

.auth-bg-blob--2 {
    width: 280px; height: 280px;
    background: radial-gradient(circle, #e07b54 0%, transparent 70%);
    bottom: -60px; right: -60px;
    opacity: .15;
}

.auth-card {
    position: relative;
    z-index: 1;
}

.auth-brand-icon {
    text-align: center;
    font-size: 2.6rem;
    margin-bottom: .5rem;
    line-height: 1;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,.12));
}

.auth-header {
    margin-bottom: 1.5rem;
}

.auth-header h2 {
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -.4px;
}

/* Icon-wrapped inputs */
.input-icon-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon-wrap .input-icon {
    position: absolute;
    left: 12px;
    font-size: 1rem;
    z-index: 2;
    pointer-events: none;
    line-height: 1;
}

.input-icon-wrap input {
    padding-left: 2.6rem !important;
}

/* Password field with icon */
.input-password.input-icon-wrap input {
    border: none;
    padding-left: 2.6rem !important;
    padding-right: .5rem;
}

/* Divider */
.auth-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 1.2rem 0 .6rem;
    color: var(--text-muted, #aaa);
    font-size: .8rem;
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border, #e5e5e5);
}

.btn-primary.btn-full {
    margin-top: 1rem;
    letter-spacing: .02em;
    font-size: .95rem;
}
</style>

<script>
    document.querySelectorAll('.toggle-pw').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            input.type = input.type === 'password' ? 'text' : 'password';
            btn.textContent = input.type === 'password' ? '👁' : '🙈';
        });
    });
</script>