-- Evita una subconsulta correlacionada que algunas versiones de phpMyAdmin
-- no pueden analizar al mostrar la definición de v_catalogo_ofertas.
CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW v_oferta_convocatorias_priorizadas AS
SELECT c.*,
       ROW_NUMBER() OVER (
           PARTITION BY c.oferta_id
           ORDER BY CASE c.estado WHEN 'abierta' THEN 0 ELSE 1 END,
                    CASE WHEN c.estado='abierta' THEN c.inscripcion_desde END DESC,
                    CASE WHEN c.estado='programada' THEN COALESCE(c.inscripcion_desde,'9999-12-31') END ASC,
                    c.id DESC
       ) AS prioridad
FROM oferta_convocatorias c
WHERE c.estado IN ('abierta','programada');

CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW v_catalogo_ofertas AS
SELECT o.id,o.slug,o.titulo,o.resumen,o.descripcion,o.titulo_otorgado,o.perfil_egresado,o.requisitos_ingreso,
       o.duracion_valor,o.duracion_unidad,o.carga_horaria,o.norma_aprobacion,o.imagen_url,o.destacada,
       o.estado_oferta,o.estado_publicacion,o.publicada_at,o.orden,
       c.id categoria_id,c.nombre categoria_nombre,c.slug categoria_slug,c.ruta categoria_ruta,
       n.codigo nivel_codigo,n.nombre nivel_nombre,
       m.codigo modalidad_codigo,m.nombre modalidad_nombre,
       u.nombre unidad_academica_nombre,
       cv.id convocatoria_id,cv.anio convocatoria_anio,cv.edicion convocatoria_edicion,
       cv.inscripcion_desde,cv.inscripcion_hasta,cv.inicio_cursada,cv.fin_cursada,cv.cupo,
       cv.url_preinscripcion,cv.estado convocatoria_estado
FROM ofertas o
JOIN catalogo_categorias c ON c.id=o.categoria_id
JOIN catalogo_niveles n ON n.id=c.nivel_id
JOIN modalidades m ON m.id=o.modalidad_id
LEFT JOIN unidades_academicas u ON u.id=o.unidad_academica_id
LEFT JOIN v_oferta_convocatorias_priorizadas cv ON cv.oferta_id=o.id AND cv.prioridad=1
WHERE o.estado_publicacion='publicada' AND o.deleted_at IS NULL;
