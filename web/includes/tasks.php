<?php

// Valid priority values — must match the ENUM in db/init.sql
const PRIORITIES = ['Low', 'Medium', 'High'];


// Get only open (not completed) tasks — used on the Dashboard
function get_open_tasks(PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE completed = false ORDER BY due_date ASC NULLS LAST');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Get all tasks — used in the Archive
function get_all_tasks(PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT * FROM tasks ORDER BY completed ASC, due_date ASC NULLS LAST');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Insert a new task into the database
function add_task(PDO $pdo, string $title, string $description, string $course, string $priority, ?string $due_date): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO tasks (title, description, course, priority, due_date)
         VALUES (:title, :description, :course, :priority, :due_date)'
    );
    $stmt->execute([
        ':title'       => $title,
        ':description' => $description,
        ':course'      => $course,
        ':priority'    => $priority,
        ':due_date'    => $due_date,
    ]);
}


// Mark a task done (true) or reopen it (false).
// bindValue with PARAM_BOOL sends a real boolean to the BOOLEAN column,
// instead of relying on string 'true'/'false' coercion.
function set_completed(PDO $pdo, int $id, bool $completed): void
{
    $stmt = $pdo->prepare('UPDATE tasks SET completed = :completed WHERE id = :id');
    $stmt->bindValue(':completed', $completed, PDO::PARAM_BOOL);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}


// Permanently delete a task
function delete_task(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id');
    $stmt->execute([':id' => $id]);
}