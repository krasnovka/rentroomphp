<?php

require_once 'config.php';

// make sure session exists
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// check login safely
if (!isset($_SESSION['user']['id'])) {
    set_message('Please log in first.', 'error');
    redirect('login.php');
    exit;
}