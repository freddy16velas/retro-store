<?php
$pageTitle = 'Mis Pedidos';
require_once 'includes/auth.php';
requireLogin();

$pdo  = getDB();
$uid  = (int)$_SESSION['usuario_id'];

$pedidos = $pdo->prepare(
    'SELECT p.*, COUNT(d.id) AS num_items
     FROM pedidos p
     LEFT JOIN detalle_pedido d ON d.pedido_id = p.id
     WHERE p.usuario_id = ?
     GROUP BY p.id
     ORDER BY p.fecha_pedido DESC'
);
$pedidos->execute([$uid]);
$pedidos = $pedidos->fetchAll();

// Detail?
$detailId = (int)($_GET['pedido'] ?? 0);
$detalle  = [];
$pedInfo  = null;
if ($detailId > 0) {
    $s = $pdo->prepare('SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?');
    $s->execute([$detailId, $uid]);
    $pedInfo = $s->fetch();

    if ($pedInfo) {
        $s = $pdo->prepare(
            'SELECT d.*, p.nombre, p.imagen FROM detalle_pedido d
             JOIN productos p ON p.id = d.producto_id
             WHERE d.pedido_id = ?'
        );
        $s->execute([$detailId]);
        $detalle = $s->fetchAll();
    }
}

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container position-relative" style="z-index:1;">
        <span class="section-eyebrow text-neon-green">▶ HISTORIAL</span>
        <h1 class="section-title mt-2">MIS <span class="hl">PEDIDOS</span></h1>
    </div>
</div>

<div class="container py-5">

<?php if ($pedInfo && !empty($detalle)): ?>
    <!-- ── Order detail ── -->
    <div class="mb-3">
        <a href="pedidos.php" class="retro-btn-sm"><i class="bi bi-arrow-left"></i> Volver a pedidos</a>
    </div>
    <div style="background:var(--card-bg);border:1px solid var(--border);padding:2rem;">
        <div class="row mb-3">
            <div class="col-md-6">
                <span style="font-family:'Press Start 2P';font-size:.5rem;color:var(--neon-cyan);">
                    PEDIDO #<?= $pedInfo['id'] ?>
                </span>
                <div style="font-family:'VT323';font-size:1.2rem;color:var(--text-dim);margin-top:.3rem;">
                    <?= date('d/m/Y H:i', strtotime($pedInfo['fecha_pedido'])) ?>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="badge-estado badge-<?= $pedInfo['estado'] ?>"><?= strtoupper($pedInfo['estado']) ?></span>
            </div>
        </div>
        <table class="retro-table">
            <thead><tr><th>PRODUCTO</th><th>PRECIO UNIT.</th><th>CANTIDAD</th><th>SUBTOTAL</th></tr></thead>
            <tbody>
                <?php foreach ($detalle as $d): ?>
                <tr>
                    <td><?= e($d['nombre']) ?></td>
                    <td style="font-family:'VT323';font-size:1.2rem;color:var(--neon-green);"><?= formatPrice($d['precio']) ?></td>
                    <td><?= $d['cantidad'] ?></td>
                    <td style="font-family:'VT323';font-size:1.2rem;color:var(--neon-yellow);"><?= formatPrice($d['precio'] * $d['cantidad']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-end mt-3 pt-3" style="border-top:1px solid var(--border);">
            <span style="font-family:'Press Start 2P';font-size:.5rem;color:var(--text-dim);">TOTAL:</span>
            <span style="font-family:'VT323';font-size:2rem;color:var(--neon-green);margin-left:1rem;"><?= formatPrice($pedInfo['total']) ?></span>
        </div>
        <div class="mt-3 p-3" style="background:var(--bg3);border-left:3px solid var(--neon-cyan);">
            <span style="font-family:'Press Start 2P';font-size:.45rem;color:var(--neon-cyan);">
                <?= $pedInfo['metodo_entrega'] === 'tienda' ? '🏪 RECOGER EN TIENDA' : '🚚 ENTREGA A DOMICILIO' ?>
            </span>
            <?php if ($pedInfo['direccion']): ?>
                <div style="color:var(--text-dim);font-family:'VT323';font-size:1.2rem;margin-top:.3rem;"><?= e($pedInfo['direccion']) ?></div>
            <?php else: ?>
                <div style="color:var(--text-dim);font-family:'VT323';font-size:1.2rem;margin-top:.3rem;">
                    Ciudad de Guatemala · Lun–Vie 1:00PM–5:00PM
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php elseif (empty($pedidos)): ?>
    <div class="text-center py-5">
        <i class="bi bi-box-seam" style="font-size:4rem;color:var(--text-dim);"></i>
        <h3 style="font-family:'Press Start 2P';font-size:.7rem;color:var(--text-dim);margin-top:1.5rem;">
            AÚN NO TIENES PEDIDOS
        </h3>
        <a href="tienda.php" class="btn-neon mt-3 d-inline-block">
            <i class="bi bi-shop"></i> IR A LA TIENDA
        </a>
    </div>

<?php else: ?>
    <table class="retro-table">
        <thead>
            <tr>
                <th>#</th>
                <th>FECHA</th>
                <th>ITEMS</th>
                <th>TOTAL</th>
                <th>ENTREGA</th>
                <th>ESTADO</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $p): ?>
            <tr>
                <td style="font-family:'Press Start 2P';font-size:.45rem;color:var(--neon-cyan);">#<?= $p['id'] ?></td>
                <td style="color:var(--text-dim);"><?= date('d/m/Y', strtotime($p['fecha_pedido'])) ?></td>
                <td><?= $p['num_items'] ?> ítem<?= $p['num_items']!==1?'s':'' ?></td>
                <td style="font-family:'VT323';font-size:1.2rem;color:var(--neon-green);"><?= formatPrice($p['total']) ?></td>
                <td>
                    <?php if ($p['metodo_entrega']==='tienda'): ?>
                        <span style="color:var(--neon-cyan);"><i class="bi bi-shop"></i> Tienda</span>
                    <?php else: ?>
                        <span style="color:var(--neon-yellow);"><i class="bi bi-truck"></i> Domicilio</span>
                    <?php endif; ?>
                </td>
                <td><span class="badge-estado badge-<?= $p['estado'] ?>"><?= strtoupper($p['estado']) ?></span></td>
                <td>
                    <a href="pedidos.php?pedido=<?= $p['id'] ?>" class="retro-btn-sm" style="font-size:.38rem;">
                        <i class="bi bi-eye"></i> VER
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
