<?php
// config.php

// Start session for login and flash messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database settings
$host = 'localhost';
$db_name = 'varo_db';
$db_user = 'root';
$db_pass = '';

// Change DB settings here for your XAMPP MySQL or hosting
try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database connection error: ' . $e->getMessage());
}

// Шпаргалка: e() нужна для безопасного вывода текста на страницу.
// Она превращает специальные символы в безопасный HTML, чтобы пользователь не мог вставить вредный код.
// Пример: echo e($user['name']);
function e($text)
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

// Шпаргалка: redirect() переносит пользователя на другую страницу.
// После header() обязательно делаем exit, чтобы старый код дальше не выполнялся.
// Пример: redirect('login.php');
function redirect($page)
{
    header('Location: ' . $page);
    exit;
}

// Шпаргалка: csrf_token() создает защитный токен для важных форм.
// Этот токен нужен, чтобы чужой сайт не смог отправить форму от имени пользователя.
// Пример: в форме добавляют hidden input с value csrf_token().
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Шпаргалка: check_csrf_token() проверяет защитный токен из POST-формы.
// Если токен неправильный или пустой, действие останавливается и пользователь уходит на главную.
// Используется перед удалением, отменой брони, выходом и другими важными действиями.
function check_csrf_token()
{
    $token = $_POST['csrf_token'] ?? '';

    if ($token === '' || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        set_message('Security check failed. Please try again.', 'error');
        redirect('index.php');
    }
}

// Шпаргалка: is_logged_in() проверяет, есть ли пользователь в сессии.
// Если $_SESSION['user'] существует, значит пользователь вошел в аккаунт.
// Пример: if (is_logged_in()) { ... }
function is_logged_in()
{
    return isset($_SESSION['user']);
}

// Шпаргалка: is_admin() проверяет роль пользователя.
// Возвращает true только если пользователь вошел и его role равен admin.
// Нужно для защиты админских страниц.
function is_admin()
{
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

// Шпаргалка: set_message() сохраняет короткое сообщение в сессию.
// Оно используется для сообщений типа "успешно" или "ошибка" после redirect.
// Пример: set_message('Room added.', 'success');
function set_message($text, $type = 'success')
{
    $_SESSION['message'] = [
        'text' => $text,
        'type' => $type
    ];
}

// Шпаргалка: get_message() берет сообщение из сессии и сразу удаляет его.
// Поэтому сообщение показывается только один раз, а не на каждой странице.
// Обычно вызывается в header.php.
function get_message()
{
    if (!isset($_SESSION['message'])) {
        return null;
    }

    $message = $_SESSION['message'];
    unset($_SESSION['message']);
    return $message;
}

// Шпаргалка: get_booking_status_data() определяет красивый статус бронирования.
// На вход получает дату, время окончания и status из базы данных.
// Возвращает текст статуса и CSS-класс для таблиц бронирований.
function get_booking_status_data($booking_date, $end_time, $status)
{
    
if ($status === 'cancelled') {
    return [
        'label' => 'Peruttu',
        'class' => 'status-cancelled'
    ];
}

    $today = date('Y-m-d');
    $end_datetime = strtotime($booking_date . ' ' . $end_time);
    $now = time();

if ($booking_date > $today) {
    return [
        'label' => 'Tuleva',
        'class' => 'status-upcoming'
    ];
}

if ($booking_date === $today && $end_datetime !== false && $end_datetime >= $now) {
    return [
        'label' => 'Tänään',
        'class' => 'status-today'
    ];
}

return [
    'label' => 'Päättynyt',
    'class' => 'status-finished'
];
}
