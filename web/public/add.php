<?php
// add.php — HTML form to create a task. On POST it validates, then either saves
// and redirects to the Dashboard, or re-renders the form with error messages.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/tasks.php';
require_once __DIR__ . '/../includes/helpers.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $priority = $_POST['priority'] ?? '';
    $due_date = $_POST['due_date'] ?? '';

    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if (!in_array($priority, PRIORITIES, true)) {
        $errors[] = 'Please select a valid priority.';
    }

    if (empty($errors)) {
        // Convert an empty date field to NULL so the DB stores "no due date".
        $due_date = $due_date === '' ? null : $due_date;
        add_task($pdo, $title, $description, $course, $priority, $due_date);
        header('Location: /index.php');
        exit;
    }
}
?>

<?php require __DIR__ . '/../includes/partials/header.php'; ?>

<h1>Add task</h1>
<p class="page-subtitle">Fill in the details below to create a new task. Ugh, not again..</p>

<?php foreach ($errors as $error): ?>
    <p class="error"><?= e($error) ?></p>
<?php endforeach; ?>

<form method="post">

    <div class="form-group">
        <label for="title">Title *</label>
        <input type="text" id="title" name="title" required value="<?= e($_POST['title'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description"><?= e($_POST['description'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label for="course">Course</label>
        <input type="text" id="course" name="course" value="<?= e($_POST['course'] ?? '') ?>"
            placeholder="e.g. Programming For The Internet">
    </div>

    <div class="form-group">
        <label for="priority">Priority *</label>
        <select id="priority" name="priority">
            <?php foreach (PRIORITIES as $p): ?>
                <option value="<?= e($p) ?>" <?= ($p === ($_POST['priority'] ?? 'Medium')) ? 'selected' : '' ?>>
                    <?= e($p) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="due_date">Due Date</label>
        <input type="date" id="due_date" name="due_date" value="<?= e($_POST['due_date'] ?? '') ?>">
    </div>

    <button type="submit" class="btn-primary">Add Task</button>

</form>

<?php require __DIR__ . '/../includes/partials/footer.php'; ?>