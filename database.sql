-- ============================================================
--  RetroVault - Base de Datos
--  Motor: MySQL 8+
-- ============================================================

CREATE DATABASE IF NOT EXISTS retrovault CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE retrovault;

-- ────────────────────────────────────────────────────────────
-- USUARIOS
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100)  NOT NULL,
    usuario         VARCHAR(50)   NOT NULL UNIQUE,
    password        VARCHAR(255)  NOT NULL,
    rol             ENUM('admin','cliente') NOT NULL DEFAULT 'cliente',
    fecha_creacion  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- CATEGORÍAS
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categorias (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    descripcion TEXT
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- PRODUCTOS
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS productos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id    INT           NOT NULL,
    nombre          VARCHAR(150)  NOT NULL,
    descripcion     TEXT,
    precio          DECIMAL(10,2) NOT NULL,
    stock           INT           NOT NULL DEFAULT 0,
    imagen          VARCHAR(255),
    fecha_creacion  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- CARRITO
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS carrito (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad    INT NOT NULL DEFAULT 1,
    FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    UNIQUE KEY uq_usuario_producto (usuario_id, producto_id)
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- PEDIDOS
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS pedidos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT             NOT NULL,
    total           DECIMAL(10,2)   NOT NULL,
    metodo_entrega  ENUM('tienda','domicilio') NOT NULL,
    direccion       VARCHAR(255),
    estado          ENUM('pendiente','procesando','enviado','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
    fecha_pedido    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- DETALLE DE PEDIDO
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS detalle_pedido (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id   INT           NOT NULL,
    producto_id INT           NOT NULL,
    cantidad    INT           NOT NULL,
    precio      DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id)   REFERENCES pedidos(id)   ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- CONTACTO
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS contacto (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    apellido    VARCHAR(100) NOT NULL,
    correo      VARCHAR(150) NOT NULL,
    mensaje     TEXT         NOT NULL,
    fecha_envio DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- DATOS INICIALES
-- ============================================================

-- Usuarios (passwords hasheados con password_hash en PHP; aquí usamos hash de '1234')
INSERT INTO usuarios (nombre, usuario, password, rol) VALUES
('Administrador', 'admin',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Freddy',        'freddy', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente');
-- NOTA: el hash de arriba es para 'password' del package de Laravel/bcrypt.
-- Para producción, hashear '1234' con password_hash('1234', PASSWORD_BCRYPT)
-- Ejecuta en PHP: echo password_hash('1234', PASSWORD_BCRYPT);
-- y reemplaza los hashes arriba.

-- Categorías
INSERT INTO categorias (nombre, descripcion) VALUES
('Videojuegos', 'Clásicos de los 80s y 90s que definieron una generación'),
('Libros',       'Literatura icónica de ciencia ficción, fantasía y terror'),
('CDs',          'Álbumes que marcaron épocas en formato disco compacto'),
('Vinilos',      'El formato más puro del sonido analógico');

-- Productos: Videojuegos (categoria_id = 1)
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, imagen) VALUES
(1, 'Super Mario Bros',       'El plomero más famoso del mundo en su aventura clásica para NES. Salva a la Princesa Peach del malvado Bowser.',              149.99, 15, 'mario.jpg'),
(1, 'Sonic The Hedgehog',     'El erizo azul de SEGA a máxima velocidad. Detén al Dr. Robotnik en este clásico de Genesis.',                              129.99, 12, 'sonic.jpg'),
(1, 'The Legend of Zelda',    'Link inicia su épica travesía en Hyrule. Aventura de acción y exploración para NES.',                                       179.99, 8,  'zelda.jpg'),
(1, 'Street Fighter II',      'El rey de los juegos de pelea. Elige tu luchador y domina el Hadouken en SNES.',                                            139.99, 10, 'sf2.jpg'),
(1, 'Donkey Kong Country',    'El gorila Kong regresa con gráficos revolucionarios y jugabilidad adictiva para SNES.',                                      119.99, 9,  'dkc.jpg');

-- Productos: Libros (categoria_id = 2)
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, imagen) VALUES
(2, 'El Señor de los Anillos', 'La obra maestra de Tolkien. Un hobbit y sus compañeros emprenden la misión de destruir el Anillo Único.',                  89.99, 20, 'lotr.jpg'),
(2, '1984',                    'La distopía definitiva de George Orwell. Gran Hermano te vigila en un futuro donde el pensamiento es crimen.',              59.99, 25, '1984.jpg'),
(2, 'Dune',                    'La épica galáctica de Frank Herbert. Control del especio más valioso del universo en el planeta Arrakis.',                  79.99, 18, 'dune.jpg'),
(2, 'Fundación',               'Isaac Asimov y el declive del Imperio Galáctico. Ciencia ficción en su máxima expresión.',                                  69.99, 22, 'foundation.jpg'),
(2, 'Drácula',                 'El conde inmortal de Bram Stoker. Terror gótico victoriano que continúa escalofriando generaciones.',                       49.99, 30, 'dracula.jpg');

-- Productos: CDs (categoria_id = 3)
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, imagen) VALUES
(3, 'Thriller',       'El álbum más vendido de la historia por Michael Jackson. Incluye Thriller, Beat It y Billie Jean.',    99.99, 14, 'thriller.jpg'),
(3, 'Back in Black',  'AC/DC en su cúspide. Uno de los discos de rock más vendidos de todos los tiempos.',                   89.99, 11, 'backinblack.jpg'),
(3, 'The Wall',       'Pink Floyd y su ópera rock conceptual sobre aislamiento, trauma y redención.',                         109.99, 7,  'thewall.jpg'),
(3, 'Nevermind',      'Nirvana y el grunge que cambió la música. Smells Like Teen Spirit definió una generación.',             94.99, 13, 'nevermind.jpg'),
(3, 'Hybrid Theory',  'El álbum debut de Linkin Park que fusionó rock, metal y hip-hop para siempre.',                        84.99, 16, 'hybridtheory.jpg');

-- Productos: Vinilos (categoria_id = 4)
INSERT INTO productos (categoria_id, nombre, descripcion, precio, stock, imagen) VALUES
(4, 'Abbey Road',             'El último álbum grabado por The Beatles. Una joya del rock con el famoso cruce de cebra.',     199.99, 6,  'abbeyroad.jpg'),
(4, 'Dark Side of the Moon',  'Pink Floyd en vinilo de 180g. Uno de los álbumes más importantes de la historia del rock.',   219.99, 5,  'darkside.jpg'),
(4, 'Rumours',                'Fleetwood Mac y su obra maestra grabada en los 70s. Dreams, Go Your Own Way y más.',           189.99, 8,  'rumours.jpg'),
(4, 'Hotel California',       'Eagles y la esencia del rock californiano. Guitarra, ambición y misterio en vinilo.',          194.99, 7,  'hotelcalifornia.jpg'),
(4, 'Purple Rain',            'Prince en su momento más brillante. Soundtrack de la película que lo catapultó al estrellato.',209.99, 4,  'purplerain.jpg');
