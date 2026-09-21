CREATE TABLE IF NOT EXISTS submissions (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    lecture     TEXT    NOT NULL,
    email       TEXT    NOT NULL,
    score       INTEGER NOT NULL,
    max_score   INTEGER NOT NULL,
    answers     TEXT    NOT NULL,   -- JSON: {"q1":"B", "q2":"C", ...}
    submitted_at TEXT   NOT NULL,   -- ISO 8601, set server-side
    ip          TEXT
);

CREATE INDEX IF NOT EXISTS idx_submissions_lecture ON submissions(lecture);
CREATE INDEX IF NOT EXISTS idx_submissions_email ON submissions(email);
