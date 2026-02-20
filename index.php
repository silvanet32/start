<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$search = trim($_GET['search'] ?? '');
$seriesList = getSeries($search);

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1>Descubra Mini Séries Incríveis</h1>
    <p>Seu catálogo pessoal com login, cadastro e gestão de conteúdo.</p>
</section>

<form method="GET" class="search-form">
    <input type="text" name="search" placeholder="Buscar por título ou gênero" value="<?= e($search); ?>">
    <button type="submit">Buscar</button>
</form>

<section class="cards-grid">
    <?php if (!$seriesList): ?>
        <div class="empty-state">
            <p>Nenhuma mini série cadastrada ainda.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($seriesList as $serie): ?>
        <article class="card">
            <img src="<?= e($serie['cover_url'] ?: 'https://placehold.co/600x400?text=Mini+Serie'); ?>" alt="Capa de <?= e($serie['title']); ?>">
            <div class="card-body">
                <h3><?= e($serie['title']); ?></h3>
                <p><?= e($serie['synopsis']); ?></p>
                <ul>
                    <li><strong>Ano:</strong> <?= e((string) $serie['release_year']); ?></li>
                    <li><strong>Temporadas:</strong> <?= e((string) $serie['seasons']); ?></li>
                    <li><strong>Gênero:</strong> <?= e($serie['genre']); ?></li>
                </ul>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
