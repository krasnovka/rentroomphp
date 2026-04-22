<?php
// index.php

require_once 'config.php';

// Latest rooms for home page preview
$sql = "SELECT * FROM rooms ORDER BY id DESC LIMIT 3";
$stmt = $conn->prepare($sql);
$stmt->execute();
$rooms = $stmt->fetchAll();

// Small counters for the home hero cards
$sql = "SELECT COUNT(*) FROM rooms";
$stmt = $conn->prepare($sql);
$stmt->execute();
$room_count = (int)$stmt->fetchColumn();

$sql = "SELECT COUNT(*) FROM bookings WHERE status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$active_booking_count = (int)$stmt->fetchColumn();

$sql = "SELECT COUNT(*) FROM users";
$stmt = $conn->prepare($sql);
$stmt->execute();
$user_count = (int)$stmt->fetchColumn();

include 'header.php';
?>

<!-- Home hero section -->
<div class="hero-block">
    <div class="hero-text">
        <span class="hero-badge">VARO</span>
        <h1>Room booking made simple</h1>
        <p>View rooms, choose time and create bookings in a few steps.</p>
        <div class="actions">
            <a href="rooms.php" class="btn">View Rooms</a>
            <?php if (!is_logged_in()): ?>
                <a href="register.php" class="btn btn-secondary">Register</a>
            <?php else: ?>
                <a href="my_bookings.php" class="btn btn-secondary">My Bookings</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero-side">
        <div class="mini-card">
            <strong><?php echo e($room_count); ?></strong>
            <span>Rooms</span>
        </div>
        <div class="mini-card">
            <strong><?php echo e($active_booking_count); ?></strong>
            <span>Active bookings</span>
        </div>
        <div class="mini-card">
            <strong><?php echo e($user_count); ?></strong>
            <span>Users</span>
        </div>
    </div>
</div>

<!-- Short notes under hero -->
<div class="hero-note fade-card">
    <span>Simple interface</span>
    <span>Easy booking flow</span>
    <span>Works for users and admin</span>
</div>

<!-- Two info cards on home page -->
<div class="grid-two">
    <div class="card fade-card delay-1">
        <h2>Main Features</h2>
        <ul>
            <li>View rooms and room details</li>
            <li>Make bookings by date and time</li>
            <li>See and cancel your bookings</li>
            <li>Manage rooms in admin panel</li>
        </ul>
    </div>

    <div class="card fade-card delay-2">
        <h2>How It Works</h2>
        <ul>
            <li>Choose a room</li>
            <li>Select date and time</li>
            <li>Create booking</li>
            <li>Check your bookings later</li>
        </ul>
    </div>
</div>

<!-- Quick actions block -->
<div class="card fade-card delay-3">
    <h2>Quick Actions</h2>
    <div class="quick-links">
        <a href="rooms.php" class="quick-link">Open room list</a>
        <a href="about.php" class="quick-link">Open about page</a>
        <?php if (is_logged_in()): ?>
            <a href="my_bookings.php" class="quick-link">Open my bookings</a>
            <a href="change_password.php" class="quick-link">Change password</a>
        <?php else: ?>
            <a href="login.php" class="quick-link">Login to account</a>
        <?php endif; ?>
        <?php if (is_admin()): ?>
            <a href="admin_panel.php" class="quick-link">Go to admin panel</a>
        <?php endif; ?>
    </div>
</div>

<!-- Latest rooms table -->
<div class="card fade-card delay-3">
    <h2>Latest Rooms</h2>
    <?php if ($rooms): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Capacity</th>
            </tr>
            <?php foreach ($rooms as $row): ?>
                <tr>
                    <td><?php echo e($row['id']); ?></td>
                    <td><?php echo e($row['name']); ?></td>
                    <td><?php echo e($row['description']); ?></td>
                    <td><?php echo e($row['capacity']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p style="margin-top: 15px;">
            <a href="rooms.php" class="btn">See All Rooms</a>
        </p>
    <?php else: ?>
        <p>No rooms found.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
