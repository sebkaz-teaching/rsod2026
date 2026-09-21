<?php
// One-time cleanup: wipes all rows from `submissions` (test data). Upload
// next to submit.php, open once WITHOUT ?confirm=yes to see what would be
// deleted, then again WITH ?confirm=yes to actually delete. DELETE THIS
// FILE afterward — it's a standing "erase everything" button otherwise.
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

require __DIR__ . '/../db.php';

$db = rsod_db();
$rows = $db->query('SELECT id, course, lecture, email, score, max_score, submitted_at FROM submissions ORDER BY submitted_at')->fetchAll(PDO::FETCH_ASSOC);

echo "Wierszy w bazie: " . count($rows) . "\n\n";
foreach ($rows as $r) {
    echo "#{$r['id']}  {$r['submitted_at']}  {$r['course']}/{$r['lecture']}  {$r['email']}  {$r['score']}/{$r['max_score']}\n";
}

if (empty($rows)) {
    echo "\nNic do skasowania.\n";
    exit;
}

if (($_GET['confirm'] ?? '') !== 'yes') {
    echo "\nTo NIE usunęło niczego. Żeby skasować WSZYSTKIE powyższe wiersze, otwórz ten sam adres z ?confirm=yes na końcu.\n";
    exit;
}

$deleted = $db->exec('DELETE FROM submissions');
echo "\nUsunieto {$deleted} wierszy.\n";
