<?php
/**
 * header.php — included at the top of every page.
 * Starts the session and outputs the HTML head + navbar.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Resolve the base path so assets and links work from any sub-folder
$basePath = '/sport_event/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>Sport Events</title>
    <link rel="stylesheet" href="<?= $basePath ?>assets/style.css">
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
    <div class="nav-container">
        <!-- Logo -->
        <a href="<?= $basePath ?>pages/home.php" class="nav-logo">
            <span class="logo-icon">&#9917;</span> Sport<span class="logo-accent">Events</span>
        </a>

        <!-- Hamburger for mobile -->
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>

        <!-- Nav links -->
        <ul class="nav-links" id="navLinks">
            <li><a href="<?= $basePath ?>pages/home.php">Home</a></li>
            <li><a href="<?= $basePath ?>pages/events.php">Events</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="<?= $basePath ?>pages/dashboard.php">Dashboard</a></li>
                <li>
                    <a href="<?= $basePath ?>pages/logout.php" class="btn-nav">
                        Logout
                    </a>
                </li>
                <li class="nav-user">
                    Hi, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                        <span class="badge-admin">Admin</span>
                    <?php endif; ?>
                </li>
            <?php else: ?>
                <li><a href="<?= $basePath ?>pages/login.php">Login</a></li>
                <li><a href="<?= $basePath ?>pages/register.php" class="btn-nav">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- ===== FLASH MESSAGES ===== -->
<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="flash flash-success">
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
        <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="flash flash-error">
        <?= htmlspecialchars($_SESSION['flash_error']) ?>
        <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<main class="main-content">
