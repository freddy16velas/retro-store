<?php
$pageTitle = 'Pedidos';
require_once __DIR__ . '/header.php';

$pdo = getDB();
$id  = (int)($_GET['id'] ?? 0);

// ── Change status ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['estado'])) {
    $pedId  = (int)$_POST['pedido_id'];
    $estado = $_POST['estado'];
    $valid  = ['pendiente','procesando','enviado','entregado','cancelado'];
    if (in_array($estado, $valid) && $pedId > 0) {
        $pdo->prepare('UPDATE pedidos SET estado=? WHERE id=?')->execute([$estado, $pedId]);
        setFlash('success', 'Estado actualizado a: ' . strtoupper($estado));
    }
    header('Location: ' . SITE_URL . '/admin/pedidos.php' . ($id ? "?id=$id" : ''));
    exit;
}

// ── Order detail ──────────────────────────────────────────────
$detalle = [];
$pedInfo = null;
if ($id > 0) {
    $s = $pdo->prepare(
        'SELECT p.*, u.nombre AS cliente, u.usuario
         FROM pedidos p JOIN usuarios u ON u.id=p.usuario_id WHERE p.id=?'
    );
    $s->execute([$id]);
    $pedInfo = $s->fetch();

    if ($pedInfo) {
        $s = $pdo->prepare(
            'SELECT d.*, pr.nombre, pr.imagen FROM detalle_pedido d
             JOIN productos pr ON pr.id=d.producto_id WHERE d.pedido_id=?'
        );
        $s->execute([$id]);
        $detalle = $s->fetchAll();
    }
}

// ── All orders ────────────────────────────────────────────────
$filtEstado = $_GET['estado'] ?? '';
$params = [];
$wsql   = '';
if ($filtEstado) { $wsql = "WHERE p.estado = ?"; $params[] = $filtEstado; }
$s = $pdo->prepare(
    "SELECT p.*, u.nombre AS cliente, COUNT(d.id) AS items
     FROM pedidos p
     JOIN usuarios u ON u.id=p.usuario_id
     LEFT JOIN detalle_pedido d ON d.pedido_id=p.id
     $wsql
     GROUP BY p.id
     ORDER BY p.fecha_pedido DESC"
);
$s->execute($params);
$pedidos = $s->fetchAll();
?>

