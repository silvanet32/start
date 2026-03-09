<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

session_destroy();
session_start();
setFlash('success', 'Você saiu da sua conta.');
redirect('/login.php');
