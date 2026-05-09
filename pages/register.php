<?php
/**
 * register.php — Registration form.
 * On submit, POSTs to actions/register_action.php.
 */

$pageTitle = 'Register';
require_once __DIR__ . '/../includes/header.php';

// Already logged in? redirect
if (isset($_SESSION['user_id'])) {
    header('Location: /sport_event/pages/dashboard.php');
    exit;
}

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

// Preserve submitted values so the form isn't blank on error
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Create Account</h2>
        <p class="auth-subtitle">Join SportEvents and start booking today</p>

        <?php if ($error): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="/sport_event/actions/register_action.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                       placeholder="johndoe" required
                       value="<?= htmlspecialchars($old['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control"
                       placeholder="you@example.com" required
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Min. 6 characters" required>
                <span class="form-hint">At least 6 characters</span>
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                       placeholder="Repeat your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">
                Create Account
            </button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="/sport_event/pages/login.php">Login here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
