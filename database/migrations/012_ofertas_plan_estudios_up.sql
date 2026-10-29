-- Plan de estudios flexible por años, tramos o módulos.
CREATE TABLE IF NOT EXISTS oferta_plan_estudios (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    oferta_id BIGINT UNSIGNED NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    contenido TEXT NOT NULL,
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_plan_oferta_orden (oferta_id, orden, id),
    CONSTRAINT fk_plan_estudios_oferta FOREIGN KEY (oferta_id) REFERENCES ofertas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
