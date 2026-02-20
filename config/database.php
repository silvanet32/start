<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function database(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!is_dir(dirname(DB_PATH))) {
        mkdir(dirname(DB_PATH), 0777, true);
    }

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    migrate($pdo);

    return $pdo;
}

function migrate(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT "user",
            created_at TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS mini_series (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            genre TEXT NOT NULL,
            episodes INTEGER NOT NULL,
            description TEXT NOT NULL,
            created_by INTEGER NOT NULL,
            created_at TEXT NOT NULL,
            FOREIGN KEY(created_by) REFERENCES users(id)
        )'
    );

    seedDefaultAdmin($pdo);
}

function seedDefaultAdmin(PDO $pdo): void
{
    $statement = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role = :role');
    $statement->execute(['role' => 'admin']);

    if ((int) $statement->fetchColumn() > 0) {
        return;
    }

    $insert = $pdo->prepare(
        'INSERT INTO users (name, email, password, role, created_at)
         VALUES (:name, :email, :password, :role, :created_at)'
    );

    $insert->execute([
        'name' => 'Administrador',
        'email' => 'admin@miniseries.local',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'admin',
        'created_at' => date('c'),
    ]);
}
