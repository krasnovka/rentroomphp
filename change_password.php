<?php
// change_password.php

require_once 'auth.php';

$errors = [];

// Password change form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
        $errors[] = 'All fields are required.';
    }

    if ($new_password !== '' && strlen($new_password) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    }

    if ($new_password !== $confirm_password) {
        $errors[] = 'New passwords do not match.';
    }

    if (!$errors) {
        // Load current password hash
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_SESSION['user']['id']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current_password, $user['password'])) {
            $errors[] = 'Current password is wrong.';
        } else {
            // Save new password hash
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$new_hash, $_SESSION['user']['id']]);

            set_message('Password changed successfully.', 'success');
            redirect('profile.php');
        }
    }
}

include 'header.php';
?>

<!-- Page heading -->
<div class="page-intro fade-card">
    <span class="section-tag">Security</span>
    <h1>Change password</h1>
    <p>Update your account password and keep your profile safe.</p>
</div>

<!-- Password form -->
<div class="card glass-card fade-card delay-1">
    <h2>Password Form</h2>

    <?php if ($errors): ?>
        <div class="message error">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password">
        </div>

        <div class="grid-two">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
        </div>

        <div class="actions">
            <button type="submit">Change Password</button>
            <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
