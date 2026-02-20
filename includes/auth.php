<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $statement = database()->prepare('SELECT id, name, email, role FROM users WHERE id = :id');
    $statement->execute(['id' => (int) $_SESSION['user_id']]);
    $user = $statement->fetch();

    return $user ?: null;
}

function isAdmin(): bool
{
    $user = currentUser();

    return $user !== null && $user['role'] === 'admin';
}

function redirect(string $path): void
{
    header('Location: ' . BASE_URL . ltrim($path, '/'));
    exit;
}

function requireGuest(): void
{
    if (currentUser() !== null) {
        redirect('index.php');
    }
}

function requireAuth(): void
{
    if (currentUser() === null) {
        flash('error', 'Faça login para continuar.');
        redirect('login.php');
    }
}

function requireAdmin(): void
{
    requireAuth();

    if (!isAdmin()) {
        flash('error', 'Apenas administradores podem acessar essa área.');
        redirect('index.php');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function pullFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}
