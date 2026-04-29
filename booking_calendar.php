<?php
// booking_calendar.php

require_once 'config.php';

// Month and room filters
$month = trim($_GET['month'] ?? date('Y-m'));
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    $month = date('Y-m');
}

$month_start = DateTime::createFromFormat('Y-m-d', $month . '-01');

if (!$month_start) {
    $month_start = new DateTime('first day of this month');
    $month = $month_start->format('Y-m');
}

$first_day = $month_start->format('Y-m-01');
$last_day = $month_start->format('Y-m-t');
$days_in_month = (int)$month_start->format('t');
$first_weekday = (int)$month_start->format('N');
$prev_month = (clone $month_start)->modify('-1 month')->format('Y-m');
$next_month = (clone $month_start)->modify('+1 month')->format('Y-m');

// Load rooms for filter
$sql = "SELECT id, name FROM rooms ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$rooms = $stmt->fetchAll();

// Load active bookings for selected month
$params = [$first_day, $last_day];
$room_filter_sql = '';

if ($room_id > 0) {
    $room_filter_sql = " AND bookings.room_id = ?";
    $params[] = $room_id;
}

$sql = "SELECT bookings.*, rooms.name AS room_name, users.name AS user_name
        FROM bookings
        INNER JOIN rooms ON bookings.room_id = rooms.id
        INNER JOIN users ON bookings.user_id = users.id
        WHERE bookings.status = 'active'
        AND bookings.booking_date BETWEEN ? AND ?
        $room_filter_sql
        ORDER BY bookings.booking_date ASC, bookings.start_time ASC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$calendar_rows = $stmt->fetchAll();

// Group bookings by date
$bookings_by_date = [];
foreach ($calendar_rows as $row) {
    $bookings_by_date[$row['booking_date']][] = $row;
}

include 'header.php';
?>

<!-- Page heading -->
<div class="page-intro fade-card">
    <span class="section-tag">Calendar</span>
    <h1>Booking calendar</h1>
    <p>Check which rooms are already booked before making a new booking.</p>
</div>

<!-- Calendar filters -->
<div class="card glass-card fade-card delay-1">
    <div class="section-head">
        <div>
            <h2>Calendar Filters</h2>
            <p class="small-text">Choose a month and filter by room if needed.</p>
        </div>
    </div>

    <form method="get" class="calendar-filter">
        <div class="calendar-filter-row">
            <div class="form-group">
                <label for="month">Month</label>
                <input type="month" id="month" name="month" value="<?php echo e($month); ?>">
            </div>

            <div class="form-group">
                <label for="room_id">Room</label>
                <select id="room_id" name="room_id">
                    <option value="0">All rooms</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo e($room['id']); ?>" <?php echo $room_id === (int)$room['id'] ? 'selected' : ''; ?>>
                            <?php echo e($room['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="calendar-filter-actions">
                <button type="submit">Show</button>
                <a href="booking_calendar.php" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- Month navigation -->
<div class="calendar-toolbar fade-card delay-2">
    <a class="btn btn-secondary" href="booking_calendar.php?month=<?php echo e($prev_month); ?>&room_id=<?php echo e($room_id); ?>">Previous</a>
    <h2><?php echo e($month_start->format('F Y')); ?></h2>
    <a class="btn btn-secondary" href="booking_calendar.php?month=<?php echo e($next_month); ?>&room_id=<?php echo e($room_id); ?>">Next</a>
</div>

<!-- Calendar grid -->
<div class="calendar-grid fade-card delay-3">
    <div class="calendar-weekday">Mon</div>
    <div class="calendar-weekday">Tue</div>
    <div class="calendar-weekday">Wed</div>
    <div class="calendar-weekday">Thu</div>
    <div class="calendar-weekday">Fri</div>
    <div class="calendar-weekday">Sat</div>
    <div class="calendar-weekday">Sun</div>

    <?php for ($i = 1; $i < $first_weekday; $i++): ?>
        <div class="calendar-day calendar-empty"></div>
    <?php endfor; ?>

    <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
        <?php
        $date = $month_start->format('Y-m-') . str_pad((string)$day, 2, '0', STR_PAD_LEFT);
        $day_bookings = $bookings_by_date[$date] ?? [];
        $is_today = $date === date('Y-m-d');
        ?>
        <div class="calendar-day <?php echo $is_today ? 'calendar-today' : ''; ?>">
            <div class="calendar-day-head">
                <strong><?php echo e($day); ?></strong>
                <?php if ($is_today): ?>
                    <span>Today</span>
                <?php endif; ?>
            </div>

            <?php if ($day_bookings): ?>
                <div class="calendar-bookings">
                    <?php foreach ($day_bookings as $booking): ?>
                        <a class="calendar-booking-chip" href="book_room.php?room_id=<?php echo e($booking['room_id']); ?>&date=<?php echo e($date); ?>">
                            <span><?php echo e(substr($booking['start_time'], 0, 5)); ?>-<?php echo e(substr($booking['end_time'], 0, 5)); ?></span>
                            <strong><?php echo e($booking['room_name']); ?></strong>
                            <?php if (is_admin()): ?>
                                <small><?php echo e($booking['user_name']); ?></small>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <span class="calendar-free">Free</span>
            <?php endif; ?>

            <?php if (is_logged_in() && $room_id > 0): ?>
                <a class="calendar-book-link" href="book_room.php?room_id=<?php echo e($room_id); ?>&date=<?php echo e($date); ?>">Book this day</a>
            <?php endif; ?>
        </div>
    <?php endfor; ?>
</div>

<?php include 'footer.php'; ?>
