<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$user = currentUser();
$flash = pullFlash();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php"><?= htmlspecialchars(APP_NAME) ?></a>
        <div class="collapse navbar-collapse show">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <?php if ($user): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="admin.php">Painel Admin</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><span class="nav-link">Olá, <?= htmlspecialchars($user['name']) ?></span></li>
                    <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="logout.php">Sair</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-primary btn-sm" href="register.php">Cadastro</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container pb-5">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>
