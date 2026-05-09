# Sport Events Platform

A dynamic sports event management web application built with **PHP 8**, **MySQL**, **HTML/CSS**, and **JavaScript**.

## Features

- Browse and filter sports events (Football, Basketball, Tennis, Running, Marathon)
- Book events with real-time seat tracking
- User authentication (register, login, logout)
- User dashboard: view & cancel bookings
- Admin panel: add, edit, delete events; manage users; platform stats

---

## Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Backend    | PHP 8+ (sessions, PDO, bcrypt)    |
| Database   | MySQL 5.7+ / MariaDB              |
| Frontend   | HTML5, CSS3 (Grid + Flexbox), Vanilla JS |
| Server     | Apache (XAMPP)                    |

---

## Quick Start

### 1. Prerequisites
- [XAMPP](https://www.apachefriends.org/) with Apache and MySQL running

### 2. Install

```bash
# Copy the project folder into your htdocs
# The folder should be named: sport_event
C:\xampp\htdocs\sport_event\
```

### 3. Database Setup (choose one method)

**Option A — Setup Script (recommended)**
1. Start Apache and MySQL in XAMPP
2. Open your browser and go to: `http://localhost/sport_event/setup.php`
3. You will see a success message when the DB, tables, and sample data are created
4. **Delete `setup.php`** from the project folder after setup

**Option B — phpMyAdmin**
1. Open `http://localhost/phpmyadmin`
2. Create a database named `sport_events`
3. Import `database/sport_event.sql`
4. Run `setup.php` once to create the admin user (or create it manually)

### 4. Open the app

```
http://localhost/sport_event/
```

---

## Admin Credentials

| Field    | Value              |
|----------|--------------------|
| Email    | admin@sport.com    |
| Password | admin123           |

> The admin account is created by `setup.php`. Do not share these credentials in production.

---

## Project Structure

```
sport_event/
├── index.php              → Entry point (redirects to home)
├── setup.php              → One-time DB setup (delete after use)
├── .htaccess              → Disable directory listing
├── README.md
│
├── config/
│   └── db.php             → PDO database connection
│
├── database/
│   └── sport_event.sql    → Schema reference
│
├── includes/
│   ├── header.php         → HTML head + navbar + session_start
│   └── footer.php         → Footer + JS
│
├── pages/
│   ├── home.php           → Landing page with hero + upcoming events
│   ├── events.php         → Filterable events listing
│   ├── event_details.php  → Single event + booking button
│   ├── login.php          → Login form
│   ├── register.php       → Registration form
│   ├── dashboard.php      → User dashboard + Admin panel
│   └── logout.php         → Destroys session
│
├── actions/
│   ├── login_action.php   → Process login POST
│   ├── register_action.php→ Process registration POST
│   ├── book_event.php     → Book an event (with transaction)
│   ├── cancel_booking.php → Cancel a booking
│   ├── add_event.php      → Admin: add event
│   ├── update_event.php   → Admin: edit event
│   ├── delete_event.php   → Admin: delete event
│   └── delete_user.php    → Admin: delete user
│
└── assets/
    ├── style.css          → Full custom stylesheet
    └── images/            → Event images (optional)
```

---

## Security Notes

- All SQL queries use **PDO prepared statements** — no raw user input in SQL
- Passwords stored with **bcrypt** (`password_hash` / `password_verify`)
- User input displayed with **`htmlspecialchars()`** to prevent XSS
- Admin-only actions check `$_SESSION['role'] === 'admin'` before executing
- Booking uses a **database transaction** to prevent race conditions on seat counts

---

## Database Schema

```sql
users     (id, username, email, password, role, created_at)
events    (id, title, sport_type, date, location, price, available_seats, description, image_url, created_at)
bookings  (id, user_id, event_id, booked_at)
```

Foreign keys in `bookings` use `ON DELETE CASCADE`, so deleting a user or event automatically removes related bookings.
# sport_event
