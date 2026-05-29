# Deadlinez — Technical Documentation

A minimalistic TODO / deadline tracker for university students. Server-rendered
PHP backed by PostgreSQL, running entirely in Docker. No framework, no build
step, no JavaScript bundler — vanilla PHP for the pages and a little vanilla JS
for client-side filtering.

This document explains **what every file does**, **how the pieces connect**, and
**how to run the project**.

---

## 1. Tech stack

| Layer    | Technology                                  |
|----------|---------------------------------------------|
| Web server | Apache (inside the `php:8.3-apache` image) |
| Language | PHP 8.3 (server-rendered HTML)              |
| Database | PostgreSQL 16                               |
| DB access | PDO with the `pdo_pgsql` driver            |
| Frontend | HTML5, CSS, a single vanilla JS file        |
| Icons    | Lucide (loaded from a CDN)                  |
| Tooling  | Docker + Docker Compose                     |

The whole app is two Docker containers: **`web`** (PHP/Apache) and **`db`**
(PostgreSQL). They talk over an internal Docker network.

---

## 2. How to run it

Prerequisites: **Docker Desktop** installed and running.

```bash
docker compose up --build      # build images + start both containers
```

Then open <http://localhost:8080>.

```bash
docker compose down            # stop the containers
docker compose down -v         # stop AND delete the database volume
```

> **Reseeding the database:** `db/init.sql` only runs the **first** time the
> database volume is created. To wipe the data and re-run the seed, use
> `docker compose down -v` (the `-v` removes the volume), then
> `docker compose up --build` again.

---

## 3. Project layout

```
deadlinez/
├── docker-compose.yml          Defines the two services: web + db
├── db/
│   └── init.sql                Schema + seed data (runs on first DB start)
└── web/
    ├── Dockerfile              Builds the PHP/Apache image with pdo_pgsql
    ├── includes/               Server-side logic (NOT directly reachable by URL)
    │   ├── db.php              Opens the PostgreSQL connection ($pdo)
    │   ├── tasks.php           All SQL queries, one function per operation
    │   ├── helpers.php         View helpers: escaping, date chip, button form, quotes
    │   └── partials/           Reusable chunks of HTML
    │       ├── header.php      <head>, top navigation, opening <main>
    │       ├── footer.php      closing tags, footer, script includes
    │       ├── filters.php     filter-chip bar (priority / due / status)
    │       └── task-card.php   renders ONE task card (used by 2 pages)
    └── public/                 Apache's document root — these URLs are reachable
        ├── index.php           Dashboard (open tasks)
        ├── add.php             Add-task form
        ├── archive.php         All tasks + search
        ├── action.php          Write-only controller (complete/reopen/delete)
        └── src/
            ├── styles/style.css   All styling
            └── scripts/app.js     Client-side search + chip filtering
```

### Why `includes/` and `public/` are separate

Apache's document root points at `web/public/` (see the `Dockerfile`). Only files
in `public/` can be requested by a browser. Everything in `includes/` is pulled
in **server-side** via `require`, so the connection logic, SQL, and partial
templates are never directly downloadable. This is a standard, simple way to keep
"things the browser asks for" separate from "things that only run on the server".

---

## 4. The big picture — how a request flows

Deadlinez uses the oldest, simplest web pattern: **every page is a PHP file that
runs top to bottom and prints HTML.** There is no router and no framework.

A typical page request (e.g. the Dashboard):

```
Browser  ──GET /index.php──▶  Apache  ──▶  index.php runs:
                                          1. require db.php      → opens $pdo
                                          2. require tasks.php   → query functions
                                          3. require helpers.php → view helpers
                                          4. $tasks = get_open_tasks($pdo)
                                          5. require header.php  → prints page top
                                          6. loop tasks → require task-card.php each
                                          7. require footer.php  → prints page bottom
                                          ◀── finished HTML ──  Browser renders it
```

