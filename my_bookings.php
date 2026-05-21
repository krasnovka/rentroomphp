<?php

require_once 'auth.php';

// load current user's bookings
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

<!-- page header -->
<div class="page-intro fade-card">
    <span class="section-tag">Bookings</span>
    <h1>My bookings</h1>
    <p>View your room bookings, check status and cancel active ones.</p>
</div>

<!-- bookings table -->
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

                <?php
                // calculate booking status display data
                $status_data = get_booking_status_data(
                    $row['booking_date'],
                    $row['end_time'],
                    $row['status']
                );
                ?>

                <tr>
                    <td><?php echo e($row['id']); ?></td>
                    <td><strong><?php echo e($row['room_name']); ?></strong></td>
                    <td><?php echo e($row['booking_date']); ?></td>
                    <td><?php echo e(substr($row['start_time'], 0, 5)); ?></td>
                    <td><?php echo e(substr($row['end_time'], 0, 5)); ?></td>

                    <td>
                        <span class="status-pill <?php echo e($status_data['class']); ?>">
                            <?php echo e($status_data['label']); ?>
                        </span>
                    </td>

                    <td>
                        <?php if ($row['status'] === 'active'): ?>
                            <form method="post" action="cancel_booking.php"
                                  class="inline-form"
                                  onsubmit="return confirm('Cancel this booking?')">

                                <input type="hidden" name="id" value="<?php echo e($row['id']); ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">

                                <button type="submit" class="btn btn-danger">Cancel</button>
                            </form>
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