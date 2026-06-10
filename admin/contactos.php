<?php
$pageTitle = 'Mensajes de Contacto';
require_once __DIR__ . '/header.php';

$pdo = getDB();

// ── Delete ────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $mid = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM contacto WHERE id=?')->execute([$mid]);
    setFlash('success', 'Mensaje eliminado.');
    header('Location: ' . SITE_URL . '/admin/contactos.php');
    exit;
}

// ── View single ───────────────────────────────────────────────
$viewMsg = null;
if (isset($_GET['ver'])) {
    $s = $pdo->prepare('SELECT * FROM contacto WHERE id=?');
    $s->execute([(int)$_GET['ver']]);
    $viewMsg = $s->fetch();
}

$mensajes = $pdo->query(
    'SELECT * FROM contacto ORDER BY fecha_envio DESC'
)->fetchAll();
?>

<?php if ($viewMsg): ?>
<!-- ── Single message ── -->
<div class="mb-3">
    <a href="contactos.php" class="retro-btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
</div>
<div style="background:var(--card-bg);border:1px solid var(--neon-cyan);padding:2rem;max-width:700px;">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h2 style="font-family:'Press Start 2P';font-size:.6rem;color:var(--neon-cyan);">
                <?= e($viewMsg['nombre']) ?> <?= e($viewMsg['apellido']) ?>
            </h2>
            <a href="mailto:<?= e($viewMsg['correo']) ?>"
               style="font-family:'VT323';font-size:1.2rem;color:var(--neon-yellow);text-decoration:none;">
                <?= e($viewMsg['correo']) ?>
            </a>
        </div>
        <div style="font-family:'VT323';font-size:1rem;color:var(--text-dim);">
            <?= date('d/m/Y H:i', strtotime($viewMsg['fecha_envio'])) ?>
        </div>
    </div>
    <hr style="border-color:var(--border);">
    <div style="color:var(--text);line-height:1.7;font-size:1rem;white-space:pre-wrap;"><?= e($viewMsg['mensaje']) ?></div>
    <div class="mt-4">
        <a href="mailto:<?= e($viewMsg['correo']) ?>" class="btn-neon btn-neon-green" style="font-size:.45rem;">
            <i class="bi bi-reply"></i> RESPONDER
        </a>
        <a href="contactos.php?delete=<?= $viewMsg['id'] ?>"
           class="btn-neon btn-neon-pink ms-2" style="font-size:.45rem;"
           onclick="return confirm('¿Eliminar este mensaje?')">
            <i class="bi bi-trash3"></i> ELIMINAR
        </a>
    </div>
</div>

<?php else: ?>
<!-- ── Messages list ── -->
<div style="background:var(--card-bg);border:1px solid var(--border);padding:1.5rem;overflow-x:auto;">
    <h2 style="font-family:'Press Start 2P';font-size:.55rem;color:var(--neon-cyan);margin-bottom:1.2rem;">
        <i class="bi bi-envelope"></i> MENSAJES RECIBIDOS (<?= count($mensajes) ?>)
    </h2>
    <?php if (empty($mensajes)): ?>
        <p style="color:var(--text-dim);">No hay mensajes de contacto.</p>
    <?php else: ?>
    <table class="retro-table">
        <thead>
            <tr><th>FECHA</th><th>NOMBRE</th><th>CORREO</th><th>MENSAJE</th><th>ACCIONES</th></tr>
        </thead>
        <tbody>
            <?php foreach ($mensajes as $m): ?>
            <tr>
                <td style="color:var(--text-dim);font-size:.9rem;white-space:nowrap;">
                    <?= date('d/m/Y H:i', strtotime($m['fecha_envio'])) ?>
                </td>
                <td style="font-weight:600;"><?= e($m['nombre']) ?> <?= e($m['apellido']) ?></td>
                <td style="color:var(--neon-yellow);">
                    <a href="mailto:<?= e($m['correo']) ?>" style="color:inherit;text-decoration:none;">
                        <?= e($m['correo']) ?>
                    </a>
                </td>
                <td style="color:var(--text-dim);max-width:250px;">
                    <?= e(mb_substr($m['mensaje'], 0, 60)) ?>…
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="contactos.php?ver=<?= $m['id'] ?>"
                           class="retro-btn-sm" style="font-size:.35rem;">
                            <i class="bi bi-eye"></i> VER
                        </a>
                        <a href="contactos.php?delete=<?= $m['id'] ?>"
                           class="retro-btn-sm btn-neon-pink" style="font-size:.35rem;"
                           onclick="return confirm('¿Eliminar mensaje?')">
                            <i class="bi bi-trash3"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
