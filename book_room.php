<?php
// book_room.php

require_once 'auth.php';

$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : (int)($_POST['room_id'] ?? 0);
$booking_date = '';
$start_time = '';
$end_time = '';
$errors = [];

if ($room_id <= 0) {
    set_message('Room not found.', 'error');
    redirect('rooms.php');
}

$sql = "SELECT * FROM rooms WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    set_message('Room not found.', 'error');
    redirect('rooms.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = trim($_POST['booking_date'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');

    if ($booking_date === '' || $start_time === '' || $end_time === '') {
        $errors[] = 'All fields are required.';
    }

    if (!$errors) {
        $start_datetime = strtotime($booking_date . ' ' . $start_time);
        $end_datetime = strtotime($booking_date . ' ' . $end_time);
        $now = time();

        if ($start_datetime === false || $end_datetime === false) {
            $errors[] = 'Invalid date or time.';
        } else {
            if ($start_datetime < $now) {
                $errors[] = 'You cannot book past time.';
            }

            if ($end_datetime <= $start_datetime) {
                $errors[] = 'End time must be later than start time.';
            }
        }
    }

    if (!$errors) {
        $sql = "SELECT id FROM bookings
                WHERE room_id = ?
                AND booking_date = ?
                AND status = 'active'
                AND start_time < ?
                AND end_time > ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$room_id, $booking_date, $end_time, $start_time]);
        $busy = $stmt->fetch();

        if ($busy) {
            $errors[] = 'This time is already booked.';
        } else {
            $sql = "INSERT INTO bookings (user_id, room_id, booking_date, start_time, end_time, status)
                    VALUES (?, ?, ?, ?, ?, 'active')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$_SESSION['user']['id'], $room_id, $booking_date, $start_time, $end_time]);

            set_message('Booking created successfully.', 'success');
            redirect('my_bookings.php');
        }
    }
}

include 'header.php';
?>

<div class="page-intro fade-card">
    <span class="section-tag">Booking</span>
    <h1>Book a room</h1>
    <p>Choose date and time for your booking and confirm the reservation.</p>
</div>

<div class="booking-layout">
    <div class="card glass-card fade-card">
        <h2><?php echo e($room['name']); ?></h2>
        <div class="room-summary">
            <div class="room-summary-item">
                <span>Description</span>
                <strong><?php echo e($room['description']); ?></strong>
            </div>
            <div class="room-summary-item">
                <span>Capacity</span>
                <strong><?php echo e($room['capacity']); ?> people</strong>
            </div>
        </div>
    </div>

    <div class="card glass-card fade-card delay-1">
        <h2>Booking Form</h2>

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
                <label for="booking_date">Date</label>
                <input type="date" id="booking_date" name="booking_date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo e($booking_date); ?>">
            </div>

            <div class="grid-two">
                <div class="form-group">
                    <label for="start_time">Start Time</label>
                    <input type="time" id="start_time" name="start_time" value="<?php echo e($start_time); ?>">
                </div>

                <div class="form-group">
                    <label for="end_time">End Time</label>
                    <input type="time" id="end_time" name="end_time" value="<?php echo e($end_time); ?>">
                </div>
            </div>

            <div class="actions">
                <button type="submit">Create Booking</button>
                <a href="rooms.php" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
