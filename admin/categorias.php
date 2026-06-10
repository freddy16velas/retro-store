<?php
$pageTitle = 'Categorías';
require_once __DIR__ . '/header.php';

$pdo    = getDB();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// ── Handle POST ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $desc   = trim($_POST['descripcion'] ?? '');
    $act    = $_POST['_action'] ?? '';

    if ($nombre === '') {
        setFlash('error', 'El nombre es obligatorio.');
    } else {
        if ($act === 'create') {
            $pdo->prepare('INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)')->execute([$nombre, $desc]);
            setFlash('success', 'Categoría creada correctamente.');
        } elseif ($act === 'edit' && $id > 0) {
            $pdo->prepare('UPDATE categorias SET nombre=?, descripcion=? WHERE id=?')->execute([$nombre, $desc, $id]);
            setFlash('success', 'Categoría actualizada.');
        }
    }
    header('Location: ' . SITE_URL . '/admin/categorias.php');
    exit;
}

// ── Handle delete ─────────────────────────────────────────────
if ($action === 'delete' && $id > 0) {
    $pdo->prepare('DELETE FROM categorias WHERE id=?')->execute([$id]);
    setFlash('success', 'Categoría eliminada.');
    header('Location: ' . SITE_URL . '/admin/categorias.php');
    exit;
}

// ── Load for edit ─────────────────────────────────────────────
$editCat = null;
if ($action === 'edit' && $id > 0) {
    $s = $pdo->prepare('SELECT * FROM categorias WHERE id=?');
    $s->execute([$id]);
    $editCat = $s->fetch();
}

$cats = $pdo->query('SELECT c.*, COUNT(p.id) AS total FROM categorias c LEFT JOIN productos p ON p.categoria_id=c.id GROUP BY c.id ORDER BY c.nombre')->fetchAll();
?>

<div class="row g-4">

    <!-- ── Form ── -->
    <div class="col-lg-4">
        <div style="background:var(--card-bg);border:1px solid var(--border);padding:1.5rem;">
            <h2 style="font-family:'Press Start 2P';font-size:.55rem;color:var(--neon-cyan);margin-bottom:1.2rem;">
                <i class="bi bi-<?= $editCat ? 'pencil' : 'plus-circle' ?>"></i>
                <?= $editCat ? 'EDITAR' : 'NUEVA' ?> CATEGORÍA
            </h2>
            <form method="post">
                <input type="hidden" name="_action" value="<?= $editCat ? 'edit' : 'create' ?>">
                <div class="retro-form-group">
                    <label class="retro-label">NOMBRE</label>
                    <input type="text" name="nombre" class="retro-input"
                           placeholder="Nombre de la categoría"
                           value="<?= e($editCat['nombre'] ?? '') ?>" required>
                </div>
                <div class="retro-form-group">
                    <label class="retro-label">DESCRIPCIÓN</label>
                    <textarea name="descripcion" class="retro-textarea" rows="3"
                              placeholder="Descripción opcional..."><?= e($editCat['descripcion'] ?? '') ?></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-neon <?= $editCat ? 'btn-neon-pink' : 'btn-neon-green' ?>"
                            style="flex:1;font-size:.45rem;">
                        <i class="bi bi-<?= $editCat ? 'check-lg' : 'plus-lg' ?>"></i>
                        <?= $editCat ? 'ACTUALIZAR' : 'CREAR' ?>
                    </button>
                    <?php if ($editCat): ?>
                    <a href="categorias.php" class="btn-neon" style="font-size:.45rem;">CANCELAR</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- ── List ── -->
    <div class="col-lg-8">
        <div style="background:var(--card-bg);border:1px solid var(--border);padding:1.5rem;">
            <h2 style="font-family:'Press Start 2P';font-size:.55rem;color:var(--neon-cyan);margin-bottom:1.2rem;">
                <i class="bi bi-tags"></i> CATEGORÍAS (<?= count($cats) ?>)
            </h2>
            <table class="retro-table">
                <thead>
                    <tr><th>ID</th><th>NOMBRE</th><th>DESCRIPCIÓN</th><th>PRODUCTOS</th><th>ACCIONES</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($cats as $c): ?>
                    <tr>
                        <td style="font-family:'VT323';font-size:1.1rem;color:var(--neon-cyan);"><?= $c['id'] ?></td>
                        <td style="font-weight:600;"><?= e($c['nombre']) ?></td>
                        <td style="color:var(--text-dim);font-size:.9rem;"><?= e(mb_substr($c['descripcion'],0,50)) ?>…</td>
                        <td style="font-family:'VT323';font-size:1.2rem;color:var(--neon-yellow);"><?= $c['total'] ?></td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="categorias.php?action=edit&id=<?= $c['id'] ?>"
                                   class="retro-btn-sm" style="font-size:.35rem;">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="categorias.php?action=delete&id=<?= $c['id'] ?>"
                                   class="retro-btn-sm btn-neon-pink" style="font-size:.35rem;"
                                   onclick="return confirm('¿Eliminar categoría y todos sus productos?')">
                                    <i class="bi bi-trash3"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
