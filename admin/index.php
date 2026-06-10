<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/header.php';

$pdo = getDB();

$stats = [
    'productos' => $pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn(),
    'pedidos'   => $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn(),
    'usuarios'  => $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='cliente'")->fetchColumn(),
    'mensajes'  => $pdo->query('SELECT COUNT(*) FROM contacto')->fetchColumn(),
    'ingresos'  => $pdo->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE estado != 'cancelado'")->fetchColumn(),
    'agotados'  => $pdo->query('SELECT COUNT(*) FROM productos WHERE stock = 0')->fetchColumn(),
];

$recentOrders = $pdo->query(
    'SELECT p.*, u.nombre AS cliente
     FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id
     ORDER BY p.fecha_pedido DESC LIMIT 5'
)->fetchAll();

$lowStock = $pdo->query(
    'SELECT p.*, c.nombre AS cat FROM productos p JOIN categorias c ON c.id=p.categoria_id
     WHERE p.stock <= 3 ORDER BY p.stock ASC LIMIT 8'
)->fetchAll();
?>

<!-- Stats grid -->
<div class="row g-3 mb-4">
    <?php
    $statCards = [
        ['label'=>'PRODUCTOS',  'value'=>$stats['productos'], 'icon'=>'bi-box-seam',      'color'=>'var(--neon-cyan)'],
        ['label'=>'PEDIDOS',    'value'=>$stats['pedidos'],   'icon'=>'bi-cart-check',     'color'=>'var(--neon-yellow)'],
        ['label'=>'CLIENTES',   'value'=>$stats['usuarios'],  'icon'=>'bi-people',         'color'=>'var(--neon-green)'],
        ['label'=>'MENSAJES',   'value'=>$stats['mensajes'],  'icon'=>'bi-envelope',       'color'=>'var(--neon-purple)'],
        ['label'=>'INGRESOS',   'value'=>'Q '.number_format($stats['ingresos'],2), 'icon'=>'bi-currency-dollar', 'color'=>'var(--neon-green)'],
        ['label'=>'AGOTADOS',   'value'=>$stats['agotados'],  'icon'=>'bi-exclamation-triangle', 'color'=>'var(--neon-pink)'],
    ];
    foreach ($statCards as $card):
    ?>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="admin-stat-card">
            <i class="bi <?= $card['icon'] ?>" style="font-size:1.8rem;color:<?= $card['color'] ?>;"></i>
            <div class="admin-stat-number mt-2" style="color:<?= $card['color'] ?>;">
                <?= $card['value'] ?>
            </div>
            <div class="admin-stat-label"><?= $card['label'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">

    <!-- Recent orders -->
    <div class="col-lg-7">
        <div style="background:var(--card-bg);border:1px solid var(--border);padding:1.5rem;">
            <h2 style="font-family:'Press Start 2P';font-size:.55rem;color:var(--neon-cyan);margin-bottom:1.2rem;">
                <i class="bi bi-clock-history"></i> PEDIDOS RECIENTES
            </h2>
            <?php if (empty($recentOrders)): ?>
                <p style="color:var(--text-dim);">Sin pedidos aún.</p>
            <?php else: ?>
            <table class="retro-table">
                <thead><tr><th>#</th><th>CLIENTE</th><th>TOTAL</th><th>ESTADO</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($recentOrders as $o): ?>
                    <tr>
                        <td style="font-family:'Press Start 2P';font-size:.4rem;color:var(--neon-cyan);">#<?= $o['id'] ?></td>
                        <td><?= e($o['cliente']) ?></td>
                        <td style="font-family:'VT323';font-size:1.2rem;color:var(--neon-green);"><?= formatPrice($o['total']) ?></td>
                        <td><span class="badge-estado badge-<?= $o['estado'] ?>"><?= strtoupper($o['estado']) ?></span></td>
                        <td><a href="pedidos.php?id=<?= $o['id'] ?>" class="retro-btn-sm" style="font-size:.35rem;padding:.3rem .5rem;">VER</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="mt-2 text-end">
                <a href="pedidos.php" class="retro-btn-sm"><i class="bi bi-arrow-right"></i> Ver todos</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Low stock -->
    <div class="col-lg-5">
        <div style="background:var(--card-bg);border:1px solid var(--neon-pink);padding:1.5rem;">
            <h2 style="font-family:'Press Start 2P';font-size:.55rem;color:var(--neon-pink);margin-bottom:1.2rem;">
                <i class="bi bi-exclamation-triangle"></i> STOCK BAJO / AGOTADO
            </h2>
            <?php if (empty($lowStock)): ?>
                <p style="color:var(--neon-green);"><i class="bi bi-check-circle"></i> Todo en stock.</p>
            <?php else: ?>
            <table class="retro-table">
                <thead><tr><th>PRODUCTO</th><th>STOCK</th></tr></thead>
                <tbody>
                    <?php foreach ($lowStock as $p): ?>
                    <tr>
                        <td>
                            <div style="font-size:.95rem;color:#fff;"><?= e($p['nombre']) ?></div>
                            <div style="font-size:.8rem;color:var(--text-dim);"><?= e($p['cat']) ?></div>
                        </td>
                        <td>
                            <span style="font-family:'VT323';font-size:1.4rem;color:<?= $p['stock']===0?'var(--neon-pink)':'var(--neon-yellow)' ?>;">
                                <?= $p['stock'] === 0 ? 'AGOTADO' : $p['stock'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="mt-2 text-end">
                <a href="inventario.php" class="retro-btn-sm"><i class="bi bi-arrow-right"></i> Ver inventario</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Quick links -->
<div class="row g-3 mt-3">
    <?php
    $links = [
        ['href'=>'productos.php?action=new', 'icon'=>'bi-plus-circle', 'label'=>'NUEVO PRODUCTO', 'color'=>'var(--neon-green)'],
        ['href'=>'categorias.php',           'icon'=>'bi-tags',        'label'=>'CATEGORÍAS',      'color'=>'var(--neon-cyan)'],
        ['href'=>'pedidos.php',              'icon'=>'bi-cart-check',  'label'=>'VER PEDIDOS',     'color'=>'var(--neon-yellow)'],
        ['href'=>'contactos.php',            'icon'=>'bi-envelope',    'label'=>'MENSAJES',        'color'=>'var(--neon-purple)'],
    ];
    foreach ($links as $l):
    ?>
    <div class="col-6 col-md-3">
        <a href="<?= $l['href'] ?>" class="btn-neon d-block text-center"
           style="border-color:<?= $l['color'] ?>;color:<?= $l['color'] ?>;">
            <i class="bi <?= $l['icon'] ?>"></i><br>
            <span style="font-size:.4rem;"><?= $l['label'] ?></span>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
