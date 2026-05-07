<?php
// config.php

// Start session for login and flash messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database settings
$host = 'sql209.infinityfree.com    ';
$db_name = 'if0_41844343_varoo1';
$db_user = 'if0_41844343';
$db_pass = 'eQDqTS5LNRHn0HD'
// Change DB settings here for your XAMPP MySQL
try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database connection error: ' . $e->getMessage());
}

// Safe output helper
function e($text)
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

// Redirect helper
function redirect($page)
{
    header('Location: ' . $page);
    exit;
}

// CSRF token for forms with important actions
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Check CSRF token from POST form
function check_csrf_token()
{
    $token = $_POST['csrf_token'] ?? '';

    if ($token === '' || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        set_message('Security check failed. Please try again.', 'error');
        redirect('index.php');
    }
}

// Check if user is logged in
function is_logged_in()
{
    return isset($_SESSION['user']);
}

// Check admin role
function is_admin()
{
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

// Save flash message
function set_message($text, $type = 'success')
{
    $_SESSION['message'] = [
        'text' => $text,
        'type' => $type
    ];
}

// Read and clear flash message
function get_message()
{
    if (!isset($_SESSION['message'])) {
        return null;
    }

    $message = $_SESSION['message'];
    unset($_SESSION['message']);
    return $message;
}

// Booking status helper for user and admin tables
function get_booking_status_data($booking_date, $end_time, $status)
{
    if ($status === 'cancelled') {
        return [
            'label' => 'Cancelled',
            'class' => 'status-cancelled'
        ];
    }

    $today = date('Y-m-d');
    $end_datetime = strtotime($booking_date . ' ' . $end_time);
    $now = time();

    if ($booking_date > $today) {
        return [
            'label' => 'Upcoming',
            'class' => 'status-upcoming'
        ];
    }

    if ($booking_date === $today && $end_datetime !== false && $end_datetime >= $now) {
        return [
            'label' => 'Today',
            'class' => 'status-today'
        ];
    }

    return [
        'label' => 'Finished',
        'class' => 'status-finished'
    ];
}
