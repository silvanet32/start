<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        setFlash('error', 'Preencha e-mail e senha.');
        redirect('/login.php');
    }

    $user = findUserByEmail($email);

    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        setFlash('error', 'Credenciais inválidas.');
        redirect('/login.php');
    }

    $_SESSION['user_id'] = (int) $user['id'];
    setFlash('success', 'Login realizado com sucesso!');
    redirect('/index.php');
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-card">
    <h1>Entrar</h1>
    <form method="POST" class="auth-form">
        <label>E-mail</label>
        <input type="email" name="email" required>

        <label>Senha</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
