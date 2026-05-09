<?php
/**
 * events.php — Full events listing with sport-type and date filters.
 */

$pageTitle = 'All Events';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

// --- Read filter parameters from the URL (GET request)
$filterSport = trim($_GET['sport'] ?? '');
$filterDate  = trim($_GET['date']  ?? '');

// Build the query dynamically based on active filters
$sql    = "SELECT * FROM events WHERE 1=1";
$params = [];

if ($filterSport !== '') {
    $sql    .= " AND sport_type = ?";
    $params[] = $filterSport;
}
if ($filterDate !== '') {
    $sql    .= " AND date = ?";
    $params[] = $filterDate;
}

$sql .= " ORDER BY date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

// Sport options for the filter dropdown
$sportTypes = ['Football', 'Basketball', 'Tennis', 'Running', 'Marathon'];

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

<!-- Page header -->
<div class="page-header">
    <h1>All Events</h1>
    <p>Find and book the sports events you love</p>
</div>

<div class="container" style="padding-top: 40px; padding-bottom: 60px;">

    <!-- ===== FILTER BAR ===== -->
    <form class="filter-bar" method="GET" action="">
        <div class="form-group">
            <label for="sport">Sport Type</label>
            <select name="sport" id="sport" class="form-control">
                <option value="">All Sports</option>
                <?php foreach ($sportTypes as $s): ?>
                    <option value="<?= $s ?>" <?= $filterSport === $s ? 'selected' : '' ?>>
                        <?= $s ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="date">Date</label>
            <input type="date" name="date" id="date" class="form-control"
                   value="<?= htmlspecialchars($filterDate) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if ($filterSport || $filterDate): ?>
            <a href="/sport_event/pages/events.php" class="btn btn-outline">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Results count -->
    <p style="color:var(--gray-400);font-size:.9rem;margin-bottom:20px;">
        <?= count($events) ?> event<?= count($events) !== 1 ? 's' : '' ?> found
        <?php if ($filterSport): ?> in <strong><?= htmlspecialchars($filterSport) ?></strong><?php endif; ?>
    </p>

    <!-- ===== EVENTS GRID ===== -->
    <?php if (empty($events)): ?>
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <h3>No events found</h3>
            <p>Try adjusting your filters or <a href="/sport_event/pages/events.php">view all events</a>.</p>
        </div>
    <?php else: ?>
        <div class="events-grid">
            <?php foreach ($events as $event): ?>
                <div class="event-card">
                    <div class="event-card-img <?= sportClass($event['sport_type']) ?>">
                        <?= sportEmoji($event['sport_type']) ?>
                        <span class="sport-badge"><?= htmlspecialchars($event['sport_type']) ?></span>
                    </div>
                    <div class="event-card-body">
                        <h3><?= htmlspecialchars($event['title']) ?></h3>
                        <div class="event-meta">
                            <span><span class="icon">📅</span> <?= date('M j, Y', strtotime($event['date'])) ?></span>
                            <span><span class="icon">📍</span> <?= htmlspecialchars($event['location']) ?></span>
                        </div>
                        <div class="event-price">$<?= number_format($event['price'], 2) ?></div>
                        <div class="event-seats <?= $event['available_seats'] < 10 ? 'low' : '' ?>">
                            <?= $event['available_seats'] ?> seats available
                        </div>
                        <div class="event-card-actions">
                            <a href="/sport_event/pages/event_details.php?id=<?= $event['id'] ?>"
                               class="btn btn-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
