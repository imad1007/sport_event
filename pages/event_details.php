<?php
/**
 * event_details.php — Full details for a single event.
 * Shows event info, booking button (logged-in users), and participant list.
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

// Validate and fetch the event ID from the URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash_error'] = 'Invalid event.';
    header('Location: /sport_event/pages/events.php');
    exit;
}

// Fetch the event
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
    $_SESSION['flash_error'] = 'Event not found.';
    header('Location: /sport_event/pages/events.php');
    exit;
}

$pageTitle = $event['title'];

// Fetch participants (users who booked this event)
$pStmt = $pdo->prepare(
    "SELECT u.username FROM bookings b
     JOIN users u ON b.user_id = u.id
     WHERE b.event_id = ?
     ORDER BY b.booked_at ASC"
);
$pStmt->execute([$id]);
$participants = $pStmt->fetchAll();

// Check if the current logged-in user already booked this event
$alreadyBooked = false;
if (isset($_SESSION['user_id'])) {
    $bStmt = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? AND event_id = ?");
    $bStmt->execute([$_SESSION['user_id'], $id]);
    $alreadyBooked = (bool) $bStmt->fetch();
}

function sportClass(string $t): string {
    return match(strtolower($t)) {
        'football'   => 'sport-football',
        'basketball' => 'sport-basketball',
        'tennis'     => 'sport-tennis',
        'running'    => 'sport-running',
        'marathon'   => 'sport-marathon',
        default      => 'sport-default',
    };
}
function sportEmoji(string $t): string {
    return match(strtolower($t)) {
        'football'   => '⚽',
        'basketball' => '🏀',
        'tennis'     => '🎾',
        'running'    => '🏃',
        'marathon'   => '🏃‍♂️',
        default      => '🏆',
    };
}
?>

<div class="event-detail-layout">

    <!-- ===== LEFT: Main Event Info ===== -->
    <div class="event-detail-main">
        <!-- Sport banner / image -->
        <div class="event-detail-hero <?= sportClass($event['sport_type']) ?>">
            <?= sportEmoji($event['sport_type']) ?>
        </div>

        <div class="event-detail-body">
            <span class="sport-badge" style="position:static;display:inline-block;margin-bottom:12px;background:var(--navy);">
                <?= htmlspecialchars($event['sport_type']) ?>
            </span>
            <h1><?= htmlspecialchars($event['title']) ?></h1>

            <!-- Key details row -->
            <div class="detail-meta-row">
                <div class="detail-meta-item">
                    <span>📅</span>
                    <div>
                        <div style="font-size:.75rem;color:var(--gray-400);text-transform:uppercase;letter-spacing:.5px;">Date</div>
                        <strong><?= date('l, F j, Y', strtotime($event['date'])) ?></strong>
                    </div>
                </div>
                <div class="detail-meta-item">
                    <span>📍</span>
                    <div>
                        <div style="font-size:.75rem;color:var(--gray-400);text-transform:uppercase;letter-spacing:.5px;">Location</div>
                        <strong><?= htmlspecialchars($event['location']) ?></strong>
                    </div>
                </div>
                <div class="detail-meta-item">
                    <span>🎟️</span>
                    <div>
                        <div style="font-size:.75rem;color:var(--gray-400);text-transform:uppercase;letter-spacing:.5px;">Price</div>
                        <strong style="color:var(--orange);">$<?= number_format($event['price'], 2) ?></strong>
                    </div>
                </div>
                <div class="detail-meta-item">
                    <span>💺</span>
                    <div>
                        <div style="font-size:.75rem;color:var(--gray-400);text-transform:uppercase;letter-spacing:.5px;">Available Seats</div>
                        <strong class="<?= $event['available_seats'] < 10 ? 'event-seats low' : '' ?>"><?= $event['available_seats'] ?></strong>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <h3 style="margin-bottom:12px;color:var(--navy);">About this Event</h3>
            <p style="color:var(--gray-600);line-height:1.8;">
                <?= nl2br(htmlspecialchars($event['description'] ?? 'No description provided.')) ?>
            </p>
        </div>
    </div>

    <!-- ===== RIGHT: Sidebar ===== -->
    <div class="event-sidebar">

        <!-- Booking card -->
        <div class="sidebar-card">
            <h3>Book Your Spot</h3>
            <div class="price-display">$<?= number_format($event['price'], 2) ?></div>
            <div class="seats-display">
                <?php if ($event['available_seats'] > 0): ?>
                    <?= $event['available_seats'] ?> seats remaining
                <?php else: ?>
                    <span style="color:var(--danger);font-weight:700;">Sold Out</span>
                <?php endif; ?>
            </div>
            <div style="margin-top:20px;">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <!-- Not logged in -->
                    <div class="alert alert-info">
                        <a href="/sport_event/pages/login.php">Login</a> to book this event.
                    </div>
                    <a href="/sport_event/pages/login.php" class="btn btn-primary btn-full">Login to Book</a>

                <?php elseif ($alreadyBooked): ?>
                    <!-- Already booked -->
                    <div class="alert alert-warning">
                        ✅ You have already booked this event.
                    </div>
                    <a href="/sport_event/pages/dashboard.php" class="btn btn-outline btn-full">View My Bookings</a>

                <?php elseif ($event['available_seats'] <= 0): ?>
                    <!-- Sold out -->
                    <button class="btn btn-danger btn-full" disabled>Sold Out</button>

                <?php else: ?>
                    <!-- Book Now button — submits POST to book_event.php -->
                    <form action="/sport_event/actions/book_event.php" method="POST">
                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                        <button type="submit" class="btn btn-primary btn-full" style="font-size:1.05rem;padding:14px;">
                            🎟️ Book Now
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Participants card -->
        <div class="sidebar-card">
            <h3>Participants (<?= count($participants) ?>)</h3>
            <?php if (empty($participants)): ?>
                <p style="color:var(--gray-400);font-size:.88rem;">No bookings yet. Be the first!</p>
            <?php else: ?>
                <div class="participant-list">
                    <?php foreach ($participants as $p): ?>
                        <div class="participant-item">
                            <div class="participant-avatar">
                                <?= strtoupper(substr($p['username'], 0, 1)) ?>
                            </div>
                            <?= htmlspecialchars($p['username']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
