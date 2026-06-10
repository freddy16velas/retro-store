<?php
$pageTitle = 'Checkout';
require_once 'includes/auth.php';
requireLogin();

$items = getCartItems();
$total = getCartTotal();

if (empty($items)) {
    header('Location: ' . SITE_URL . '/carrito.php');
    exit;
}

$pdo = getDB();
$step = $_GET['step'] ?? 'resumen'; // resumen | pago | entrega | confirmacion

// ── Process payment ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metodo   = $_POST['metodo']    ?? '';
    $direccion = trim($_POST['direccion'] ?? '');

    if ($metodo === 'tienda' || ($metodo === 'domicilio' && $direccion !== '')) {

        // Create order
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO pedidos (usuario_id, total, metodo_entrega, direccion, estado)
                 VALUES (?, ?, ?, ?, "pendiente")'
            );
            $stmt->execute([
                $_SESSION['usuario_id'],
                $total,
                $metodo,
                $metodo === 'tienda' ? null : $direccion,
            ]);
            $pedidoId = (int)$pdo->lastInsertId();

            // Order details
            $ins = $pdo->prepare(
                'INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio)
                 VALUES (?, ?, ?, ?)'
            );
            foreach ($items as $item) {
                $ins->execute([$pedidoId, $item['id'], $item['cantidad'], $item['precio']]);
                // Reduce stock
                $pdo->prepare('UPDATE productos SET stock = stock - ? WHERE id = ?')
                    ->execute([$item['cantidad'], $item['id']]);
            }

            // Clear cart
            $pdo->prepare('DELETE FROM carrito WHERE usuario_id = ?')
                ->execute([$_SESSION['usuario_id']]);

            $pdo->commit();

            $orderNum = 'RV-' . date('Ymd') . '-' . strtoupper(substr($pedidoId . 'x' . uniqid(), 0, 6));
            header('Location: ' . SITE_URL . '/checkout.php?step=confirmacion&pedido=' . $pedidoId . '&num=' . urlencode($orderNum));
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            setFlash('error', 'Error al procesar el pedido. Intenta de nuevo.');
            header('Location: ' . SITE_URL . '/carrito.php');
            exit;
        }
    } else {
        $errorEntrega = 'Por favor ingresa tu dirección de entrega.';
    }
}

// ── Confirmation page ─────────────────────────────────────────
if ($step === 'confirmacion') {
    $pedidoId = (int)($_GET['pedido'] ?? 0);
    $orderNum = e($_GET['num']    ?? 'RV-ERROR');
    $pedido   = $pdo->prepare('SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?');
    $pedido->execute([$pedidoId, $_SESSION['usuario_id']]);
    $pedido   = $pedido->fetch();
}

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container position-relative" style="z-index:1;">
        <span class="section-eyebrow text-neon-green">▶ FINALIZAR COMPRA</span>
        <h1 class="section-title mt-2">CHECKOUT <span class="hl"></span></h1>
    </div>
</div>

<div class="container py-5">

<?php if ($step === 'confirmacion' && $pedido): ?>
    <!-- ── Order success ── -->
    <div class="payment-box">
        <span class="payment-success-icon">
            <i class="bi bi-patch-check-fill"></i>
        </span>
        <h2 style="font-family:'Press Start 2P';font-size:.9rem;color:var(--neon-green);text-shadow:0 0 12px var(--neon-green);">
            ¡PAGO EXITOSO!
        </h2>
        <p style="color:var(--text-dim);font-family:'VT323';font-size:1.4rem;margin:1rem 0;">
            Tu pedido ha sido registrado correctamente
        </p>
        <div class="order-number">
            # <?= $orderNum ?>
        </div>
        <div class="mt-3" style="font-family:'VT323';font-size:1.3rem;color:var(--text-dim);">
            <strong style="color:#fff">Total pagado:</strong>
            <span style="color:var(--neon-green)"><?= formatPrice($pedido['total']) ?></span>
        </div>
        <div class="mt-2 p-3" style="background:var(--bg3);border:1px solid var(--border);text-align:left;">
            <?php if ($pedido['metodo_entrega'] === 'tienda'): ?>
                <p style="color:var(--neon-cyan);font-family:'Press Start 2P';font-size:.5rem;margin:0 0 .5rem;">
                    <i class="bi bi-shop"></i> RECOGER EN TIENDA
                </p>
                <p style="color:var(--text-dim);font-family:'VT323';font-size:1.2rem;margin:0;">
                    Ciudad de Guatemala, Guatemala<br>
                    Lunes a Viernes · 1:00 PM – 5:00 PM
                </p>
            <?php else: ?>
                <p style="color:var(--neon-cyan);font-family:'Press Start 2P';font-size:.5rem;margin:0 0 .5rem;">
                    <i class="bi bi-truck"></i> ENTREGA A DOMICILIO
                </p>
                <p style="color:var(--text-dim);font-family:'VT323';font-size:1.2rem;margin:0;">
                    <?= e($pedido['direccion']) ?>
                </p>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-3 justify-content-center mt-4">
            <a href="pedidos.php" class="btn-neon"><i class="bi bi-box-seam"></i> MIS PEDIDOS</a>
            <a href="tienda.php" class="btn-neon btn-neon-pink"><i class="bi bi-shop"></i> SEGUIR COMPRANDO</a>
        </div>
    </div>

