<?php
// edit_room.php

require_once 'admin_auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
$errors = [];

if ($id <= 0) {
    set_message('Huoneet ei löydy.', 'error');
    redirect('admin_panel.php');
}

$sql = "SELECT * FROM rooms WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$room = $stmt->fetch();

if (!$room) {
    set_message('Huonetta ei löytynyt.', 'error');
    redirect('admin_panel.php');
}

$name = $room['name'];
$description = $room['description'];
$capacity = $room['capacity'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $capacity = trim($_POST['capacity'] ?? '');

    if ($name === '' || $description === '' || $capacity === '') {
        $errors[] = 'Kaikki kentät ovat pakollisia.';
    }

    if ($capacity !== '' && (!is_numeric($capacity) || (int)$capacity <= 0)) {
        $errors[] = 'Kapasiteetin täytyy olla positiivinen numero.';
    }

    if (!$errors) {
        $sql = "UPDATE rooms SET name = ?, description = ?, capacity = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $description, (int)$capacity, $id]);

        set_message('Huone päivitetty onnistuneesti.', 'success');
        redirect('admin_panel.php');
    }
}
include 'header.php';
?>

<div class="card">
    <h1>Muokkaa huonetta</h1>

    <?php if ($errors): ?>
        <div class="message error">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo e($id); ?>">

        <div class="form-group">
            <label for="name">Huoneen nimi</label>
            <input type="text" id="name" name="name" value="<?php echo e($name); ?>">
        </div>

        <div class="form-group">
            <label for="description">Kuvaus</label>
            <textarea id="description" name="description"><?php echo e($description); ?></textarea>
        </div>

        <div class="form-group">
            <label for="capacity">Kapasiteetti</label>
            <input type="number" id="capacity" name="capacity" min="1" value="<?php echo e($capacity); ?>">
        </div>

        <button type="submit">Tallenna muutokset</button>
        <a href="admin_panel.php" class="btn btn-secondary">Takaisin</a>
    </form>
</div>

<?php include 'footer.php'; ?>