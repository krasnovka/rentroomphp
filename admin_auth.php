<?php
// admin_auth.php

require_once 'config.php';

// Шпаргалка: сначала проверяем, вошел ли пользователь в аккаунт.
if (!is_logged_in()) {
    set_message('Please log in first.', 'error');
    redirect('login.php');
}

// Шпаргалка: потом проверяем роль admin, обычного пользователя сюда не пускаем.
if (!is_admin()) {
    set_message('Access denied.', 'error');
    redirect('index.php');
}
