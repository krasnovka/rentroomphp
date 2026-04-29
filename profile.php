<?php
// profile.php

require_once 'auth.php';

// Current logged in user id
$user_id = $_SESSION['user']['id'];
$errors = [];

// Load current user profile
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    redirect('login.php');
}

$name = $user['name'];
$email = $user['email'];
$avatar_url = $user['avatar_url'] ?? '';

// Profile update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || $email === '') {
        $errors[] = 'Name and email are required.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email.';
    }

    if (!$errors) {
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email, $user_id]);
        $same_email = $stmt->fetch();

        if ($same_email) {
            $errors[] = 'This email is already used.';
        } else {
            $new_avatar_url = $avatar_url;

            // Avatar upload block
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = 'Error while uploading file.';
                } else {
                    $file_name = $_FILES['avatar']['name'];
                    $file_size = (int)$_FILES['avatar']['size'];
                    $tmp_name = $_FILES['avatar']['tmp_name'];
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                    if (!in_array($ext, $allowed, true)) {
                        $errors[] = 'Allowed files: jpg, jpeg, png, gif, webp.';
                    }

                    if ($file_size > 2 * 1024 * 1024) {
                        $errors[] = 'File is too large. Max size is 2 MB.';
                    }

                    if (!@getimagesize($tmp_name)) {
                        $errors[] = 'Uploaded file must be an image.';
                    }

                    if (!$errors) {
                        $upload_dir = __DIR__ . '/uploads/avatars/';

                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }

                        $new_file_name = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
                        $target_file = $upload_dir . $new_file_name;

                        if (move_uploaded_file($tmp_name, $target_file)) {
                            if (!empty($avatar_url) && strpos($avatar_url, 'uploads/avatars/') === 0) {
                                $old_file = __DIR__ . '/' . $avatar_url;
                                if (file_exists($old_file)) {
                                    unlink($old_file);
                                }
                            }

                            $new_avatar_url = 'uploads/avatars/' . $new_file_name;
                        } else {
                            $errors[] = 'Could not save uploaded image.';
                        }
                    }
                }
            }

            // Save profile changes
            if (!$errors) {
                $sql = "UPDATE users SET name = ?, email = ?, avatar_url = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$name, $email, $new_avatar_url !== '' ? $new_avatar_url : null, $user_id]);

                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['avatar_url'] = $new_avatar_url;

                set_message('Profile updated.', 'success');
                redirect('profile.php');
            }
        }
    }
}

// Profile statistics
$sql = "SELECT COUNT(*) FROM bookings WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$total_bookings = (int)$stmt->fetchColumn();

$sql = "SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$active_bookings = (int)$stmt->fetchColumn();

$sql = "SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'cancelled'";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$cancelled_bookings = (int)$stmt->fetchColumn();

// Most used room
$sql = "SELECT rooms.name, COUNT(*) AS total_count
        FROM bookings
        INNER JOIN rooms ON bookings.room_id = rooms.id
        WHERE bookings.user_id = ?
        GROUP BY bookings.room_id, rooms.name
        ORDER BY total_count DESC
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$top_room = $stmt->fetch();

// Last booking info
$sql = "SELECT booking_date, start_time, end_time
        FROM bookings
        WHERE user_id = ?
        ORDER BY booking_date DESC, start_time DESC
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$last_booking = $stmt->fetch();

// Recent bookings list
$sql = "SELECT bookings.*, rooms.name AS room_name
        FROM bookings
        INNER JOIN rooms ON bookings.room_id = rooms.id
        WHERE bookings.user_id = ?
        ORDER BY bookings.booking_date DESC, bookings.start_time DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$recent_bookings = $stmt->fetchAll();

// Placeholder letter for avatar fallback
$avatar_text = strtoupper(substr($user['name'], 0, 1));
if ($avatar_text === '') {
    $avatar_text = 'U';
}

include 'header.php';
?>

