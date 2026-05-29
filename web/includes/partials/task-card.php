<?php
// task-card.php — renders ONE task as a card. Used by index.php and archive.php.
// Set these before including (inside the loop):
//   $task — one row from the tasks table (associative array)
//   $back — page to return to after an action ('index.php' | 'archive.php')
$due = format_due($task['due_date']);   // due-date chip, or null if no date
?>
<div class="task-card <?= $task['completed'] ? 'task-done' : '' ?> priority-<?= strtolower(e($task['priority'])) ?>"
     data-title="<?= e($task['title']) ?>"
     data-priority="<?= strtolower(e($task['priority'])) ?>"
     data-due="<?= $due['class'] ?? 'none' ?>"
     data-status="<?= $task['completed'] ? 'done' : 'open' ?>">

    <div class="task-row">
        <div class="task-left">
            <span class="task-title"><?= e($task['title']) ?></span>
            <?php if ($task['course'] !== ''): ?>
                <span class="task-tag"><?= e($task['course']) ?></span>
            <?php endif; ?>
        </div>
        <?php if ($due): ?>
            <span class="due due-<?= $due['class'] ?>">
                <i data-lucide="<?= $due['icon'] ?>"></i><?= e($due['label']) ?>
            </span>
        <?php endif; ?>
    </div>

    <?php if ($task['description'] !== ''): ?>
        <p class="task-desc"><?= e($task['description']) ?></p>
    <?php endif; ?>

    <div class="task-actions">
        <?php if ($task['completed']): ?>
            <?php action_form((int) $task['id'], 'reopen',   $back, 'rotate-ccw', 'Reopen',       'btn-reopen'); ?>
        <?php else: ?>
            <?php action_form((int) $task['id'], 'complete', $back, 'check',      'Mark as done', 'btn-done'); ?>
        <?php endif; ?>
        <?php action_form((int) $task['id'], 'delete', $back, 'trash-2', 'Delete', 'btn-delete', true); ?>
    </div>
</div>
