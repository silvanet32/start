<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $episodes = (int) ($_POST['episodes'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $genre === '' || $episodes <= 0 || $description === '') {
        flash('error', 'Preencha todos os campos corretamente.');
        redirect('admin.php');
    }

    $insert = database()->prepare(
        'INSERT INTO mini_series (title, genre, episodes, description, created_by, created_at)
         VALUES (:title, :genre, :episodes, :description, :created_by, :created_at)'
    );

    $insert->execute([
        'title' => $title,
        'genre' => $genre,
        'episodes' => $episodes,
        'description' => $description,
        'created_by' => currentUser()['id'],
        'created_at' => date('c'),
    ]);

    flash('success', 'Mini série adicionada com sucesso.');
    redirect('admin.php');
}

$series = database()->query('SELECT id, title, genre, episodes, created_at FROM mini_series ORDER BY created_at DESC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Cadastrar nova mini série</h1>
                <form method="post" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Título</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">Gênero</label>
                        <input type="text" name="genre" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Episódios</label>
                        <input type="number" min="1" name="episodes" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" rows="4" class="form-control" required></textarea>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-success w-100" type="submit">Salvar mini série</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Últimas mini séries cadastradas</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Gênero</th>
                                <th>Episódios</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!$series): ?>
                            <tr><td colspan="4" class="text-center text-muted">Sem registros.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($series as $serie): ?>
                            <tr>
                                <td><?= htmlspecialchars($serie['title']) ?></td>
                                <td><?= htmlspecialchars($serie['genre']) ?></td>
                                <td><?= (int) $serie['episodes'] ?></td>
                                <td><?= date('d/m/Y', strtotime($serie['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
