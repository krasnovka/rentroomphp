<?php
// cancel_booking.php

require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_message('Invalid cancellation request.', 'error');
    redirect('my_bookings.php');
}

check_csrf_token();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    set_message('Booking not found.', 'error');
    redirect('my_bookings.php');
}

$sql = "SELECT * FROM bookings WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id, $_SESSION['user']['id']]);
$booking = $stmt->fetch();

if (!$booking) {
    set_message('You cannot cancel this booking.', 'error');
    redirect('my_bookings.php');
}

if ($booking['status'] !== 'active') {
    set_message('This booking is already cancelled.', 'error');
    redirect('my_bookings.php');
}

$sql = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);

set_message('Booking cancelled.', 'success');
redirect('my_bookings.php');
