<?php
declare(strict_types=1);

function rsod_config(): array
{
    $local = __DIR__ . '/config.local.php';
    $path = is_file($local) ? $local : __DIR__ . '/config.php';
    $config = require $path;

    // Register the consolidated PAS test even on deployments predating PAS.
    // Keep other keys/settings and any explicit combined key unchanged.
    if (!isset($config['answer_keys']['pas2026']['test-po-wykladach'])) {
        $combined = [];
        // Values are stable option IDs; the browser shuffles display letters.
        foreach ([1, 1, 2, 2, 3, 1, 1, 2, 2, 3] as $index => $points) {
            $combined['q' . ($index + 1)] = ['correct' => 'B', 'points' => $points];
        }
        $config['answer_keys']['pas2026']['test-po-wykladach'] = $combined;
    }

    return $config;
}

function rsod_db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $cfg = rsod_config();
    $dbPath = $cfg['db_path'];
    $dbDir = dirname($dbPath);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0770, true);
    }

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA journal_mode = WAL;');
    $pdo->exec(file_get_contents(__DIR__ . '/schema.sql'));

    return $pdo;
}
