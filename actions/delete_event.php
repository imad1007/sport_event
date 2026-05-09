<?php
/**
 * delete_event.php — Admin-only: deletes an event and all its bookings
 * (cascaded automatically via FK ON DELETE CASCADE).
 */

session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['flash_error'] = 'Access denied.';
    header('Location: /sport_event/pages/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /sport_event/pages/dashboard.php?tab=events');
    exit;
}

$eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);

if (!$eventId) {
    $_SESSION['flash_error'] = 'Invalid event ID.';
    header('Location: /sport_event/pages/dashboard.php?tab=events');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
$stmt->execute([$eventId]);

if ($stmt->rowCount() === 0) {
    $_SESSION['flash_error'] = 'Event not found.';
} else {
    $_SESSION['flash_success'] = 'Event deleted successfully.';
}

header('Location: /sport_event/pages/dashboard.php?tab=events');
exit;
