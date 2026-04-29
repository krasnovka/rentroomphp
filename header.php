<?php
// header.php

// Current page for active menu item
$current_page = basename($_SERVER['PHP_SELF']);

// Flash message from session
$message = get_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VARO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Fraunces:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <div class="top-bar">
            <!-- Logo block -->
            <div class="logo-block">
                <a href="index.php" class="logo">VARO</a>
                <span class="logo-text">Smart Room Booking</span>
            </div>

            <!-- Main navigation -->
            <nav class="menu">
                <a href="index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Home</a>
                <a href="rooms.php" class="<?php echo $current_page === 'rooms.php' ? 'active' : ''; ?>">Rooms</a>
                <a href="booking_calendar.php" class="<?php echo $current_page === 'booking_calendar.php' ? 'active' : ''; ?>">Calendar</a>
                <a href="feedback.php" class="<?php echo $current_page === 'feedback.php' ? 'active' : ''; ?>">Feedback</a>
                <a href="about.php" class="<?php echo $current_page === 'about.php' ? 'active' : ''; ?>">About</a>
                <?php if (is_logged_in()): ?>
                    <a href="profile.php" class="<?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">Profile</a>
                    <a href="my_bookings.php" class="<?php echo $current_page === 'my_bookings.php' ? 'active' : ''; ?>">My Bookings</a>
                    <?php if (is_admin()): ?>
                        <a href="admin_panel.php" class="<?php echo $current_page === 'admin_panel.php' ? 'active' : ''; ?>">Admin Panel</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="<?php echo $current_page === 'login.php' ? 'active' : ''; ?>">Login</a>
                    <a href="register.php" class="<?php echo $current_page === 'register.php' ? 'active' : ''; ?>">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>

<main class="container main-content">
    <?php if ($message): ?>
        <div class="message <?php echo e($message['type']); ?>">
            <?php echo e($message['text']); ?>
        </div>
    <?php endif; ?>
