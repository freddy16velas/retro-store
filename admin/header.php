<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
$adminPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Admin — RetroVault</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323&family=Rajdhani:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/css/retro.css" rel="stylesheet">
    <style>
        body { display: flex; min-height: 100vh; }
        .admin-wrapper { display: flex; width: 100%; }
        .admin-main { flex: 1; padding: 2rem; background: var(--bg); min-height: 100vh; }
        .admin-topbar {
            background: var(--bg2);
            border-bottom: 1px solid var(--border);
            padding: .6rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .sidebar-brand {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: .5rem;
        }
    </style>
</head>
<body>
<div class="scanlines" aria-hidden="true"></div>
<div class="admin-wrapper">

    <!-- ── Sidebar ── -->
    <nav class="admin-sidebar" style="width:220px;flex-shrink:0;">
        <div class="sidebar-brand">
            <a href="<?= SITE_URL ?>/index.php" style="text-decoration:none;">
                <div style="font-family:'Press Start 2P';font-size:.6rem;color:var(--neon-cyan);text-shadow:0 0 8px var(--neon-cyan);">
                    [RETRO<span style="color:var(--neon-pink)">VAULT</span>]
                </div>
            </a>
            <div style="font-family:'Press Start 2P';font-size:.38rem;color:var(--neon-pink);margin-top:.4rem;">
                ADMIN PANEL
            </div>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= $adminPage==='index.php'?'active':'' ?>" href="<?= SITE_URL ?>/admin/index.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $adminPage==='categorias.php'?'active':'' ?>" href="<?= SITE_URL ?>/admin/categorias.php">
                    <i class="bi bi-tags"></i> Categorías
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $adminPage==='productos.php'?'active':'' ?>" href="<?= SITE_URL ?>/admin/productos.php">
                    <i class="bi bi-box-seam"></i> Productos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $adminPage==='pedidos.php'?'active':'' ?>" href="<?= SITE_URL ?>/admin/pedidos.php">
                    <i class="bi bi-cart-check"></i> Pedidos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $adminPage==='inventario.php'?'active':'' ?>" href="<?= SITE_URL ?>/admin/inventario.php">
                    <i class="bi bi-clipboard-data"></i> Inventario
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $adminPage==='contactos.php'?'active':'' ?>" href="<?= SITE_URL ?>/admin/contactos.php">
                    <i class="bi bi-envelope"></i> Contactos
                </a>
            </li>
            <li style="border-top:1px solid var(--border);margin-top:.5rem;padding-top:.5rem;">
                <a class="nav-link" href="<?= SITE_URL ?>/index.php">
                    <i class="bi bi-house-door"></i> Ver Tienda
                </a>
                <a class="nav-link" href="<?= SITE_URL ?>/logout.php" style="color:var(--neon-pink)!important;">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </li>
        </ul>
    </nav>

    <!-- ── Main ── -->
    <div class="admin-main">
        <div class="admin-topbar">
            <span style="font-family:'Press Start 2P';font-size:.5rem;color:var(--neon-cyan);">
                <?= isset($pageTitle) ? e($pageTitle) : 'Dashboard' ?>
            </span>
            <span style="font-family:'VT323';font-size:1.1rem;color:var(--text-dim);">
                <i class="bi bi-person-circle"></i> <?= e($_SESSION['username'] ?? 'admin') ?>
                &nbsp;|&nbsp;
                <?= date('d/m/Y H:i') ?>
            </span>
        </div>

        <!-- Flash -->
        <?php showFlash(); ?>
