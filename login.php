<?php

require_once 'config.php';

// ohjaa käyttäjä etusivulle jos on jo kirjautunut sisään
if (is_logged_in()) {
    redirect('index.php');
}

$email = '';
$errors = [];

// käsittele kirjautumislomake
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // tarkista pakolliset kentät
    if ($email === '' || $password === '') {
        $errors[] = 'Kaikki kentät ovat pakollisia.';
    } else {

        // hae käyttäjä sähköpostilla
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // tarkista salasana
        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Väärä sähköposti tai salasana.';
        } else {

            // luo sessio
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'avatar_url' => $user['avatar_url']
            ];

            set_message('Kirjautuminen onnistui.', 'success');
            redirect('index.php');
        }
    }
}

include 'header.php';
?>

<!-- sivun rakenne -->
<div class="auth-layout">

    <!-- tervetulo-osio -->
    <div class="page-intro fade-card">
        <span class="section-tag">Tervetuloa takaisin</span>
        <h1>Kirjaudu tilillesi</h1>
        <p>Avaa profiilisi, huonevaraukset ja oma hallintapaneeli.</p>
    </div>

    <!-- kirjautumislomake -->
    <div class="card glass-card fade-card delay-1 auth-card">
        <h2>Kirjaudu</h2>

        <?php if ($errors): ?>
            <div class="message error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <div class="form-group">
                <label for="email">Sähköposti</label>
                <input type="email" id="email" name="email" value="<?php echo e($email); ?>">
            </div>

            <div class="form-group">
                <label for="password">Salasana</label>
                <input type="password" id="password" name="password">
            </div>

            <div class="actions">
                <button type="submit">Kirjaudu</button>
                <a href="register.php" class="btn btn-secondary">Luo tili</a>
            </div>

        </form>
    </div>

</div>

<?php include 'footer.php'; ?>