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
