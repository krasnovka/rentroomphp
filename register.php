<?php
// register.php

require_once 'config.php';

if (is_logged_in()) {
    redirect('index.php');
}

$name = '';
$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if ($name === '' || $email === '' || $password === '' || $confirm_password === '') {
        $errors[] = 'All fields are required.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email.';
    }

    if ($password !== '' && strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $errors[] = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $email, $hash]);

            set_message('Registration completed. You can log in now.', 'success');
            redirect('login.php');
        }
    }
}

include 'header.php';
?>

<div class="auth-layout">
    <div class="page-intro fade-card">
        <span class="section-tag">New Account</span>
        <h1>Create your VARO account</h1>
        <p>Register to book rooms, manage your schedule and open your personal profile.</p>
    </div>

    <div class="card glass-card fade-card delay-1 auth-card">
        <h2>Register</h2>

        <?php if ($errors): ?>
            <div class="message error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="grid-two">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo e($name); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo e($email); ?>">
                </div>
            </div>

            <div class="grid-two">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
            </div>

            <div class="actions">
                <button type="submit">Register</button>
                <a href="login.php" class="btn btn-secondary">Login</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
