<?php
// One-time diagnostic: shows the exact parse/fatal error from
// config.local.php, which is otherwise hidden (display_errors off in
// production). Upload next to submit.php, open once, then DELETE.
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

$path = __DIR__ . '/../config.local.php';
echo "Sprawdzam: $path\n\n";

if (!is_file($path)) {
    echo "Plik nie istnieje.\n";
    exit;
}

try {
    $cfg = require $path;
    echo "OK - plik sie wczytal.\n\n";
    echo "Klucze najwyzszego poziomu: " . implode(', ', array_keys($cfg)) . "\n";
    if (isset($cfg['answer_keys'])) {
        echo "Przedmioty w answer_keys: " . implode(', ', array_keys($cfg['answer_keys'])) . "\n";
        foreach ($cfg['answer_keys'] as $course => $lectures) {
            echo "  $course: " . implode(', ', array_keys($lectures)) . "\n";
        }
    }
} catch (Throwable $e) {
    echo "BLAD: " . get_class($e) . "\n";
    echo $e->getMessage() . "\n";
    echo "Linia: " . $e->getLine() . "\n";
}
