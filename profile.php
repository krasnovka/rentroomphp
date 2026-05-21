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
        $errors[] = 'Nimi ja sähköposti ovat pakollisia.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Anna kelvollinen sähköposti.';
    }

    if (!$errors) {
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email, $user_id]);
        $same_email = $stmt->fetch();

        if ($same_email) {
            $errors[] = 'Tämä sähköposti on jo käytössä.';
        } else {
            $new_avatar_url = $avatar_url;

            // Avatar upload block
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = 'Virhe tiedoston latauksessa.';
                } else {
                    $file_name = $_FILES['avatar']['name'];
                    $file_size = (int)$_FILES['avatar']['size'];
                    $tmp_name = $_FILES['avatar']['tmp_name'];
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                    if (!in_array($ext, $allowed, true)) {
                        $errors[] = 'Sallitut tiedostot: jpg, jpeg, png, gif, webp.';
                    }

                    if ($file_size > 2 * 1024 * 1024) {
                        $errors[] = 'Tiedosto on liian suuri. Maksimi 2 MB.';
                    }

                    if (!@getimagesize($tmp_name)) {
                        $errors[] = 'Ladattavan tiedoston täytyy olla kuva.';
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
                            $errors[] = 'Kuvan tallennus epäonnistui.';
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

                set_message('Profiili päivitetty.', 'success');
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
    <span class="section-tag">Profiili</span>
    <h1>Henkilökohtainen profiili</h1>
    <p>Hallitse tietojasi, lisää kuva ja tarkastele varauksiasi.</p>
</div>

<!-- Main profile cards -->
<div class="profile-layout">
    <div class="card glass-card profile-card fade-card">
        <div class="profile-top">
            <?php if (!empty($user['avatar_url'])): ?>
                <img src="<?php echo e($user['avatar_url']); ?>" alt="Profiilikuva" class="profile-avatar">
            <?php else: ?>
                <div class="profile-avatar profile-avatar-placeholder"><?php echo e($avatar_text); ?></div>
            <?php endif; ?>

            <div>
                <h1><?php echo e($user['name']); ?></h1>
                <p class="profile-role"><?php echo e(ucfirst($user['role'])); ?> tili</p>
                <p class="small-text">Sähköposti: <?php echo e($user['email']); ?></p>
                <p class="small-text">Käyttäjä-ID: <?php echo e($user['id']); ?></p>
                <p class="small-text">Liittynyt: <?php echo e(date('Y-m-d', strtotime($user['created_at']))); ?></p>
            </div>
        </div>
    </div>

    <div class="profile-side">
        <div class="card glass-card fade-card delay-1">
            <h2>Tilastot</h2>
            <div class="profile-stats">
                <div class="mini-card">
                    <strong><?php echo e($total_bookings); ?></strong>
                    <span>Varauksia yhteensä</span>
                </div>
                <div class="mini-card">
                    <strong><?php echo e($active_bookings); ?></strong>
                    <span>Aktiiviset varaukset</span>
                </div>
                <div class="mini-card">
                    <strong><?php echo e($cancelled_bookings); ?></strong>
                    <span>Perutut varaukset</span>
                </div>
            </div>
        </div>

        <div class="card glass-card fade-card delay-2">
            <h2>Lisätiedot</h2>
            <p><strong>Käytetyin huone:</strong> <?php echo e($top_room['name'] ?? 'Ei tietoja vielä'); ?></p>
            <p><strong>Viime varaus:</strong>
                <?php if ($last_booking): ?>
                    <?php echo e($last_booking['booking_date']); ?>,
                    <?php echo e(substr($last_booking['start_time'], 0, 5)); ?> - <?php echo e(substr($last_booking['end_time'], 0, 5)); ?>
                <?php else: ?>
                    Ei varauksia vielä
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<!-- Edit form -->
<div class="grid-two">
    <div class="card glass-card fade-card delay-3">
        <h2>Muokkaa profiilia</h2>

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
                    <label for="name">Nimi</label>
                    <input type="text" id="name" name="name" value="<?php echo e($name); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Sähköposti</label>
                    <input type="email" id="email" name="email" value="<?php echo e($email); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="avatar">Profiilikuva</label>
                <input type="file" id="avatar" name="avatar">
            </div>

            <button type="submit">Tallenna</button>
        </form>
    </div>

    <div class="card glass-card fade-card delay-3">
        <h2>Viimeisimmät varaukset</h2>

        <?php if ($recent_bookings): ?>
            <div class="recent-bookings">
                <?php foreach ($recent_bookings as $row): ?>
                    <div>
                        <strong><?php echo e($row['room_name']); ?></strong>
                        <p class="small-text">
                            <?php echo e($row['booking_date']); ?> |
                            <?php echo e(substr($row['start_time'], 0, 5)); ?> - <?php echo e(substr($row['end_time'], 0, 5)); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Ei varauksia vielä</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>