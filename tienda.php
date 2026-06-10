<?php
$pageTitle = 'Tienda';
require_once 'includes/auth.php';

$pdo = getDB();

// Filtros
$catId  = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$search = trim($_GET['q'] ?? '');

// Categorías
$cats = $pdo->query('SELECT * FROM categorias ORDER BY nombre')->fetchAll();

// Productos con filtros
$where  = ['p.stock >= 0'];
$params = [];

if ($catId > 0) {
    $where[]  = 'p.categoria_id = ?';
    $params[] = $catId;
}
if ($search !== '') {
    $where[]  = '(p.nombre LIKE ? OR p.descripcion LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql = 'SELECT p.*, c.nombre AS cat_nombre
        FROM productos p
        JOIN categorias c ON c.id = p.categoria_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY c.nombre, p.nombre';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$catColors = [1=>'var(--neon-pink)',2=>'var(--neon-cyan)',3=>'var(--neon-yellow)',4=>'var(--neon-purple)'];
$catIcons  = [1=>'bi-joystick',2=>'bi-book',3=>'bi-disc',4=>'bi-vinyl'];

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container position-relative" style="z-index:1;">
        <span class="section-eyebrow text-neon-green">▶ CATÁLOGO COMPLETO</span>
        <h1 class="section-title mt-2">
            NUESTRA <span class="hl">TIENDA</span>
        </h1>
    </div>
</div>

<div class="container py-5">

    <!-- ── Search & Filter ── -->
    <div class="retro-search-bar mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="retro-label"><i class="bi bi-search"></i> BUSCAR PRODUCTO</label>
                <input type="text" id="searchInput"
                       class="retro-input" placeholder="Escribe para buscar..."
                       value="<?= e($search) ?>">
            </div>
            <div class="col-md-4">
                <label class="retro-label"><i class="bi bi-funnel"></i> CATEGORÍA</label>
                <select class="retro-select w-100"
                        onchange="window.location='tienda.php?categoria='+this.value">
                    <option value="0" <?= $catId===0?'selected':'' ?>>Todas las categorías</option>
                    <?php foreach ($cats as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $catId===$c['id']?'selected':'' ?>>
                            <?= e($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2 flex-wrap">
                    <button class="filter-btn <?= $catId===0?'active':'' ?>"
                            onclick="window.location='tienda.php'">TODOS</button>
                    <?php foreach ($cats as $c): ?>
                        <button class="filter-btn <?= $catId===$c['id']?'active':'' ?>"
                                onclick="window.location='tienda.php?categoria=<?= $c['id'] ?>'">
                            <?= e(strtoupper(substr($c['nombre'],0,4))) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Results count ── -->
    <div class="mb-3" style="font-family:'VT323';font-size:1.3rem;color:var(--text-dim);">
        <?= count($productos) ?> producto<?= count($productos)!==1?'s':'' ?> encontrado<?= count($productos)!==1?'s':'' ?>
        <?php if ($catId>0): $currentCat = array_values(array_filter($cats,fn($c)=>$c['id']===$catId))[0] ?? null; ?>
            en <span style="color:var(--neon-cyan)"><?= $currentCat ? e($currentCat['nombre']) : '' ?></span>
        <?php endif; ?>
    </div>

    <!-- ── Products grid ── -->
    <?php if (empty($productos)): ?>
        <div class="text-center py-5">
            <i class="bi bi-search" style="font-size:3rem;color:var(--text-dim);"></i>
            <p style="font-family:'Press Start 2P';font-size:.6rem;color:var(--text-dim);margin-top:1rem;">
                NO SE ENCONTRARON PRODUCTOS
            </p>
            <a href="tienda.php" class="btn-neon mt-3 d-inline-block">VER TODOS</a>
        </div>
    <?php else: ?>
        <div class="row g-3" id="productsGrid">
            <?php foreach ($productos as $p): ?>
            <div class="col-6 col-md-4 col-lg-3 product-item"
                 data-name="<?= e(strtolower($p['nombre'])) ?>"
                 data-cat="<?= e(strtolower($p['cat_nombre'])) ?>">
                <div class="product-card">
                    <div class="product-img-wrap">
                        <?php if ($p['imagen'] && file_exists(__DIR__ . '/images/products/' . $p['imagen'])): ?>
                            <img src="<?= SITE_URL ?>/images/products/<?= e($p['imagen']) ?>"
                                 alt="<?= e($p['nombre']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="product-img-placeholder">
                                <i class="bi <?= $catIcons[$p['categoria_id']] ?? 'bi-box' ?>"
                                   style="color:<?= $catColors[$p['categoria_id']] ?? 'var(--neon-cyan)' ?>"></i>
                            </div>
                        <?php endif; ?>
                        <?php if ($p['stock'] < 3 && $p['stock'] > 0): ?>
                            <span class="product-badge">ÚLTIMOS</span>
                        <?php elseif ($p['stock'] < 1): ?>
                            <span class="product-badge" style="background:var(--text-dim)">AGOTADO</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-body">
                        <div class="product-category"
                             style="color:<?= $catColors[$p['categoria_id']] ?? 'var(--neon-cyan)' ?>">
                            <?= e($p['cat_nombre']) ?>
                        </div>
                        <div class="product-name"><?= e($p['nombre']) ?></div>
                        <div class="product-desc">
                            <?= e(mb_substr($p['descripcion'], 0, 90)) ?>…
                        </div>
                        <div class="product-footer">
                            <div>
                                <div class="product-price"><?= formatPrice($p['precio']) ?></div>
                                <div class="product-stock">
                                    <?php if ($p['stock'] > 0): ?>
                                        <i class="bi bi-check-circle text-neon-green"></i> Stock: <?= $p['stock'] ?>
                                    <?php else: ?>
                                        <i class="bi bi-x-circle text-neon-pink"></i> Agotado
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php if (isLoggedIn()): ?>
                            <button class="btn-add-cart" onclick="addToCart(<?= $p['id'] ?>, this)"
                                <?= $p['stock'] < 1 ? 'disabled' : '' ?>>
                                <i class="bi bi-cart-plus"></i>
                                <?= $p['stock'] < 1 ? 'AGOTADO' : 'AGREGAR AL CARRITO' ?>
                            </button>
                        <?php else: ?>
                            <a href="login.php" class="btn-add-cart"
                               style="display:block;text-align:center;text-decoration:none;">
                                <i class="bi bi-person-lock"></i> INICIAR SESIÓN
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
