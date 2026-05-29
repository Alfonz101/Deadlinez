# Deadlinez

![hippo](https://media2.giphy.com/media/v1.Y2lkPTZjMDliOTUyemNybWk1dGQ0bGl3ajRud2QxOTZrdDZvYjhmemd5amV5NXQxY3NqbCZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/IiBlJr6FZTtvi/giphy.gif)

A small uni project for the "Programming for the Internet" course — a
minimalistic, glorified TODO app for keeping track of university deadlines.
Stay organized folks!

Server-rendered **PHP + PostgreSQL**, running entirely in **Docker**. No
framework, no build step — vanilla PHP for the pages, a little vanilla JS for
client-side filtering.

## Features

- **Dashboard** — your open tasks, sorted by deadline, with urgency-graded due
  chips (overdue / soon / later).
- **Add task** — a validated HTML form (title, description, course, priority,
  due date).
- **Archive** — every task, with live text search and filter chips for
  status / priority / due.
- One-click complete, reopen, and delete on each task.

## Tech stack

| Layer    | Technology              |
|----------|-------------------------|
| Backend  | PHP 8.3 (Apache)        |
| Database | PostgreSQL 16           |
| Frontend | HTML5, CSS, vanilla JS  |
| Tooling  | Docker + Docker Compose |

## Usage (local only)

Requires **Docker Desktop** installed and running. Clone the repo, then:

```bash
docker compose up --build      # build + start the web and db containers
```

Open <http://localhost:8080>.

```bash
docker compose down            # stop the containers
docker compose down -v         # stop AND wipe the database (re-runs the seed next start)
```

## Pages

- **Dashboard** (`index.php`) — open tasks, filterable by priority and due date.
- **Add task** (`add.php`) — HTML form that validates and saves a new task.
- **Archive** (`archive.php`) — every task, filterable by status / priority / due
  + live text search.

## Layout

```
docker-compose.yml   web (PHP/Apache) + db (PostgreSQL) services
db/init.sql          schema + sample tasks (runs on first start)
web/Dockerfile       php:apache image with the pdo_pgsql driver
web/public/          pages served by Apache (the document root)
web/includes/        DB connection, task queries, shared layout partials
```

## Documentation

See [`DOCUMENTATION.md`](DOCUMENTATION.md) for a full technical walkthrough — how
each file works, how the pieces connect, the database schema, the request flow,
and security notes.

## Authors ❤️

- [@Alfonz101](https://www.github.com/Alfonz101)
