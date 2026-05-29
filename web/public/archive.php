<?php
// archive.php — All tasks (open + done) with text search and filter chips.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/tasks.php';
require_once __DIR__ . '/../includes/helpers.php';

$tasks = get_all_tasks($pdo);
$back  = 'archive.php';   // task-card.php uses this to know where to return
?>

<?php require __DIR__ . '/../includes/partials/header.php'; ?>

<h1>All tasks</h1>
<p class="page-subtitle">Review your completed and archived activities. Pride yourself on your past achievements!</p>

<input type="search" id="search" placeholder="Filter by text...">

<?php $show_status = true;
require __DIR__ . '/../includes/partials/filters.php'; ?>

<div id="task-list">

    <?php if (empty($tasks)): ?>

        <p>No tasks yet. <a href="/add.php">Add one?</a></p>

    <?php else: ?>

        <?php foreach ($tasks as $task): ?>
            <?php require __DIR__ . '/../includes/partials/task-card.php'; ?>
        <?php endforeach; ?>

        <p class="no-match" id="no-match" hidden>Nothing matches these filters.</p>

    <?php endif; ?>

</div>

<?php require __DIR__ . '/../includes/partials/footer.php'; ?>