An **action** (marking a task done, deleting, etc.) is a separate, write-only
round-trip that performs the change and then redirects:

```
Browser  ──POST /action.php (id, action, back)──▶  action.php runs:
                                                   1. require db.php + tasks.php
                                                   2. read id + action from $_POST
                                                   3. call set_completed()/delete_task()
                                                   4. header('Location: /<back>')  → redirect
Browser  ◀── 302 redirect to the page you came from ── then re-GETs that page
```

This "redirect after a write" is intentional: it means refreshing the page never
re-submits the action, and `action.php` itself never prints any HTML.

---

## 5. The database

### Schema (`db/init.sql`)

```sql
CREATE TYPE priority_level AS ENUM ('Low', 'Medium', 'High');

CREATE TABLE tasks (
    id          SERIAL PRIMARY KEY,                         -- auto-incrementing ID
    title       TEXT           NOT NULL,                    -- required
    description TEXT           NOT NULL DEFAULT '',         -- optional (defaults to empty)
    course      TEXT           NOT NULL DEFAULT '',         -- optional
    priority    priority_level NOT NULL DEFAULT 'Medium',   -- Low / Medium / High
    completed   BOOLEAN        NOT NULL DEFAULT FALSE,       -- done flag
    due_date    DATE                                        -- nullable: a task can have no deadline
);
```

Key points to be able to explain:

- **`priority_level` is an ENUM** — the database itself only allows the three
  values `'Low'`, `'Medium'`, `'High'`. The PHP `PRIORITIES` constant in
  `tasks.php` mirrors this list, and `add.php` validates against it before
  inserting.
- **`due_date` is nullable** — a task with no deadline stores `NULL`. The PHP
  converts an empty form field to `NULL` (in `add.php`).
- **`completed` is a real BOOLEAN** — `set_completed()` binds a PHP `bool` to it
  with `PDO::PARAM_BOOL`.
- The file starts with `DROP TABLE ... / DROP TYPE ...` so re-running it always
  begins from a clean state.

The seed `INSERT` at the bottom adds six example tasks (a mix of priorities, due
dates, and completed/open) so the app is not empty on first run.

### How the seed gets loaded

In `docker-compose.yml`, the file is mounted into the Postgres container:

```yaml
volumes:
  - ./db/init.sql:/docker-entrypoint-initdb.d/init.sql
```

The official Postgres image automatically runs any `.sql` file placed in
`/docker-entrypoint-initdb.d/` **the first time** the data directory is empty.

---

## 6. File-by-file reference

### `docker-compose.yml`

Defines the two services:

- **`web`** — builds the image from `./web/Dockerfile`, maps host port `8080` to
  container port `80`, mounts `./web` into the container (so editing a PHP file is
  reflected immediately, no rebuild needed), and passes the DB credentials as
  environment variables. `depends_on: db` makes the database start first.
- **`db`** — uses `postgres:16-alpine`, sets the database name/user/password, and
  stores its data in the named volume `db_data` so data survives restarts.

The DB credentials are intentionally simple (`deadlinez` / `deadlinez`) because
this is a local-only course project that never leaves `localhost`.

### `web/Dockerfile`

```dockerfile
FROM php:8.3-apache
RUN apt-get update && apt-get install -y libpq-dev          # PostgreSQL C headers
RUN docker-php-ext-install pdo pdo_pgsql                    # PHP's PostgreSQL driver
RUN sed -i 's|/var/www/html|/var/www/public|g' \           # move Apache's doc root
    /etc/apache2/sites-available/000-default.conf           #   to web/public
```

It starts from the official PHP+Apache image, compiles in the PostgreSQL driver
that PDO needs, and repoints Apache's document root at `public/`.

### `web/includes/db.php`

Reads the four `DB_*` environment variables (set in `docker-compose.yml`), opens
a PDO connection to PostgreSQL, and configures PDO to **throw exceptions** on
errors instead of failing silently. The connection object is `$pdo`, which every
page passes into the query functions.

