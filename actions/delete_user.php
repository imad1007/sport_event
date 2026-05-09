<?php
/**
 * delete_user.php — Admin-only: deletes a user account.
 * Admins cannot delete their own account via this action.
 */

session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['flash_error'] = 'Access denied.';
    header('Location: /sport_event/pages/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /sport_event/pages/dashboard.php?tab=users');
    exit;
}

$targetUserId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

// Prevent self-deletion
if ($targetUserId === (int) $_SESSION['user_id']) {
    $_SESSION['flash_error'] = 'You cannot delete your own account.';
    header('Location: /sport_event/pages/dashboard.php?tab=users');
    exit;
}

if (!$targetUserId) {
    $_SESSION['flash_error'] = 'Invalid user ID.';
    header('Location: /sport_event/pages/dashboard.php?tab=users');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$targetUserId]);

$_SESSION['flash_success'] = 'User deleted successfully.';
header('Location: /sport_event/pages/dashboard.php?tab=users');
exit;
