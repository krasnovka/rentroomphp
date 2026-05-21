<?php
// cancel_booking.php

require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_message('Virheellinen peruutuspyyntö.', 'error');
    redirect('my_bookings.php');
}

check_csrf_token();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    set_message('Varausta ei löytynyt.', 'error');
    redirect('my_bookings.php');
}

$sql = "SELECT * FROM bookings WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id, $_SESSION['user']['id']]);
$booking = $stmt->fetch();

if (!$booking) {
    set_message('Et voi peruuttaa tätä varausta.', 'error');
    redirect('my_bookings.php');
}

if ($booking['status'] !== 'active') {
    set_message('Tämä varaus on jo peruttu.', 'error');
    redirect('my_bookings.php');
}

$sql = "UPDATE Bookings SET status = 'Cancelled ' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);

set_message('Varaus peruttu', 'success');
redirect('my_bookings.php');
