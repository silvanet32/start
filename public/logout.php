<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$_SESSION = [];
session_destroy();

session_start();
flash('success', 'Você saiu da sua conta.');
redirect('login.php');
