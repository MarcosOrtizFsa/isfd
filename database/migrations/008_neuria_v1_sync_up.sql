SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE noticias
    ADD COLUMN IF NOT EXISTS neuria_uuid VARCHAR(64) NULL AFTER neuria_id,
    ADD COLUMN IF NOT EXISTS neuria_estado VARCHAR(40) NULL AFTER origen,
    ADD COLUMN IF NOT EXISTS neuria_bloqueada TINYINT(1) NOT NULL DEFAULT 0 AFTER neuria_estado,
    ADD COLUMN IF NOT EXISTS neuria_titulo VARCHAR(240) NULL AFTER neuria_bloqueada,
    ADD COLUMN IF NOT EXISTS neuria_bajada VARCHAR(600) NULL AFTER neuria_titulo,
    ADD COLUMN IF NOT EXISTS neuria_contenido MEDIUMTEXT NULL AFTER neuria_bajada,
    ADD COLUMN IF NOT EXISTS neuria_categoria VARCHAR(160) NULL AFTER neuria_contenido,
    ADD COLUMN IF NOT EXISTS neuria_etiquetas JSON NULL AFTER neuria_categoria,
    ADD COLUMN IF NOT EXISTS neuria_fuentes JSON NULL AFTER neuria_etiquetas,
    ADD COLUMN IF NOT EXISTS neuria_canal VARCHAR(160) NULL AFTER neuria_fuentes,
    ADD COLUMN IF NOT EXISTS neuria_generado_at DATETIME NULL AFTER neuria_canal,
    ADD COLUMN IF NOT EXISTS neuria_disponible_at DATETIME NULL AFTER neuria_generado_at,
    ADD COLUMN IF NOT EXISTS neuria_original JSON NULL AFTER neuria_disponible_at,
    ADD COLUMN IF NOT EXISTS neuria_importado_at DATETIME NULL AFTER neuria_original,
    ADD COLUMN IF NOT EXISTS neuria_sincronizado_at DATETIME NULL AFTER neuria_importado_at;

SET @uuid_index=(SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='noticias' AND INDEX_NAME='uq_noticias_neuria_uuid');
SET @uuid_sql=IF(@uuid_index=0,'ALTER TABLE noticias ADD UNIQUE KEY uq_noticias_neuria_uuid (neuria_uuid)','SELECT 1');
PREPARE uuid_stmt FROM @uuid_sql;EXECUTE uuid_stmt;DEALLOCATE PREPARE uuid_stmt;

CREATE TABLE IF NOT EXISTS neuria_sincronizaciones (
    flujo ENUM('contenidos','cambios') PRIMARY KEY,
    cursor_valor TEXT NULL,
    ultima_ejecucion_at DATETIME NULL,
    ultimo_exito_at DATETIME NULL,
    ultimo_error VARCHAR(500) NULL,
    recibidos INT UNSIGNED NOT NULL DEFAULT 0,
    importados INT UNSIGNED NOT NULL DEFAULT 0,
    omitidos INT UNSIGNED NOT NULL DEFAULT 0,
    errores INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO neuria_sincronizaciones (flujo) VALUES ('contenidos'),('cambios');
