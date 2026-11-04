-- Ejecutar en la base seleccionada. Compatible con hosting compartido, sin DEFINER.
CREATE TABLE IF NOT EXISTS portal_preinscripciones (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 oferta_id BIGINT UNSIGNED NOT NULL,
 oferta_titulo VARCHAR(200) NOT NULL,
 anio SMALLINT UNSIGNED NOT NULL,
 nombre VARCHAR(100) NOT NULL,
 apellido VARCHAR(100) NOT NULL,
 dni VARCHAR(10) NOT NULL,
 email VARCHAR(190) NOT NULL,
 telefono VARCHAR(20) NOT NULL,
 estado ENUM('pendiente','en_revision','documentacion_pendiente','formalizada','cancelada') NOT NULL DEFAULT 'pendiente',
 ip_hash CHAR(64) NOT NULL,
 consentimiento_at DATETIME NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_preinscripcion_persona (oferta_id,anio,dni),
 KEY idx_preinscripcion_gestion (anio,estado,oferta_id),
 KEY idx_preinscripcion_limite (ip_hash,created_at),
 CONSTRAINT fk_preinscripcion_oferta FOREIGN KEY (oferta_id) REFERENCES ofertas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portal_preinscripcion_historial (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 preinscripcion_id BIGINT UNSIGNED NOT NULL,
 estado VARCHAR(40) NOT NULL,
 nota TEXT NOT NULL,
 usuario_id BIGINT UNSIGNED NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_preinscripcion_historial (preinscripcion_id,id),
 CONSTRAINT fk_preinscripcion_historial FOREIGN KEY (preinscripcion_id) REFERENCES portal_preinscripciones(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
