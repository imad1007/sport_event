<?php
/**
 * update_event.php — Admin-only: updates an existing event.
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

$eventId        = filter_input(INPUT_POST, 'event_id',        FILTER_VALIDATE_INT);
$title          = trim($_POST['title']           ?? '');
$sportType      = trim($_POST['sport_type']      ?? '');
$date           = trim($_POST['date']            ?? '');
$location       = trim($_POST['location']        ?? '');
$price          = filter_input(INPUT_POST, 'price',           FILTER_VALIDATE_FLOAT);
$availableSeats = filter_input(INPUT_POST, 'available_seats', FILTER_VALIDATE_INT);
$description    = trim($_POST['description']     ?? '');

if (!$eventId || empty($title) || empty($sportType) || empty($date) || empty($location) || $price === false || $availableSeats === false) {
    $_SESSION['flash_error'] = 'Please fill in all required fields.';
    header('Location: /sport_event/pages/dashboard.php?tab=events');
    exit;
}

$stmt = $pdo->prepare(
    "UPDATE events
     SET title = ?, sport_type = ?, date = ?, location = ?, price = ?, available_seats = ?, description = ?
     WHERE id = ?"
);
$stmt->execute([$title, $sportType, $date, $location, $price, $availableSeats, $description, $eventId]);

$_SESSION['flash_success'] = 'Event updated successfully!';
header('Location: /sport_event/pages/dashboard.php?tab=events');
exit;