<?php else: ?>
    <!-- ── Checkout form ── -->
    <div class="row g-4">
        <!-- Left: delivery form -->
        <div class="col-lg-7">
            <div style="background:var(--card-bg);border:1px solid var(--border);padding:2rem;">
                <h2 style="font-family:'Press Start 2P';font-size:.7rem;color:var(--neon-cyan);margin-bottom:1.5rem;">
                    <i class="bi bi-truck"></i> MÉTODO DE ENTREGA
                </h2>

                <?php if (!empty($errorEntrega)): ?>
                    <div class="alert alert-danger retro-alert mb-3"><?= e($errorEntrega) ?></div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <div class="mb-4">
                        <label class="retro-label">SELECCIONA CÓMO RECIBIRÁS TU PEDIDO</label>
                        <div class="row g-3 mt-1">
                            <div class="col-6">
                                <label style="cursor:pointer;">
                                    <input type="radio" name="metodo" value="tienda" id="metTienda"
                                           onchange="toggleDireccion()" checked style="display:none;">
                                    <div class="p-3 text-center" id="cardTienda"
                                         style="border:2px solid var(--neon-cyan);transition:.2s;background:rgba(0,245,255,.05)">
                                        <i class="bi bi-shop" style="font-size:2rem;color:var(--neon-cyan);"></i>
                                        <div style="font-family:'Press Start 2P';font-size:.5rem;color:var(--neon-cyan);margin-top:.5rem;">RECOGER</div>
                                        <div style="font-family:'Press Start 2P';font-size:.45rem;color:var(--neon-cyan);">EN TIENDA</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6">
                                <label style="cursor:pointer;">
                                    <input type="radio" name="metodo" value="domicilio" id="metDomicilio"
                                           onchange="toggleDireccion()" style="display:none;">
                                    <div class="p-3 text-center" id="cardDomicilio"
                                         style="border:2px solid var(--border);transition:.2s;">
                                        <i class="bi bi-truck" style="font-size:2rem;color:var(--text-dim);"></i>
                                        <div style="font-family:'Press Start 2P';font-size:.5rem;color:var(--text-dim);margin-top:.5rem;">ENTREGA A</div>
                                        <div style="font-family:'Press Start 2P';font-size:.45rem;color:var(--text-dim);">DOMICILIO</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Tienda info -->
                    <div id="tiendaInfo" class="mb-4 p-3"
                         style="background:var(--bg3);border-left:3px solid var(--neon-cyan);">
                        <p style="font-family:'VT323';font-size:1.3rem;color:var(--text-dim);margin:0;">
                            <i class="bi bi-geo-alt text-neon-cyan"></i> Ciudad de Guatemala, Guatemala<br>
                            <i class="bi bi-clock text-neon-cyan"></i> Lunes a Viernes · 1:00 PM – 5:00 PM
                        </p>
                    </div>

                    <!-- Address -->
                    <div id="domicilioInfo" class="mb-4" style="display:none;">
                        <div class="retro-form-group">
                            <label class="retro-label" for="direccion">
                                <i class="bi bi-pin-map"></i> DIRECCIÓN DE ENTREGA
                            </label>
                            <textarea name="direccion" id="direccion" class="retro-textarea"
                                      placeholder="Zona, colonia, calle, número de casa…"
                                      rows="3"></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn-neon btn-neon-green w-100" style="width:100%;font-size:.55rem;">
                        <i class="bi bi-credit-card"></i> CONFIRMAR Y PAGAR
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: order summary -->
        <div class="col-lg-5">
            <div style="background:var(--card-bg);border:1px solid var(--border);padding:2rem;">
                <h2 style="font-family:'Press Start 2P';font-size:.65rem;color:var(--neon-pink);margin-bottom:1.5rem;">
                    <i class="bi bi-receipt"></i> RESUMEN DEL PEDIDO
                </h2>
                <?php foreach ($items as $item): ?>
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2"
                     style="border-bottom:1px solid var(--border);">
                    <div>
                        <div style="color:#fff;font-size:.95rem;font-weight:600;"><?= e($item['nombre']) ?></div>
                        <div style="font-family:'VT323';font-size:1rem;color:var(--text-dim);">
                            x<?= $item['cantidad'] ?> × <?= formatPrice($item['precio']) ?>
                        </div>
                    </div>
                    <div style="font-family:'VT323';font-size:1.2rem;color:var(--neon-yellow);">
                        <?= formatPrice($item['subtotal']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2"
                     style="border-top:2px solid var(--neon-green);">
                    <span style="font-family:'Press Start 2P';font-size:.5rem;color:var(--text-dim);">TOTAL</span>
                    <span style="font-family:'VT323';font-size:2rem;color:var(--neon-green);text-shadow:0 0 8px var(--neon-green);">
                        <?= formatPrice($total) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
<script>
function toggleDireccion() {
    const tienda    = document.getElementById('metTienda').checked;
    const cardT     = document.getElementById('cardTienda');
    const cardD     = document.getElementById('cardDomicilio');
    const infoT     = document.getElementById('tiendaInfo');
    const infoD     = document.getElementById('domicilioInfo');
    const neonCyan  = 'var(--neon-cyan)';
    const borderDim = 'var(--border)';

    if (tienda) {
        cardT.style.borderColor = neonCyan;
        cardT.style.background  = 'rgba(0,245,255,.05)';
        cardD.style.borderColor = borderDim;
        cardD.style.background  = 'none';
        infoT.style.display = '';
        infoD.style.display = 'none';
    } else {
        cardD.style.borderColor = neonCyan;
        cardD.style.background  = 'rgba(0,245,255,.05)';
        cardT.style.borderColor = borderDim;
        cardT.style.background  = 'none';
        infoT.style.display = 'none';
        infoD.style.display = '';
    }
}
document.querySelectorAll('input[name="metodo"]').forEach(r => r.addEventListener('change', toggleDireccion));
</script>
