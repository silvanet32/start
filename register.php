<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect('/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        setFlash('error', 'Preencha todos os campos.');
        redirect('/register.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Informe um e-mail válido.');
        redirect('/register.php');
    }

    if (strlen($password) < 6) {
        setFlash('error', 'A senha deve ter no mínimo 6 caracteres.');
        redirect('/register.php');
    }

    if (findUserByEmail($email)) {
        setFlash('error', 'Este e-mail já está em uso.');
        redirect('/register.php');
    }

    $role = countUsers() === 0 ? 'admin' : 'user';
    $userId = createUser($name, $email, password_hash($password, PASSWORD_DEFAULT), $role);
    $_SESSION['user_id'] = $userId;

    setFlash('success', 'Conta criada com sucesso!');
    redirect('/index.php');
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-card">
    <h1>Criar conta</h1>
    <form method="POST" class="auth-form">
        <label>Nome</label>
        <input type="text" name="name" required>

        <label>E-mail</label>
        <input type="email" name="email" required>

        <label>Senha</label>
        <input type="password" name="password" required minlength="6">

        <button type="submit">Cadastrar</button>
    </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
