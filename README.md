
# Deadlinez

A very small uni project for the "Programming for the internet" course, essentially a minimalistic glorified TODO app for uni students.
Stay organized folks!


## Tech Stack

**Frontend:** HTML, JS, CSS

**Backend:** PHP. PostgreSQL

**Docker:** Yes


## Usage (local only)

To use this app, clone this repo and run (with Docker desktop installed and running):

```bash
  docker compose up --build
```

Then open <http://localhost:8080>.

### Pages

- **Dashboard** (`index.php`) — open tasks, filterable by priority.
- **Add task** (`add.php`) — HTML form that validates and saves a new task.
- **Archive** (`archive.php`) — every task, filterable by status/priority + live text search.

### Layout

```
docker-compose.yml   web (PHP/Apache) + db (PostgreSQL) services
db/init.sql          schema + sample tasks (runs on first start)
web/Dockerfile       php:apache image with the pdo_pgsql driver
web/public/          page scripts served by Apache (the document root)
web/includes/        DB connection, task queries, shared layout partials
```


## Authors ❤️

- [@Alfonz101](https://www.github.com/Alfonz101)

