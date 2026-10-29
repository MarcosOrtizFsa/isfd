-- Multimedia administrada por cada oferta académica.
ALTER TABLE ofertas
    ADD COLUMN youtube_codigo VARCHAR(20) NULL AFTER imagen_url;
