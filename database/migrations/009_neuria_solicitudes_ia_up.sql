SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS neuria_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    neuria_request_id VARCHAR(64) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'PENDIENTE',
    progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
    requested_title VARCHAR(240) NOT NULL,
    request_payload JSON NOT NULL,
    response_payload JSON NULL,
    error_message VARCHAR(500) NULL,
    dedupe_key CHAR(64) NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    completed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_neuria_requests_remote (neuria_request_id),
    UNIQUE KEY uq_neuria_requests_dedupe (dedupe_key),
    KEY idx_neuria_requests_status (status,updated_at),
    KEY idx_neuria_requests_user (created_by,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE noticias
    ADD COLUMN IF NOT EXISTS neuria_request_id VARCHAR(64) NULL AFTER neuria_uuid,
    ADD COLUMN IF NOT EXISTS estado_revision ENUM('pendiente','revisada','rechazada') NOT NULL DEFAULT 'pendiente' AFTER estado,
    ADD COLUMN IF NOT EXISTS imported_at DATETIME NULL AFTER neuria_importado_at;

SET @idx_request=(SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='noticias' AND INDEX_NAME='idx_noticias_neuria_request');
SET @sql_request=IF(@idx_request=0,'ALTER TABLE noticias ADD KEY idx_noticias_neuria_request (neuria_request_id)','SELECT 1');
PREPARE request_stmt FROM @sql_request;EXECUTE request_stmt;DEALLOCATE PREPARE request_stmt;
