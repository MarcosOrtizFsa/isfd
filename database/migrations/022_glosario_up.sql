-- Ejecutar en la base seleccionada. Conserva términos existentes.
CREATE TABLE IF NOT EXISTS institucional_glosario (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 palabra VARCHAR(150) NOT NULL,
 descripcion TEXT NOT NULL,
 visible TINYINT UNSIGNED NOT NULL DEFAULT 1,
 revision INT UNSIGNED NOT NULL DEFAULT 1,
 deleted_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 KEY idx_glosario_publico (visible,deleted_at,palabra)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
