<?php
$pageTitle = 'Productos';
require_once __DIR__ . '/header.php';

$pdo    = getDB();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);
$cats   = $pdo->query('SELECT * FROM categorias ORDER BY nombre')->fetchAll();

// ── Handle POST ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act        = $_POST['_action']     ?? '';
    $nombre     = trim($_POST['nombre']     ?? '');
    $catId      = (int)$_POST['categoria_id'];
    $desc       = trim($_POST['descripcion'] ?? '');
    $precio     = (float)$_POST['precio'];
    $stock      = (int)$_POST['stock'];
    $imgName    = trim($_POST['imagen_actual'] ?? '');

    // Handle image upload
    if (!empty($_FILES['imagen']['name'])) {
        $ext     = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $newName = uniqid('prod_') . '.' . $ext;
            $dest    = dirname(__DIR__) . '/images/products/' . $newName;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dest)) {
                $imgName = $newName;
            }
        }
    }

    if ($nombre === '' || $catId < 1 || $precio <= 0) {
        setFlash('error', 'Nombre, categoría y precio son obligatorios.');
    } else {
        if ($act === 'create') {
            $pdo->prepare(
                'INSERT INTO productos (categoria_id,nombre,descripcion,precio,stock,imagen) VALUES (?,?,?,?,?,?)'
            )->execute([$catId, $nombre, $desc, $precio, $stock, $imgName ?: null]);
            setFlash('success', 'Producto creado.');
        } elseif ($act === 'edit' && $id > 0) {
            $pdo->prepare(
                'UPDATE productos SET categoria_id=?,nombre=?,descripcion=?,precio=?,stock=?,imagen=? WHERE id=?'
            )->execute([$catId, $nombre, $desc, $precio, $stock, $imgName ?: null, $id]);
            setFlash('success', 'Producto actualizado.');
        }
    }
    header('Location: ' . SITE_URL . '/admin/productos.php');
    exit;
}

// ── Delete ────────────────────────────────────────────────────
if ($action === 'delete' && $id > 0) {
    $pdo->prepare('DELETE FROM productos WHERE id=?')->execute([$id]);
    setFlash('success', 'Producto eliminado.');
    header('Location: ' . SITE_URL . '/admin/productos.php');
    exit;
}

// ── Load for edit ─────────────────────────────────────────────
$editProd = null;
if (($action === 'edit' || $action === 'new') && ($id > 0 || $action === 'new')) {
    if ($id > 0) {
        $s = $pdo->prepare('SELECT * FROM productos WHERE id=?');
        $s->execute([$id]);
        $editProd = $s->fetch();
    } else {
        $editProd = []; // new
    }
}

// ── Product list ──────────────────────────────────────────────
$search  = trim($_GET['q'] ?? '');
$catFilt = (int)($_GET['cat'] ?? 0);
$where   = [];
$params  = [];
if ($search !== '') { $where[] = 'p.nombre LIKE ?'; $params[] = "%$search%"; }
if ($catFilt > 0)   { $where[] = 'p.categoria_id = ?'; $params[] = $catFilt; }
$wsql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$s = $pdo->prepare("SELECT p.*, c.nombre AS cat FROM productos p JOIN categorias c ON c.id=p.categoria_id $wsql ORDER BY c.nombre, p.nombre");
$s->execute($params);
$prods = $s->fetchAll();
?>

