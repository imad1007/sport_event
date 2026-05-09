<?php
/**
 * home.php — Main landing page.
 * Shows hero search, upcoming events, and popular sport categories.
 */

$pageTitle = 'Home';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

// Fetch the 3 most recent upcoming events
$stmt = $pdo->prepare(
    "SELECT * FROM events WHERE date >= CURDATE() ORDER BY date ASC LIMIT 3"
);
$stmt->execute();
$upcomingEvents = $stmt->fetchAll();

// Helper: return a CSS class based on sport type
function sportClass(string $type): string {
    $map = [
        'football'   => 'sport-football',
        'basketball' => 'sport-basketball',
        'tennis'     => 'sport-tennis',
        'running'    => 'sport-running',
        'marathon'   => 'sport-marathon',
    ];
    return $map[strtolower($type)] ?? 'sport-default';
}

// Helper: return an emoji for a sport type
function sportEmoji(string $type): string {
    $map = [
        'football'   => '⚽',
        'basketball' => '🏀',
        'tennis'     => '🎾',
        'running'    => '🏃',
        'marathon'   => '🏃‍♂️',
    ];
    return $map[strtolower($type)] ?? '🏆';
}
?>

<!-- ===== HERO ===== -->
<section class="hero">
    <div class="hero-content">
        <h1>Discover &amp; Book <span>Sports Events</span></h1>
        <p>Find football matches, marathons, tennis tournaments and more — all in one place.</p>

        <!-- Search form — submits to events.php with GET params -->
        <form class="search-bar" action="/sport_event/pages/events.php" method="GET">
            <select name="sport" class="form-control">
                <option value="">All Sports</option>
                <option value="Football">⚽ Football</option>
                <option value="Basketball">🏀 Basketball</option>
                <option value="Tennis">🎾 Tennis</option>
                <option value="Running">🏃 Running</option>
                <option value="Marathon">🏃‍♂️ Marathon</option>
            </select>
            <input type="date" name="date" placeholder="Pick a date">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
</section>

<!-- ===== UPCOMING EVENTS ===== -->
<section class="section" style="background:#fff;">
    <div class="container">
        <div class="section-header">
            <h2>Upcoming Events</h2>
            <a href="/sport_event/pages/events.php" class="btn btn-outline">View All Events</a>
        </div>

        <?php if (empty($upcomingEvents)): ?>
            <div class="empty-state">
                <div class="empty-icon">🏆</div>
                <h3>No upcoming events yet</h3>
                <p>Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach ($upcomingEvents as $event): ?>
                    <div class="event-card">
                        <!-- Sport-type coloured banner -->
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
                                <a href="/sport_event/pages/event_details.php?id=<?= $event['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===== POPULAR SPORTS ===== -->
<section class="section" style="background:var(--gray-100);">
    <div class="container">
        <div class="section-header">
            <h2>Popular Sports</h2>
        </div>
        <div class="sports-grid">
            <a class="sport-pill" href="/sport_event/pages/events.php?sport=Football">
                <span class="pill-icon">⚽</span> Football
            </a>
            <a class="sport-pill" href="/sport_event/pages/events.php?sport=Basketball">
                <span class="pill-icon">🏀</span> Basketball
            </a>
            <a class="sport-pill" href="/sport_event/pages/events.php?sport=Tennis">
                <span class="pill-icon">🎾</span> Tennis
            </a>
            <a class="sport-pill" href="/sport_event/pages/events.php?sport=Running">
                <span class="pill-icon">🏃</span> Running
            </a>
            <a class="sport-pill" href="/sport_event/pages/events.php?sport=Marathon">
                <span class="pill-icon">🏃‍♂️</span> Marathon
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
