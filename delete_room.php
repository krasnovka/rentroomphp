<?php
// delete_room.php

require_once 'admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_message('Invalid delete request.', 'error');
    redirect('admin_panel.php');
}

check_csrf_token();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    set_message('Room not found.', 'error');
    redirect('admin_panel.php');
}

$sql = "SELECT id FROM rooms WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$room = $stmt->fetch();

if (!$room) {
    set_message('Room not found.', 'error');
    redirect('admin_panel.php');
}

$sql = "SELECT id FROM bookings WHERE room_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$booking = $stmt->fetch();

if ($booking) {
    set_message('Cannot delete room with existing bookings.', 'error');
    redirect('admin_panel.php');
}

$sql = "DELETE FROM rooms WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);

set_message('Room deleted.', 'success');
redirect('admin_panel.php');
