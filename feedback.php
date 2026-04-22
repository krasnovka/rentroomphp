<?php
// feedback.php

require_once 'config.php';

$rating = '';
$message_text = '';
$errors = [];

// Feedback form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_logged_in()) {
        set_message('Please log in to leave feedback.', 'error');
        redirect('login.php');
    }

    $rating = trim($_POST['rating'] ?? '');
    $message_text = trim($_POST['message'] ?? '');

    if ($rating === '' || $message_text === '') {
        $errors[] = 'All fields are required.';
    }

    if ($rating !== '' && (!is_numeric($rating) || (int)$rating < 1 || (int)$rating > 5)) {
        $errors[] = 'Rating must be from 1 to 5.';
    }

    if ($message_text !== '' && strlen($message_text) < 10) {
        $errors[] = 'Feedback must be at least 10 characters.';
    }

    if ($message_text !== '' && strlen($message_text) > 500) {
        $errors[] = 'Feedback is too long. Max 500 characters.';
    }

    if (!$errors) {
        $sql = "INSERT INTO feedback (user_id, rating, message, status) VALUES (?, ?, ?, 'visible')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_SESSION['user']['id'], (int)$rating, $message_text]);

        set_message('Thank you for your feedback.', 'success');
        redirect('feedback.php');
    }
}

// Load visible feedback list
$sql = "SELECT feedback.*, users.name AS user_name, users.avatar_url
        FROM feedback
        INNER JOIN users ON feedback.user_id = users.id
        WHERE feedback.status = 'visible'
        ORDER BY feedback.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$feedback_list = $stmt->fetchAll();

// Average rating for small summary
$sql = "SELECT COUNT(*) AS total_count, AVG(rating) AS average_rating FROM feedback WHERE status = 'visible'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$rating_stats = $stmt->fetch();

$total_feedback = (int)($rating_stats['total_count'] ?? 0);
$average_rating = $rating_stats['average_rating'] ? round((float)$rating_stats['average_rating'], 1) : 0;

include 'header.php';
?>

<!-- Page heading -->
<div class="page-intro fade-card">
    <span class="section-tag">Feedback</span>
    <h1>User feedback</h1>
    <p>Leave a review about VARO or read what other users think about the system.</p>
</div>

<!-- Feedback summary -->
<div class="grid-two">
    <div class="card glass-card fade-card delay-1">
        <h2>Feedback Summary</h2>
        <div class="profile-stats">
            <div class="mini-card">
                <strong><?php echo e($total_feedback); ?></strong>
                <span>Total reviews</span>
            </div>
            <div class="mini-card">
                <strong><?php echo e($average_rating); ?>/5</strong>
                <span>Average rating</span>
            </div>
        </div>
    </div>

    <!-- Feedback form -->
    <div class="card glass-card fade-card delay-2">
        <h2>Leave Feedback</h2>

        <?php if (!is_logged_in()): ?>
            <p>You need to log in before leaving feedback.</p>
            <a href="login.php" class="btn">Login</a>
        <?php else: ?>
            <?php if ($errors): ?>
                <div class="message error">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="rating">Rating</label>
                    <select id="rating" name="rating">
                        <option value="">Choose rating</option>
                        <option value="5" <?php echo $rating === '5' ? 'selected' : ''; ?>>5 - Excellent</option>
                        <option value="4" <?php echo $rating === '4' ? 'selected' : ''; ?>>4 - Good</option>
                        <option value="3" <?php echo $rating === '3' ? 'selected' : ''; ?>>3 - Normal</option>
                        <option value="2" <?php echo $rating === '2' ? 'selected' : ''; ?>>2 - Could be better</option>
                        <option value="1" <?php echo $rating === '1' ? 'selected' : ''; ?>>1 - Bad</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message">Your Feedback</label>
                    <textarea id="message" name="message" placeholder="Write your review here..."><?php echo e($message_text); ?></textarea>
                    <p class="small-text">Minimum 10 characters, maximum 500 characters.</p>
                </div>

                <button type="submit">Send Feedback</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Feedback list -->
<div class="card glass-card fade-card delay-3">
    <h2>All Reviews</h2>

    <?php if ($feedback_list): ?>
        <div class="feedback-list">
            <?php foreach ($feedback_list as $row): ?>
                <?php $first_letter = strtoupper(substr($row['user_name'], 0, 1)); ?>
                <div class="feedback-item">
                    <div class="feedback-user">
                        <?php if (!empty($row['avatar_url'])): ?>
                            <img src="<?php echo e($row['avatar_url']); ?>" alt="User photo" class="feedback-avatar">
                        <?php else: ?>
                            <div class="feedback-avatar feedback-avatar-placeholder"><?php echo e($first_letter); ?></div>
                        <?php endif; ?>
                        <div>
                            <strong><?php echo e($row['user_name']); ?></strong>
                            <p class="small-text"><?php echo e(date('Y-m-d H:i', strtotime($row['created_at']))); ?></p>
                        </div>
                    </div>

                    <div class="feedback-rating">
                        <?php echo str_repeat('★', (int)$row['rating']); ?><?php echo str_repeat('☆', 5 - (int)$row['rating']); ?>
                    </div>

                    <p><?php echo e($row['message']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No feedback yet. Be the first to leave a review.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
