<?php
// logout.php

require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_message('Invalid logout request.', 'error');
    redirect('index.php');
}

check_csrf_token();

$_SESSION = [];
session_destroy();

session_start();
set_message('You have logged out.', 'success');
redirect('login.php');
