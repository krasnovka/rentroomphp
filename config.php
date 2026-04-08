<?php
// config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db_name = 'varo_db';
$db_user = 'root';
$db_pass = '';

// Change DB settings here 
try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database connection error: ' . $e->getMessage());
}

function e($text)
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function redirect($page)
{
    header('Location: ' . $page);
    exit;
}

function is_logged_in()
{
    return isset($_SESSION['user']);
}

function is_admin()
{
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function set_message($text, $type = 'success')
{
    $_SESSION['message'] = [
        'text' => $text,
        'type' => $type
    ];
}

function get_message()
{
    if (!isset($_SESSION['message'])) {
        return null;
    }

    $message = $_SESSION['message'];
    unset($_SESSION['message']);
    return $message;
}
