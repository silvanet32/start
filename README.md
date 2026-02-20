# MiniSeries Hub (PHP)

Site em PHP para catálogo de mini séries, com:

- Login e cadastro de usuários.
- Definição automática do primeiro usuário como administrador.
- Painel admin para adicionar novas mini séries.
- Layout organizado e responsivo.

## Requisitos

- PHP 8+
- MySQL 8+ (ou MariaDB compatível)
- Servidor local (ex.: `php -S`)

## Como configurar

1. Crie o banco e tabelas:

```bash
mysql -u root -p < sql/schema.sql
```

2. Configure variáveis de ambiente do banco (opcional):

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=miniseries_db
export DB_USER=root
export DB_PASS=''
```

3. Rode o projeto:

```bash
php -S 0.0.0.0:8000
```

4. Abra no navegador:

```text
http://localhost:8000/index.php
```

## Estrutura

- `index.php`: listagem e busca de mini séries.
- `register.php` / `login.php`: autenticação.
- `admin.php`: cadastro de séries (somente admin).
- `includes/`: header, footer e funções utilitárias.
- `config/database.php`: conexão PDO.
- `sql/schema.sql`: script do banco.
- `assets/css/style.css`: visual do template.

## Fluxo de permissões

- Usuário não logado: vê catálogo + login/cadastro.
- Usuário comum: vê catálogo autenticado.
- Admin: pode acessar `/admin.php` e adicionar séries.
