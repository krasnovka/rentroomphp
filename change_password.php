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
        $errors[] = 'Kaikki kentät ovat pakollisia.';
    }

    if ($new_password !== '' && strlen($new_password) < 6) {
        $errors[] = 'Uuden salasanan täytyy olla vähintään 6 merkkiä.';
    }

    if ($new_password !== $confirm_password) {
        $errors[] = 'Uudet salasanat eivät täsmää.';
    }

    if (!$errors) {
        // Load current password hash
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_SESSION['user']['id']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current_password, $user['password'])) {
            $errors[] = 'Nykyinen salasana on väärä.';
        } else {
            // Save new password hash
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$new_hash, $_SESSION['user']['id']]);

            set_message('Salasana vaihdettu onnistuneesti.', 'success');
            redirect('profile.php');
        }
    }
}

include 'header.php';
?>

<!-- Page heading -->
<div class="page-intro fade-card">
    <span class="section-tag">Turvallisuus</span>
    <h1>Vaihda salasana</h1>
    <p>Päivitä tilisi salasana ja pidä profiilisi turvassa.</p>
</div>

<!-- Password form -->
<div class="card glass-card fade-card delay-1">
    <h2>Salasanalomake</h2>

    <?php if ($errors): ?>
        <div class="message error">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
    <div class="form-group">
        <label for="current_password">Nykyinen salasana</label>
        <input type="password" id="current_password" name="current_password">
    </div>

    <div class="grid-two">
        <div class="form-group">
            <label for="new_password">Uusi salasana</label>
            <input type="password" id="new_password" name="new_password">
        </div>

        <div class="form-group">
            <label for="confirm_password">Vahvista uusi salasana</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>
    </div>

    <div class="actions">
        <button type="submit">Vaihda salasana</button>
        <a href="profile.php" class="btn btn-secondary">Takaisin profiiliin</a>
    </div>
</form>
</div>

<?php include 'footer.php'; ?>
