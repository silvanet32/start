<?php

declare(strict_types=1);

function getDbMode(): string
{
    $connection = strtolower((string) (getenv('DB_CONNECTION') ?: 'auto'));

    if ($connection === 'file') {
        return 'file';
    }

    if (class_exists('PDO')) {
        $drivers = PDO::getAvailableDrivers();

        if ($connection === 'mysql' && in_array('mysql', $drivers, true)) {
            return 'mysql';
        }

        if ($connection === 'sqlite' && in_array('sqlite', $drivers, true)) {
            return 'sqlite';
        }

        if ($connection === 'auto') {
            if (in_array('mysql', $drivers, true)) {
                return 'mysql';
            }

            if (in_array('sqlite', $drivers, true)) {
                return 'sqlite';
            }
        }
    }

    if (($connection === 'sqlite3' || $connection === 'auto') && class_exists('SQLite3')) {
        return 'sqlite3';
    }

    return 'file';
}

function getPDO(): ?PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $mode = getDbMode();
    if ($mode !== 'mysql' && $mode !== 'sqlite') {
        return null;
    }

    if ($mode === 'mysql') {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $dbname = getenv('DB_NAME') ?: 'miniseries_db';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return $pdo;
    }

    $path = getenv('DB_SQLITE_PATH') ?: __DIR__ . '/../storage/miniseries.sqlite';
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function getSQLite3(): ?SQLite3
{
    static $sqlite = null;

    if ($sqlite instanceof SQLite3) {
        return $sqlite;
    }

    if (getDbMode() !== 'sqlite3') {
        return null;
    }

    $path = getenv('DB_SQLITE_PATH') ?: __DIR__ . '/../storage/miniseries.sqlite';
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $sqlite = new SQLite3($path);
    $sqlite->enableExceptions(true);
    return $sqlite;
}

function getFileDatabasePath(): string
{
    $path = getenv('DB_FILE_PATH') ?: __DIR__ . '/../storage/database.json';
    $dir = dirname($path);

    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    return $path;
}
