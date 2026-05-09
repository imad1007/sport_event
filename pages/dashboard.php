<?php
/**
 * dashboard.php — User dashboard and Admin panel.
 * Requires login. Admins see extra management sections.
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

// Access control — must be logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Please login to access your dashboard.';
    header('Location: /sport_event/pages/login.php');
    exit;
}

$userId   = $_SESSION['user_id'];
$role     = $_SESSION['role'] ?? 'user';
$username = $_SESSION['username'] ?? 'User';
$isAdmin  = ($role === 'admin');

// --- Fetch current user's bookings
$bookStmt = $pdo->prepare(
    "SELECT b.id AS booking_id, e.id AS event_id, e.title, e.sport_type, e.date, e.location, e.price
     FROM bookings b
     JOIN events e ON b.event_id = e.id
     WHERE b.user_id = ?
     ORDER BY e.date ASC"
);
$bookStmt->execute([$userId]);
$myBookings = $bookStmt->fetchAll();

// --- Admin data
$allEvents = [];
$allUsers  = [];
$stats     = [];

if ($isAdmin) {
    // All events
    $allEvents = $pdo->query("SELECT * FROM events ORDER BY date ASC")->fetchAll();

    // All users
    $allUsers = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();

    // Dashboard stats
    $stats['events']   = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $stats['users']    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['bookings'] = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $stats['revenue']  = $pdo->query("SELECT SUM(e.price) FROM bookings b JOIN events e ON b.event_id = e.id")->fetchColumn() ?? 0;
}

// Determine which tab to show (for admin)
$activeTab = $_GET['tab'] ?? 'bookings';
?>

<div class="dashboard-layout">

    <!-- ===== SIDEBAR ===== -->
    <aside class="dashboard-sidebar">
        <div class="dash-user-info">
            <div class="dash-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
            <h3><?= htmlspecialchars($username) ?></h3>
            <p><?= $isAdmin ? '👑 Administrator' : '👤 Member' ?></p>
        </div>
        <nav class="dash-nav">
            <a href="?tab=bookings" class="<?= $activeTab === 'bookings' ? 'active' : '' ?>">
                🎟️ My Bookings
            </a>
            <?php if ($isAdmin): ?>
                <a href="?tab=events" class="<?= $activeTab === 'events' ? 'active' : '' ?>">
                    📅 Manage Events
                </a>
                <a href="?tab=add_event" class="<?= $activeTab === 'add_event' ? 'active' : '' ?>">
                    ➕ Add Event
                </a>
                <a href="?tab=users" class="<?= $activeTab === 'users' ? 'active' : '' ?>">
                    👥 Manage Users
                </a>
            <?php endif; ?>
            <a href="/sport_event/pages/logout.php" style="margin-top:16px;">
                🚪 Logout
            </a>
        </nav>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="dashboard-main">

        <!-- Welcome banner -->
        <div class="dash-card dash-welcome">
            <h1>Welcome, <?= htmlspecialchars($username) ?>! 👋</h1>
            <p>
                <?php if ($isAdmin): ?>
                    You are logged in as an administrator. Manage events and users from the sidebar.
                <?php else: ?>
                    Manage your bookings and account settings below.
                <?php endif; ?>
            </p>
        </div>

        <?php if ($isAdmin && $activeTab === 'bookings'): ?>
            <!-- Admin stats overview when on bookings tab -->
            <div class="dash-card">
                <h2>📊 Platform Overview</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= $stats['events'] ?></div>
                        <div class="stat-label">Total Events</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $stats['users'] ?></div>
                        <div class="stat-label">Registered Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $stats['bookings'] ?></div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">$<?= number_format($stats['revenue'], 0) ?></div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ====== TAB: MY BOOKINGS ====== -->
        <?php if ($activeTab === 'bookings'): ?>
            <div class="dash-card">
                <h2>🎟️ My Bookings</h2>
                <?php if (empty($myBookings)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">🎟️</div>
                        <h3>No bookings yet</h3>
                        <p><a href="/sport_event/pages/events.php">Browse events</a> and book your first event!</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Sport</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myBookings as $b): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($b['title']) ?></strong></td>
                                        <td><?= htmlspecialchars($b['sport_type']) ?></td>
                                        <td><?= date('M j, Y', strtotime($b['date'])) ?></td>
                                        <td><?= htmlspecialchars($b['location']) ?></td>
                                        <td>$<?= number_format($b['price'], 2) ?></td>
                                        <td>
                                            <a href="/sport_event/pages/event_details.php?id=<?= $b['event_id'] ?>"
                                               class="btn btn-outline btn-sm">View</a>
                                            <!-- Cancel booking -->
                                            <form action="/sport_event/actions/cancel_booking.php" method="POST"
                                                  style="display:inline;"
                                                  onsubmit="return confirm('Cancel this booking?')">
                                                <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- ====== TAB: MANAGE EVENTS (admin) ====== -->
        <?php if ($isAdmin && $activeTab === 'events'): ?>
            <div class="dash-card">
                <h2>📅 Manage Events</h2>
                <div class="admin-actions">
                    <a href="?tab=add_event" class="btn btn-primary">➕ Add New Event</a>
                </div>
                <?php if (empty($allEvents)): ?>
                    <p style="color:var(--gray-400);">No events in the database.</p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Sport</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Price</th>
                                    <th>Seats</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allEvents as $ev): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($ev['title']) ?></strong></td>
                                        <td><?= htmlspecialchars($ev['sport_type']) ?></td>
                                        <td><?= date('M j, Y', strtotime($ev['date'])) ?></td>
                                        <td><?= htmlspecialchars($ev['location']) ?></td>
                                        <td>$<?= number_format($ev['price'], 2) ?></td>
                                        <td><?= $ev['available_seats'] ?></td>
                                        <td style="white-space:nowrap;">
                                            <a href="?tab=edit_event&event_id=<?= $ev['id'] ?>"
                                               class="btn btn-outline btn-sm">Edit</a>
                                            <form action="/sport_event/actions/delete_event.php" method="POST"
                                                  style="display:inline;"
                                                  onsubmit="return confirm('Delete this event? This will also remove all bookings.')">
                                                <input type="hidden" name="event_id" value="<?= $ev['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- ====== TAB: ADD EVENT (admin) ====== -->
        <?php if ($isAdmin && $activeTab === 'add_event'): ?>
            <div class="dash-card">
                <h2>➕ Add New Event</h2>
                <form action="/sport_event/actions/add_event.php" method="POST" class="admin-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Event Title *</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Champions League Final">
                        </div>
                        <div class="form-group">
                            <label>Sport Type *</label>
                            <select name="sport_type" class="form-control" required>
                                <option value="">Select sport...</option>
                                <option value="Football">Football</option>
                                <option value="Basketball">Basketball</option>
                                <option value="Tennis">Tennis</option>
                                <option value="Running">Running</option>
                                <option value="Marathon">Marathon</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date *</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Location *</label>
                            <input type="text" name="location" class="form-control" required placeholder="e.g. London Stadium">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Price ($) *</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label>Available Seats *</label>
                            <input type="number" name="available_seats" class="form-control" min="1" required placeholder="50">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" placeholder="Describe the event..."></textarea>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">Add Event</button>
                        <a href="?tab=events" class="btn btn-outline" style="margin-left:10px;">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- ====== TAB: EDIT EVENT (admin) ====== -->
        <?php if ($isAdmin && $activeTab === 'edit_event' && isset($_GET['event_id'])): ?>
            <?php
            $editId   = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
            $editStmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
            $editStmt->execute([$editId]);
            $editEvent = $editStmt->fetch();
            ?>
            <?php if ($editEvent): ?>
                <div class="dash-card">
                    <h2>✏️ Edit Event</h2>
                    <form action="/sport_event/actions/update_event.php" method="POST" class="admin-form">
                        <input type="hidden" name="event_id" value="<?= $editEvent['id'] ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Event Title *</label>
                                <input type="text" name="title" class="form-control" required
                                       value="<?= htmlspecialchars($editEvent['title']) ?>">
                            </div>
                            <div class="form-group">
                                <label>Sport Type *</label>
                                <select name="sport_type" class="form-control" required>
                                    <?php foreach (['Football','Basketball','Tennis','Running','Marathon'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $editEvent['sport_type'] === $s ? 'selected' : '' ?>>
                                            <?= $s ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Date *</label>
                                <input type="date" name="date" class="form-control" required
                                       value="<?= $editEvent['date'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Location *</label>
                                <input type="text" name="location" class="form-control" required
                                       value="<?= htmlspecialchars($editEvent['location']) ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Price ($) *</label>
                                <input type="number" name="price" class="form-control" step="0.01" min="0" required
                                       value="<?= $editEvent['price'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Available Seats *</label>
                                <input type="number" name="available_seats" class="form-control" min="0" required
                                       value="<?= $editEvent['available_seats'] ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control"><?= htmlspecialchars($editEvent['description'] ?? '') ?></textarea>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="?tab=events" class="btn btn-outline" style="margin-left:10px;">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="dash-card">
                    <p class="alert alert-warning">Event not found.</p>
                    <a href="?tab=events" class="btn btn-outline">Back to Events</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- ====== TAB: MANAGE USERS (admin) ====== -->
        <?php if ($isAdmin && $activeTab === 'users'): ?>
            <div class="dash-card">
                <h2>👥 All Users</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($u['username']) ?></strong>
                                        <?php if ($u['id'] == $userId): ?>
                                            <span style="color:var(--orange);font-size:.75rem;"> (you)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <span style="
                                            background: <?= $u['role']==='admin' ? 'var(--orange)' : 'var(--gray-200)' ?>;
                                            color:      <?= $u['role']==='admin' ? 'var(--white)'  : 'var(--gray-800)' ?>;
                                            padding: 3px 10px; border-radius: 20px; font-size:.78rem; font-weight:700;">
                                            <?= $u['role'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                                    <td>
                                        <?php if ($u['id'] != $userId): ?>
                                            <form action="/sport_event/actions/delete_user.php" method="POST"
                                                  style="display:inline;"
                                                  onsubmit="return confirm('Delete user <?= htmlspecialchars($u['username']) ?>? This is permanent.')">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color:var(--gray-400);font-size:.8rem;">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div><!-- /.dashboard-main -->
</div><!-- /.dashboard-layout -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
