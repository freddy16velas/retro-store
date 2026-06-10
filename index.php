<?php
$pageTitle = 'Inicio';
require_once 'includes/auth.php';

$pdo = getDB();

// Productos destacados (4 de categorías distintas)
$featured = $pdo->query(
    'SELECT p.*, c.nombre AS cat_nombre
     FROM productos p
     JOIN categorias c ON c.id = p.categoria_id
     WHERE p.stock > 0
     ORDER BY p.fecha_creacion DESC
     LIMIT 8'
)->fetchAll();

// Categorías con conteo
$cats = $pdo->query(
    'SELECT c.*, COUNT(p.id) AS total
     FROM categorias c
     LEFT JOIN productos p ON p.categoria_id = c.id
     GROUP BY c.id'
)->fetchAll();

// Colores por categoría
$catColors = [1 => 'var(--neon-pink)', 2 => 'var(--neon-cyan)', 3 => 'var(--neon-yellow)', 4 => 'var(--neon-purple)'];
$catIcons  = [1 => 'bi-joystick', 2 => 'bi-book', 3 => 'bi-disc', 4 => 'bi-vinyl'];

require_once 'includes/header.php';
?>

<!-- ── Page Hero ── -->
<div class="page-hero">
    <div class="container position-relative" style="z-index:1;">
        <span class="section-eyebrow text-neon-green">▶ BIENVENIDO A RETROVAULT</span>
        <h1 class="section-title mt-2">
            TU TIENDA <span class="hl">RETRO</span><br>
            <span style="color:var(--text-dim);font-size:.7em;">Ciudad de Guatemala</span>
        </h1>
    </div>
</div>

<div class="container py-5">

    <!-- ── Promo banner ── -->
    <div class="mb-5 p-4" style="background:var(--card-bg);border:1px solid var(--neon-pink);box-shadow:0 0 20px rgba(255,45,120,.15);">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="section-eyebrow text-neon-pink">★ PROMOCIÓN ESPECIAL</span>
                <h3 style="font-family:'Press Start 2P';font-size:.8rem;color:#fff;margin:.5rem 0;">
                    ENVÍO GRATIS en compras mayores a Q500
                </h3>
                <p style="color:var(--text-dim);font-family:'VT323';font-size:1.3rem;margin:0;">
                    Retira en tienda Lun–Vie · 1:00 PM – 5:00 PM · Ciudad de Guatemala
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="tienda.php" class="btn-neon btn-neon-pink">
                    <i class="bi bi-lightning"></i> VER OFERTAS
                </a>
            </div>
        </div>
    </div>

    <!-- ── Categorías ── -->
    <div class="section-header">
        <span class="section-eyebrow text-neon-cyan">NAVEGA POR</span>
        <h2 class="section-title">CATEGORÍAS <span class="hl">DISPONIBLES</span></h2>
        <div class="section-line"></div>
    </div>
    <div class="row g-3 mb-5">
        <?php foreach ($cats as $cat): ?>
        <div class="col-6 col-md-3">
            <a href="tienda.php?categoria=<?= $cat['id'] ?>" class="category-card"
               style="--accent-color:<?= $catColors[$cat['id']] ?? 'var(--neon-cyan)' ?>">
                <i class="bi <?= $catIcons[$cat['id']] ?? 'bi-box' ?> category-icon"
                   style="color:<?= $catColors[$cat['id']] ?? 'var(--neon-cyan)' ?>"></i>
                <div class="category-name" style="color:<?= $catColors[$cat['id']] ?? 'var(--neon-cyan)' ?>">
                    <?= e($cat['nombre']) ?>
                </div>
                <div class="category-desc"><?= $cat['total'] ?> productos</div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Featured products ── -->
    <div class="section-header">
        <span class="section-eyebrow text-neon-pink">SELECCIÓN CURADA</span>
        <h2 class="section-title">PRODUCTOS <span class="hl">DESTACADOS</span></h2>
        <div class="section-line"></div>
    </div>
    <div class="row g-3">
        <?php foreach ($featured as $p): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="product-card">
                <div class="product-img-wrap">
                    <?php if ($p['imagen'] && file_exists(__DIR__ . '/images/products/' . $p['imagen'])): ?>
                        <img src="<?= SITE_URL ?>/images/products/<?= e($p['imagen']) ?>"
                             alt="<?= e($p['nombre']) ?>" loading="lazy">
                    <?php else: ?>
                        <div class="product-img-placeholder">
                            <i class="bi <?= $catIcons[$p['categoria_id']] ?? 'bi-box' ?>"></i>
                            <small style="font-size:.6rem;margin-top:.5rem;font-family:'Press Start 2P'">NO IMG</small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="product-body">
                    <div class="product-category"><?= e($p['cat_nombre']) ?></div>
                    <div class="product-name"><?= e($p['nombre']) ?></div>
                    <div class="product-desc"><?= e(mb_substr($p['descripcion'], 0, 80)) ?>…</div>
                    <div class="product-footer">
                        <div>
                            <div class="product-price"><?= formatPrice($p['precio']) ?></div>
                            <div class="product-stock">Stock: <?= $p['stock'] ?></div>
                        </div>
                    </div>
                    <?php if (isLoggedIn()): ?>
                        <button class="btn-add-cart" onclick="addToCart(<?= $p['id'] ?>, this)"
                            <?= $p['stock'] < 1 ? 'disabled' : '' ?>>
                            <i class="bi bi-cart-plus"></i>
                            <?= $p['stock'] < 1 ? 'AGOTADO' : 'AGREGAR AL CARRITO' ?>
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn-add-cart" style="display:block;text-align:center;text-decoration:none;">
                            <i class="bi bi-person-lock"></i> INICIAR SESIÓN
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Store info ── -->
    <div class="row g-3 mt-5">
        <div class="col-md-4">
            <div class="p-4" style="background:var(--card-bg);border:1px solid var(--border);height:100%;">
                <i class="bi bi-geo-alt text-neon-cyan" style="font-size:2rem;"></i>
                <h5 style="font-family:'Press Start 2P';font-size:.6rem;color:#fff;margin:1rem 0 .5rem;">UBICACIÓN</h5>
                <p style="color:var(--text-dim);">Ciudad de Guatemala, Guatemala</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4" style="background:var(--card-bg);border:1px solid var(--border);height:100%;">
                <i class="bi bi-clock text-neon-green" style="font-size:2rem;"></i>
                <h5 style="font-family:'Press Start 2P';font-size:.6rem;color:#fff;margin:1rem 0 .5rem;">HORARIO</h5>
                <p style="color:var(--text-dim);">Lunes a Viernes<br>1:00 PM – 5:00 PM</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4" style="background:var(--card-bg);border:1px solid var(--border);height:100%;">
                <i class="bi bi-truck text-neon-yellow" style="font-size:2rem;"></i>
                <h5 style="font-family:'Press Start 2P';font-size:.6rem;color:#fff;margin:1rem 0 .5rem;">ENTREGA</h5>
                <p style="color:var(--text-dim);">Retiro en tienda o entrega a domicilio en Guatemala</p>
            </div>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
<script src="<?= SITE_URL ?>/js/retro.js"></script>
