<?php
// book_room.php

require_once 'auth.php';

// Room id from GET or POST
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : (int)($_POST['room_id'] ?? 0);
$booking_date = '';
$start_time = '';
$end_time = '';
$errors = [];

// Load selected room
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

// Date from calendar page
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $date_from_get = trim($_GET['date'] ?? '');
    $date_obj = DateTime::createFromFormat('Y-m-d', $date_from_get);

    if ($date_from_get !== '' && $date_obj && $date_obj->format('Y-m-d') === $date_from_get) {
        $booking_date = $date_from_get;
    } else {
        $booking_date = date('Y-m-d');
    }
}

// Booking form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = trim($_POST['booking_date'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');

    if ($booking_date === '' || $start_time === '' || $end_time === '') {
        $errors[] = 'All fields are required.';
    }

    // Validate date and time format
    $date_obj = DateTime::createFromFormat('Y-m-d', $booking_date);
    $start_obj = DateTime::createFromFormat('H:i', $start_time);
    $end_obj = DateTime::createFromFormat('H:i', $end_time);

    if ($booking_date !== '' && (!$date_obj || $date_obj->format('Y-m-d') !== $booking_date)) {
        $errors[] = 'Date format is invalid.';
    }

    if ($start_time !== '' && !$start_obj) {
        $errors[] = 'Start time format is invalid.';
    }

    if ($end_time !== '' && !$end_obj) {
        $errors[] = 'End time format is invalid.';
    }

    // Check booking rules
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

            if (($end_datetime - $start_datetime) > 12 * 60 * 60) {
                $errors[] = 'Booking time is too long. Max duration is 12 hours.';
            }
        }
    }

    // Check conflicts and save booking
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

// Busy slots for selected date
$busy_date = $booking_date !== '' ? $booking_date : date('Y-m-d');
$busy_date_obj = DateTime::createFromFormat('Y-m-d', $busy_date);

if (!$busy_date_obj || $busy_date_obj->format('Y-m-d') !== $busy_date) {
    $busy_date = date('Y-m-d');
}

$sql = "SELECT bookings.*, users.name AS user_name
        FROM bookings
        INNER JOIN users ON bookings.user_id = users.id
        WHERE bookings.room_id = ?
        AND bookings.booking_date = ?
        AND bookings.status = 'active'
        ORDER BY bookings.start_time ASC";
$stmt = $conn->prepare($sql);
$stmt->execute([$room_id, $busy_date]);
$busy_slots = $stmt->fetchAll();

include 'header.php';
?>

<!-- Page heading -->
<div class="page-intro fade-card">
    <span class="section-tag">Booking</span>
    <h1>Book a room</h1>
    <p>Choose date and time for your booking and confirm the reservation.</p>
</div>

<!-- Room info and booking form -->
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
            <div class="room-summary-item">
                <span>Booking Rules</span>
                <strong>Choose future date and time. End time must be later than start time.</strong>
            </div>
        </div>

        <!-- Busy times for this room -->
        <div class="busy-box">
            <div class="busy-box-head">
                <div>
                    <h3>Busy times</h3>
                    <p class="small-text">Selected date: <?php echo e($busy_date); ?></p>
                </div>
                <a href="booking_calendar.php?room_id=<?php echo e($room_id); ?>&month=<?php echo e(substr($busy_date, 0, 7)); ?>" class="btn btn-secondary">Calendar</a>
            </div>

            <form method="get" class="mini-date-form">
                <input type="hidden" name="room_id" value="<?php echo e($room_id); ?>">
                <input type="date" name="date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo e($busy_date); ?>">
                <button type="submit">Check</button>
            </form>

            <?php if ($busy_slots): ?>
                <div class="busy-slots">
                    <?php foreach ($busy_slots as $slot): ?>
                        <div class="busy-slot">
                            <strong><?php echo e(substr($slot['start_time'], 0, 5)); ?>-<?php echo e(substr($slot['end_time'], 0, 5)); ?></strong>
                            <?php if (is_admin()): ?>
                                <span><?php echo e($slot['user_name']); ?></span>
                            <?php else: ?>
                                <span>Booked</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="calendar-free-note">No bookings for this room on this date.</p>
            <?php endif; ?>
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
                <input type="date" id="booking_date" name="booking_date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo e($booking_date); ?>" required>
            </div>

            <div class="grid-two">
                <div class="form-group">
                    <label for="start_time">Start Time</label>
                    <input type="time" id="start_time" name="start_time" value="<?php echo e($start_time); ?>" required>
                </div>

                <div class="form-group">
                    <label for="end_time">End Time</label>
                    <input type="time" id="end_time" name="end_time" value="<?php echo e($end_time); ?>" required>
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
