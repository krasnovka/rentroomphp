<?php
// admin_panel.php

require_once 'admin_auth.php';

$sql = "SELECT * FROM rooms ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$rooms = $stmt->fetchAll();

$sql = "SELECT bookings.*, users.name AS user_name, users.email, rooms.name AS room_name
        FROM bookings
        INNER JOIN users ON bookings.user_id = users.id
        INNER JOIN rooms ON bookings.room_id = rooms.id
        ORDER BY bookings.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$bookings = $stmt->fetchAll();

include 'header.php';
?>

<div class="page-intro fade-card">
    <span class="section-tag">Admin</span>
    <h1>Admin panel</h1>
    <p>Manage rooms and review all bookings in one place.</p>
</div>

<div class="card glass-card fade-card delay-1">
    <div class="section-head">
        <div>
            <h2>Rooms</h2>
            <p class="small-text">Add, edit and remove rooms.</p>
        </div>
        <a href="add_room.php" class="btn">Add Room</a>
    </div>

    <?php if ($rooms): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Capacity</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($rooms as $row): ?>
                <tr>
                    <td><?php echo e($row['id']); ?></td>
                    <td><strong><?php echo e($row['name']); ?></strong></td>
                    <td><?php echo e($row['description']); ?></td>
                    <td><?php echo e($row['capacity']); ?> people</td>
                    <td>
                        <div class="actions">
                            <a href="edit_room.php?id=<?php echo e($row['id']); ?>" class="btn btn-secondary">Edit</a>
                            <a href="delete_room.php?id=<?php echo e($row['id']); ?>" class="btn btn-danger" onclick="return confirm('Delete this room?')">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No rooms found.</p>
    <?php endif; ?>
</div>

<div class="card glass-card fade-card delay-2">
    <div class="section-head">
        <div>
            <h2>All bookings</h2>
            <p class="small-text">Latest booking activity in the system.</p>
        </div>
    </div>

    <?php if ($bookings): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Email</th>
                <th>Room</th>
                <th>Date</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
            <?php foreach ($bookings as $row): ?>
                <tr>
                    <td><?php echo e($row['id']); ?></td>
                    <td><strong><?php echo e($row['user_name']); ?></strong></td>
                    <td><?php echo e($row['email']); ?></td>
                    <td><?php echo e($row['room_name']); ?></td>
                    <td><?php echo e($row['booking_date']); ?></td>
                    <td><?php echo e(substr($row['start_time'], 0, 5)); ?></td>
                    <td><?php echo e(substr($row['end_time'], 0, 5)); ?></td>
                    <td>
                        <span class="status-pill <?php echo $row['status'] === 'active' ? 'status-active' : 'status-cancelled'; ?>">
                            <?php echo e($row['status']); ?>
                        </span>
                    </td>
                    <td><?php echo e($row['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No bookings found.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
