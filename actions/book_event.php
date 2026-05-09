<?php
/**
 * book_event.php — Processes an event booking.
 * Checks login, seat availability, and prevents duplicate bookings.
 * Uses a transaction to atomically insert the booking and decrement seats.
 */

session_start();
require_once __DIR__ . '/../config/db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Please login to book an event.';
    header('Location: /sport_event/pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /sport_event/pages/events.php');
    exit;
}

$userId  = $_SESSION['user_id'];
$eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);

if (!$eventId) {
    $_SESSION['flash_error'] = 'Invalid event.';
    header('Location: /sport_event/pages/events.php');
    exit;
}

try {
    // Start a transaction so booking insert + seat decrement are atomic
    $pdo->beginTransaction();

    // Lock the event row and re-check available seats
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? FOR UPDATE");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();

    if (!$event) {
        $pdo->rollBack();
        $_SESSION['flash_error'] = 'Event not found.';
        header('Location: /sport_event/pages/events.php');
        exit;
    }

    // Check if the user already has a booking for this event
    $dupStmt = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? AND event_id = ?");
    $dupStmt->execute([$userId, $eventId]);
    if ($dupStmt->fetch()) {
        $pdo->rollBack();
        $_SESSION['flash_error'] = 'You have already booked this event.';
        header('Location: /sport_event/pages/event_details.php?id=' . $eventId);
        exit;
    }

    // Check seats are still available
    if ($event['available_seats'] <= 0) {
        $pdo->rollBack();
        $_SESSION['flash_error'] = 'Sorry, this event is fully booked.';
        header('Location: /sport_event/pages/event_details.php?id=' . $eventId);
        exit;
    }

    // Insert the booking record
    $bookStmt = $pdo->prepare("INSERT INTO bookings (user_id, event_id) VALUES (?, ?)");
    $bookStmt->execute([$userId, $eventId]);

    // Decrement available seats by 1
    $seatStmt = $pdo->prepare("UPDATE events SET available_seats = available_seats - 1 WHERE id = ?");
    $seatStmt->execute([$eventId]);

    $pdo->commit();

    $_SESSION['flash_success'] = 'Booking confirmed for "' . $event['title'] . '"! See you there 🎉';
    header('Location: /sport_event/pages/dashboard.php');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['flash_error'] = 'Booking failed due to a server error. Please try again.';
    header('Location: /sport_event/pages/event_details.php?id=' . $eventId);
    exit;
}
