<?php
require_once __DIR__ . '/auth.php';
$cartCount = getCartCount();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>RetroVault</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323:wght@400&family=Rajdhani:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/css/retro.css" rel="stylesheet">
</head>
<body>

<!-- Scanlines overlay -->
<div class="scanlines" aria-hidden="true"></div>

<!-- Topbar -->
<div class="topbar">
    <span class="topbar-ticker">
        ★ ENVÍOS A TODO GUATEMALA ★ &nbsp;&nbsp;
        ★ VINILOS DE 180G EN STOCK ★ &nbsp;&nbsp;
        ★ RETROVAULT — EL PASADO NUNCA MUERE ★ &nbsp;&nbsp;
        ★ ENVÍOS A TODO GUATEMALA ★ &nbsp;&nbsp;
        ★ VINILOS DE 180G EN STOCK ★ &nbsp;&nbsp;
    </span>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg retro-navbar">
    <div class="container">
        <a class="navbar-brand retro-logo" href="<?= SITE_URL ?>/index.php">
            <span class="logo-bracket">[</span>
            RETRO<span class="logo-accent">VAULT</span>
            <span class="logo-bracket">]</span>
        </a>
        <button class="navbar-toggler retro-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <i class="bi bi-grid-3x3"></i>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto gap-2">
                <li class="nav-item">
                    <a class="nav-link retro-nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/index.php">
                        <i class="bi bi-house-door"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link retro-nav-link <?= $currentPage === 'tienda.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/tienda.php">
                        <i class="bi bi-shop"></i> Tienda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link retro-nav-link <?= $currentPage === 'contacto.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/contacto.php">
                        <i class="bi bi-envelope"></i> Contacto
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <!-- Carrito -->
                <a href="<?= SITE_URL ?>/carrito.php" class="retro-cart-btn">
                    <i class="bi bi-cart3"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
                <!-- Usuario -->
                <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="retro-user-btn dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?= e($_SESSION['username'] ?? 'Usuario') ?>
                        </button>
                        <ul class="dropdown-menu retro-dropdown">
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>/pedidos.php"><i class="bi bi-box-seam"></i> Mis Pedidos</a></li>
                            <?php if (isAdmin()): ?>
                                <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/index.php"><i class="bi bi-shield-lock"></i> Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider" style="border-color:#333"></li>
                            <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/logout.php"><i class="bi bi-box-arrow-right"></i> Salir</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/login.php" class="retro-btn-sm">
                        <i class="bi bi-person"></i> Ingresar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Flash messages -->
<div class="container mt-2" id="flash-container">
    <?php showFlash(); ?>
</div>
