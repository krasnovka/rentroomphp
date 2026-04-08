    <?php
// logout.php

require_once 'config.php';

$_SESSION = [];
session_destroy();

session_start();
set_message('You have logged out.', 'success');
redirect('login.php');
