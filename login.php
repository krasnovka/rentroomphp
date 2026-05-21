<?php

require_once 'config.php';

// redirect if already logged in
if (is_logged_in()) {
    redirect('index.php');
}

$email = '';
$errors = [];

// handle login form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // check required fields
    if ($email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    } else {

        // find user by email
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // verify password
        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Wrong email or password.';
        } else {

            // create session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'avatar_url' => $user['avatar_url']
            ];

            set_message('Login successful.', 'success');
            redirect('index.php');
        }
    }
}

include 'header.php';
?>

<!-- page layout -->
<div class="auth-layout">

    <!-- intro section -->
    <div class="page-intro fade-card">
        <span class="section-tag">Welcome Back</span>
        <h1>Login to your account</h1>
        <p>Access your profile, room bookings and personal dashboard.</p>
    </div>

    <!-- login form -->
    <div class="card glass-card fade-card delay-1 auth-card">
        <h2>Login</h2>

        <?php if ($errors): ?>
            <div class="message error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo e($email); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
            </div>

            <div class="actions">
                <button type="submit">Login</button>
                <a href="register.php" class="btn btn-secondary">Create Account</a>
            </div>

        </form>
    </div>

</div>

<?php include 'footer.php'; ?>