<?php
    if (isset($_SESSION['user_id'])) {
        header("Location: index.php?page=home");
        exit();
    }

    $error   = $_SESSION['login_error'] ?? null;
    $old_email = $_SESSION['login_old_email'] ?? '';
    unset($_SESSION['login_error'], $_SESSION['login_old_email']);

    $success = $_SESSION['register_success'] ?? null;
    unset($_SESSION['register_success']);
?>

<main class="auth-wrapper">
    <div class="auth-card auth-card--sm">

        <div class="auth-header">
            <h2>Welcome Back 🐾</h2>
            <p>Login to your AdoptMe account</p>
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
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($old_email) ?>"
                       placeholder="juan@email.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-password">
                    <input type="password" id="password" name="password"
                           placeholder="Your password" required>
                    <button type="button" class="toggle-pw" data-target="password">👁</button>
                </div>
            </div>

            <button type="submit" class="btn-primary btn-full">Login</button>

        </form>

        <p class="auth-switch">
            No account yet? <a href="index.php?page=register">Register here</a>
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
</script>