### `web/includes/tasks.php`

The data layer — all SQL lives here, one function per operation. Coming from
Python, think of this as a small repository module.

| Function | What it does |
|----------|--------------|
| `PRIORITIES` (constant) | `['Low','Medium','High']` — mirrors the DB ENUM, used for validation and the form dropdown |
| `get_open_tasks($pdo)` | open tasks only (`completed = false`), soonest deadline first — Dashboard |
| `get_all_tasks($pdo)` | every task, open ones first then by deadline — Archive |
| `add_task(...)` | inserts a new task |
| `set_completed($pdo, $id, $completed)` | flips the `completed` flag (complete or reopen) |
| `delete_task($pdo, $id)` | permanently removes a task |

**All queries use prepared statements with named placeholders** (`:title`, `:id`,
…). User input is passed as parameters, never concatenated into the SQL string —
this is what prevents SQL injection.

`ORDER BY due_date ASC NULLS LAST` means tasks are sorted by deadline, and tasks
with no due date sink to the bottom rather than the top.

### `web/includes/helpers.php`

View helpers — small functions used while printing HTML.

- **`e($string)`** — HTML-escapes a string. **Used every time user data is printed
  into a page.** It turns characters like `<` `>` `"` into harmless entities, so a
  task titled `<script>` shows as text instead of running as code. This is the
  defense against cross-site scripting (XSS).
- **`format_due($due)`** — turns a stored date into a small status "chip". Returns
  `null` when there is no date; otherwise returns a `label` (e.g. "Overdue 3d",
  "Due today", "12 Jun"), a `class` (`overdue` / `soon` / `later`) used for color,
  and an `icon` name. The urgency grading lives here in one place.
- **`action_form($id, $action, $back, $icon, $label, $css, $confirm)`** — prints
  one small single-button `<form>` that POSTs to `action.php`. Because complete,
  reopen, and delete are all the same form shape, this builds it in **one place**
  instead of repeating the markup. `$confirm = true` adds the "Delete this task?"
  pop-up.
- **`get_random_quote()`** — picks a random motivational quote for the Dashboard
  banner; a new one shows on each page load.

### `web/includes/partials/header.php`

The top of every page: `<!DOCTYPE html>`, `<head>` (title + stylesheet link), the
navigation bar, and the opening `<main>` tag. It also detects the current page
(via `basename($_SERVER['PHP_SELF'])`) to highlight the active nav link.

### `web/includes/partials/footer.php`

The bottom of every page: closes `<main>`, prints the footer, and loads the two
scripts — the app's own `app.js` and the Lucide icon library (which replaces every
`<i data-lucide="...">` with a real SVG icon).

### `web/includes/partials/filters.php`

The filter-chip bar. Renders the **Priority** and **Due** chip groups, plus an
optional **Status** group (Open/Done) that only appears when the including page
sets `$show_status = true` first (the Archive does this; the Dashboard does not,
because it only ever shows open tasks). Each `.filter-group` has a `data-filter`
attribute (`priority`, `due`, `status`) that matches a `data-*` attribute on the
task cards — that pairing is what `app.js` uses to filter.

### `web/includes/partials/task-card.php`

Renders **one** task card, and is the single source of truth for what a card looks
like. Both `index.php` and `archive.php` include it inside their loop. Before
including it, the page sets two variables:

- `$task` — one row from the database (an associative array).
- `$back` — which page to return to after an action (`index.php` or `archive.php`).

The partial computes its own due-date chip with `format_due()`, prints the
title / course tag / description / due chip, and renders the action buttons by
calling `action_form()`. It shows **Reopen** for completed tasks and **Mark as
done** for open ones, plus a **Delete** button on every card. It works unchanged
on the Dashboard because Dashboard tasks are always open, so the "done" styling
and Reopen button simply never trigger there.

