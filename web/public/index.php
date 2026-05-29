<?php
// index.php — Dashboard. Lists OPEN tasks with the client-side filter chips.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/tasks.php';
require_once __DIR__ . '/../includes/helpers.php';

$tasks = get_open_tasks($pdo);
$back  = 'index.php';   // task-card.php uses this to know where to return
?>

<?php require __DIR__ . '/../includes/partials/header.php'; ?>

<h1>Dashboard</h1>
<p class="page-subtitle">Your open tasks and upcoming deadlines. Let's get that degree!</p>

<?php $quote = get_random_quote(); ?>
<figure class="quote-banner">
    <blockquote><?= e($quote['text']) ?></blockquote>
    <figcaption>— <?= e($quote['author']) ?></figcaption>
</figure>

<?php if (empty($tasks)): ?>

    <p>No open tasks. <a href="/add.php">Add one?</a></p>

<?php else: ?>

    <?php require __DIR__ . '/../includes/partials/filters.php'; ?>

    <div id="task-list">

        <?php foreach ($tasks as $task): ?>
            <?php require __DIR__ . '/../includes/partials/task-card.php'; ?>
        <?php endforeach; ?>

        <p class="no-match" id="no-match" hidden>Nothing matches these filters.</p>

    </div><!-- #task-list -->

<?php endif; ?>

<?php require __DIR__ . '/../includes/partials/footer.php'; ?>