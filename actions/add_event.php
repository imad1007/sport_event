<?php
/**
 * add_event.php — Admin-only: inserts a new event into the database.
 */

session_start();
require_once __DIR__ . '/../config/db.php';

// Access control — admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['flash_error'] = 'Access denied.';
    header('Location: /sport_event/pages/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /sport_event/pages/dashboard.php?tab=add_event');
    exit;
}

// Collect and sanitize input
$title          = trim($_POST['title']           ?? '');
$sportType      = trim($_POST['sport_type']      ?? '');
$date           = trim($_POST['date']            ?? '');
$location       = trim($_POST['location']        ?? '');
$price          = filter_input(INPUT_POST, 'price',           FILTER_VALIDATE_FLOAT);
$availableSeats = filter_input(INPUT_POST, 'available_seats', FILTER_VALIDATE_INT);
$description    = trim($_POST['description']     ?? '');

// Validate required fields
if (empty($title) || empty($sportType) || empty($date) || empty($location) || $price === false || $availableSeats === false) {
    $_SESSION['flash_error'] = 'Please fill in all required fields.';
    header('Location: /sport_event/pages/dashboard.php?tab=add_event');
    exit;
}

if ($price < 0 || $availableSeats < 1) {
    $_SESSION['flash_error'] = 'Price must be 0 or more, and seats must be at least 1.';
    header('Location: /sport_event/pages/dashboard.php?tab=add_event');
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO events (title, sport_type, date, location, price, available_seats, description)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->execute([$title, $sportType, $date, $location, $price, $availableSeats, $description]);

$_SESSION['flash_success'] = 'Event "' . $title . '" added successfully!';
header('Location: /sport_event/pages/dashboard.php?tab=events');
exit;
