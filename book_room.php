<?php
// book_room.php

require_once 'auth.php';

$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : (int)($_POST['room_id'] ?? 0);

$booking_date = '';
$start_time = '';
$end_time = '';
$errors = [];

// LOAD ROOM
if ($room_id <= 0) {
    set_message('Huonetta ei löytynyt.', 'error');
    redirect('rooms.php');
}

$stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    set_message('Huonetta ei löytynyt.', 'error');
    redirect('rooms.php');
}

// DEFAULT DATE
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $booking_date = date('Y-m-d');
}

// SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $booking_date = trim($_POST['booking_date'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');

    if (!$booking_date || !$start_time || !$end_time) {
        $errors[] = 'All fields are required.';
    }

    $start = strtotime("$booking_date $start_time");
    $end = strtotime("$booking_date $end_time");

    if ($start && $end) {

        if ($start < time()) $errors[] = 'You cannot book past time.';
        if ($end <= $start) $errors[] = 'End must be later than start.';

    } else {
        $errors[] = 'Invalid date/time.';
    }

    if (!$errors) {

        $stmt = $conn->prepare("
            SELECT id FROM bookings
            WHERE room_id = ?
            AND booking_date = ?
            AND status = 'active'
            AND start_time < ?
            AND end_time > ?
        ");
        $stmt->execute([$room_id, $booking_date, $end_time, $start_time]);

        if ($stmt->fetch()) {
            $errors[] = 'This time is already booked.';
        } else {

            $stmt = $conn->prepare("
                INSERT INTO bookings (user_id, room_id, booking_date, start_time, end_time, status)
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([
                $_SESSION['user']['id'],
                $room_id,
                $booking_date,
                $start_time,
                $end_time
            ]);

            set_message('Booking created successfully.', 'success');
            redirect('my_bookings.php');
        }
    }
}

// BUSY SLOTS
$stmt = $conn->prepare("
    SELECT bookings.*, users.name AS user_name
    FROM bookings
    JOIN users ON bookings.user_id = users.id
    WHERE room_id = ?
    AND booking_date = ?
    AND status = 'active'
    ORDER BY start_time ASC
");
$stmt->execute([$room_id, $booking_date]);
$busy_slots = $stmt->fetchAll();

include 'header.php';
?>

<!-- HEADER -->
<div class="page-intro fade-card">
    <span class="section-tag">Varaus</span>
    <h1><?php echo e($room['name']); ?></h1>
</div>

<!-- LAYOUT -->
<div class="booking-layout">

    <!-- LEFT SIDE -->
    <div class="card glass-card fade-card">

        <!-- IMAGE -->
        <div style="width:100%;height:260px;border-radius:14px;overflow:hidden;background:#f3f6fa;">
            <?php if (!empty($room['image_url'])): ?>
                <img src="<?php echo e($room['image_url']); ?>"
                     style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
                <div style="display:flex;align-items:center;justify-content:center;height:100%;">
                    No image
                </div>
            <?php endif; ?>
        </div>

        <div class="room-summary" style="margin-top:15px;">
            <div class="room-summary-item">
                <span>Kuvaus</span>
                <strong><?php echo e($room['description']); ?></strong>
            </div>

            <div class="room-summary-item">
                <span>Henkilömäärä</span>
                <strong><?php echo e($room['capacity']); ?> henkilöä</strong>
            </div>
        </div>

        <!-- CALENDAR BUTTON -->
        <div style="margin-top:15px;">
            <a class="btn btn-secondary"
               href="booking_calendar.php?room_id=<?php echo e($room_id); ?>">
                Kalenteri
            </a>
        </div>

        <!-- BUSY SLOTS (RESTORED) -->
        <div class="busy-box">

            <div class="busy-box-head">
                <div>
                    <h3>Varatut ajat</h3>
                    <p class="small-text"><?php echo e($booking_date); ?></p>
                </div>
            </div>

            <?php if ($busy_slots): ?>
                <div class="busy-slots">
                    <?php foreach ($busy_slots as $slot): ?>
                        <div class="busy-slot">
                            <strong>
                                <?php echo e(substr($slot['start_time'],0,5)); ?>
                                -
                                <?php echo e(substr($slot['end_time'],0,5)); ?>
                            </strong>

                            <span>
                                <?php echo is_admin() ? e($slot['user_name']) : 'Varattu'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="calendar-free-note">Ei varauksia tälle päivälle.</p>
            <?php endif; ?>

        </div>

    </div>

    <!-- RIGHT SIDE -->
    <div class="card glass-card fade-card delay-1">

        <h2>Varaa aika</h2>

        <?php if ($errors): ?>
            <div class="message error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="room_id" value="<?php echo e($room_id); ?>">

            <div class="form-group">
                <label>Päivämäärä</label>
                <input type="date" name="booking_date"
                       value="<?php echo e($booking_date); ?>"
                       min="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="grid-two">
                <div class="form-group">
                    <label>Aloitus</label>
                    <input type="time" name="start_time" required>
                </div>

                <div class="form-group">
                    <label>Loppu</label>
                    <input type="time" name="end_time" required>
                </div>
            </div>

            <div class="actions">
                <button type="submit">Varaa</button>
                <a href="rooms.php" class="btn btn-secondary">Takaisin</a>
            </div>
        </form>

    </div>
</div>

<?php include 'footer.php'; ?>