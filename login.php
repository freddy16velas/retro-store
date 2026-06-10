<?php
$pageTitle = 'Ingresar';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario']  ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($usuario === '' || $password === '') {
        $error = 'Completa todos los campos.';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = ? LIMIT 1');
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['username']   = $user['usuario'];
            $_SESSION['rol']        = $user['rol'];

            setFlash('success', '¡Bienvenido de vuelta, ' . $user['nombre'] . '!');
            header('Location: ' . SITE_URL . '/index.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar | RetroVault</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323&family=Rajdhani:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/css/retro.css" rel="stylesheet">
</head>
<body>
<div class="scanlines" aria-hidden="true"></div>

<div class="container">
    <div class="auth-box">
        <div class="text-center mb-4">
            <a href="<?= SITE_URL ?>/landing.php" style="text-decoration:none;">
                <div class="retro-logo" style="font-size:.8rem;">
                    <span class="logo-bracket">[</span>RETRO<span class="logo-accent">VAULT</span><span class="logo-bracket">]</span>
                </div>
            </a>
        </div>
        <h1 class="auth-title">INICIAR SESIÓN</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger retro-alert mb-3">
                <i class="bi bi-exclamation-triangle"></i> <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="retro-form-group">
                <label class="retro-label" for="usuario"><i class="bi bi-person"></i> USUARIO</label>
                <input type="text" id="usuario" name="usuario" class="retro-input"
                       placeholder="tu_usuario" autocomplete="username"
                       value="<?= e($_POST['usuario'] ?? '') ?>" required>
            </div>
            <div class="retro-form-group">
                <label class="retro-label" for="password"><i class="bi bi-lock"></i> CONTRASEÑA</label>
                <input type="password" id="password" name="password" class="retro-input"
                       placeholder="••••••••" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn-neon w-100 mt-3" style="width:100%;">
                <i class="bi bi-power"></i> ENTRAR
            </button>
        </form>

        <div class="text-center mt-4" style="font-family:'VT323';font-size:1.1rem;color:var(--text-dim);">
            <i class="bi bi-info-circle"></i>
            Usuario: <span style="color:var(--neon-cyan)">freddy</span> |
            Admin: <span style="color:var(--neon-pink)">admin</span><br>
            Contraseña: <span style="color:var(--neon-green)">1234</span>
        </div>

        <div class="text-center mt-3">
            <a href="<?= SITE_URL ?>/landing.php" class="retro-btn-sm">
                <i class="bi bi-arrow-left"></i> Volver al inicio
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
