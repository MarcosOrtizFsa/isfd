SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS integracion_credenciales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    servicio VARCHAR(40) NOT NULL,
    base_url VARCHAR(500) NOT NULL,
    token_cifrado MEDIUMTEXT NULL,
    token_hint VARCHAR(20) NULL,
    timeout_segundos SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    sync_limite SMALLINT UNSIGNED NOT NULL DEFAULT 50,
    activa TINYINT(1) NOT NULL DEFAULT 1,
    verificada_at DATETIME NULL,
    verificacion_datos JSON NULL,
    ultimo_error VARCHAR(500) NULL,
    creado_por BIGINT UNSIGNED NULL,
    actualizado_por BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_integracion_servicio (servicio),
    KEY idx_integracion_activa (activa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
