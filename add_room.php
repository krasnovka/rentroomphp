<?php
// add_room.php

require_once 'admin_auth.php';

$name = '';
$description = '';
$capacity = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $capacity = trim($_POST['capacity'] ?? '');

    if ($name === '' || $description === '' || $capacity === '') {
        $errors[] = 'All fields are required.';
    }

    if ($capacity !== '' && (!is_numeric($capacity) || (int)$capacity <= 0)) {
        $errors[] = 'Capacity must be a positive number.';
    }

    if (!$errors) {
        $sql = "INSERT INTO rooms (name, description, capacity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $description, (int)$capacity]);

        set_message('Room added successfully.', 'success');
        redirect('admin_panel.php');
    }
}

include 'header.php';
?>

<div class="card">
    <h1>Add Room</h1>

    <?php if ($errors): ?>
        <div class="message error">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="name">Room Name</label>
            <input type="text" id="name" name="name" value="<?php echo e($name); ?>">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?php echo e($description); ?></textarea>
        </div>

        <div class="form-group">
            <label for="capacity">Capacity</label>
            <input type="number" id="capacity" name="capacity" min="1" value="<?php echo e($capacity); ?>">
        </div>

        <button type="submit">Add Room</button>
        <a href="admin_panel.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<?php include 'footer.php'; ?>
