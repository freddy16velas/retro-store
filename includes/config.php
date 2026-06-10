<?php
// ============================================================
//  RetroVault - Configuración de Base de Datos
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'retrovault');
define('DB_USER', 'root');       // Cambia por tu usuario MySQL
define('DB_PASS', '');           // Cambia por tu contraseña MySQL
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'RetroVault');
define('SITE_URL',  'http://localhost/retro-shop');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:monospace;background:#0a0a0a;color:#ff2d78;padding:2rem;">
                 <h2>ERROR DE CONEXIÓN A BASE DE DATOS</h2>
                 <p>' . htmlspecialchars($e->getMessage()) . '</p>
                 <p>Verifica las credenciales en includes/config.php</p>
                 </div>');
        }
    }
    return $pdo;
}
