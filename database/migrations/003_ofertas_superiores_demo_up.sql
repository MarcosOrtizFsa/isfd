-- Ofertas superiores de demostración para validar filtros y buscadores.
-- No representan información académica oficial.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO ofertas (categoria_id,unidad_academica_id,modalidad_id,slug,titulo,resumen,descripcion,titulo_otorgado,destacada,estado_oferta,estado_publicacion,publicada_at,orden)
SELECT c.id,u.id,m.id,'demo-profesorado-educacion-secundaria-economia','Profesorado de Educación Secundaria en Economía','Propuesta demostrativa de formación docente en economía.','Contenido DEMO pendiente de validación institucional.','Profesor/a de Educación Secundaria en Economía',0,'a_termino','publicada',NOW(),20
FROM catalogo_categorias c JOIN unidades_academicas u ON u.slug='nivel-superior' JOIN modalidades m ON m.codigo='presencial' WHERE c.slug='profesorados'
ON DUPLICATE KEY UPDATE categoria_id=VALUES(categoria_id),unidad_academica_id=VALUES(unidad_academica_id),modalidad_id=VALUES(modalidad_id),titulo=VALUES(titulo),resumen=VALUES(resumen),descripcion=VALUES(descripcion),titulo_otorgado=VALUES(titulo_otorgado),estado_oferta=VALUES(estado_oferta),estado_publicacion='publicada';

INSERT INTO ofertas (categoria_id,unidad_academica_id,modalidad_id,slug,titulo,resumen,descripcion,titulo_otorgado,destacada,estado_oferta,estado_publicacion,publicada_at,orden)
SELECT c.id,u.id,m.id,'demo-tecnicatura-superior-desarrollo-software','Tecnicatura Superior en Desarrollo de Software','Formación técnica demostrativa orientada al desarrollo de aplicaciones.','Contenido DEMO pendiente de validación institucional.','Técnico/a Superior en Desarrollo de Software',1,'inscripcion_abierta','publicada',NOW(),30
FROM catalogo_categorias c JOIN unidades_academicas u ON u.slug='nivel-superior' JOIN modalidades m ON m.codigo='presencial' WHERE c.slug='tecnicaturas-superiores'
ON DUPLICATE KEY UPDATE categoria_id=VALUES(categoria_id),unidad_academica_id=VALUES(unidad_academica_id),modalidad_id=VALUES(modalidad_id),titulo=VALUES(titulo),resumen=VALUES(resumen),descripcion=VALUES(descripcion),titulo_otorgado=VALUES(titulo_otorgado),estado_oferta=VALUES(estado_oferta),estado_publicacion='publicada';

INSERT INTO ofertas (categoria_id,unidad_academica_id,modalidad_id,slug,titulo,resumen,descripcion,destacada,estado_oferta,estado_publicacion,publicada_at,orden)
SELECT c.id,u.id,m.id,'demo-diplomatura-gestion-educativa','Diplomatura en Gestión Educativa','Trayecto demostrativo sobre organización y gestión de instituciones educativas.','Contenido DEMO pendiente de validación institucional.',0,'proximamente','publicada',NOW(),40
FROM catalogo_categorias c JOIN unidades_academicas u ON u.slug='nivel-superior' JOIN modalidades m ON m.codigo='semipresencial' WHERE c.slug='diplomaturas'
ON DUPLICATE KEY UPDATE categoria_id=VALUES(categoria_id),unidad_academica_id=VALUES(unidad_academica_id),modalidad_id=VALUES(modalidad_id),titulo=VALUES(titulo),resumen=VALUES(resumen),descripcion=VALUES(descripcion),estado_oferta=VALUES(estado_oferta),estado_publicacion='publicada';

INSERT INTO ofertas (categoria_id,unidad_academica_id,modalidad_id,slug,titulo,resumen,descripcion,destacada,estado_oferta,estado_publicacion,publicada_at,orden)
SELECT c.id,u.id,m.id,'demo-curso-herramientas-digitales-docencia','Curso de Herramientas Digitales para la Docencia','Capacitación demostrativa para integrar recursos digitales en propuestas de enseñanza.','Contenido DEMO pendiente de validación institucional.',0,'inscripcion_abierta','publicada',NOW(),50
FROM catalogo_categorias c JOIN unidades_academicas u ON u.slug='nivel-superior' JOIN modalidades m ON m.codigo='virtual' WHERE c.slug='cursos-capacitaciones'
ON DUPLICATE KEY UPDATE categoria_id=VALUES(categoria_id),unidad_academica_id=VALUES(unidad_academica_id),modalidad_id=VALUES(modalidad_id),titulo=VALUES(titulo),resumen=VALUES(resumen),descripcion=VALUES(descripcion),estado_oferta=VALUES(estado_oferta),estado_publicacion='publicada';

INSERT IGNORE INTO oferta_turno (oferta_id,turno_id) SELECT o.id,t.id FROM ofertas o JOIN turnos t ON t.codigo='manana' WHERE o.slug='demo-profesorado-educacion-secundaria-economia';
INSERT IGNORE INTO oferta_turno (oferta_id,turno_id) SELECT o.id,t.id FROM ofertas o JOIN turnos t ON t.codigo='noche' WHERE o.slug='demo-tecnicatura-superior-desarrollo-software';
INSERT IGNORE INTO oferta_turno (oferta_id,turno_id) SELECT o.id,t.id FROM ofertas o JOIN turnos t ON t.codigo='sin_turno' WHERE o.slug IN ('demo-diplomatura-gestion-educativa','demo-curso-herramientas-digitales-docencia');

INSERT IGNORE INTO oferta_area (oferta_id,area_id) SELECT o.id,a.id FROM ofertas o JOIN areas_conocimiento a ON a.slug IN ('educacion-docencia','economia-gestion') WHERE o.slug='demo-profesorado-educacion-secundaria-economia';
INSERT IGNORE INTO oferta_area (oferta_id,area_id) SELECT o.id,a.id FROM ofertas o JOIN areas_conocimiento a ON a.slug='tecnologia-informatica' WHERE o.slug='demo-tecnicatura-superior-desarrollo-software';
INSERT IGNORE INTO oferta_area (oferta_id,area_id) SELECT o.id,a.id FROM ofertas o JOIN areas_conocimiento a ON a.slug IN ('educacion-docencia','economia-gestion') WHERE o.slug='demo-diplomatura-gestion-educativa';
INSERT IGNORE INTO oferta_area (oferta_id,area_id) SELECT o.id,a.id FROM ofertas o JOIN areas_conocimiento a ON a.slug IN ('educacion-docencia','tecnologia-informatica') WHERE o.slug='demo-curso-herramientas-digitales-docencia';

INSERT INTO oferta_convocatorias (oferta_id,anio,edicion,inscripcion_desde,inscripcion_hasta,estado,observaciones)
SELECT id,YEAR(CURDATE()),'',CURDATE(),DATE_ADD(CURDATE(),INTERVAL 60 DAY),'abierta','Convocatoria DEMO pendiente de validación institucional.' FROM ofertas WHERE slug IN ('demo-tecnicatura-superior-desarrollo-software','demo-curso-herramientas-digitales-docencia')
ON DUPLICATE KEY UPDATE inscripcion_desde=VALUES(inscripcion_desde),inscripcion_hasta=VALUES(inscripcion_hasta),estado=VALUES(estado),observaciones=VALUES(observaciones);
