<?php
// One-time migration: adds the `course` column to an existing
// submissions table (created before the backend served more than one
// course) and backfills it. Upload next to submit.php, open once in
// the browser, then DELETE THIS FILE.
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

require __DIR__ . '/../db.php';

$backfillCourse = 'rsod2026'; // every row written before this migration was rsod2026

$db = rsod_db();
$cols = $db->query("PRAGMA table_info(submissions)")->fetchAll(PDO::FETCH_ASSOC);
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

$db->exec("ALTER TABLE submissions ADD COLUMN course TEXT NOT NULL DEFAULT '$backfillCourse'");
$db->exec("CREATE INDEX IF NOT EXISTS idx_submissions_course_lecture ON submissions(course, lecture)");

$count = (int)$db->query("SELECT COUNT(*) FROM submissions WHERE course = '$backfillCourse'")->fetchColumn();
echo "Gotowe. Dodano kolumne 'course', {$count} istniejacych wierszy oznaczono jako '{$backfillCourse}'.\n";
