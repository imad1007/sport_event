<?php
/**
 * login_action.php — Handles the login form submission.
 * Verifies credentials, sets session, then redirects.
 */

session_start();
require_once __DIR__ . '/../config/db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /sport_event/pages/login.php');
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

// Basic presence check
if (empty($email) || empty($password)) {
    $_SESSION['flash_error'] = 'Please fill in all fields.';
    header('Location: /sport_event/pages/login.php');
    exit;
}

// Look up the user by email (prepared statement — no raw input in SQL)
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Verify password against the stored bcrypt hash
if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['flash_error'] = 'Incorrect email or password. Please try again.';
    header('Location: /sport_event/pages/login.php');
    exit;
}

// Credentials are valid — store user info in session
$_SESSION['user_id']  = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email']    = $user['email'];
$_SESSION['role']     = $user['role'];

$_SESSION['flash_success'] = 'Welcome back, ' . $user['username'] . '!';
header('Location: /sport_event/pages/dashboard.php');
exit;
