<?php
// One-time diagnostic: prints the absolute filesystem paths needed for
// .htaccess's AuthUserFile. Upload next to submit.php, open once, copy
// the printed path, then DELETE THIS FILE.
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

echo "Ten katalog (rsod-quiz):\n";
echo __DIR__ . "\n\n";

$candidate = __DIR__ . '/../.htpasswd/passwd';
echo "Sciezka do .htpasswd/passwd jeden poziom wyzej niz public_html:\n";
echo (realpath($candidate) ?: '(nie znaleziono pod: ' . $candidate . ')') . "\n\n";

$candidate2 = dirname(__DIR__, 2) . '/.htpasswd/passwd';
echo "Sciezka do .htpasswd/passwd dwa poziomy wyzej (jesli public_html jest posrednim katalogiem):\n";
echo (realpath($candidate2) ?: '(nie znaleziono pod: ' . $candidate2 . ')') . "\n";
