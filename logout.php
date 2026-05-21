<?php

require_once 'auth.php';

// allow only POST request for security
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_message('Invalid logout request.', 'error');
    redirect('index.php');
}

// verify CSRF token
check_csrf_token();

// clear session data
$_SESSION = [];
session_destroy();

// restart session for flash message
session_start();

set_message('You have logged out.', 'success');
redirect('login.php');