# MiniSeries Hub (PHP)

Site em PHP para catálogo de mini séries, com:

- Login e cadastro de usuários.
- Definição automática do primeiro usuário como administrador.
- Painel admin para adicionar novas mini séries.
- Layout organizado e responsivo.

## Requisitos

- PHP 8+
- O sistema funciona nestes modos:
  - `mysql` (PDO + `pdo_mysql`)
  - `sqlite` (PDO + `pdo_sqlite`)
  - `sqlite3` (extensão `sqlite3`)
  - `file` (JSON local, sem banco e sem extensões de BD)

## Melhor opção para quem não sabe senha do MySQL

Use **modo arquivo** (não pede senha):

```bash
export DB_CONNECTION=file
php -S 0.0.0.0:8000
```

No Windows (CMD):

```bat
set DB_CONNECTION=file
php -S localhost:8000
```

O arquivo será criado em `storage/database.json`.

## Configuração por modo

### MySQL

```bash
export DB_CONNECTION=mysql
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=miniseries_db
export DB_USER=root
export DB_PASS=''
mysql -u root -p < sql/schema.sql
```

### SQLite (PDO)

```bash
export DB_CONNECTION=sqlite
export DB_SQLITE_PATH=/caminho/para/miniseries.sqlite
```

### SQLite3 (sem PDO)

```bash
export DB_CONNECTION=sqlite3
export DB_SQLITE_PATH=/caminho/para/miniseries.sqlite
```

### Arquivo JSON (sem MySQL/SQLite)

```bash
export DB_CONNECTION=file
export DB_FILE_PATH=/caminho/para/database.json
```

## Rodando o projeto

```bash
php -S 0.0.0.0:8000
```

Abra no navegador:

```text
http://localhost:8000/index.php
```
