<?php
// my_bookings.php

require_once 'auth.php';

$sql = "SELECT bookings.*, rooms.name AS room_name
        FROM bookings
        INNER JOIN rooms ON bookings.room_id = rooms.id
        WHERE bookings.user_id = ?
        ORDER BY bookings.booking_date DESC, bookings.start_time DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$_SESSION['user']['id']]);
$bookings = $stmt->fetchAll();

include 'header.php';
?>

<div class="page-intro fade-card">
    <span class="section-tag">Bookings</span>
    <h1>My bookings</h1>
    <p>View your room bookings, check status and cancel active ones.</p>
</div>

<div class="card glass-card fade-card delay-1">
    <?php if ($bookings): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Room</th>
                <th>Date</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php foreach ($bookings as $row): ?>
                <tr>
                    <td><?php echo e($row['id']); ?></td>
                    <td><strong><?php echo e($row['room_name']); ?></strong></td>
                    <td><?php echo e($row['booking_date']); ?></td>
                    <td><?php echo e(substr($row['start_time'], 0, 5)); ?></td>
                    <td><?php echo e(substr($row['end_time'], 0, 5)); ?></td>
                    <td>
                        <span class="status-pill <?php echo $row['status'] === 'active' ? 'status-active' : 'status-cancelled'; ?>">
                            <?php echo e($row['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($row['status'] === 'active'): ?>
                            <a href="cancel_booking.php?id=<?php echo e($row['id']); ?>" class="btn btn-danger" onclick="return confirm('Cancel this booking?')">Cancel</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>You have no bookings yet.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
