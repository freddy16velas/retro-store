<?php
$pageTitle = 'Bienvenido';
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RetroVault — El pasado nunca muere</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323&family=Rajdhani:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/css/retro.css" rel="stylesheet">
</head>
<body>

<div class="scanlines" aria-hidden="true"></div>

<!-- ── Hero Landing ── -->
<section class="hero-section">
    <div class="hero-grid-bg"></div>
    <div class="hero-glow"></div>
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="hero-eyebrow">▶ SISTEMA INICIADO — EST. 1984</span>
                <h1 class="hero-title">
                    <span class="hl-cyan">RETRO</span><span class="hl-pink">VAULT</span><br>
                    <span style="font-size:.6em;color:#ccc;">EL PASADO<br>NUNCA MUERE</span>
                </h1>
                <p class="hero-subtitle">
                    Videojuegos · Libros · CDs · Vinilos<br>
                    Ciudad de Guatemala, Guatemala
                </p>
                <div class="hero-cta-row">
                    <a href="<?= SITE_URL ?>/index.php" class="btn-neon">
                        <i class="bi bi-power"></i> ENTRAR A LA TIENDA
                    </a>
                    <a href="<?= SITE_URL ?>/tienda.php" class="btn-neon btn-neon-pink">
                        <i class="bi bi-shop"></i> VER CATÁLOGO
                    </a>
                </div>
                <div class="hero-stats">
                    <div>
                        <div class="hero-stat-number">20+</div>
                        <div class="hero-stat-label">PRODUCTOS</div>
                    </div>
                    <div>
                        <div class="hero-stat-number">4</div>
                        <div class="hero-stat-label">CATEGORÍAS</div>
                    </div>
                    <div>
                        <div class="hero-stat-number">80s</div>
                        <div class="hero-stat-label">ESTÉTICA</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-center align-items-center">
                <!-- ASCII Art / Decorative -->
                <div style="font-family:'Press Start 2P';font-size:.5rem;color:var(--neon-cyan);line-height:2.2;text-shadow:0 0 10px var(--neon-cyan);opacity:.7;text-align:center;animation:logoFlicker 4s infinite;">
                    ┌──────────────────────┐<br>
                    │  ♪  RETROVAULT  ♪   │<br>
                    │  ┌────────────────┐  │<br>
                    │  │  ▓▓▓▓▓▓▓▓▓▓▓  │  │<br>
                    │  │  ▓ PRESS  ▓  │  │<br>
                    │  │  ▓ START  ▓  │  │<br>
                    │  │  ▓▓▓▓▓▓▓▓▓▓▓  │  │<br>
                    │  └────────────────┘  │<br>
                    │   INSERT COIN →  Q1  │<br>
                    └──────────────────────┘
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Categories ── -->
<section class="py-6" style="padding:5rem 0;background:var(--bg);">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-eyebrow text-neon-green">SELECCIONA TU MUNDO</span>
            <h2 class="section-title">CATEGORÍAS <span class="hl">DISPONIBLES</span></h2>
            <div class="section-line mx-auto"></div>
        </div>
        <div class="row g-3 mt-4">
            <div class="col-6 col-md-3">
                <a href="<?= SITE_URL ?>/tienda.php?categoria=1" class="category-card" style="--accent-color:var(--neon-pink)">
                    <i class="bi bi-joystick category-icon" style="color:var(--neon-pink)"></i>
                    <div class="category-name" style="color:var(--neon-pink)">VIDEOJUEGOS</div>
                    <div class="category-desc">NES · SNES · GENESIS</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= SITE_URL ?>/tienda.php?categoria=2" class="category-card" style="--accent-color:var(--neon-cyan)">
                    <i class="bi bi-book category-icon" style="color:var(--neon-cyan)"></i>
                    <div class="category-name" style="color:var(--neon-cyan)">LIBROS</div>
                    <div class="category-desc">Sci-Fi · Fantasía · Terror</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= SITE_URL ?>/tienda.php?categoria=3" class="category-card" style="--accent-color:var(--neon-yellow)">
                    <i class="bi bi-disc category-icon" style="color:var(--neon-yellow)"></i>
                    <div class="category-name" style="color:var(--neon-yellow)">CDs</div>
                    <div class="category-desc">Rock · Pop · Metal · Grunge</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= SITE_URL ?>/tienda.php?categoria=4" class="category-card" style="--accent-color:var(--neon-purple)">
                    <i class="bi bi-vinyl category-icon" style="color:var(--neon-purple)"></i>
                    <div class="category-name" style="color:var(--neon-purple)">VINILOS</div>
                    <div class="category-desc">180g · Prensado clásico</div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ── CTA ── -->
<section style="padding:5rem 0;background:linear-gradient(135deg,#0a0020,var(--bg));">
    <div class="container text-center">
        <h2 class="section-title mb-3">¿LISTO PARA <span class="hl">EMPEZAR</span>?</h2>
        <p class="text-dim mb-4" style="font-family:'VT323';font-size:1.5rem;color:var(--text-dim)">
            Inicia sesión y accede a todos los productos de la tienda
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?= SITE_URL ?>/login.php"  class="btn-neon"><i class="bi bi-person"></i> INGRESAR</a>
            <a href="<?= SITE_URL ?>/tienda.php" class="btn-neon btn-neon-pink"><i class="bi bi-shop"></i> EXPLORAR</a>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/js/retro.js"></script>
</body>
</html>
