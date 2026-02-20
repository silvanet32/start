# MiniSeries Hub (PHP)

Site em PHP para catálogo de mini séries, com:

- Login e cadastro de usuários.
- Definição automática do primeiro usuário como administrador.
- Painel admin para adicionar novas mini séries.
- Layout organizado e responsivo.

## Requisitos

- PHP 8+
- Banco de dados:
  - **MySQL/MariaDB** com extensão `pdo_mysql`, ou
  - **SQLite** com extensão `pdo_sqlite`, ou
  - **SQLite3 nativo** com extensão `sqlite3` (fallback sem PDO).

## Configuração rápida (recomendada)

No modo automático (`DB_CONNECTION` ausente), a prioridade é:

1. `mysql` (se `pdo_mysql` existir)
2. `sqlite` (se `pdo_sqlite` existir)
3. `sqlite3` (se extensão `sqlite3` existir)

Arquivo padrão SQLite/SQLite3:

- `storage/miniseries.sqlite`

## Como configurar MySQL

1. Crie o banco e tabelas:

```bash
mysql -u root -p < sql/schema.sql
```

2. Configure variáveis de ambiente:

```bash
export DB_CONNECTION=mysql
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=miniseries_db
export DB_USER=root
export DB_PASS=''
```

## Como configurar SQLite (PDO)

```bash
export DB_CONNECTION=sqlite
# opcional:
export DB_SQLITE_PATH=/caminho/para/miniseries.sqlite
```

## Como configurar SQLite3 (sem PDO)

```bash
export DB_CONNECTION=sqlite3
# opcional:
export DB_SQLITE_PATH=/caminho/para/miniseries.sqlite
```

> As tabelas SQLite/SQLite3 são criadas automaticamente na primeira execução.

## Rodando o projeto

```bash
php -S 0.0.0.0:8000
```

Abra no navegador:

```text
http://localhost:8000/index.php
```

## Erro "could not find driver"

Se aparecer esse erro:

1. Verifique extensões disponíveis:

```bash
php -m
```

2. Escolha uma opção:

- Habilitar `pdo_mysql` e usar MySQL.
- Habilitar `pdo_sqlite` e usar SQLite via PDO.
- Habilitar `sqlite3` e usar `DB_CONNECTION=sqlite3`.

3. Reinicie o servidor PHP.

## Estrutura

- `index.php`: listagem e busca de mini séries.
- `register.php` / `login.php`: autenticação.
- `admin.php`: cadastro de séries (somente admin).
- `includes/`: header, footer e funções utilitárias.
- `config/database.php`: conexão de banco com fallback MySQL/SQLite/SQLite3.
- `sql/schema.sql`: script do MySQL.
- `assets/css/style.css`: visual do template.

## Fluxo de permissões

- Usuário não logado: vê catálogo + login/cadastro.
- Usuário comum: vê catálogo autenticado.
- Admin: pode acessar `/admin.php` e adicionar séries.
