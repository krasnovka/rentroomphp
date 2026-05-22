<?php

require_once 'config.php';

// redirect if already logged in
if (is_logged_in()) {
    redirect('index.php');
}

$name = '';
$email = '';
$errors = [];

// handle register form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // validate required fields
    if ($name === '' || $email === '' || $password === '' || $confirm_password === '') {
        $errors[] = 'Kaikki kentät ovat pakollisia.';
    }

    // validate email format
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Anna kelvollinen sähköposti.';
    }

    // validate password length
    if ($password !== '' && strlen($password) < 6) {
        $errors[] = 'Salasanan täytyy olla vähintään 6 merkkiä.';
    }

    // check password confirmation
    if ($password !== $confirm_password) {
        $errors[] = 'Salasanat eivät täsmää.';
    }

    if (!$errors) {

        // check if email already exists
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $errors[] = 'Tämä sähköposti on jo rekisteröity.';
        } else {

            // hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // insert new user
            $sql = "INSERT INTO users (name, email, password, role)
                    VALUES (?, ?, ?, 'user')";

            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $email, $hash]);

            set_message('Rekisteröinti onnistui. Voit nyt kirjautua sisään.', 'success');
            redirect('login.php');
        }
    }
}

include 'header.php';
?>

<!-- page layout -->
<div class="auth-layout">

    <!-- intro section -->
    <div class="page-intro fade-card">
        <span class="section-tag">Uusi tili</span>
        <h1>Luo VARO-tili</h1>
        <p>Rekisteröidy varataksesi huoneita, hallitaksesi aikatauluasi ja avataksesi profiilisi.</p>
    </div>

    <!-- register form -->
    <div class="card glass-card fade-card delay-1 auth-card">
        <h2>Rekisteröidy</h2>

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
                    <label for="name">Nimi</label>
                    <input type="text" id="name" name="name" value="<?php echo e($name); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Sähköposti</label>
                    <input type="email" id="email" name="email" value="<?php echo e($email); ?>">
                </div>
            </div>

            <div class="grid-two">
                <div class="form-group">
                    <label for="password">Salasana</label>
                    <input type="password" id="password" name="password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Vahvista salasana</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
            </div>

            <div class="actions">
                <button type="submit">Rekisteröidy</button>
                <a href="login.php" class="btn btn-secondary">Kirjaudu sisään</a>
            </div>

        </form>
    </div>

</div>

<?php include 'footer.php'; ?>