<?php if ($pedInfo): ?>
<!-- ── Order detail view ── -->
<div class="mb-3 d-flex gap-2 align-items-center">
    <a href="pedidos.php" class="retro-btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
    <span style="font-family:'Press Start 2P';font-size:.5rem;color:var(--neon-cyan);">
        PEDIDO #<?= $pedInfo['id'] ?>
    </span>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div style="background:var(--card-bg);border:1px solid var(--border);padding:1.5rem;">
            <h3 style="font-family:'Press Start 2P';font-size:.55rem;color:var(--neon-cyan);margin-bottom:1.2rem;">
                <i class="bi bi-receipt"></i> DETALLE DEL PEDIDO
            </h3>
            <table class="retro-table">
                <thead><tr><th>PRODUCTO</th><th>PRECIO UNIT.</th><th>CANT.</th><th>SUBTOTAL</th></tr></thead>
                <tbody>
                    <?php foreach ($detalle as $d): ?>
                    <tr>
                        <td><?= e($d['nombre']) ?></td>
                        <td style="font-family:'VT323';font-size:1.2rem;color:var(--neon-green);"><?= formatPrice($d['precio']) ?></td>
                        <td><?= $d['cantidad'] ?></td>
                        <td style="font-family:'VT323';font-size:1.2rem;color:var(--neon-yellow);"><?= formatPrice($d['precio']*$d['cantidad']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="text-end mt-3 pt-2" style="border-top:1px solid var(--border);">
                <span style="font-family:'Press Start 2P';font-size:.45rem;color:var(--text-dim);">TOTAL:</span>
                <span style="font-family:'VT323';font-size:2rem;color:var(--neon-green);margin-left:1rem;"><?= formatPrice($pedInfo['total']) ?></span>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Client info -->
        <div style="background:var(--card-bg);border:1px solid var(--border);padding:1.5rem;margin-bottom:1rem;">
            <h3 style="font-family:'Press Start 2P';font-size:.5rem;color:var(--neon-yellow);margin-bottom:1rem;">
                <i class="bi bi-person"></i> CLIENTE
            </h3>
            <p style="margin:0;color:#fff;font-weight:600;"><?= e($pedInfo['cliente']) ?></p>
            <p style="color:var(--text-dim);margin:.3rem 0 0;">@<?= e($pedInfo['usuario']) ?></p>
            <div class="mt-2 p-2" style="background:var(--bg3);font-family:'VT323';font-size:1.1rem;color:var(--text-dim);">
                <?= $pedInfo['metodo_entrega']==='tienda'
                    ? '<i class="bi bi-shop text-neon-cyan"></i> Retiro en tienda<br>Lun–Vie 1:00PM–5:00PM'
                    : '<i class="bi bi-truck text-neon-yellow"></i> Domicilio:<br>'.e($pedInfo['direccion']) ?>
            </div>
            <div style="margin-top:.5rem;font-family:'VT323';font-size:1rem;color:var(--text-dim);">
                <?= date('d/m/Y H:i', strtotime($pedInfo['fecha_pedido'])) ?>
            </div>
        </div>

        <!-- Status change -->
        <div style="background:var(--card-bg);border:1px solid var(--neon-cyan);padding:1.5rem;">
            <h3 style="font-family:'Press Start 2P';font-size:.5rem;color:var(--neon-cyan);margin-bottom:1rem;">
                <i class="bi bi-arrow-repeat"></i> CAMBIAR ESTADO
            </h3>
            <span class="badge-estado badge-<?= $pedInfo['estado'] ?> d-block text-center mb-3" style="font-size:.5rem;">
                ACTUAL: <?= strtoupper($pedInfo['estado']) ?>
            </span>
            <form method="post">
                <input type="hidden" name="pedido_id" value="<?= $pedInfo['id'] ?>">
                <select name="estado" class="retro-select w-100 mb-2">
                    <?php foreach (['pendiente','procesando','enviado','entregado','cancelado'] as $st): ?>
                        <option value="<?= $st ?>" <?= $pedInfo['estado']===$st?'selected':'' ?>>
                            <?= strtoupper($st) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-neon btn-neon-cyan w-100" style="font-size:.45rem;width:100%;">
                    <i class="bi bi-check-lg"></i> ACTUALIZAR
                </button>
            </form>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ── Orders list ── -->

<!-- Estado filter -->
<div class="d-flex gap-2 flex-wrap mb-4">
    <?php foreach (['','pendiente','procesando','enviado','entregado','cancelado'] as $st): ?>
        <a href="pedidos.php<?= $st ? '?estado='.$st : '' ?>"
           class="filter-btn <?= $filtEstado===$st?'active':'' ?>">
            <?= $st ? strtoupper($st) : 'TODOS' ?>
        </a>
    <?php endforeach; ?>
</div>

<div style="background:var(--card-bg);border:1px solid var(--border);padding:1.5rem;overflow-x:auto;">
    <h2 style="font-family:'Press Start 2P';font-size:.55rem;color:var(--neon-cyan);margin-bottom:1.2rem;">
        <i class="bi bi-cart-check"></i> PEDIDOS (<?= count($pedidos) ?>)
    </h2>
    <?php if (empty($pedidos)): ?>
        <p style="color:var(--text-dim);">No hay pedidos con este filtro.</p>
    <?php else: ?>
    <table class="retro-table">
        <thead>
            <tr><th>#</th><th>FECHA</th><th>CLIENTE</th><th>ITEMS</th><th>TOTAL</th><th>ENTREGA</th><th>ESTADO</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $p): ?>
            <tr>
                <td style="font-family:'Press Start 2P';font-size:.4rem;color:var(--neon-cyan);">#<?= $p['id'] ?></td>
                <td style="color:var(--text-dim);font-size:.9rem;"><?= date('d/m/Y', strtotime($p['fecha_pedido'])) ?></td>
                <td><?= e($p['cliente']) ?></td>
                <td style="font-family:'VT323';font-size:1.1rem;"><?= $p['items'] ?></td>
                <td style="font-family:'VT323';font-size:1.2rem;color:var(--neon-green);"><?= formatPrice($p['total']) ?></td>
                <td>
                    <?= $p['metodo_entrega']==='tienda'
                        ? '<span style="color:var(--neon-cyan)"><i class="bi bi-shop"></i> Tienda</span>'
                        : '<span style="color:var(--neon-yellow)"><i class="bi bi-truck"></i> Domicilio</span>' ?>
                </td>
                <td><span class="badge-estado badge-<?= $p['estado'] ?>"><?= strtoupper($p['estado']) ?></span></td>
                <td>
                    <a href="pedidos.php?id=<?= $p['id'] ?>" class="retro-btn-sm" style="font-size:.35rem;">
                        <i class="bi bi-eye"></i> VER
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
