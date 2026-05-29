<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deadlinez</title>
    <link rel="stylesheet" href="/src/styles/style.css">
</head>
<body>

<?php
// Detect which page we're on so the correct nav link gets the "active" class.
$page = basename($_SERVER['PHP_SELF']);
?>

<nav>
    <a href="/index.php" class="nav-logo">deadline<span class="z">z</span></a>
    <div class="nav-links">
        <a href="/index.php"   class="<?= $page === 'index.php'   ? 'active' : '' ?>">Dashboard</a>
        <a href="/add.php"     class="<?= $page === 'add.php'     ? 'active' : '' ?>">Add task</a>
        <a href="/archive.php" class="<?= $page === 'archive.php' ? 'active' : '' ?>">Archive</a>
    </div>
</nav>

<main>