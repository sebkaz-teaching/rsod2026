<?php
declare(strict_types=1);

function rsod_config(): array
{
    $local = __DIR__ . '/config.local.php';
    $path = is_file($local) ? $local : __DIR__ . '/config.php';
    return require $path;
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
