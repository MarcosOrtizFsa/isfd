-- Una sección tiene revisiones completas e inmutables; sus valores son filas relacionadas.
CREATE TABLE IF NOT EXISTS institucional_secciones (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 clave VARCHAR(40) NOT NULL UNIQUE,
 version_actual INT UNSIGNED NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS institucional_versiones (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 seccion_id INT UNSIGNED NOT NULL,
 anio SMALLINT UNSIGNED NOT NULL,
 usuario_id INT NULL,
 motivo VARCHAR(250) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_version_seccion (seccion_id,id),
 CONSTRAINT fk_institucional_seccion FOREIGN KEY (seccion_id) REFERENCES institucional_secciones(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS institucional_valores (
 version_id INT UNSIGNED NOT NULL,
 campo VARCHAR(60) NOT NULL,
 valor TEXT NOT NULL,
 PRIMARY KEY (version_id,campo),
 CONSTRAINT fk_institucional_version FOREIGN KEY (version_id) REFERENCES institucional_versiones(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- version_actual se actualiza bajo bloqueo y solo a una revisión de su propia sección.
INSERT IGNORE INTO institucional_secciones (clave) VALUES ('identidad'),('contacto'),('horarios'),('historia'),('autoridades'),('portada'),('recursos');
