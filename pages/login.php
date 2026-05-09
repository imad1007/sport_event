<?php
/**
 * login.php — Login form.
 * On submit, the form POSTs to actions/login_action.php.
 */

$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /sport_event/pages/dashboard.php');
    exit;
}

// Carry forward any error from login_action.php (stored in session)
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p class="auth-subtitle">Sign in to your SportEvents account</p>

        <?php if ($error): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="/sport_event/actions/login_action.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control"
                       placeholder="you@example.com" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">
                Login
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="/sport_event/pages/register.php">Register here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
