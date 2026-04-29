<?php
// rooms.php

require_once 'config.php';

// Search value from query string
$search = trim($_GET['search'] ?? '');

// Room search or full room list
if ($search !== '') {
    $sql = "SELECT * FROM rooms
            WHERE name LIKE ? OR description LIKE ? OR capacity LIKE ?
            ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $like = '%' . $search . '%';
    $stmt->execute([$like, $like, $like]);
} else {
    $sql = "SELECT * FROM rooms ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}

$rooms = $stmt->fetchAll();

include 'header.php';
?>

<!-- Page heading -->
<div class="page-intro fade-card">
    <span class="section-tag">Rooms</span>
    <h1>Browse available rooms</h1>
    <p>Choose a room, check the details and create a booking.</p>
</div>

<!-- Search block -->
<div class="card glass-card fade-card delay-1">
    <div class="section-head">
        <div>
            <h2>Search Rooms</h2>
            <p class="small-text">Search by room name, description or capacity.</p>
        </div>
    </div>

    <form method="get" class="search-form">
        <div class="search-row">
            <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search rooms...">
            <button type="submit">Search</button>
            <?php if ($search !== ''): ?>
                <a href="rooms.php" class="btn btn-secondary">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Rooms table -->
<div class="card glass-card fade-card delay-2">
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
                            <a href="booking_calendar.php?room_id=<?php echo e($row['id']); ?>" class="btn btn-secondary">Calendar</a>
                            <?php if (is_admin()): ?>
                                <a href="edit_room.php?id=<?php echo e($row['id']); ?>" class="btn btn-secondary">Edit</a>
                                <form method="post" action="delete_room.php" class="inline-form" onsubmit="return confirm('Delete this room?')">
                                    <input type="hidden" name="id" value="<?php echo e($row['id']); ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No rooms found for this search.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
