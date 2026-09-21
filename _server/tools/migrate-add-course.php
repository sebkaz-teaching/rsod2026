<?php
// One-time migration: adds the `course` column to an existing
// submissions table (created before the backend served more than one
// course) and backfills it. Upload next to submit.php, open once in
// the browser, then DELETE THIS FILE.
//
// Connects to SQLite directly — NOT via db.php's rsod_db(), which also
// runs schema.sql on every connection. schema.sql now assumes `course`
// already exists (it indexes on it), so running it against an
// old, un-migrated database throws before this script gets a chance to
// add the column. Do the ALTER first, on a bare connection.
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

require __DIR__ . '/../db.php'; // only for rsod_config(), not rsod_db()

$backfillCourse = 'rsod2026'; // every row written before this migration was rsod2026

$cfg = rsod_config();
$pdo = new PDO('sqlite:' . $cfg['db_path']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cols = $pdo->query("PRAGMA table_info(submissions)")->fetchAll(PDO::FETCH_ASSOC);
$hasCourse = false;
foreach ($cols as $c) {
    if ($c['name'] === 'course') {
        $hasCourse = true;
    }
}

if ($hasCourse) {
    echo "Kolumna 'course' juz istnieje - nic do zrobienia.\n";
    exit;
}

$pdo->exec("ALTER TABLE submissions ADD COLUMN course TEXT NOT NULL DEFAULT '$backfillCourse'");
$pdo->exec("CREATE INDEX IF NOT EXISTS idx_submissions_course_lecture ON submissions(course, lecture)");

$count = (int)$pdo->query("SELECT COUNT(*) FROM submissions WHERE course = '$backfillCourse'")->fetchColumn();
echo "Gotowe. Dodano kolumne 'course', {$count} istniejacych wierszy oznaczono jako '{$backfillCourse}'.\n";
echo "Teraz mozna bezpiecznie uzywac zwyklych submit.php / results.php (one uzywaja rsod_db() ze schema.sql).\n";