<!-- Search bar -->
<div class="d-flex gap-3 align-items-end mb-4">
    <div style="flex:1;">
        <label class="retro-label">BUSCAR</label>
        <input type="text" class="retro-input" id="prodSearch" placeholder="Nombre del producto…"
               value="<?= e($search) ?>"
               onkeyup="filterProds(this.value)">
    </div>
    <div>
        <label class="retro-label">CATEGORÍA</label>
        <select class="retro-select" onchange="window.location='productos.php?cat='+this.value">
            <option value="0">Todas</option>
            <?php foreach ($cats as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $catFilt===$c['id']?'selected':'' ?>><?= e($c['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <a href="productos.php?action=new" class="btn-neon btn-neon-green" style="font-size:.45rem;white-space:nowrap;">
        <i class="bi bi-plus-lg"></i> NUEVO
    </a>
</div>

<!-- ── Inline form (create/edit) ── -->
<?php if (isset($editProd)): ?>
<div style="background:var(--card-bg);border:2px solid var(--neon-cyan);padding:1.5rem;margin-bottom:2rem;">
    <h2 style="font-family:'Press Start 2P';font-size:.55rem;color:var(--neon-cyan);margin-bottom:1.2rem;">
        <i class="bi bi-<?= $id>0?'pencil':'plus-circle' ?>"></i>
        <?= $id>0 ? 'EDITAR PRODUCTO #'.$id : 'NUEVO PRODUCTO' ?>
    </h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="_action"      value="<?= $id>0?'edit':'create' ?>">
        <input type="hidden" name="imagen_actual" value="<?= e($editProd['imagen'] ?? '') ?>">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="retro-label">NOMBRE *</label>
                <input type="text" name="nombre" class="retro-input"
                       value="<?= e($editProd['nombre'] ?? '') ?>" required>
            </div>
            <div class="col-md-3">
                <label class="retro-label">CATEGORÍA *</label>
                <select name="categoria_id" class="retro-select w-100" required>
                    <option value="">Selecciona…</option>
                    <?php foreach ($cats as $c): ?>
                        <option value="<?= $c['id'] ?>"
                            <?= ($editProd['categoria_id']??0)==$c['id']?'selected':'' ?>>
                            <?= e($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="retro-label">PRECIO *</label>
                <input type="number" name="precio" class="retro-input"
                       step="0.01" min="0"
                       value="<?= $editProd['precio'] ?? '' ?>" required>
            </div>
            <div class="col-md-1">
                <label class="retro-label">STOCK</label>
                <input type="number" name="stock" class="retro-input"
                       min="0" value="<?= $editProd['stock'] ?? 0 ?>">
            </div>
            <div class="col-md-12">
                <label class="retro-label">DESCRIPCIÓN</label>
                <textarea name="descripcion" class="retro-textarea" rows="2"
                          placeholder="Descripción del producto…"><?= e($editProd['descripcion'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="retro-label">IMAGEN</label>
                <input type="file" name="imagen" class="retro-input" accept="image/*"
                       style="padding:.4rem;">
                <?php if (!empty($editProd['imagen'])): ?>
                    <div style="margin-top:.4rem;font-family:'VT323';font-size:1rem;color:var(--neon-green);">
                        <i class="bi bi-check-circle"></i> Imagen actual: <?= e($editProd['imagen']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn-neon btn-neon-green" style="font-size:.45rem;">
                <i class="bi bi-check-lg"></i> <?= $id>0?'ACTUALIZAR':'CREAR PRODUCTO' ?>
            </button>
            <a href="productos.php" class="btn-neon" style="font-size:.45rem;">CANCELAR</a>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- ── Products table ── -->
<div style="background:var(--card-bg);border:1px solid var(--border);padding:1.5rem;">
    <h2 style="font-family:'Press Start 2P';font-size:.55rem;color:var(--neon-cyan);margin-bottom:1.2rem;">
        <i class="bi bi-box-seam"></i> PRODUCTOS (<?= count($prods) ?>)
    </h2>
    <div style="overflow-x:auto;">
        <table class="retro-table" id="prodTable">
            <thead>
                <tr>
                    <th>ID</th><th>NOMBRE</th><th>CATEGORÍA</th>
                    <th>PRECIO</th><th>STOCK</th><th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prods as $p): ?>
                <tr class="prod-row" data-name="<?= e(strtolower($p['nombre'])) ?>">
                    <td style="font-family:'VT323';font-size:1.1rem;color:var(--neon-cyan);"><?= $p['id'] ?></td>
                    <td style="font-weight:600;"><?= e($p['nombre']) ?></td>
                    <td style="color:var(--text-dim);"><?= e($p['cat']) ?></td>
                    <td style="font-family:'VT323';font-size:1.2rem;color:var(--neon-green);"><?= formatPrice($p['precio']) ?></td>
                    <td>
                        <span style="font-family:'VT323';font-size:1.2rem;color:<?= $p['stock']===0?'var(--neon-pink)':($p['stock']<=3?'var(--neon-yellow)':'var(--neon-green)') ?>;">
                            <?= $p['stock'] ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="productos.php?action=edit&id=<?= $p['id'] ?>"
                               class="retro-btn-sm" style="font-size:.35rem;">
                                <i class="bi bi-pencil"></i> EDITAR
                            </a>
                            <a href="productos.php?action=delete&id=<?= $p['id'] ?>"
                               class="retro-btn-sm btn-neon-pink" style="font-size:.35rem;"
                               onclick="return confirm('¿Eliminar producto?')">
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

<script>
function filterProds(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.prod-row').forEach(r => {
        r.style.display = r.dataset.name.includes(q) ? '' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
