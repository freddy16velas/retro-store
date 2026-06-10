<?php
$pageTitle = 'Mi Carrito';
require_once 'includes/auth.php';
requireLogin();

$items = getCartItems();
$total = getCartTotal();

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container position-relative" style="z-index:1;">
        <span class="section-eyebrow text-neon-green">▶ MI SELECCIÓN</span>
        <h1 class="section-title mt-2">CARRITO <span class="hl">DE COMPRAS</span></h1>
    </div>
</div>

<div class="container py-5">

<?php if (empty($items)): ?>
    <div class="text-center py-5">
        <i class="bi bi-cart-x" style="font-size:5rem;color:var(--text-dim);"></i>
        <h3 style="font-family:'Press Start 2P';font-size:.8rem;color:var(--text-dim);margin-top:1.5rem;">
            TU CARRITO ESTÁ VACÍO
        </h3>
        <p style="color:var(--text-dim);font-family:'VT323';font-size:1.3rem;">
            Explora nuestra tienda y agrega productos
        </p>
        <a href="tienda.php" class="btn-neon mt-3 d-inline-block">
            <i class="bi bi-shop"></i> IR A LA TIENDA
        </a>
    </div>

<?php else: ?>

    <div class="row g-4">
        <!-- Products table -->
        <div class="col-lg-8">
            <div style="overflow-x:auto;">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>PRODUCTO</th>
                            <th>PRECIO</th>
                            <th>CANTIDAD</th>
                            <th>SUBTOTAL</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr data-row="<?= $item['carrito_id'] ?>">
                            <td data-label="Producto">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width:60px;height:60px;background:var(--bg3);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="bi bi-box" style="color:var(--text-dim);font-size:1.5rem;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight:700;color:#fff;"><?= e($item['nombre']) ?></div>
                                        <div style="font-size:.85rem;color:var(--text-dim);">Stock: <?= $item['stock'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Precio" style="font-family:'VT323';font-size:1.3rem;color:var(--neon-green);">
                                <?= formatPrice($item['precio']) ?>
                            </td>
                            <td data-label="Cantidad">
                                <div class="d-flex align-items-center gap-2">
                                    <button onclick="changeQty(<?= $item['carrito_id'] ?>, -1, this)"
                                            style="background:none;border:1px solid var(--border);color:var(--text);width:28px;height:28px;cursor:pointer;font-family:'Press Start 2P';font-size:.5rem;">−</button>
                                    <input type="number" class="cart-qty-input"
                                           value="<?= $item['cantidad'] ?>"
                                           min="1" max="<?= $item['stock'] ?>"
                                           id="qty-<?= $item['carrito_id'] ?>"
                                           onchange="updateCartQty(<?= $item['carrito_id'] ?>, this.value)">
                                    <button onclick="changeQty(<?= $item['carrito_id'] ?>, 1, this)"
                                            style="background:none;border:1px solid var(--border);color:var(--text);width:28px;height:28px;cursor:pointer;font-family:'Press Start 2P';font-size:.5rem;">+</button>
                                </div>
                            </td>
                            <td data-label="Subtotal"
                                data-subtotal="<?= $item['carrito_id'] ?>"
                                style="font-family:'VT323';font-size:1.3rem;color:var(--neon-yellow);">
                                <?= formatPrice($item['subtotal']) ?>
                            </td>
                            <td>
                                <button onclick="removeFromCart(<?= $item['carrito_id'] ?>)"
                                        style="background:none;border:none;color:var(--neon-pink);cursor:pointer;font-size:1.2rem;"
                                        title="Eliminar">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <a href="tienda.php" class="retro-btn-sm">
                    <i class="bi bi-arrow-left"></i> Seguir comprando
                </a>
            </div>
        </div>

        <!-- Order summary -->
        <div class="col-lg-4">
            <div class="cart-total-box">
                <div class="cart-total-label">TOTAL A PAGAR</div>
                <span class="cart-total-amount"><?= formatPrice($total) ?></span>
                <div style="font-family:'VT323';font-size:1rem;color:var(--text-dim);margin:.5rem 0 1.5rem;">
                    <?= count($items) ?> producto<?= count($items)!==1?'s':'' ?> en el carrito
                </div>
                <a href="checkout.php" class="btn-neon btn-neon-green w-100" style="display:block;text-align:center;">
                    <i class="bi bi-credit-card"></i> PROCEDER AL PAGO
                </a>
                <div style="font-family:'VT323';font-size:1rem;color:var(--text-dim);text-align:center;margin-top:1rem;">
                    <i class="bi bi-shield-check text-neon-green"></i>
                    Pago 100% seguro y simulado
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
<script>
function changeQty(carritoId, delta, btn) {
    const input = document.getElementById('qty-' + carritoId);
    let val = parseInt(input.value) + delta;
    val = Math.max(1, Math.min(val, parseInt(input.max)));
    input.value = val;
    updateCartQty(carritoId, val);
}
</script>
