
<footer class="retro-footer mt-5">
    <div class="footer-glitch-bar"></div>
    <div class="container py-4">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="footer-logo mb-2">
                    <span class="logo-bracket">[</span>RETRO<span class="logo-accent">VAULT</span><span class="logo-bracket">]</span>
                </div>
                <p class="footer-tagline">El pasado nunca muere.<br>Solo cambia de estante.</p>
                <div class="mt-3">
                    <span class="pixel-badge">EST. 1984</span>
                    <span class="pixel-badge ms-2">GT</span>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <h6 class="footer-heading">CATEGORÍAS</h6>
                <ul class="footer-links">
                    <li><a href="<?= SITE_URL ?>/tienda.php?categoria=1"><i class="bi bi-joystick"></i> Videojuegos</a></li>
                    <li><a href="<?= SITE_URL ?>/tienda.php?categoria=2"><i class="bi bi-book"></i> Libros</a></li>
                    <li><a href="<?= SITE_URL ?>/tienda.php?categoria=3"><i class="bi bi-disc"></i> CDs</a></li>
                    <li><a href="<?= SITE_URL ?>/tienda.php?categoria=4"><i class="bi bi-vinyl"></i> Vinilos</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h6 class="footer-heading">CONTACTO</h6>
                <ul class="footer-info">
                    <li><i class="bi bi-geo-alt"></i> Ciudad de Guatemala, Guatemala</li>
                    <li><i class="bi bi-clock"></i> Lun–Vie: 1:00 PM – 5:00 PM</li>
                    <li><a href="<?= SITE_URL ?>/contacto.php"><i class="bi bi-envelope"></i> Enviar mensaje</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <?= date('Y') ?> RetroVault — Ciudad de Guatemala</span>
            <span class="footer-pixel-art">▓▒░ POWERED BY LAMP ░▒▓</span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/js/retro.js"></script>
</body>
</html>