> **Why `require` (not `require_once`) in the loop:** `require` re-includes the
> file every iteration, so every task gets a card. `require_once` would render
> only the first card and skip the rest.

### `web/public/index.php` — Dashboard

Fetches open tasks (`get_open_tasks`), shows the random-quote banner, then either
an empty-state message or the filter bar + a card per task (via the partial). Sets
`$back = 'index.php'` so actions return here.

### `web/public/add.php` — Add task

Two jobs in one file:

1. **On POST:** reads and trims the form fields, validates (title required,
   priority must be a valid `PRIORITIES` value). If valid, converts an empty due
   date to `NULL`, calls `add_task()`, and redirects to the Dashboard. If invalid,
   it falls through and re-renders the form.
2. **Always:** prints the form. Any validation errors are shown at the top, and
   each field is repopulated with what the user typed (escaped with `e()`) so a
   failed submit does not lose their input. The Priority `<select>` is built from
   the `PRIORITIES` constant.

### `web/public/archive.php` — All tasks

Fetches every task (`get_all_tasks`), adds a live **text search** box, sets
`$show_status = true` so the Open/Done filter group appears, and renders a card
per task via the same partial. Sets `$back = 'archive.php'`.

### `web/public/action.php` — Write-only controller

Handles the three mutating actions. It reads `id`, `action`, and `back` from the
POST body, casting `id` to an integer for safety. It **whitelists** the `back`
value to `index.php` / `archive.php` only, so a crafted form cannot redirect the
user to an external site (an open-redirect guard). It then calls the right
function (`set_completed` or `delete_task`) and redirects back. It never outputs
HTML.

### `web/public/src/styles/style.css`

All styling. (Out of scope for this backend documentation, but it consumes the
`priority-*`, `due-*`, `task-done`, and chip classes that the PHP prints.)

### `web/public/src/scripts/app.js`

Client-side filtering for the Dashboard and Archive — no server request involved.
It keeps a small `state` object (one entry per filter group, default `'all'`),
listens for chip clicks and search input, then loops over every `.task-card` and
shows or hides it based on whether it matches the text query **and** every active
chip. Matching works by comparing each group's selected value against the card's
matching `data-*` attribute (`data-priority`, `data-due`, `data-status`,
`data-title`). When nothing matches, it reveals the "Nothing matches these
filters" note.

---

## 7. The three pages and their navigation

| Page | URL | Purpose | Data source |
|------|-----|---------|-------------|
| Dashboard | `/index.php` | open tasks, filter by priority/due | `get_open_tasks()` |
| Add task | `/add.php` | HTML form to create a task | writes via `add_task()` |
| Archive | `/archive.php` | all tasks, search + status/priority/due filters | `get_all_tasks()` |

All three are linked from the top navigation bar in `header.php`. This satisfies
the assignment's "3+ pages connected by navigation, at least one with a form"
requirement.

---

## 8. Security notes

Small project, but it does the three things that matter:

1. **SQL injection** — every query is a prepared statement with bound parameters
   (`tasks.php`). User input never touches the SQL string directly.
2. **Cross-site scripting (XSS)** — all user-supplied data is escaped with `e()`
   before being printed into HTML.
3. **Open redirect** — `action.php` only accepts a `back` target from a fixed
   whitelist, so it cannot be tricked into redirecting elsewhere.

---

## 9. Common tasks

**Change the seed data:** edit `db/init.sql`, then
`docker compose down -v && docker compose up --build` (the `-v` is required so the
seed re-runs).

**Edit a page or style:** just save the file — `./web` is mounted into the
container, so a browser refresh shows the change. No rebuild needed.

**Rebuild after changing the `Dockerfile` or installing an extension:**
`docker compose up --build`.

**Inspect the database directly:**
```bash
docker compose exec db psql -U deadlinez -d deadlinez
# then e.g.:  SELECT * FROM tasks;
```
