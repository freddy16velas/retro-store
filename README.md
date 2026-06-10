# 🕹️ RetroVault — Guía de Instalación

Tienda online retro para LAMP (Linux · Apache · MySQL · PHP 8+)

---

## 📁 Estructura del Proyecto

```
retro-shop/
├── landing.php              ← Página de bienvenida (entry point)
├── index.php                ← Inicio / Home
├── tienda.php               ← Catálogo de productos
├── carrito.php              ← Carrito de compras
├── checkout.php             ← Proceso de pago
├── pedidos.php              ← Historial de pedidos (usuario)
├── login.php                ← Inicio de sesión
├── logout.php               ← Cerrar sesión
├── contacto.php             ← Formulario de contacto
│
├── includes/
│   ├── config.php           ← Configuración DB y constantes
│   ├── auth.php             ← Sesiones, autenticación, helpers
│   ├── header.php           ← Header navbar
│   └── footer.php           ← Footer
│
├── actions/
│   └── cart_action.php      ← Endpoint AJAX para el carrito
│
├── admin/
│   ├── index.php            ← Dashboard admin
│   ├── categorias.php       ← CRUD de categorías
│   ├── productos.php        ← CRUD de productos
│   ├── pedidos.php          ← Gestión de pedidos
│   ├── inventario.php       ← Control de inventario
│   ├── contactos.php        ← Mensajes de contacto
│   ├── header.php           ← Header del panel admin
│   └── footer.php           ← Footer del panel admin
│
├── css/
│   └── retro.css            ← Estilos retro completos
│
├── js/
│   └── retro.js             ← JavaScript (carrito AJAX, filtros)
│
├── images/
│   └── products/            ← Imágenes de productos (subir aquí)
│
└── database.sql             ← Script completo de BD con datos
```

---

## ⚙️ Instalación Paso a Paso

### 1. Copiar archivos al servidor

```bash
# Copiar la carpeta dentro de tu DocumentRoot de Apache
cp -r retro-shop/ /var/www/html/
# o en XAMPP:
cp -r retro-shop/ /opt/lampp/htdocs/
```

### 2. Crear la base de datos

```bash
mysql -u root -p
```

```sql
SOURCE /var/www/html/retro-shop/database.sql;
```

O desde phpMyAdmin: importar el archivo `database.sql`.

### 3. Configurar la conexión

Editar `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'retrovault');
define('DB_USER', 'root');       // tu usuario MySQL
define('DB_PASS', '');           // tu contraseña MySQL
define('SITE_URL', 'http://localhost/retro-shop');
```

### 4. Actualizar contraseñas en la BD

⚠️ El SQL incluye un hash de ejemplo. Para que funcione con `password_verify()` de PHP, ejecuta esto en tu terminal PHP o en un script temporal:

```php
<?php
echo password_hash('1234', PASSWORD_BCRYPT);
```

Luego actualiza en MySQL:
```sql
UPDATE usuarios SET password = 'EL_HASH_GENERADO' WHERE usuario IN ('admin','freddy');
```

**O simplemente ejecuta este script una vez:**

```bash
php -r "
\$hash = password_hash('1234', PASSWORD_BCRYPT);
\$pdo = new PDO('mysql:host=localhost;dbname=retrovault', 'root', '');
\$pdo->prepare('UPDATE usuarios SET password=?')->execute([\$hash]);
echo 'Contraseñas actualizadas: ' . \$hash . PHP_EOL;
"
```

### 5. Permisos de carpeta de imágenes

```bash
chmod 775 /var/www/html/retro-shop/images/products/
chown www-data:www-data /var/www/html/retro-shop/images/products/
```

---

## 🚀 Acceso

| URL                                    | Descripción              |
|----------------------------------------|--------------------------|
| `http://localhost/retro-shop/landing.php` | Landing page            |
| `http://localhost/retro-shop/`            | Inicio / Home            |
| `http://localhost/retro-shop/tienda.php`  | Catálogo                 |
| `http://localhost/retro-shop/login.php`   | Login                    |
| `http://localhost/retro-shop/admin/`      | Panel Administrativo     |

---

## 👤 Credenciales

| Rol     | Usuario  | Contraseña |
|---------|----------|------------|
| Cliente | `freddy` | `1234`     |
| Admin   | `admin`  | `1234`     |

---

## 🧰 Tecnologías

- **PHP 8+** con PDO (protección SQL Injection)
- **MySQL 8** con utf8mb4
- **Apache** (LAMP stack)
- **Bootstrap 5.3** (responsive)
- **Press Start 2P** + **VT323** (fuentes pixel/retro)
- **AJAX** para carrito sin recarga
- **Sesiones PHP** con `session_regenerate_id()`

---

## 🎨 Diseño

Estética **80s/90s arcade**:
- Fondo negro `#0a0a0a` con scanlines animadas
- Neón: rosa `#ff2d78`, cian `#00f5ff`, verde `#39ff14`
- Grid de perspectiva animado en el hero
- Tipografía pixel `Press Start 2P` + monoespaciada `VT323`
- Efecto glitch en hover de títulos
- Ticker de noticias animado en topbar

---

## 📋 Funcionalidades Implementadas

### Cliente
- [x] Landing page con hero, categorías y CTA
- [x] Home con productos destacados y promotions
- [x] Tienda con búsqueda y filtro por categoría
- [x] Carrito AJAX (agregar, eliminar, actualizar cantidad)
- [x] Checkout con simulación de pago
- [x] Método de entrega: tienda o domicilio
- [x] Número de pedido generado automáticamente
- [x] Historial de pedidos con detalle
- [x] Página de contacto con almacenado en BD
- [x] Login / Logout con sesiones seguras

### Admin
- [x] Dashboard con estadísticas en tiempo real
- [x] CRUD completo de categorías
- [x] CRUD completo de productos con subida de imágenes
- [x] Gestión de pedidos con cambio de estado
- [x] Control de inventario con filtros
- [x] Visualización y eliminación de mensajes de contacto

---

## 🔒 Seguridad

- PDO con prepared statements (anti SQL Injection)
- `password_hash()` / `password_verify()` para contraseñas
- `htmlspecialchars()` en toda salida de datos
- `session_regenerate_id()` al iniciar sesión
- Verificación de rol en rutas admin
- Validación server-side en todos los formularios
