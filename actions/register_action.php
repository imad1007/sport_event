<?php
/**
 * register_action.php — Handles the registration form submission.
 * Validates input, hashes the password, inserts the user, then redirects.
 */

session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /sport_event/pages/register.php');
    exit;
}

$username        = trim($_POST['username']         ?? '');
$email           = trim($_POST['email']            ?? '');
$password        = trim($_POST['password']         ?? '');
$passwordConfirm = trim($_POST['password_confirm'] ?? '');

// Helper: redirect back to register with an error message
function redirectWithError(string $msg, array $old): void {
    $_SESSION['flash_error'] = $msg;
    $_SESSION['old_input']   = $old;
    header('Location: /sport_event/pages/register.php');
    exit;
}

$old = compact('username', 'email');

// Validate required fields
if (empty($username) || empty($email) || empty($password) || empty($passwordConfirm)) {
    redirectWithError('Please fill in all fields.', $old);
}

// Username length
if (strlen($username) < 3 || strlen($username) > 50) {
    redirectWithError('Username must be between 3 and 50 characters.', $old);
}

// Valid email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithError('Please enter a valid email address.', $old);
}

// Password length
if (strlen($password) < 6) {
    redirectWithError('Password must be at least 6 characters.', $old);
}

// Password confirmation match
if ($password !== $passwordConfirm) {
    redirectWithError('Passwords do not match.', $old);
}

// Check for duplicate email
$checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$checkStmt->execute([$email]);
if ($checkStmt->fetch()) {
    redirectWithError('That email address is already registered. Please login.', $old);
}

// Hash the password with bcrypt (PHP default — very secure)
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert the new user
$insertStmt = $pdo->prepare(
    "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')"
);
$insertStmt->execute([$username, $email, $hashedPassword]);

$_SESSION['flash_success'] = 'Account created! Please login.';
header('Location: /sport_event/pages/login.php');
exit;
