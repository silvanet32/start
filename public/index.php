<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$statement = database()->query(
    'SELECT mini_series.id, title, genre, episodes, description, mini_series.created_at, users.name AS creator
     FROM mini_series
     INNER JOIN users ON users.id = mini_series.created_by
     ORDER BY mini_series.created_at DESC'
);
$seriesList = $statement->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="p-4 p-md-5 mb-4 text-bg-dark rounded-3">
    <h1 class="display-6">Catálogo de Mini Séries</h1>
    <p class="lead mb-0">Explore lançamentos e novidades cadastradas pelo time de administradores.</p>
</div>

<div class="row g-4">
    <?php if (!$seriesList): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="mb-0">Ainda não há mini séries cadastradas. Faça login como administrador para adicionar.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($seriesList as $serie): ?>
        <div class="col-md-6 col-lg-4">
            <article class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5"><?= htmlspecialchars($serie['title']) ?></h2>
                    <p class="text-muted mb-2"><strong>Gênero:</strong> <?= htmlspecialchars($serie['genre']) ?></p>
                    <p class="text-muted mb-2"><strong>Episódios:</strong> <?= (int) $serie['episodes'] ?></p>
                    <p><?= nl2br(htmlspecialchars($serie['description'])) ?></p>
                </div>
                <div class="card-footer bg-white border-0 small text-muted">
                    Cadastrado por <?= htmlspecialchars($serie['creator']) ?> em <?= date('d/m/Y H:i', strtotime($serie['created_at'])) ?>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
