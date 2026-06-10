<?php
// ============================================================
//  RetroVault — Cart Actions (AJAX endpoint)
// ============================================================

require_once dirname(__DIR__) . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión', 'redirect' => SITE_URL . '/login.php']);
    exit;
}

$action = $_POST['action'] ?? '';
$uid    = (int)$_SESSION['usuario_id'];
$pdo    = getDB();

function cartCount(PDO $pdo, int $uid): int {
    $s = $pdo->prepare('SELECT COALESCE(SUM(cantidad),0) FROM carrito WHERE usuario_id = ?');
    $s->execute([$uid]);
    return (int)$s->fetchColumn();
}

function cartTotal(PDO $pdo, int $uid): float {
    $s = $pdo->prepare(
        'SELECT COALESCE(SUM(p.precio * c.cantidad),0)
         FROM carrito c JOIN productos p ON p.id = c.producto_id
         WHERE c.usuario_id = ?'
    );
    $s->execute([$uid]);
    return (float)$s->fetchColumn();
}

switch ($action) {

    // ── Add ──────────────────────────────────────────────────
    case 'add':
        $pid = (int)($_POST['producto_id'] ?? 0);
        if ($pid < 1) { echo json_encode(['success'=>false,'message'=>'Producto inválido']); exit; }

        // Check stock
        $s = $pdo->prepare('SELECT stock FROM productos WHERE id = ?');
        $s->execute([$pid]);
        $stock = (int)($s->fetchColumn() ?: 0);

        // Current qty in cart
        $s = $pdo->prepare('SELECT cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?');
        $s->execute([$uid, $pid]);
        $inCart = (int)($s->fetchColumn() ?: 0);

        if ($inCart >= $stock) {
            echo json_encode(['success'=>false,'message'=>'Stock máximo alcanzado']);
            exit;
        }

        // Upsert
        $pdo->prepare(
            'INSERT INTO carrito (usuario_id, producto_id, cantidad)
             VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE cantidad = cantidad + 1'
        )->execute([$uid, $pid]);

        echo json_encode([
            'success'    => true,
            'message'    => 'Producto agregado al carrito',
            'cart_count' => cartCount($pdo, $uid),
        ]);
        break;

    // ── Update ───────────────────────────────────────────────
    case 'update':
        $cid = (int)($_POST['carrito_id'] ?? 0);
        $qty = (int)($_POST['cantidad']   ?? 0);

        if ($qty < 1) {
            $pdo->prepare('DELETE FROM carrito WHERE id = ? AND usuario_id = ?')->execute([$cid, $uid]);
        } else {
            // Verify stock
            $s = $pdo->prepare('SELECT p.stock FROM carrito c JOIN productos p ON p.id=c.producto_id WHERE c.id=? AND c.usuario_id=?');
            $s->execute([$cid, $uid]);
            $stock = (int)($s->fetchColumn() ?: 0);
            $qty   = min($qty, $stock);
            $pdo->prepare('UPDATE carrito SET cantidad = ? WHERE id = ? AND usuario_id = ?')->execute([$qty, $cid, $uid]);
        }

        // Get row subtotal
        $s = $pdo->prepare('SELECT p.precio * c.cantidad FROM carrito c JOIN productos p ON p.id=c.producto_id WHERE c.id=? AND c.usuario_id=?');
        $s->execute([$cid, $uid]);
        $subtotal = (float)($s->fetchColumn() ?: 0);

        echo json_encode([
            'success'    => true,
            'carrito_id' => $cid,
            'subtotal'   => $subtotal,
            'total'      => cartTotal($pdo, $uid),
            'cart_count' => cartCount($pdo, $uid),
        ]);
        break;

    // ── Remove ───────────────────────────────────────────────
    case 'remove':
        $cid = (int)($_POST['carrito_id'] ?? 0);
        $pdo->prepare('DELETE FROM carrito WHERE id = ? AND usuario_id = ?')->execute([$cid, $uid]);

        echo json_encode([
            'success'    => true,
            'cart_count' => cartCount($pdo, $uid),
            'total'      => cartTotal($pdo, $uid),
        ]);
        break;

    default:
        echo json_encode(['success'=>false,'message'=>'Acción desconocida']);
}
