-- Shared quiz backend for all courses (rsod2026, prba2026, ...). A fresh
-- install gets `course` from the start; an existing database created
-- before this column existed needs tools/migrate-add-course.php run
-- once (see _server/README.md).
CREATE TABLE IF NOT EXISTS submissions (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    course      TEXT    NOT NULL,
    lecture     TEXT    NOT NULL,
    email       TEXT    NOT NULL,
    score       INTEGER NOT NULL,
    max_score   INTEGER NOT NULL,
    answers     TEXT    NOT NULL,   -- JSON: {"q1":"B", "q2":"C", ...}
    submitted_at TEXT   NOT NULL,   -- ISO 8601, set server-side
    ip          TEXT
);

CREATE INDEX IF NOT EXISTS idx_submissions_course_lecture ON submissions(course, lecture);
CREATE INDEX IF NOT EXISTS idx_submissions_email ON submissions(email);

-- Open-ended coursework uses the same database without changing quiz scores.
CREATE TABLE IF NOT EXISTS assignment_submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    request_id TEXT NOT NULL UNIQUE,
    course TEXT NOT NULL,
    lecture TEXT NOT NULL,
    email TEXT NOT NULL,
    partner_email TEXT NOT NULL,
    authors TEXT NOT NULL,
    answers TEXT NOT NULL,
    submitted_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_assignments_course_lecture
    ON assignment_submissions(course, lecture);
