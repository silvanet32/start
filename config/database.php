<?php

declare(strict_types=1);

function databaseErrorResponse(string $title, string $details): never
{
    http_response_code(500);

    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeDetails = htmlspecialchars($details, ENT_QUOTES, 'UTF-8');

    echo <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro de configuração do banco</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f3f4f6; color: #111827; }
        .wrap { max-width: 860px; margin: 40px auto; padding: 24px; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; }
        h1 { margin-top: 0; color: #b91c1c; }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 6px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>{$safeTitle}</h1>
        <p>{$safeDetails}</p>
        <p>Consulte o <code>README.md</code> para instruções de instalação de extensões e configuração do banco.</p>
    </div>
</div>
</body>
</html>
HTML;

    exit;
}

function initializeSqliteDatabase(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT "user",
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS series (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        synopsis TEXT NOT NULL,
        release_year INTEGER NOT NULL,
        seasons INTEGER NOT NULL DEFAULT 1,
        genre TEXT NOT NULL,
        cover_url TEXT NULL,
        created_by INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    )');
}

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!class_exists('PDO')) {
        databaseErrorResponse(
            'Extensão PDO não está habilitada.',
            'Ative a extensão PDO no PHP e reinicie o servidor.'
        );
    }

    $availableDrivers = PDO::getAvailableDrivers();
    $connection = strtolower((string) (getenv('DB_CONNECTION') ?: 'auto'));

    if ($connection === 'auto') {
        $connection = in_array('mysql', $availableDrivers, true) ? 'mysql' : 'sqlite';
    }

    try {
        if ($connection === 'mysql') {
            if (!in_array('mysql', $availableDrivers, true)) {
                databaseErrorResponse(
                    'Driver do MySQL não encontrado no PHP.',
                    'Ative a extensão pdo_mysql no seu php.ini ou use DB_CONNECTION=sqlite se o driver sqlite estiver instalado.'
                );
            }

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

        if ($connection === 'sqlite') {
            if (!in_array('sqlite', $availableDrivers, true)) {
                databaseErrorResponse(
                    'Driver do SQLite não encontrado no PHP.',
                    'Ative a extensão pdo_sqlite no seu php.ini ou configure DB_CONNECTION=mysql com pdo_mysql habilitado.'
                );
            }

            $sqlitePath = getenv('DB_SQLITE_PATH') ?: __DIR__ . '/../storage/miniseries.sqlite';
            $sqliteDir = dirname($sqlitePath);
            if (!is_dir($sqliteDir)) {
                mkdir($sqliteDir, 0775, true);
            }

            $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $pdo->exec('PRAGMA foreign_keys = ON');
            initializeSqliteDatabase($pdo);

            return $pdo;
        }
    } catch (PDOException $exception) {
        databaseErrorResponse(
            'Não foi possível conectar ao banco de dados.',
            $exception->getMessage()
        );
    }

    databaseErrorResponse(
        'Valor inválido em DB_CONNECTION.',
        'Use DB_CONNECTION=mysql, DB_CONNECTION=sqlite ou remova a variável para modo automático.'
    );
}
