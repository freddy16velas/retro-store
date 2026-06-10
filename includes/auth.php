<?php
// ============================================================
//  RetroVault - Funciones de Autenticación y Sesión
// ============================================================

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['usuario_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
    $stmt->execute([$_SESSION['usuario_id']]);
    return $stmt->fetch() ?: null;
}

// ── Carrito ──────────────────────────────────────────────────

function getCartCount(): int {
    if (!isLoggedIn()) return 0;
    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(cantidad),0) FROM carrito WHERE usuario_id = ?');
    $stmt->execute([$_SESSION['usuario_id']]);
    return (int)$stmt->fetchColumn();
}

function getCartItems(): array {
    if (!isLoggedIn()) return [];
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'SELECT c.id AS carrito_id, c.cantidad,
                p.id, p.nombre, p.precio, p.imagen, p.stock,
                (p.precio * c.cantidad) AS subtotal
         FROM carrito c
         JOIN productos p ON p.id = c.producto_id
         WHERE c.usuario_id = ?'
    );
    $stmt->execute([$_SESSION['usuario_id']]);
    return $stmt->fetchAll();
}

function getCartTotal(): float {
    $items = getCartItems();
    return array_sum(array_column($items, 'subtotal'));
}

// ── Flash messages ───────────────────────────────────────────

function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function showFlash(): void {
    $f = getFlash();
    if (!$f) return;
    $cls = match($f['type']) {
        'success' => 'alert-success',
        'error'   => 'alert-danger',
        'warning' => 'alert-warning',
        default   => 'alert-info',
    };
    echo '<div class="alert ' . $cls . ' alert-dismissible fade show retro-alert" role="alert">'
       . htmlspecialchars($f['msg'])
       . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
       . '</div>';
}

// ── Utilidades ───────────────────────────────────────────────

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function formatPrice(float $p): string {
    return 'Q ' . number_format($p, 2);
}

function generateOrderNumber(): string {
    return 'RV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}
