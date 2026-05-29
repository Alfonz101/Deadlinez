<?php
// action.php — write-only controller. Runs one task action (complete / reopen /
// delete) based on the submitted form, then redirects. Never prints any HTML.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/tasks.php';

// Read the values sent by the form (all via hidden inputs).
$id     = (int)($_POST['id']     ?? 0);  // (int) cast ensures it's a number, never raw user text
$action = $_POST['action']       ?? '';
$back   = $_POST['back']         ?? 'index.php';

// Only allow redirecting to pages we know about — nothing else.
// This prevents someone crafting a form that redirects to an external site.
$allowed = ['index.php', 'archive.php'];
if (!in_array($back, $allowed, true)) {
    $back = 'index.php';
}

// Run the correct function based on which button was clicked.
if ($id > 0) {
    if ($action === 'complete') {
        set_completed($pdo, $id, true);
    } elseif ($action === 'reopen') {
        set_completed($pdo, $id, false);
    } elseif ($action === 'delete') {
        delete_task($pdo, $id);
    }
}

// Redirect back to whichever page the button was on.
// This file never outputs any HTML — it only acts, then redirects.
header('Location: /' . $back);
exit;