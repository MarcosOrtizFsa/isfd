-- Tarjetas de contacto: QR, chatbot y otros enlaces institucionales.
-- Repetible; ejecutar en la base seleccionada, sin modificar registros existentes.
CREATE TABLE IF NOT EXISTS institucional_recursos_contacto (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tema VARCHAR(160) NOT NULL,
 url VARCHAR(1000) NOT NULL,
 imagen_url VARCHAR(500) NOT NULL,
 orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 visible TINYINT UNSIGNED NOT NULL DEFAULT 1,
 revision INT UNSIGNED NOT NULL DEFAULT 1,
 deleted_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 KEY idx_recursos_contacto_publicos (visible,deleted_at,orden,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
