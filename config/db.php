<?php
/**
 * Database connection — included by every page that needs DB access.
 * Uses PDO with prepared statements for security.
 */

$host   = 'localhost';
$dbname = 'sport_events';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,   false);
} catch (PDOException $e) {
    // In production you would log this, not display it
    die('<p style="font-family:sans-serif;color:red;">Database connection failed. Make sure MySQL is running and you have imported the database. Error: ' . htmlspecialchars($e->getMessage()) . '</p>');
}
