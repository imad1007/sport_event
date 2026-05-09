-- ============================================================
-- Sport Events Platform — Database Schema
-- Import this file via phpMyAdmin or run: setup.php
-- ============================================================

CREATE DATABASE IF NOT EXISTS `sport_events`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `sport_events`;

-- ------------------------------------------------------------
-- Table: users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `username`   VARCHAR(100) NOT NULL,
    `email`      VARCHAR(150) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `role`       ENUM('user', 'admin') DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: events
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `events` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `title`           VARCHAR(200) NOT NULL,
    `sport_type`      VARCHAR(100) NOT NULL,
    `date`            DATE NOT NULL,
    `location`        VARCHAR(200) NOT NULL,
    `price`           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `available_seats` INT NOT NULL DEFAULT 0,
    `description`     TEXT,
    `image_url`       VARCHAR(500),
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: bookings
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
    `id`        INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`   INT NOT NULL,
    `event_id`  INT NOT NULL,
    `booked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`)  REFERENCES `users`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_booking` (`user_id`, `event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Admin user — password is 'admin123'
-- Generate the hash in PHP: password_hash('admin123', PASSWORD_DEFAULT)
-- Or just run setup.php which does this automatically.
-- ------------------------------------------------------------
-- INSERT INTO `users` (`username`, `email`, `password`, `role`)
-- VALUES ('admin', 'admin@sport.com', '<hash_here>', 'admin');

-- ------------------------------------------------------------
-- Sample events (no password hashing needed here)
-- ------------------------------------------------------------
INSERT INTO `events` (`title`, `sport_type`, `date`, `location`, `price`, `available_seats`, `description`) VALUES
('Champions League Final',   'Football',   '2026-06-15', 'London Stadium, London',        120.00, 25,  'The biggest match of the year! Book your tickets now.'),
('City Marathon 2026',       'Running',    '2026-06-22', 'Central Park, New York',         45.00, 100, 'Join thousands of runners in this annual city marathon. All fitness levels welcome!'),
('Tennis Open Championship', 'Tennis',     '2026-07-05', 'Roland Garros, Paris',           85.00, 50,  'Watch top professional tennis players compete in this prestigious championship.'),
('NBA All-Star Basketball',  'Basketball', '2026-07-18', 'Madison Square Garden, NYC',    150.00, 30,  'Experience the best basketball players in the world at this exclusive All-Star event.'),
('International Marathon',   'Marathon',   '2026-08-10', 'Olympic Stadium, Athens',        60.00, 75,  'Historic marathon route through Athens — the birthplace of the marathon.');
