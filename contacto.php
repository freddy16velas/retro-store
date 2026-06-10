<?php
$pageTitle = 'Contacto';
require_once 'includes/auth.php';

$success = false;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']   ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $correo   = trim($_POST['correo']   ?? '');
    $mensaje  = trim($_POST['mensaje']  ?? '');

    if ($nombre   === '') $errors[] = 'El nombre es obligatorio.';
    if ($apellido === '') $errors[] = 'El apellido es obligatorio.';
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo electrónico inválido.';
    if (mb_strlen($mensaje) < 10) $errors[] = 'El mensaje debe tener al menos 10 caracteres.';

    if (empty($errors)) {
        $pdo = getDB();
        $pdo->prepare(
            'INSERT INTO contacto (nombre, apellido, correo, mensaje) VALUES (?, ?, ?, ?)'
        )->execute([$nombre, $apellido, $correo, $mensaje]);
        $success = true;
    }
}

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container position-relative" style="z-index:1;">
        <span class="section-eyebrow text-neon-green">▶ ESCRÍBENOS</span>
        <h1 class="section-title mt-2">CONTÁCTANOS <span class="hl"></span></h1>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">

        <!-- ── Form ── -->
        <div class="col-lg-7">
            <?php if ($success): ?>
                <div class="p-5 text-center" style="background:var(--card-bg);border:2px solid var(--neon-green);box-shadow:0 0 20px rgba(57,255,20,.2);">
                    <i class="bi bi-envelope-check" style="font-size:3rem;color:var(--neon-green);text-shadow:0 0 12px var(--neon-green);"></i>
                    <h3 style="font-family:'Press Start 2P';font-size:.7rem;color:var(--neon-green);margin:1rem 0;">
                        ¡MENSAJE ENVIADO!
                    </h3>
                    <p style="color:var(--text-dim);font-family:'VT323';font-size:1.3rem;">
                        Nos pondremos en contacto contigo pronto.
                    </p>
                    <a href="contacto.php" class="btn-neon mt-3 d-inline-block">
                        <i class="bi bi-arrow-left"></i> VOLVER
                    </a>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger retro-alert mb-3">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div style="background:var(--card-bg);border:1px solid var(--border);padding:2rem;">
                    <h2 style="font-family:'Press Start 2P';font-size:.65rem;color:var(--neon-pink);margin-bottom:1.5rem;">
                        <i class="bi bi-envelope"></i> FORMULARIO DE CONTACTO
                    </h2>
                    <form method="post" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="retro-form-group">
                                    <label class="retro-label" for="nombre">NOMBRE</label>
                                    <input type="text" id="nombre" name="nombre" class="retro-input"
                                           placeholder="Tu nombre"
                                           value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="retro-form-group">
                                    <label class="retro-label" for="apellido">APELLIDO</label>
                                    <input type="text" id="apellido" name="apellido" class="retro-input"
                                           placeholder="Tu apellido"
                                           value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="retro-form-group">
                            <label class="retro-label" for="correo">CORREO ELECTRÓNICO</label>
                            <input type="email" id="correo" name="correo" class="retro-input"
                                   placeholder="correo@ejemplo.com"
                                   value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required>
                        </div>
                        <div class="retro-form-group">
                            <label class="retro-label" for="mensaje">MENSAJE</label>
                            <textarea id="mensaje" name="mensaje" class="retro-textarea"
                                      placeholder="Escribe tu mensaje aquí..." rows="5" required><?= htmlspecialchars($_POST['mensaje'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn-neon btn-neon-pink w-100" style="width:100%;">
                            <i class="bi bi-send"></i> ENVIAR MENSAJE
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Info ── -->
        <div class="col-lg-5">
            <div style="background:var(--card-bg);border:1px solid var(--border);padding:2rem;margin-bottom:1.5rem;">
                <h3 style="font-family:'Press Start 2P';font-size:.6rem;color:var(--neon-cyan);margin-bottom:1.5rem;">
                    <i class="bi bi-info-circle"></i> INFORMACIÓN
                </h3>
                <div class="d-flex gap-3 mb-3">
                    <i class="bi bi-geo-alt text-neon-cyan" style="font-size:1.5rem;flex-shrink:0;"></i>
                    <div>
                        <div style="font-family:'Press Start 2P';font-size:.45rem;color:#fff;margin-bottom:.3rem;">UBICACIÓN</div>
                        <div style="color:var(--text-dim);">Ciudad de Guatemala, Guatemala</div>
                    </div>
                </div>
                <div class="d-flex gap-3 mb-3">
                    <i class="bi bi-clock text-neon-green" style="font-size:1.5rem;flex-shrink:0;"></i>
                    <div>
                        <div style="font-family:'Press Start 2P';font-size:.45rem;color:#fff;margin-bottom:.3rem;">HORARIO</div>
                        <div style="color:var(--text-dim);">Lunes a Viernes<br>1:00 PM – 5:00 PM</div>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <i class="bi bi-shop text-neon-yellow" style="font-size:1.5rem;flex-shrink:0;"></i>
                    <div>
                        <div style="font-family:'Press Start 2P';font-size:.45rem;color:#fff;margin-bottom:.3rem;">RETIRO EN TIENDA</div>
                        <div style="color:var(--text-dim);">Disponible en los horarios de atención</div>
                    </div>
                </div>
            </div>

            <div style="background:var(--card-bg);border:1px solid var(--neon-pink);padding:2rem;box-shadow:0 0 15px rgba(255,45,120,.1);">
                <div style="font-family:'Press Start 2P';font-size:.55rem;color:var(--neon-pink);margin-bottom:1rem;">
                    ★ RETROVAULT
                </div>
                <p style="font-family:'VT323';font-size:1.3rem;color:var(--text-dim);line-height:1.6;">
                    Tu tienda de coleccionables retro en Guatemala.<br>
                    Videojuegos, libros, CDs y vinilos de las décadas que definieron la cultura pop.
                </p>
                <div style="font-family:'VT323';font-size:1rem;color:var(--text-dim);margin-top:1rem;">
                    ▓▒░ EL PASADO NUNCA MUERE ░▒▓
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
