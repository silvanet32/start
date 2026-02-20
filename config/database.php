<?php

declare(strict_types=1);

final class DatabaseStatement
{
    private PDOStatement|SQLite3Stmt $statement;
    private SQLite3Result|false|null $sqliteResult = null;

    public function __construct(PDOStatement|SQLite3Stmt $statement)
    {
        $this->statement = $statement;
    }

    public function execute(array $params = []): bool
    {
        if ($this->statement instanceof PDOStatement) {
            return $this->statement->execute($params);
        }

        foreach ($params as $key => $value) {
            $paramName = is_string($key) ? ':' . ltrim($key, ':') : (int) $key + 1;
            $type = SQLITE3_TEXT;

            if (is_int($value)) {
                $type = SQLITE3_INTEGER;
            } elseif (is_float($value)) {
                $type = SQLITE3_FLOAT;
            } elseif ($value === null) {
                $type = SQLITE3_NULL;
            }

            $this->statement->bindValue($paramName, $value, $type);
        }

        $result = $this->statement->execute();
        if ($result instanceof SQLite3Result || $result === false) {
            $this->sqliteResult = $result;
        }

        return $result !== false;
    }

    public function fetch(): array|false
    {
        if ($this->statement instanceof PDOStatement) {
            return $this->statement->fetch(PDO::FETCH_ASSOC);
        }

        if (!$this->sqliteResult instanceof SQLite3Result) {
            return false;
        }

        $row = $this->sqliteResult->fetchArray(SQLITE3_ASSOC);
        return $row === false ? false : $row;
    }

    public function fetchAll(): array
    {
        if ($this->statement instanceof PDOStatement) {
            $rows = $this->statement->fetchAll(PDO::FETCH_ASSOC);
            return is_array($rows) ? $rows : [];
        }

        if (!$this->sqliteResult instanceof SQLite3Result) {
            return [];
        }

        $rows = [];
        while ($row = $this->sqliteResult->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function fetchColumn(int $column = 0): mixed
    {
        if ($this->statement instanceof PDOStatement) {
            return $this->statement->fetchColumn($column);
        }

        $row = $this->fetch();
        if ($row === false) {
            return false;
        }

        $values = array_values($row);
        return $values[$column] ?? false;
    }
}

final class DatabaseConnection
{
    private PDO|SQLite3 $connection;

    public function __construct(PDO|SQLite3 $connection)
    {
        $this->connection = $connection;
    }

    public function prepare(string $query): DatabaseStatement
    {
        if ($this->connection instanceof PDO) {
            $statement = $this->connection->prepare($query);
            if ($statement === false) {
                throw new RuntimeException('Falha ao preparar consulta SQL.');
            }

            return new DatabaseStatement($statement);
        }

        $statement = $this->connection->prepare($query);
        if (!$statement instanceof SQLite3Stmt) {
            throw new RuntimeException('Falha ao preparar consulta SQLite3.');
        }

        return new DatabaseStatement($statement);
    }

    public function query(string $query): DatabaseStatement
    {
        if ($this->connection instanceof PDO) {
            $statement = $this->connection->query($query);
            if ($statement === false) {
                throw new RuntimeException('Falha ao executar consulta SQL.');
            }

            return new DatabaseStatement($statement);
        }

        $stmt = $this->connection->prepare($query);
        if (!$stmt instanceof SQLite3Stmt) {
            throw new RuntimeException('Falha ao preparar consulta SQLite3.');
        }

        $wrapped = new DatabaseStatement($stmt);
        $wrapped->execute();
        return $wrapped;
    }

    public function exec(string $query): int
    {
        if ($this->connection instanceof PDO) {
            $result = $this->connection->exec($query);
            return $result === false ? 0 : $result;
        }

        $ok = $this->connection->exec($query);
        return $ok ? $this->connection->changes() : 0;
    }

    public function lastInsertId(): string
    {
        if ($this->connection instanceof PDO) {
            return (string) $this->connection->lastInsertId();
        }

        return (string) $this->connection->lastInsertRowID();
    }
}

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

function initializeSqliteDatabase(DatabaseConnection $db): void
{
    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT "user",
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS series (
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

function getPDO(): DatabaseConnection
{
    static $db = null;

    if ($db instanceof DatabaseConnection) {
        return $db;
    }

    $availableDrivers = class_exists('PDO') ? PDO::getAvailableDrivers() : [];
    $connection = strtolower((string) (getenv('DB_CONNECTION') ?: 'auto'));

    if ($connection === 'auto') {
        if (in_array('mysql', $availableDrivers, true)) {
            $connection = 'mysql';
        } elseif (in_array('sqlite', $availableDrivers, true)) {
            $connection = 'sqlite';
        } elseif (class_exists('SQLite3')) {
            $connection = 'sqlite3';
        }
    }

    try {
        if ($connection === 'mysql') {
            if (!in_array('mysql', $availableDrivers, true)) {
                databaseErrorResponse(
                    'Driver do MySQL não encontrado no PHP.',
                    'Ative a extensão pdo_mysql no seu php.ini ou use DB_CONNECTION=sqlite/sqlite3.'
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

            $db = new DatabaseConnection($pdo);
            return $db;
        }

        if ($connection === 'sqlite') {
            if (!in_array('sqlite', $availableDrivers, true)) {
                databaseErrorResponse(
                    'Driver PDO do SQLite não encontrado no PHP.',
                    'Use DB_CONNECTION=sqlite3 (fallback nativo) ou ative pdo_sqlite no php.ini.'
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
            $db = new DatabaseConnection($pdo);
            $db->exec('PRAGMA foreign_keys = ON');
            initializeSqliteDatabase($db);

            return $db;
        }

        if ($connection === 'sqlite3') {
            if (!class_exists('SQLite3')) {
                databaseErrorResponse(
                    'Extensão SQLite3 não encontrada no PHP.',
                    'Ative sqlite3 no php.ini, ou use MySQL com pdo_mysql.'
                );
            }

            $sqlitePath = getenv('DB_SQLITE_PATH') ?: __DIR__ . '/../storage/miniseries.sqlite';
            $sqliteDir = dirname($sqlitePath);
            if (!is_dir($sqliteDir)) {
                mkdir($sqliteDir, 0775, true);
            }

            $sqlite = new SQLite3($sqlitePath);
            $sqlite->enableExceptions(true);

            $db = new DatabaseConnection($sqlite);
            $db->exec('PRAGMA foreign_keys = ON');
            initializeSqliteDatabase($db);

            return $db;
        }
    } catch (Throwable $exception) {
        databaseErrorResponse(
            'Não foi possível conectar ao banco de dados.',
            $exception->getMessage()
        );
    }

    databaseErrorResponse(
        'Nenhum driver de banco disponível.',
        'Configure DB_CONNECTION=mysql, sqlite ou sqlite3 e habilite as extensões necessárias no PHP.'
    );
}
