<?php

require_once 'config.php';

$rating = '';
$message_text = '';
$errors = [];

// handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // check login
    if (!is_logged_in()) {
        set_message('Kirjaudu sisään jättääksesi palautteen.', 'error');
        redirect('login.php');
    }

    $rating = trim($_POST['rating'] ?? '');
    $message_text = trim($_POST['message'] ?? '');

    // validate required fields
    if ($rating === '' || $message_text === '') {
        $errors[] = 'Kaikki kentät ovat pakollisia.';
    }

    // validate rating
    if ($rating !== '' && (!is_numeric($rating) || $rating < 1 || $rating > 5)) {
        $errors[] = 'Arvosanan täytyy olla 1–5.';
    }

    // validate message length
    if ($message_text !== '' && strlen($message_text) < 10) {
        $errors[] = 'Palaute on liian lyhyt.';
    }

    if ($message_text !== '' && strlen($message_text) > 500) {
        $errors[] = 'Palaute on liian pitkä.';
    }

    // save feedback
    if (!$errors) {

        $sql = "INSERT INTO feedback (user_id, rating, message, status)
                VALUES (?, ?, ?, 'visible')";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            $_SESSION['user']['id'],
            (int)$rating,
            $message_text
        ]);

        set_message('Kiitos palautteestasi.', 'success');
        redirect('feedback.php');
    }
}

// load feedback list
$sql = "SELECT feedback.*, users.name AS user_name, users.avatar_url
        FROM feedback
        INNER JOIN users ON feedback.user_id = users.id
        WHERE feedback.status = 'visible'
        ORDER BY feedback.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();

$feedback_list = $stmt->fetchAll();

// feedback statistics
$sql = "SELECT COUNT(*) AS total_count, AVG(rating) AS average_rating
        FROM feedback
        WHERE status = 'visible'";

$stmt = $conn->prepare($sql);
$stmt->execute();

$rating_stats = $stmt->fetch();

$total_feedback = (int)($rating_stats['total_count'] ?? 0);

$average_rating = $rating_stats['average_rating']
    ? round($rating_stats['average_rating'], 1)
    : 0;

include 'header.php';
?>

<!-- page heading -->
<div class="page-intro fade-card">
    <span class="section-tag">Palaute</span>
    <h1>Käyttäjien palaute</h1>
    <p>Jätä arvio VAROsta tai lue muiden käyttäjien mielipiteitä.</p>
</div>

<!-- stats and form -->
<div class="grid-two">

    <!-- statistics card -->
    <div class="card glass-card fade-card delay-1">
        <h2>Palautteen yhteenveto</h2>

        <div class="profile-stats">

            <div class="mini-card">
                <strong><?php echo e($total_feedback); ?></strong>
                <span>Arvostelujen määrä</span>
            </div>

            <div class="mini-card">
                <strong><?php echo e($average_rating); ?>/5</strong>
                <span>Keskimääräinen arvosana</span>
            </div>

        </div>
    </div>

    <!-- feedback form -->
    <div class="card glass-card fade-card delay-2">

        <h2>Jätä palaute</h2>

        <?php if (!is_logged_in()): ?>

            <p>Sinun täytyy kirjautua sisään ennen palautteen jättämistä.</p>

            <a href="login.php" class="btn">Kirjaudu sisään</a>

        <?php else: ?>

            <!-- error messages -->
            <?php if ($errors): ?>
                <div class="message error">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post">

                <div class="form-group">
                    <label for="rating">Arvosana</label>

                    <select id="rating" name="rating">

                        <option value="">Valitse arvosana</option>

                        <option value="5" <?php echo $rating === '5' ? 'selected' : ''; ?>>
                            5 - Erinomainen
                        </option>

                        <option value="4" <?php echo $rating === '4' ? 'selected' : ''; ?>>
                            4 - Hyvä
                        </option>

                        <option value="3" <?php echo $rating === '3' ? 'selected' : ''; ?>>
                            3 - Tavallinen
                        </option>

                        <option value="2" <?php echo $rating === '2' ? 'selected' : ''; ?>>
                            2 - Voisi olla parempi
                        </option>

                        <option value="1" <?php echo $rating === '1' ? 'selected' : ''; ?>>
                            1 - Huono
                        </option>

                    </select>
                </div>

                <div class="form-group">

                    <label for="message">Palautteesi</label>

                    <textarea
                        id="message"
                        name="message"
                        placeholder="Kirjoita arviosi tähän..."
                    ><?php echo e($message_text); ?></textarea>

                    <p class="small-text">
                        Vähintään 10 merkkiä, enintään 500 merkkiä.
                    </p>

                </div>

                <button type="submit">Lähetä palaute</button>

            </form>

        <?php endif; ?>

    </div>
</div>

<!-- feedback list -->
<div class="card glass-card fade-card delay-3">

    <h2>Arvostelut</h2>

    <?php if ($feedback_list): ?>

        <div class="feedback-list">

            <?php foreach ($feedback_list as $row): ?>

                <?php
                $first_letter = strtoupper(substr($row['user_name'], 0, 1));
                ?>

                <div class="feedback-item">

                    <div class="feedback-user">

                        <?php if (!empty($row['avatar_url'])): ?>

                            <img
                                src="<?php echo e($row['avatar_url']); ?>"
                                class="feedback-avatar"
                            >

                        <?php else: ?>

                            <div class="feedback-avatar feedback-avatar-placeholder">
                                <?php echo e($first_letter); ?>
                            </div>

                        <?php endif; ?>

                        <div>

                            <strong><?php echo e($row['user_name']); ?></strong>

                            <p class="small-text">
                                <?php echo e(date('Y-m-d H:i', strtotime($row['created_at']))); ?>
                            </p>

                        </div>
                    </div>

                    <div class="feedback-rating">

                        <?php echo str_repeat('★', (int)$row['rating']); ?>
                        <?php echo str_repeat('☆', 5 - (int)$row['rating']); ?>

                    </div>

                    <p><?php echo e($row['message']); ?></p>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <p>Ei palautetta vielä.</p>

    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>