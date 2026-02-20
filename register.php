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

    $pdo = getPDO();

    $existsStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $existsStmt->execute(['email' => $email]);
    if ($existsStmt->fetch()) {
        setFlash('error', 'Este e-mail já está em uso.');
        redirect('/register.php');
    }

    $countUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $role = $countUsers === 0 ? 'admin' : 'user';

    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $role,
    ]);

    $_SESSION['user_id'] = (int) $pdo->lastInsertId();

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
