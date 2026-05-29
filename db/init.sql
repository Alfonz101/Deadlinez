-- Drop everything first so re-running this file always starts clean
DROP TABLE IF EXISTS tasks;
DROP TYPE  IF EXISTS priority_level;

CREATE TYPE priority_level AS ENUM ('Low', 'Medium', 'High');

CREATE TABLE tasks (
    id          SERIAL PRIMARY KEY,
    title       TEXT           NOT NULL,
    description TEXT           NOT NULL DEFAULT '',
    course      TEXT           NOT NULL DEFAULT '',
    priority    priority_level NOT NULL DEFAULT 'Medium',
    completed   BOOLEAN        NOT NULL DEFAULT FALSE,
    due_date    DATE
);

-- Seed data
INSERT INTO tasks (title, description, course, priority, completed, due_date) VALUES
    ('Finish Deadlinez app',     'Build and deploy the full-stack PHP project',  'Programming For The Internet', 'High',   false, '2026-06-01'),
    ('Read chapter 5',           'SQL joins and indexes',                         'Databases',                    'Medium', false, '2026-06-05'),
    ('Group project meeting',    'Agree on the UI and split tasks',               'Software Engineering',         'Low',    false, '2026-05-30'),
    ('Write lab report',         'Cover the results from experiment 3',           'Physics',                      'High',   false, '2026-06-03'),
    ('Review lecture notes',     'Prepare for the midterm',                       'Algorithms',                   'Medium', true,  '2026-05-20'),
    ('Submit math assignment',   'Chapters 4 and 5 exercises',                    'Mathematics',                  'High',   true,  '2026-05-15');
