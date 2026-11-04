SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_ws_tokens (
    token_hash CHAR(64) PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    session_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    valid_until DATETIME NOT NULL,
    consumed TINYINT NOT NULL DEFAULT 0,
    KEY idx_chat_ws_sesion (session_hash),
    KEY idx_chat_ws_expira (valid_until),
    CONSTRAINT fk_chat_ws_usuario FOREIGN KEY (usuario_id) REFERENCES chat_usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_ws_revision (
    id TINYINT UNSIGNED PRIMARY KEY,
    revision BIGINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO chat_ws_revision (id,revision) VALUES (1,0);
