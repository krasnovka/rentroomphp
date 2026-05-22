<?php

require_once 'config.php';

// get latest rooms for homepage preview
$sql = "SELECT * FROM rooms ORDER BY id DESC LIMIT 3";
$stmt = $conn->prepare($sql);
$stmt->execute();
$rooms = $stmt->fetchAll();

// count all rooms
$sql = "SELECT COUNT(*) FROM rooms";
$stmt = $conn->prepare($sql);
$stmt->execute();
$room_count = (int)$stmt->fetchColumn();

// count active bookings
$sql = "SELECT COUNT(*) FROM bookings WHERE status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$active_booking_count = (int)$stmt->fetchColumn();

// count users
$sql = "SELECT COUNT(*) FROM users";
$stmt = $conn->prepare($sql);
$stmt->execute();
$user_count = (int)$stmt->fetchColumn();

include 'header.php';
?>
<!-- hero section -->

<div class="etusivu-hero">
    <img src="uploads/images/varo logo.png" class="etusivu-kuva">

</div>
 
<div class="hero-block">
    <div class="hero-text">
        <span class="hero-badge">VARO</span>
        <h1>Huonevarausjärjestelmä kirjastossa</h1>
        <p>Katso huoneet, valitse aika ja tee varaukset helposti ja nopeasti.</p>

        <div class="actions">
            <a href="rooms.php" class="btn">Näytä huoneet</a>

            <?php if (!is_logged_in()): ?>
                <a href="register.php" class="btn btn-secondary">Rekisteröidy</a>
            <?php else: ?>
                <a href="my_bookings.php" class="btn btn-secondary">Omat varaukset</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- statistics cards -->
    <div class="hero-side">
        <div class="mini-card">
            <strong><?php echo e($room_count); ?></strong>
            <span>Huoneet</span>
        </div>

        <div class="mini-card">
            <strong><?php echo e($active_booking_count); ?></strong>
            <span>Aktiiviset varaukset</span>
        </div>

        <div class="mini-card">
            <strong><?php echo e($user_count); ?></strong>
            <span>Käyttäjät</span>
        </div>
    </div>
</div>

<!-- small feature highlights -->
<div class="hero-note fade-card">
    <span>Yksinkertainen käyttöliittymä</span>
    <span>Helppo varausprosessi</span>
    <span>Toimii käyttäjille ja ylläpitäjille</span>
</div>

<!-- main features section -->
<div class="grid-two">
    <div class="card modern-card">
        <h2>Päätoiminnot</h2>

        <ul class="feature-list">
            <li>Katso huoneet ja niiden tiedot</li>
            <li>Tee varauksia päivämäärän ja ajan mukaan</li>
            <li>Katso ja peru varauksesi</li>
            <li>Hallitse huoneita ylläpitäjän paneelissa</li>
        </ul>
    </div>
</div>

<!-- how system works -->
<div class="card fade-card delay-2">
    <h2>Miten järjestelmä toimii</h2>
    <ul>
        <li>Valitse huone</li>
        <li>Valitse päivä ja aika</li>
        <li>Tee varaus</li>
        <li>Katso varauksesi myöhemmin</li>
    </ul>
</div>

<!-- quick actions -->
<div class="card fade-card delay-3">
    <h2>Pikatoiminnot</h2>

    <div class="quick-links">
        <a href="rooms.php" class="quick-link">Avaa huonelista</a>
        <a href="booking_calendar.php" class="quick-link">Avaa varauskalenteri</a>
        <a href="about.php" class="quick-link">Avaa tietosivu</a>

        <?php if (is_logged_in()): ?>
            <a href="my_bookings.php" class="quick-link">Omat varaukset</a>
            <a href="change_password.php" class="quick-link">Vaihda salasana</a>
        <?php else: ?>
            <a href="login.php" class="quick-link">Kirjaudu sisään</a>
        <?php endif; ?>

        <?php if (is_admin()): ?>
            <a href="admin_panel.php" class="quick-link">Ylläpitäjän paneeli</a>
        <?php endif; ?>
    </div>
</div>

<!-- latest rooms table -->
<div class="card fade-card delay-3">
    <h2>Viimeisimmät huoneet</h2>

    <?php if ($rooms): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Nimi</th>
                <th>Kuvaus</th>
                <th>Kapasiteetti</th>
            </tr>

            <?php foreach ($rooms as $row): ?>
                <tr>
                    <td><?php echo e($row['id']); ?></td>
                    <td><?php echo e($row['name']); ?></td>
                    <td><?php echo e($row['description']); ?></td>
                    <td><?php echo e($row['capacity']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <p style="margin-top: 15px;">
            <a href="rooms.php" class="btn">Näytä kaikki huoneet</a>
        </p>

    <?php else: ?>
        <p>Huoneita ei löytynyt.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>