<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

requireGuest();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        flash('error', 'Informe e-mail e senha.');
        redirect('login.php');
    }

    $statement = database()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        flash('error', 'Credenciais inválidas.');
        redirect('login.php');
    }

    $_SESSION['user_id'] = $user['id'];
    flash('success', 'Login realizado com sucesso.');
    redirect('index.php');
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Entrar</h1>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-dark w-100" type="submit">Acessar</button>
                </form>
                <p class="small text-muted mt-3 mb-0">Admin padrão: admin@miniseries.local / admin123</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
