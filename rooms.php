<?php
require_once 'config.php';

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $sql = "SELECT * FROM rooms
            WHERE name LIKE ? OR description LIKE ? OR capacity LIKE ?
            ORDER BY name ASC";

    $stmt = $conn->prepare($sql);
    $like = '%' . $search . '%';
    $stmt->execute([$like, $like, $like]);
} else {
    $sql = "SELECT * FROM rooms ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}

$rooms = $stmt->fetchAll();

include 'header.php';
?>

<div class="page-intro fade-card">
    <span class="section-tag">Huoneet</span>
    <h1>Selaa huoneita</h1>
    <p>Valitse huone ja tee varaus helposti.</p>
</div>

<div class="card glass-card fade-card delay-1">
    <h2>Hae huoneita</h2>

    <form method="get" class="search-form">
        <div class="search-row">
            <input type="text" name="search"
                   value="<?php echo e($search); ?>"
                   placeholder="Hae huoneita...">

            <button type="submit">Hae</button>

            <?php if ($search !== ''): ?>
                <a href="rooms.php" class="btn btn-secondary">Nollaa</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card glass-card fade-card delay-2">

<?php if ($rooms): ?>

<table>
    <tr>
        <th>ID</th>
        <th>Nimi</th>
        <th>Kuvaus</th>
        <th>Kapasiteetti</th>
        <th>Toiminnot</th>
    </tr>

    <?php foreach ($rooms as $row): ?>
        <tr>
            <td><?php echo e($row['id']); ?></td>
            <td><strong><?php echo e($row['name']); ?></strong></td>
            <td><?php echo e($row['description']); ?></td>
            <td><?php echo e($row['capacity']); ?> henkilöä</td>
            <td>

                <div class="actions">

                    <?php if (is_logged_in()): ?>
                        <a href="book_room.php?room_id=<?php echo e($row['id']); ?>" class="btn">
                            Varaa
                        </a>
                    <?php endif; ?>

                    <a href="booking_calendar.php?room_id=<?php echo e($row['id']); ?>" class="btn btn-secondary">
                        Kalenteri
                    </a>

                    <?php if (is_admin()): ?>
                        <a href="edit_room.php?id=<?php echo e($row['id']); ?>" class="btn btn-secondary">
                            Muokkaa
                        </a>

                        <form method="post"
                              action="delete_room.php"
                              class="inline-form"
                              onsubmit="return confirm('Poistetaanko tämä huone?')">

                            <input type="hidden" name="id" value="<?php echo e($row['id']); ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">

                            <button type="submit" class="btn btn-danger">
                                Poista
                            </button>
                        </form>
                    <?php endif; ?>

                </div>

            </td>
        </tr>
    <?php endforeach; ?>

</table>

<?php else: ?>
    <p>Ei huoneita.</p>
<?php endif; ?>

</div>

<?php include 'footer.php'; ?>