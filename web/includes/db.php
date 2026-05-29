<?php

// Read the database credentials from environment variables.
// These are set in docker-compose.yml under the "web" service.
$host = getenv('DB_HOST');  // e.g. "db"
$name = getenv('DB_NAME');  // e.g. "deadlinez"
$user = getenv('DB_USER');  // e.g. "deadlinez"
$pass = getenv('DB_PASS');  // e.g. "deadlinez"

// Connect to PostgreSQL.
// PDO is PHP's built-in way to talk to databases.
// The string "pgsql:host=...;dbname=..." tells PDO which database to connect to.
$pdo = new PDO("pgsql:host=$host;dbname=$name", $user, $pass);

// If something goes wrong (wrong password, DB not ready, etc.),
// throw an exception instead of silently failing.
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);