<?php
// add_room.php

require_once 'admin_auth.php';

$name = '';
$description = '';
$capacity = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* =========================
       GET FORM DATA
    ========================= */
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $capacity = trim($_POST['capacity'] ?? '');

    /* =========================
       BASIC VALIDATION
    ========================= */
    if ($name === '' || $description === '' || $capacity === '') {
        $errors[] = 'All fields are required.';
    }

    if (!is_numeric($capacity) || (int)$capacity <= 0) {
        $errors[] = 'Capacity must be a positive number.';
    }

    /* =========================
       IMAGE UPLOAD
    ========================= */
    $image_url = null;

    if (!empty($_FILES['image']['name']) && empty($errors)) {

        $file = $_FILES['image'];

        if ($file['error'] === UPLOAD_ERR_OK) {

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed)) {
                $errors[] = 'Only jpg, jpeg, png, webp allowed.';
            }

            if ($file['size'] > 2 * 1024 * 1024) {
                $errors[] = 'Image too large (max 2MB).';
            }

            if (!@getimagesize($file['tmp_name'])) {
                $errors[] = 'File is not a valid image.';
            }

            if (empty($errors)) {

                $dir = __DIR__ . '/uploads/rooms/';

                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                $file_name = 'room_' . time() . '.' . $ext;
                $target = $dir . $file_name;

                if (move_uploaded_file($file['tmp_name'], $target)) {
                    $image_url = 'uploads/rooms/' . $file_name;
                } else {
                    $errors[] = 'Upload failed.';
                }
            }

        } else {
            $errors[] = 'File upload error.';
        }
    }

    /* =========================
       INSERT ROOM
    ========================= */
    if (empty($errors)) {

        $sql = "INSERT INTO rooms (name, description, capacity, image_url)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $name,
            $description,
            (int)$capacity,
            $image_url
        ]);

        set_message('Room added successfully.', 'success');
        redirect('admin_panel.php');
    }
}

include 'header.php';
?>

<!-- =========================
     FORM UI
========================= -->
<div class="card">
    <h1>Lisää huone</h1>

    <?php if ($errors): ?>
        <div class="message error">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">

        <div class="form-group">
            <label>Huoneen nimi</label>
            <input type="text" name="name" value="<?php echo e($name); ?>">
        </div>

        <div class="form-group">
            <label>Kuvaus</label>
            <textarea name="description"><?php echo e($description); ?></textarea>
        </div>

        <div class="form-group">
            <label>Määrä</label>
            <input type="number" name="capacity" min="1" value="<?php echo e($capacity); ?>">
        </div>

        <div class="form-group">
            <label>Huoneen kuva</label>
            <input type="file" name="image" accept="image/*">
            <p class="small-text">Max 2MB (jpg, png, webp)</p>
        </div>

        <button type="submit">Lisää huone</button>
        <a href="admin_panel.php" class="btn btn-secondary">Takaisin</a>

    </form>
</div>

<?php include 'footer.php'; ?>