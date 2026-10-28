DROP TABLE IF EXISTS neuria_sincronizaciones;
ALTER TABLE noticias DROP INDEX uq_noticias_neuria_uuid;
ALTER TABLE noticias
    DROP COLUMN neuria_sincronizado_at,DROP COLUMN neuria_importado_at,DROP COLUMN neuria_original,
    DROP COLUMN neuria_disponible_at,DROP COLUMN neuria_generado_at,DROP COLUMN neuria_canal,
    DROP COLUMN neuria_fuentes,DROP COLUMN neuria_etiquetas,DROP COLUMN neuria_categoria,
    DROP COLUMN neuria_contenido,DROP COLUMN neuria_bajada,DROP COLUMN neuria_titulo,
    DROP COLUMN neuria_bloqueada,DROP COLUMN neuria_estado,DROP COLUMN neuria_uuid;