<!-- Profile page heading -->
<div class="page-intro fade-card">
    <span class="section-tag">Profile</span>
    <h1>Your personal profile</h1>
    <p>Manage your account details, upload a photo and check your booking activity.</p>
</div>

<!-- Main profile cards -->
<div class="profile-layout">
    <div class="card glass-card profile-card fade-card">
        <div class="profile-top">
            <?php if (!empty($user['avatar_url'])): ?>
                <img src="<?php echo e($user['avatar_url']); ?>" alt="Profile Photo" class="profile-avatar">
            <?php else: ?>
                <div class="profile-avatar profile-avatar-placeholder"><?php echo e($avatar_text); ?></div>
            <?php endif; ?>

            <div>
                <h1><?php echo e($user['name']); ?></h1>
                <p class="profile-role"><?php echo e(ucfirst($user['role'])); ?> account</p>
                <p class="small-text">Email: <?php echo e($user['email']); ?></p>
                <p class="small-text">User ID: <?php echo e($user['id']); ?></p>
                <p class="small-text">Joined: <?php echo e(date('Y-m-d', strtotime($user['created_at']))); ?></p>
            </div>
        </div>
    </div>

    <div class="profile-side">
        <div class="card glass-card fade-card delay-1">
            <h2>Stats</h2>
            <div class="profile-stats">
                <div class="mini-card">
                    <strong><?php echo e($total_bookings); ?></strong>
                    <span>Total bookings</span>
                </div>
                <div class="mini-card">
                    <strong><?php echo e($active_bookings); ?></strong>
                    <span>Active bookings</span>
                </div>
                <div class="mini-card">
                    <strong><?php echo e($cancelled_bookings); ?></strong>
                    <span>Cancelled bookings</span>
                </div>
            </div>
        </div>

        <div class="card glass-card fade-card delay-2">
            <h2>More Info</h2>
            <p><strong>Most used room:</strong> <?php echo e($top_room['name'] ?? 'No data yet'); ?></p>
            <p><strong>Last booking:</strong>
                <?php if ($last_booking): ?>
                    <?php echo e($last_booking['booking_date']); ?>,
                    <?php echo e(substr($last_booking['start_time'], 0, 5)); ?> - <?php echo e(substr($last_booking['end_time'], 0, 5)); ?>
                <?php else: ?>
                    No bookings yet
                <?php endif; ?>
            </p>
            <div class="actions" style="margin-top: 16px;">
                <a href="change_password.php" class="btn btn-secondary">Change Password</a>
                <a href="my_bookings.php" class="btn">Open Bookings</a>
            </div>
        </div>
    </div>
</div>

<!-- Edit form and recent bookings -->
<div class="grid-two">
    <div class="card glass-card fade-card delay-3">
        <h2>Edit Profile</h2>

        <?php if ($errors): ?>
            <div class="message error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="grid-two">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo e($name); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo e($email); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="avatar">Profile Photo</label>
                <input type="file" id="avatar" name="avatar" accept=".jpg,.jpeg,.png,.gif,.webp,image/*">
                <p class="small-text">Allowed: jpg, jpeg, png, gif, webp. Max size: 2 MB.</p>
            </div>

            <button type="submit">Save Profile</button>
        </form>
    </div>

    <div class="card glass-card fade-card delay-3">
        <h2>Recent Bookings</h2>
        <?php if ($recent_bookings): ?>
            <div class="recent-bookings">
                <?php foreach ($recent_bookings as $row): ?>
                    <?php $status_data = get_booking_status_data($row['booking_date'], $row['end_time'], $row['status']); ?>
                    <div class="recent-booking-item">
                        <div>
                            <strong><?php echo e($row['room_name']); ?></strong>
                            <p class="small-text"><?php echo e($row['booking_date']); ?> | <?php echo e(substr($row['start_time'], 0, 5)); ?> - <?php echo e(substr($row['end_time'], 0, 5)); ?></p>
                        </div>
                        <span class="status-pill <?php echo e($status_data['class']); ?>">
                            <?php echo e($status_data['label']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No bookings yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
