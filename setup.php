<?php
/**
 * One-time database setup script.
 * Run this once in your browser: http://localhost/sport_event/setup.php
 * DELETE this file after setup is complete.
 */

$host     = 'localhost';
$rootUser = 'root';
$rootPass = '';
$dbname   = 'sport_events';

try {
    // Connect without selecting a database
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $rootUser, $rootPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        username     VARCHAR(100) NOT NULL,
        email        VARCHAR(150) NOT NULL UNIQUE,
        password     VARCHAR(255) NOT NULL,
        role         ENUM('user', 'admin') DEFAULT 'user',
        created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Events table
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id               INT AUTO_INCREMENT PRIMARY KEY,
        title            VARCHAR(200) NOT NULL,
        sport_type       VARCHAR(100) NOT NULL,
        date             DATE NOT NULL,
        location         VARCHAR(200) NOT NULL,
        price            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        available_seats  INT NOT NULL DEFAULT 0,
        description      TEXT,
        image_url        VARCHAR(500),
        created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Bookings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        user_id     INT NOT NULL,
        event_id    INT NOT NULL,
        booked_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        UNIQUE KEY unique_booking (user_id, event_id)
    ) ENGINE=InnoDB");

    // Insert admin user (password: admin123)
    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->execute(['admin', 'admin@sport.com', $adminHash]);

    // Insert 5 sample events
    $events = [
        ['Champions League Final',    'Football',   '2026-06-15', 'London Stadium, London',          120.00, 25, 'The biggest match of the year! Book your tickets now for an unforgettable Champions League Final experience featuring top clubs from across Europe.'],
        ['City Marathon 2026',        'Running',    '2026-06-22', 'Central Park, New York',           45.00, 100, 'Join thousands of runners in this annual city marathon. All fitness levels welcome. Medals for all finishers!'],
        ['Tennis Open Championship',  'Tennis',     '2026-07-05', 'Roland Garros, Paris',             85.00, 50, 'Watch top professional tennis players compete for glory in this prestigious open championship on clay courts.'],
        ['NBA All-Star Basketball',   'Basketball', '2026-07-18', 'Madison Square Garden, NYC',      150.00, 30, 'Experience the very best basketball players in the world at this exclusive All-Star showcase event.'],
        ['International Marathon',    'Marathon',   '2026-08-10', 'Olympic Stadium, Athens',          60.00, 75, 'Historic marathon route through Athens. Experience the authentic birthplace of the marathon in this iconic race.'],
    ];

    $stmt = $pdo->prepare(
        "INSERT IGNORE INTO events (title, sport_type, date, location, price, available_seats, description)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    foreach ($events as $e) {
        $stmt->execute($e);
    }

    echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
    <title>Setup Complete</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 60px auto; padding: 0 20px; }
        .success { color: #2d6a4f; } .danger { color: #c00; }
        a { color: #ff6b35; }
    </style></head><body>';
    echo '<h2 class="success">Setup complete!</h2>';
    echo '<p>Database <strong>sport_events</strong> created with all tables and sample data.</p>';
    echo '<ul>';
    echo '<li>Admin email: <strong>admin@sport.com</strong></li>';
    echo '<li>Admin password: <strong>admin123</strong></li>';
    echo '</ul>';
    echo '<p class="danger"><strong>Delete setup.php now — it should not stay on the server.</strong></p>';
    echo '<p><a href="index.php">Go to the platform &rarr;</a></p>';
    echo '</body></html>';

} catch (PDOException $e) {
    echo '<h2 style="color:red;font-family:sans-serif">Setup failed</h2>';
    echo '<p style="font-family:sans-serif">' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p style="font-family:sans-serif">Make sure XAMPP MySQL is running and credentials in setup.php are correct.</p>';
}
