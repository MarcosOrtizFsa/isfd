DROP TABLE IF EXISTS neuria_requests;
ALTER TABLE noticias DROP INDEX idx_noticias_neuria_request;
ALTER TABLE noticias DROP COLUMN imported_at,DROP COLUMN estado_revision,DROP COLUMN neuria_request_id;
