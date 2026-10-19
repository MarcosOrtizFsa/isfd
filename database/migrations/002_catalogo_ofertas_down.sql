-- Revierte exclusivamente la migración 002. Las consultas vuelven al modelo anterior.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
DROP VIEW IF EXISTS v_catalogo_ofertas;
DROP VIEW IF EXISTS v_oferta_convocatorias_priorizadas;

ALTER TABLE portal_consultas DROP FOREIGN KEY fk_consulta_oferta;
-- Las consultas de ofertas creadas solo en el modelo nuevo no tienen un destino
-- equivalente al volver atrás; se conservan y se desvinculan de la oferta.
UPDATE portal_consultas c
JOIN ofertas nueva ON nueva.id=c.oferta_id
LEFT JOIN portal_ofertas_academicas antigua ON CONCAT('demo-',antigua.slug)=nueva.slug
SET c.oferta_id=NULL
WHERE antigua.id IS NULL;
UPDATE portal_consultas c
JOIN ofertas nueva ON nueva.id=c.oferta_id
JOIN portal_ofertas_academicas antigua ON CONCAT('demo-',antigua.slug)=nueva.slug
SET c.oferta_id=antigua.id;
ALTER TABLE portal_consultas DROP COLUMN updated_at, MODIFY oferta_id INT UNSIGNED NULL;
ALTER TABLE portal_consultas ADD CONSTRAINT fk_consulta_oferta FOREIGN KEY (oferta_id) REFERENCES portal_ofertas_academicas(id) ON DELETE SET NULL;

DROP PROCEDURE IF EXISTS catalogo_mover_categoria;
DROP PROCEDURE IF EXISTS catalogo_crear_categoria;
DROP TRIGGER IF EXISTS trg_ofertas_bu;
DROP TRIGGER IF EXISTS trg_ofertas_bi;
DROP TRIGGER IF EXISTS trg_catalogo_categorias_bu;
DROP TRIGGER IF EXISTS trg_catalogo_categorias_bi;
DROP TABLE IF EXISTS oferta_documentos;
DROP TABLE IF EXISTS oferta_convocatorias;
DROP TABLE IF EXISTS oferta_area;
DROP TABLE IF EXISTS oferta_turno;
DROP TABLE IF EXISTS ofertas;
DROP TABLE IF EXISTS areas_conocimiento;
DROP TABLE IF EXISTS turnos;
DROP TABLE IF EXISTS modalidades;
DROP TABLE IF EXISTS unidades_academicas;
DROP TABLE IF EXISTS catalogo_categorias;
DROP TABLE IF EXISTS catalogo_niveles;
