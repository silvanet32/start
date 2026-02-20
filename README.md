# Mini Séries Hub (PHP)

Template de site em PHP para mini séries com:

- Cadastro e login de usuários.
- Banco de dados SQLite com criação automática de tabelas.
- Usuário administrador padrão.
- Painel administrativo para cadastrar novas mini séries.
- Listagem pública das séries cadastradas.

## Como executar

```bash
php -S localhost:8000
```

Acesse:

- Home: `http://localhost:8000/public/index.php`
- Login: `http://localhost:8000/public/login.php`
- Cadastro: `http://localhost:8000/public/register.php`
- Admin: `http://localhost:8000/public/admin.php`

## Admin padrão

- E-mail: `admin@miniseries.local`
- Senha: `admin123`

> O banco será criado automaticamente em `storage/database.sqlite` no primeiro acesso.
