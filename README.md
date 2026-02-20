# MiniSeries Hub (PHP)

Site em PHP para catálogo de mini séries, com:

- Login e cadastro de usuários.
- Definição automática do primeiro usuário como administrador.
- Painel admin para adicionar novas mini séries.
- Layout organizado e responsivo.

## Requisitos

- PHP 8+
- Extensão `pdo` habilitada.
- Banco de dados:
  - **MySQL/MariaDB** com extensão `pdo_mysql`, ou
  - **SQLite** com extensão `pdo_sqlite`.

## Configuração rápida (recomendada)

Por padrão, o sistema usa **modo automático**:

- se `pdo_mysql` existir, usa MySQL;
- caso contrário, tenta SQLite.

Se SQLite estiver disponível, o arquivo é criado automaticamente em:

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

## Como configurar SQLite

```bash
export DB_CONNECTION=sqlite
# opcional:
export DB_SQLITE_PATH=/caminho/para/miniseries.sqlite
```

> As tabelas do SQLite são criadas automaticamente na primeira execução.

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

1. Verifique drivers disponíveis:

```bash
php -m
```

2. Habilite no `php.ini` uma destas opções:

- `extension=pdo_mysql` (MySQL)
- `extension=pdo_sqlite` (SQLite)

3. Reinicie o servidor PHP.

## Estrutura

- `index.php`: listagem e busca de mini séries.
- `register.php` / `login.php`: autenticação.
- `admin.php`: cadastro de séries (somente admin).
- `includes/`: header, footer e funções utilitárias.
- `config/database.php`: conexão PDO (MySQL/SQLite com fallback).
- `sql/schema.sql`: script do MySQL.
- `assets/css/style.css`: visual do template.

## Fluxo de permissões

- Usuário não logado: vê catálogo + login/cadastro.
- Usuário comum: vê catálogo autenticado.
- Admin: pode acessar `/admin.php` e adicionar séries.
