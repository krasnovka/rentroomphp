<?php
// admin_auth.php

require_once 'config.php';

if (!is_logged_in()) {
    set_message('Please log in first.', 'error');
    redirect('login.php');
}

if (!is_admin()) {
    set_message('Access denied.', 'error');
    redirect('index.php');
}
