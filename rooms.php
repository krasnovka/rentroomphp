<?php
// rooms.php

require_once 'config.php';

$sql = "SELECT * FROM rooms ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$rooms = $stmt->fetchAll();

include 'header.php';
?>

<div class="page-intro fade-card">
    <span class="section-tag">Rooms</span>
    <h1>Browse available rooms</h1>
    <p>Choose a room, check the details and create a booking.</p>
</div>

<div class="card glass-card fade-card delay-1">
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
                            <?php if (is_logged_in()): ?>
                                <a href="book_room.php?room_id=<?php echo e($row['id']); ?>" class="btn">Book</a>
                            <?php endif; ?>
                            <?php if (is_admin()): ?>
                                <a href="edit_room.php?id=<?php echo e($row['id']); ?>" class="btn btn-secondary">Edit</a>
                                <a href="delete_room.php?id=<?php echo e($row['id']); ?>" class="btn btn-danger" onclick="return confirm('Delete this room?')">Delete</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No rooms found.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
