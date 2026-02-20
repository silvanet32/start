<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function ensureSqliteSchema(): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }

    $mode = getDbMode();
    if ($mode === 'sqlite') {
        $pdo = getPDO();
        if (!$pdo instanceof PDO) {
            return;
        }

        $pdo->exec('PRAGMA foreign_keys = ON');
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

    if ($mode === 'sqlite3') {
        $sqlite = getSQLite3();
        if (!$sqlite instanceof SQLite3) {
            return;
        }

        $sqlite->exec('PRAGMA foreign_keys = ON');
        $sqlite->exec('CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT "user",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        $sqlite->exec('CREATE TABLE IF NOT EXISTS series (
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

    if ($mode === 'file') {
        $file = getFileDatabasePath();
        if (!is_file($file)) {
            file_put_contents($file, json_encode([
                'meta' => ['next_user_id' => 1, 'next_series_id' => 1],
                'users' => [],
                'series' => [],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    $initialized = true;
}

function fileReadDb(): array
{
    ensureSqliteSchema();
    $data = json_decode((string) file_get_contents(getFileDatabasePath()), true);
    return is_array($data) ? $data : ['meta' => ['next_user_id' => 1, 'next_series_id' => 1], 'users' => [], 'series' => []];
}

function fileWriteDb(array $data): void
{
    file_put_contents(getFileDatabasePath(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function findUserById(int $id): ?array
{
    ensureSqliteSchema();
    $mode = getDbMode();

    if ($mode === 'mysql' || $mode === 'sqlite') {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT id, name, email, role, password_hash FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    if ($mode === 'sqlite3') {
        $sqlite = getSQLite3();
        $stmt = $sqlite->prepare('SELECT id, name, email, role, password_hash FROM users WHERE id = :id LIMIT 1');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result ? $result->fetchArray(SQLITE3_ASSOC) : false;
        return $row ?: null;
    }

    $db = fileReadDb();
    foreach ($db['users'] as $user) {
        if ((int) $user['id'] === $id) {
            return $user;
        }
    }

    return null;
}

function findUserByEmail(string $email): ?array
{
    ensureSqliteSchema();
    $mode = getDbMode();

    if ($mode === 'mysql' || $mode === 'sqlite') {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT id, name, email, role, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    if ($mode === 'sqlite3') {
        $sqlite = getSQLite3();
        $stmt = $sqlite->prepare('SELECT id, name, email, role, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result ? $result->fetchArray(SQLITE3_ASSOC) : false;
        return $row ?: null;
    }

    $db = fileReadDb();
    foreach ($db['users'] as $user) {
        if (strtolower((string) $user['email']) === strtolower($email)) {
            return $user;
        }
    }

    return null;
}

function countUsers(): int
{
    ensureSqliteSchema();
    $mode = getDbMode();

    if ($mode === 'mysql' || $mode === 'sqlite') {
        return (int) getPDO()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    if ($mode === 'sqlite3') {
        $result = getSQLite3()->query('SELECT COUNT(*) AS c FROM users');
        $row = $result ? $result->fetchArray(SQLITE3_ASSOC) : false;
        return (int) ($row['c'] ?? 0);
    }

    return count(fileReadDb()['users']);
}

function createUser(string $name, string $email, string $passwordHash, string $role): int
{
    ensureSqliteSchema();
    $mode = getDbMode();

    if ($mode === 'mysql' || $mode === 'sqlite') {
        $pdo = getPDO();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
        $stmt->execute(['name' => $name, 'email' => $email, 'password_hash' => $passwordHash, 'role' => $role]);
        return (int) $pdo->lastInsertId();
    }

    if ($mode === 'sqlite3') {
        $sqlite = getSQLite3();
        $stmt = $sqlite->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':password_hash', $passwordHash, SQLITE3_TEXT);
        $stmt->bindValue(':role', $role, SQLITE3_TEXT);
        $stmt->execute();
        return (int) $sqlite->lastInsertRowID();
    }

    $db = fileReadDb();
    $id = (int) $db['meta']['next_user_id'];
    $db['meta']['next_user_id'] = $id + 1;
    $db['users'][] = [
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'password_hash' => $passwordHash,
        'role' => $role,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    fileWriteDb($db);
    return $id;
}

function getSeries(string $search = ''): array
{
    ensureSqliteSchema();
    $mode = getDbMode();

    if ($mode === 'mysql' || $mode === 'sqlite') {
        $pdo = getPDO();
        if ($search !== '') {
            $stmt = $pdo->prepare('SELECT id, title, synopsis, release_year, seasons, genre, cover_url FROM series WHERE title LIKE :search OR genre LIKE :search ORDER BY created_at DESC');
            $stmt->execute(['search' => "%{$search}%"]);
            return $stmt->fetchAll();
        }

        return $pdo->query('SELECT id, title, synopsis, release_year, seasons, genre, cover_url FROM series ORDER BY created_at DESC')->fetchAll();
    }

    if ($mode === 'sqlite3') {
        $sqlite = getSQLite3();
        if ($search !== '') {
            $stmt = $sqlite->prepare('SELECT id, title, synopsis, release_year, seasons, genre, cover_url FROM series WHERE title LIKE :search OR genre LIKE :search ORDER BY created_at DESC');
            $stmt->bindValue(':search', "%{$search}%", SQLITE3_TEXT);
            $result = $stmt->execute();
        } else {
            $result = $sqlite->query('SELECT id, title, synopsis, release_year, seasons, genre, cover_url FROM series ORDER BY created_at DESC');
        }

        $rows = [];
        if ($result) {
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    $rows = fileReadDb()['series'];
    if ($search !== '') {
        $needle = mb_strtolower($search);
        $rows = array_values(array_filter($rows, static function (array $item) use ($needle): bool {
            return str_contains(mb_strtolower((string) $item['title']), $needle) || str_contains(mb_strtolower((string) $item['genre']), $needle);
        }));
    }

    usort($rows, static fn (array $a, array $b): int => strcmp((string) $b['created_at'], (string) $a['created_at']));
    return $rows;
}

function createSeries(array $payload, int $createdBy): void
{
    ensureSqliteSchema();
    $mode = getDbMode();

    if ($mode === 'mysql' || $mode === 'sqlite') {
        $pdo = getPDO();
        $stmt = $pdo->prepare('INSERT INTO series (title, synopsis, release_year, seasons, genre, cover_url, created_by) VALUES (:title, :synopsis, :release_year, :seasons, :genre, :cover_url, :created_by)');
        $stmt->execute([
            'title' => $payload['title'],
            'synopsis' => $payload['synopsis'],
            'release_year' => $payload['release_year'],
            'seasons' => $payload['seasons'],
            'genre' => $payload['genre'],
            'cover_url' => $payload['cover_url'],
            'created_by' => $createdBy,
        ]);
        return;
    }

    if ($mode === 'sqlite3') {
        $sqlite = getSQLite3();
        $stmt = $sqlite->prepare('INSERT INTO series (title, synopsis, release_year, seasons, genre, cover_url, created_by) VALUES (:title, :synopsis, :release_year, :seasons, :genre, :cover_url, :created_by)');
        $stmt->bindValue(':title', $payload['title'], SQLITE3_TEXT);
        $stmt->bindValue(':synopsis', $payload['synopsis'], SQLITE3_TEXT);
        $stmt->bindValue(':release_year', (int) $payload['release_year'], SQLITE3_INTEGER);
        $stmt->bindValue(':seasons', (int) $payload['seasons'], SQLITE3_INTEGER);
        $stmt->bindValue(':genre', $payload['genre'], SQLITE3_TEXT);
        $stmt->bindValue(':cover_url', $payload['cover_url'], SQLITE3_TEXT);
        $stmt->bindValue(':created_by', $createdBy, SQLITE3_INTEGER);
        $stmt->execute();
        return;
    }

    $db = fileReadDb();
    $id = (int) $db['meta']['next_series_id'];
    $db['meta']['next_series_id'] = $id + 1;
    $db['series'][] = [
        'id' => $id,
        'title' => $payload['title'],
        'synopsis' => $payload['synopsis'],
        'release_year' => (int) $payload['release_year'],
        'seasons' => (int) $payload['seasons'],
        'genre' => $payload['genre'],
        'cover_url' => $payload['cover_url'],
        'created_by' => $createdBy,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    fileWriteDb($db);
}

function currentUser(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    static $cachedUser = null;
    if ($cachedUser !== null) {
        return $cachedUser;
    }

    $user = findUserById((int) $_SESSION['user_id']);
    if (!$user) {
        unset($_SESSION['user_id']);
        return null;
    }

    $cachedUser = $user;
    return $cachedUser;
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

function isAdmin(): bool
{
    $user = currentUser();
    return $user !== null && $user['role'] === 'admin';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('error', 'Você precisa entrar para acessar essa página.');
        redirect('/login.php');
    }
}

function requireAdmin(): void
{
    if (!isAdmin()) {
        setFlash('error', 'Acesso permitido apenas para administradores.');
        redirect('/index.php');
    }
}
