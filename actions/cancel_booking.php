<?php
/**
 * cancel_booking.php — Cancels a booking owned by the current user.
 * Restores one seat to the event using a transaction.
 */

session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /sport_event/pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /sport_event/pages/dashboard.php');
    exit;
}

$userId    = $_SESSION['user_id'];
$bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);

if (!$bookingId) {
    $_SESSION['flash_error'] = 'Invalid booking.';
    header('Location: /sport_event/pages/dashboard.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Fetch the booking and verify it belongs to the current user
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$bookingId, $userId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        $pdo->rollBack();
        $_SESSION['flash_error'] = 'Booking not found or access denied.';
        header('Location: /sport_event/pages/dashboard.php');
        exit;
    }

    // Delete the booking
    $delStmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    $delStmt->execute([$bookingId]);

    // Give the seat back to the event
    $seatStmt = $pdo->prepare("UPDATE events SET available_seats = available_seats + 1 WHERE id = ?");
    $seatStmt->execute([$booking['event_id']]);

    $pdo->commit();

    $_SESSION['flash_success'] = 'Booking cancelled successfully.';
    header('Location: /sport_event/pages/dashboard.php');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['flash_error'] = 'Cancellation failed. Please try again.';
    header('Location: /sport_event/pages/dashboard.php');
    exit;
}
