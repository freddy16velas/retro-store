<?php
$pageTitle = 'Inventario';
require_once __DIR__ . '/header.php';

$pdo = getDB();

// ── Quick stock update ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid   = (int)$_POST['producto_id'];
    $stock = (int)$_POST['stock'];
    if ($pid > 0 && $stock >= 0) {
        $pdo->prepare('UPDATE productos SET stock=? WHERE id=?')->execute([$stock, $pid]);
        setFlash('success', 'Stock actualizado.');
    }
    header('Location: ' . SITE_URL . '/admin/inventario.php');
    exit;
}

// ── Filters ───────────────────────────────────────────────────
$filtro  = $_GET['filtro'] ?? 'todos';
$catFilt = (int)($_GET['cat'] ?? 0);

$where  = [];
$params = [];
if ($filtro === 'agotados')  { $where[] = 'p.stock = 0'; }
if ($filtro === 'bajo')      { $where[] = 'p.stock > 0 AND p.stock <= 5'; }
if ($catFilt > 0)            { $where[] = 'p.categoria_id = ?'; $params[] = $catFilt; }
$wsql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$s = $pdo->prepare(
    "SELECT p.*, c.nombre AS cat
     FROM productos p JOIN categorias c ON c.id=p.categoria_id
     $wsql ORDER BY p.stock ASC, p.nombre"
);
$s->execute($params);
$prods = $s->fetchAll();

$cats = $pdo->query('SELECT * FROM categorias ORDER BY nombre')->fetchAll();

// Summary stats
$total    = $pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn();
$agotados = $pdo->query('SELECT COUNT(*) FROM productos WHERE stock=0')->fetchColumn();
$bajo     = $pdo->query('SELECT COUNT(*) FROM productos WHERE stock>0 AND stock<=5')->fetchColumn();
$ok       = $total - $agotados - $bajo;
?>

<!-- Summary -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="admin-stat-card">
            <i class="bi bi-boxes" style="font-size:1.8rem;color:var(--neon-cyan);"></i>
            <div class="admin-stat-number" style="color:var(--neon-cyan);"><?= $total ?></div>
            <div class="admin-stat-label">TOTAL PRODUCTOS</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-stat-card">
            <i class="bi bi-check-circle" style="font-size:1.8rem;color:var(--neon-green);"></i>
            <div class="admin-stat-number" style="color:var(--neon-green);"><?= $ok ?></div>
            <div class="admin-stat-label">EN STOCK (OK)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-stat-card">
            <i class="bi bi-exclamation-circle" style="font-size:1.8rem;color:var(--neon-yellow);"></i>
            <div class="admin-stat-number" style="color:var(--neon-yellow);"><?= $bajo ?></div>
            <div class="admin-stat-label">STOCK BAJO (≤5)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="admin-stat-card">
            <i class="bi bi-x-circle" style="font-size:1.8rem;color:var(--neon-pink);"></i>
            <div class="admin-stat-number" style="color:var(--neon-pink);"><?= $agotados ?></div>
            <div class="admin-stat-label">AGOTADOS</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="d-flex gap-2 flex-wrap align-items-center mb-4">
    <a href="inventario.php"                 class="filter-btn <?= $filtro==='todos'?'active':'' ?>">TODOS</a>
    <a href="inventario.php?filtro=bajo"     class="filter-btn <?= $filtro==='bajo'?'active':'' ?>">STOCK BAJO</a>
    <a href="inventario.php?filtro=agotados" class="filter-btn <?= $filtro==='agotados'?'active':'' ?>">AGOTADOS</a>
    <select class="retro-select ms-auto"
            onchange="window.location='inventario.php?filtro=<?= $filtro ?>&cat='+this.value">
        <option value="0">Todas las categorías</option>
        <?php foreach ($cats as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $catFilt===$c['id']?'selected':'' ?>><?= e($c['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<!-- Table -->
<div style="background:var(--card-bg);border:1px solid var(--border);padding:1.5rem;overflow-x:auto;">
    <h2 style="font-family:'Press Start 2P';font-size:.55rem;color:var(--neon-cyan);margin-bottom:1.2rem;">
        <i class="bi bi-clipboard-data"></i> INVENTARIO (<?= count($prods) ?>)
    </h2>
    <?php if (empty($prods)): ?>
        <p style="color:var(--neon-green);"><i class="bi bi-check-circle"></i> Sin productos con este filtro.</p>
    <?php else: ?>
    <table class="retro-table">
        <thead>
            <tr><th>ID</th><th>PRODUCTO</th><th>CATEGORÍA</th><th>PRECIO</th><th>STOCK ACTUAL</th><th>ACTUALIZAR</th></tr>
        </thead>
        <tbody>
            <?php foreach ($prods as $p): ?>
            <tr>
                <td style="font-family:'VT323';font-size:1.1rem;color:var(--neon-cyan);"><?= $p['id'] ?></td>
                <td style="font-weight:600;"><?= e($p['nombre']) ?></td>
                <td style="color:var(--text-dim);"><?= e($p['cat']) ?></td>
                <td style="font-family:'VT323';font-size:1.2rem;color:var(--neon-green);"><?= formatPrice($p['precio']) ?></td>
                <td>
                    <?php
                    $sc = $p['stock'];
                    $col = $sc===0 ? 'var(--neon-pink)' : ($sc<=5 ? 'var(--neon-yellow)' : 'var(--neon-green)');
                    $lbl = $sc===0 ? 'AGOTADO' : ($sc<=5 ? 'BAJO' : 'OK');
                    ?>
                    <span style="font-family:'VT323';font-size:1.4rem;color:<?= $col ?>;"><?= $sc ?></span>
                    <span class="badge-estado" style="border-color:<?= $col ?>;color:<?= $col ?>;margin-left:.4rem;"><?= $lbl ?></span>
                </td>
                <td>
                    <form method="post" class="d-flex gap-2 align-items-center">
                        <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                        <input type="number" name="stock" class="retro-input" style="width:70px;padding:.3rem;"
                               value="<?= $p['stock'] ?>" min="0">
                        <button type="submit" class="retro-btn-sm" style="font-size:.38rem;white-space:nowrap;">
                            <i class="bi bi-check-lg"></i> OK
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
