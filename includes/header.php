<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$user = currentUser();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniSeries Hub</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="topbar">
    <div class="container nav-content">
        <a href="/index.php" class="logo">MiniSeries Hub</a>
        <nav>
            <ul class="nav-links">
                <li><a href="/index.php">Séries</a></li>
                <?php if ($user): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <li><a href="/admin.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="/logout.php">Sair (<?= e($user['name']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="/login.php">Login</a></li>
                    <li><a href="/register.php">Cadastro</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main class="container main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']); ?>">
            <?= e($flash['message']); ?>
        </div>
    <?php endif; ?>
