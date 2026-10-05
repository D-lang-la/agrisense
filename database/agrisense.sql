-- =========================================================
-- AGRISENSE - Base de Datos
-- Sistema Web Inteligente para Monitoreo de Cultivos
-- =========================================================

CREATE DATABASE IF NOT EXISTS agrisense
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE agrisense;

-- ---------------------------------------------------------
-- Tabla: usuarios
-- ---------------------------------------------------------
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL,
    apellido VARCHAR(60) NOT NULL,
    correo VARCHAR(120) NOT NULL UNIQUE,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    cultivo_activo_id INT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabla: cultivos
-- ---------------------------------------------------------
CREATE TABLE cultivos (
    id_cultivo INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(80) NOT NULL,
    temperatura_min DECIMAL(5,2) NOT NULL,
    temperatura_max DECIMAL(5,2) NOT NULL,
    humedad_aire_min DECIMAL(5,2) NOT NULL,
    humedad_aire_max DECIMAL(5,2) NOT NULL,
    humedad_suelo_min DECIMAL(6,2) NOT NULL,
    humedad_suelo_max DECIMAL(6,2) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cultivo_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Ahora que 'cultivos' existe, agregamos la FK de cultivo activo en usuarios
ALTER TABLE usuarios
    ADD CONSTRAINT fk_usuario_cultivo_activo FOREIGN KEY (cultivo_activo_id)
        REFERENCES cultivos(id_cultivo) ON DELETE SET NULL;

-- ---------------------------------------------------------
-- Tabla: mediciones
-- ---------------------------------------------------------
CREATE TABLE mediciones (
    id_medicion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_cultivo INT NOT NULL,
    temperatura DECIMAL(5,2) NOT NULL,
    humedad_aire DECIMAL(5,2) NOT NULL,
    humedad_suelo DECIMAL(6,2) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_medicion_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_medicion_cultivo FOREIGN KEY (id_cultivo)
        REFERENCES cultivos(id_cultivo) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabla: recomendaciones
-- ---------------------------------------------------------
CREATE TABLE recomendaciones (
    id_recomendacion INT AUTO_INCREMENT PRIMARY KEY,
    id_medicion INT NOT NULL,
    estado ENUM('Óptimo','Atención','Crítico') NOT NULL,
    mensaje TEXT NOT NULL,
    CONSTRAINT fk_recomendacion_medicion FOREIGN KEY (id_medicion)
        REFERENCES mediciones(id_medicion) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Índices adicionales para búsquedas del historial
-- ---------------------------------------------------------
CREATE INDEX idx_mediciones_fecha ON mediciones(fecha);
CREATE INDEX idx_mediciones_usuario ON mediciones(id_usuario);
CREATE INDEX idx_mediciones_cultivo ON mediciones(id_cultivo);

-- ---------------------------------------------------------
-- Datos de ejemplo (opcional, se puede borrar)
-- Usuario: admin / Contraseña: admin1234
-- Hash generado con password_hash('admin1234', PASSWORD_DEFAULT)
-- ---------------------------------------------------------
INSERT INTO usuarios (nombre, apellido, correo, usuario, contraseña) VALUES
('Admin', 'AgriSense', 'admin@agrisense.com', 'admin',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO cultivos (id_usuario, nombre, temperatura_min, temperatura_max, humedad_aire_min, humedad_aire_max, humedad_suelo_min, humedad_suelo_max) VALUES
(1, 'Tomate', 20.00, 30.00, 60.00, 80.00, 500.00, 750.00),
(1, 'Lechuga', 15.00, 22.00, 50.00, 70.00, 450.00, 700.00),
(1, 'Chile Dulce', 18.00, 28.00, 55.00, 75.00, 480.00, 720.00);

UPDATE usuarios SET cultivo_activo_id = 1 WHERE id_usuario = 1;
