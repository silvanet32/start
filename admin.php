<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

requireLogin();
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $synopsis = trim($_POST['synopsis'] ?? '');
    $releaseYear = (int) ($_POST['release_year'] ?? 0);
    $seasons = (int) ($_POST['seasons'] ?? 1);
    $genre = trim($_POST['genre'] ?? '');
    $coverUrl = trim($_POST['cover_url'] ?? '');

    if ($title === '' || $synopsis === '' || $releaseYear < 1900 || $seasons < 1 || $genre === '') {
        setFlash('error', 'Preencha os campos obrigatórios corretamente.');
        redirect('/admin.php');
    }

    if ($coverUrl !== '' && !filter_var($coverUrl, FILTER_VALIDATE_URL)) {
        setFlash('error', 'Informe uma URL de capa válida.');
        redirect('/admin.php');
    }

    createSeries([
        'title' => $title,
        'synopsis' => $synopsis,
        'release_year' => $releaseYear,
        'seasons' => $seasons,
        'genre' => $genre,
        'cover_url' => $coverUrl,
    ], (int) currentUser()['id']);

    setFlash('success', 'Mini série adicionada com sucesso!');
    redirect('/admin.php');
}

$series = getSeries();

require_once __DIR__ . '/includes/header.php';
?>

<section class="admin-layout">
    <div class="admin-form-wrap">
        <h1>Painel do administrador</h1>
        <form method="POST" class="auth-form">
            <label>Título</label>
            <input type="text" name="title" required>

            <label>Sinopse</label>
            <textarea name="synopsis" rows="4" required></textarea>

            <label>Ano de lançamento</label>
            <input type="number" name="release_year" min="1900" max="2100" required>

            <label>Temporadas</label>
            <input type="number" name="seasons" min="1" required>

            <label>Gênero</label>
            <input type="text" name="genre" required>

            <label>URL da capa (opcional)</label>
            <input type="url" name="cover_url">

            <button type="submit">Adicionar mini série</button>
        </form>
    </div>

    <div class="admin-list-wrap">
        <h2>Cadastradas recentemente</h2>
        <ul class="admin-list">
            <?php foreach ($series as $item): ?>
                <li>
                    <strong><?= e($item['title']); ?></strong>
                    <span><?= e((string) $item['release_year']); ?> • <?= e($item['genre']); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
