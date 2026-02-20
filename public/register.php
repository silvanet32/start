<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

requireGuest();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        flash('error', 'Preencha todos os campos.');
        redirect('register.php');
    }

    $exists = database()->prepare('SELECT id FROM users WHERE email = :email');
    $exists->execute(['email' => $email]);

    if ($exists->fetch()) {
        flash('error', 'Este e-mail já está em uso.');
        redirect('register.php');
    }

    $insert = database()->prepare(
        'INSERT INTO users (name, email, password, role, created_at)
         VALUES (:name, :email, :password, :role, :created_at)'
    );

    $insert->execute([
        'name' => $name,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'user',
        'created_at' => date('c'),
    ]);

    flash('success', 'Cadastro realizado! Faça login para continuar.');
    redirect('login.php');
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Criar conta</h1>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Cadastrar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
