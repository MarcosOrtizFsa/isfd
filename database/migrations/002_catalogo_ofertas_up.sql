-- Catálogo académico normalizado.
-- Requiere que 2026_10_17_portal_academico.sql se haya ejecutado previamente.

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE catalogo_niveles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_catalogo_niveles_codigo (codigo),
    KEY idx_catalogo_niveles_filtro (activo, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalogo_categorias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nivel_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    slug VARCHAR(120) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    profundidad TINYINT UNSIGNED NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    admite_ofertas TINYINT(1) NOT NULL DEFAULT 1,
    icono VARCHAR(60) NULL,
    imagen_url VARCHAR(500) NULL,
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_catalogo_categorias_slug (slug),
    KEY idx_catalogo_categorias_nivel (nivel_id),
    KEY idx_catalogo_categorias_parent (parent_id),
    KEY idx_catalogo_categorias_ruta (ruta),
    KEY idx_catalogo_categorias_filtro (activo, admite_ofertas, orden),
    CONSTRAINT fk_catalogo_categorias_nivel FOREIGN KEY (nivel_id) REFERENCES catalogo_niveles(id) ON DELETE RESTRICT,
    CONSTRAINT fk_catalogo_categorias_parent FOREIGN KEY (parent_id) REFERENCES catalogo_categorias(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE unidades_academicas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nivel_id BIGINT UNSIGNED NOT NULL,
    slug VARCHAR(120) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_unidades_academicas_slug (slug),
    KEY idx_unidades_academicas_nivel (nivel_id),
    KEY idx_unidades_academicas_activo (activo),
    CONSTRAINT fk_unidades_academicas_nivel FOREIGN KEY (nivel_id) REFERENCES catalogo_niveles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE modalidades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_modalidades_codigo (codigo),
    KEY idx_modalidades_filtro (activo, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE turnos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    hora_desde TIME NULL,
    hora_hasta TIME NULL,
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_turnos_codigo (codigo),
    KEY idx_turnos_filtro (activo, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE areas_conocimiento (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_areas_conocimiento_slug (slug),
    KEY idx_areas_conocimiento_filtro (activo, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ofertas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id BIGINT UNSIGNED NOT NULL,
    unidad_academica_id BIGINT UNSIGNED NULL,
    modalidad_id BIGINT UNSIGNED NOT NULL,
    slug VARCHAR(160) NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    resumen VARCHAR(500) NULL,
    descripcion MEDIUMTEXT NULL,
    titulo_otorgado VARCHAR(200) NULL,
    perfil_egresado TEXT NULL,
    requisitos_ingreso TEXT NULL,
    duracion_valor DECIMAL(5,1) NULL,
    duracion_unidad ENUM('anios','cuatrimestres','meses','semanas','horas') NULL,
    carga_horaria SMALLINT UNSIGNED NULL,
    norma_aprobacion VARCHAR(200) NULL,
    imagen_url VARCHAR(500) NULL,
    destacada TINYINT(1) NOT NULL DEFAULT 0,
    estado_oferta ENUM('proximamente','inscripcion_abierta','en_curso','a_termino','cerrada') NOT NULL DEFAULT 'proximamente',
    estado_publicacion ENUM('borrador','publicada','archivada') NOT NULL DEFAULT 'borrador',
    publicada_at DATETIME NULL,
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_ofertas_slug (slug),
    KEY idx_ofertas_categoria (categoria_id),
    KEY idx_ofertas_unidad (unidad_academica_id),
    KEY idx_ofertas_modalidad (modalidad_id),
    KEY idx_ofertas_estado (estado_oferta),
    KEY idx_ofertas_publicacion (estado_publicacion),
    KEY idx_ofertas_destacada (destacada),
    FULLTEXT KEY ft_ofertas_busqueda (titulo, resumen, descripcion, titulo_otorgado),
    CONSTRAINT fk_ofertas_categoria FOREIGN KEY (categoria_id) REFERENCES catalogo_categorias(id) ON DELETE RESTRICT,
    CONSTRAINT fk_ofertas_unidad FOREIGN KEY (unidad_academica_id) REFERENCES unidades_academicas(id) ON DELETE SET NULL,
    CONSTRAINT fk_ofertas_modalidad FOREIGN KEY (modalidad_id) REFERENCES modalidades(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE oferta_turno (
    oferta_id BIGINT UNSIGNED NOT NULL,
    turno_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (oferta_id, turno_id),
    KEY idx_oferta_turno_turno (turno_id),
    CONSTRAINT fk_oferta_turno_oferta FOREIGN KEY (oferta_id) REFERENCES ofertas(id) ON DELETE CASCADE,
    CONSTRAINT fk_oferta_turno_turno FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE oferta_area (
    oferta_id BIGINT UNSIGNED NOT NULL,
    area_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (oferta_id, area_id),
    KEY idx_oferta_area_area (area_id),
    CONSTRAINT fk_oferta_area_oferta FOREIGN KEY (oferta_id) REFERENCES ofertas(id) ON DELETE CASCADE,
    CONSTRAINT fk_oferta_area_area FOREIGN KEY (area_id) REFERENCES areas_conocimiento(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE oferta_convocatorias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    oferta_id BIGINT UNSIGNED NOT NULL,
    anio SMALLINT UNSIGNED NOT NULL,
    edicion VARCHAR(40) NOT NULL DEFAULT '',
    inscripcion_desde DATE NULL,
    inscripcion_hasta DATE NULL,
    inicio_cursada DATE NULL,
    fin_cursada DATE NULL,
    cupo SMALLINT UNSIGNED NULL,
    url_preinscripcion VARCHAR(500) NULL,
    estado ENUM('programada','abierta','cerrada','finalizada') NOT NULL DEFAULT 'programada',
    observaciones TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_oferta_convocatoria_edicion (oferta_id, anio, edicion),
    KEY idx_oferta_convocatorias_oferta (oferta_id),
    KEY idx_oferta_convocatorias_estado (estado, inscripcion_desde),
    CONSTRAINT fk_oferta_convocatorias_oferta FOREIGN KEY (oferta_id) REFERENCES ofertas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE oferta_documentos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    oferta_id BIGINT UNSIGNED NOT NULL,
    tipo ENUM('plan_estudios','resolucion','requisitos','programa','calendario','otro') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    url VARCHAR(500) NOT NULL,
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_oferta_documentos_oferta (oferta_id),
    KEY idx_oferta_documentos_tipo (tipo),
    CONSTRAINT fk_oferta_documentos_oferta FOREIGN KEY (oferta_id) REFERENCES ofertas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER $$

CREATE TRIGGER trg_catalogo_categorias_bi
BEFORE INSERT ON catalogo_categorias FOR EACH ROW
BEGIN
    DECLARE v_nivel BIGINT UNSIGNED;
    DECLARE v_profundidad TINYINT UNSIGNED;
    DECLARE v_admite TINYINT UNSIGNED;
    IF NEW.parent_id IS NOT NULL THEN
        SELECT nivel_id, profundidad, admite_ofertas INTO v_nivel, v_profundidad, v_admite
          FROM catalogo_categorias WHERE id = NEW.parent_id;
        IF v_nivel <> NEW.nivel_id THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La categoria hija debe pertenecer al mismo nivel que su padre';
        END IF;
        IF v_profundidad >= 3 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El catalogo admite como maximo tres niveles de categorias';
        END IF;
        IF v_admite = 1 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Una categoria que admite ofertas no puede recibir subcategorias';
        END IF;
    END IF;
END$$

CREATE TRIGGER trg_catalogo_categorias_bu
BEFORE UPDATE ON catalogo_categorias FOR EACH ROW
BEGIN
    IF COALESCE(@catalogo_movimiento_permitido, 0) <> 1 AND
       (NOT (NEW.parent_id <=> OLD.parent_id) OR NEW.nivel_id <> OLD.nivel_id OR NEW.ruta <> OLD.ruta OR NEW.profundidad <> OLD.profundidad) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Use catalogo_mover_categoria para modificar la jerarquia';
    END IF;
    IF NEW.admite_ofertas = 1 AND EXISTS (SELECT 1 FROM catalogo_categorias WHERE parent_id = OLD.id LIMIT 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Una categoria con subcategorias no puede admitir ofertas';
    END IF;
    IF NEW.admite_ofertas = 0 AND OLD.admite_ofertas = 1 AND EXISTS (SELECT 1 FROM ofertas WHERE categoria_id = OLD.id LIMIT 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Una categoria con ofertas asociadas debe continuar admitiendo ofertas';
    END IF;
END$$

CREATE TRIGGER trg_ofertas_bi
BEFORE INSERT ON ofertas FOR EACH ROW
BEGIN
    IF NOT EXISTS (SELECT 1 FROM catalogo_categorias WHERE id = NEW.categoria_id AND activo = 1 AND admite_ofertas = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La oferta debe asociarse a una categoria hoja activa';
    END IF;
END$$

CREATE TRIGGER trg_ofertas_bu
BEFORE UPDATE ON ofertas FOR EACH ROW
BEGIN
    IF NOT EXISTS (SELECT 1 FROM catalogo_categorias WHERE id = NEW.categoria_id AND activo = 1 AND admite_ofertas = 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La oferta debe asociarse a una categoria hoja activa';
    END IF;
END$$

CREATE PROCEDURE catalogo_crear_categoria(
    IN p_nivel_id BIGINT UNSIGNED,
    IN p_parent_id BIGINT UNSIGNED,
    IN p_slug VARCHAR(120),
    IN p_nombre VARCHAR(150),
    IN p_admite_ofertas TINYINT,
    IN p_orden SMALLINT UNSIGNED
)
BEGIN
    DECLARE v_id BIGINT UNSIGNED;
    DECLARE v_profundidad TINYINT UNSIGNED DEFAULT 1;
    DECLARE v_ruta_padre VARCHAR(255) DEFAULT '/';
    IF p_parent_id IS NOT NULL THEN
        SELECT profundidad + 1, ruta INTO v_profundidad, v_ruta_padre
          FROM catalogo_categorias WHERE id = p_parent_id AND nivel_id = p_nivel_id;
    END IF;
    IF v_profundidad > 3 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El catalogo admite como maximo tres niveles de categorias';
    END IF;
    INSERT INTO catalogo_categorias
        (nivel_id, parent_id, slug, nombre, profundidad, ruta, admite_ofertas, orden)
    VALUES (p_nivel_id, p_parent_id, p_slug, p_nombre, v_profundidad, '/', p_admite_ofertas, p_orden);
    SET v_id = LAST_INSERT_ID();
    SET @catalogo_movimiento_permitido = 1;
    UPDATE catalogo_categorias SET ruta = CONCAT(v_ruta_padre, v_id, '/') WHERE id = v_id;
    SET @catalogo_movimiento_permitido = NULL;
END$$

CREATE PROCEDURE catalogo_mover_categoria(IN p_categoria_id BIGINT UNSIGNED, IN p_nuevo_parent_id BIGINT UNSIGNED)
BEGIN
    DECLARE v_nivel BIGINT UNSIGNED;
    DECLARE v_old_parent BIGINT UNSIGNED;
    DECLARE v_old_route VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_old_depth TINYINT UNSIGNED;
    DECLARE v_new_route VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_new_depth TINYINT UNSIGNED;
    DECLARE v_delta INT;
    DECLARE v_max_depth INT;

    SELECT nivel_id, parent_id, ruta, profundidad
      INTO v_nivel, v_old_parent, v_old_route, v_old_depth
      FROM catalogo_categorias WHERE id = p_categoria_id FOR UPDATE;

    IF p_nuevo_parent_id IS NULL THEN
        SET v_new_depth = 1;
        SET v_new_route = CONCAT('/', p_categoria_id, '/');
    ELSE
        IF NOT EXISTS (SELECT 1 FROM catalogo_categorias WHERE id = p_nuevo_parent_id AND nivel_id = v_nivel AND activo = 1 AND admite_ofertas = 0) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El nuevo padre debe estar activo, pertenecer al mismo nivel y no admitir ofertas';
        END IF;
        IF EXISTS (SELECT 1 FROM catalogo_categorias WHERE id = p_nuevo_parent_id AND ruta LIKE CONCAT(v_old_route, '%')) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El movimiento generaria un ciclo';
        END IF;
        SELECT profundidad + 1, CONCAT(ruta, p_categoria_id, '/')
          INTO v_new_depth, v_new_route FROM catalogo_categorias WHERE id = p_nuevo_parent_id;
    END IF;

    SET v_delta = v_new_depth - v_old_depth;
    SELECT MAX(profundidad + v_delta) INTO v_max_depth FROM catalogo_categorias WHERE ruta LIKE CONCAT(v_old_route, '%');
    IF v_max_depth > 3 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El movimiento supera la profundidad maxima del catalogo';
    END IF;

    SET @catalogo_movimiento_permitido = 1;
    UPDATE catalogo_categorias
       SET parent_id = IF(id = p_categoria_id, p_nuevo_parent_id, parent_id),
           profundidad = profundidad + v_delta,
           ruta = CONCAT(v_new_route, SUBSTRING(ruta, CHAR_LENGTH(v_old_route) + 1))
     WHERE ruta LIKE CONCAT(v_old_route, '%')
     ORDER BY profundidad;
    SET @catalogo_movimiento_permitido = NULL;
END$$

DELIMITER ;

-- Taxonomías idempotentes.
INSERT INTO catalogo_niveles (codigo, nombre, descripcion, orden) VALUES
('basica', 'Educación Básica', 'Nivel inicial, primario y secundario.', 10),
('superior', 'Educación Superior', 'Formación superior, universitaria, posgrado y continua.', 20)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), descripcion=VALUES(descripcion), orden=VALUES(orden), activo=1;

INSERT INTO modalidades (codigo, nombre, orden) VALUES
('presencial','Presencial',10),('virtual','Virtual',20),('semipresencial','Semipresencial',30)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), orden=VALUES(orden), activo=1;

INSERT INTO turnos (codigo, nombre, orden) VALUES
('manana','Mañana',10),('tarde','Tarde',20),('noche','Noche',30),('contraturno','Contraturno',40),('sin_turno','Sin turno específico',50)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), orden=VALUES(orden), activo=1;

INSERT INTO areas_conocimiento (slug, nombre, orden) VALUES
('educacion-docencia','Educación y Docencia',10),('idiomas','Idiomas',20),('economia-gestion','Economía y Gestión',30),
('ciencias-sociales-humanidades','Ciencias Sociales y Humanidades',40),('tecnologia-informatica','Tecnología e Informática',50),
('arte-comunicacion','Arte y Comunicación',60),('ciencias-exactas-naturales','Ciencias Exactas y Naturales',70),('salud-bienestar','Salud y Bienestar',80)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), orden=VALUES(orden), activo=1;

INSERT INTO unidades_academicas (nivel_id, slug, nombre) SELECT id,'nivel-inicial','Nivel Inicial (Jardín de Infantes)' FROM catalogo_niveles WHERE codigo='basica'
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), nivel_id=VALUES(nivel_id), activo=1;
INSERT INTO unidades_academicas (nivel_id, slug, nombre) SELECT id,'departamento-aplicacion-primario','Departamento de Aplicación de Nivel Primario' FROM catalogo_niveles WHERE codigo='basica'
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), nivel_id=VALUES(nivel_id), activo=1;
INSERT INTO unidades_academicas (nivel_id, slug, nombre) SELECT id,'departamento-aplicacion-secundario','Departamento de Aplicación de Nivel Secundario' FROM catalogo_niveles WHERE codigo='basica'
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), nivel_id=VALUES(nivel_id), activo=1;
INSERT INTO unidades_academicas (nivel_id, slug, nombre) SELECT id,'nivel-superior','Nivel Superior' FROM catalogo_niveles WHERE codigo='superior'
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), nivel_id=VALUES(nivel_id), activo=1;

SET @catalogo_migracion = 1;
SET @basica = (SELECT id FROM catalogo_niveles WHERE codigo='basica');
SET @superior = (SELECT id FROM catalogo_niveles WHERE codigo='superior');

INSERT INTO catalogo_categorias (nivel_id,parent_id,slug,nombre,profundidad,ruta,admite_ofertas,orden) VALUES
(@basica,NULL,'nivel-inicial','Nivel Inicial',1,'/',1,10),
(@basica,NULL,'nivel-primario','Nivel Primario',1,'/',1,20),
(@basica,NULL,'nivel-secundario','Nivel Secundario',1,'/',0,30),
(@superior,NULL,'formacion-docente','Formación Docente',1,'/',0,10),
(@superior,NULL,'tecnicaturas-superiores','Tecnicaturas Superiores',1,'/',1,20),
(@superior,NULL,'universitaria','Universitaria',1,'/',0,30),
(@superior,NULL,'posgrado','Posgrado',1,'/',0,40),
(@superior,NULL,'formacion-continua','Formación Continua',1,'/',0,50)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), nivel_id=VALUES(nivel_id), parent_id=VALUES(parent_id), admite_ofertas=VALUES(admite_ofertas), orden=VALUES(orden), activo=1;

SET @secundario=(SELECT id FROM catalogo_categorias WHERE slug='nivel-secundario');
SET @docente=(SELECT id FROM catalogo_categorias WHERE slug='formacion-docente');
SET @universitaria=(SELECT id FROM catalogo_categorias WHERE slug='universitaria');
SET @posgrado=(SELECT id FROM catalogo_categorias WHERE slug='posgrado');
SET @continua=(SELECT id FROM catalogo_categorias WHERE slug='formacion-continua');

INSERT INTO catalogo_categorias (nivel_id,parent_id,slug,nombre,profundidad,ruta,admite_ofertas,orden) VALUES
(@basica,@secundario,'ciclo-basico','Ciclo Básico',2,'/',1,10),(@basica,@secundario,'ciclo-orientado','Ciclo Orientado',2,'/',1,20),
(@superior,@docente,'profesorados','Profesorados',2,'/',1,10),
(@superior,@universitaria,'pregrado','Pregrado',2,'/',1,10),(@superior,@universitaria,'grado','Grado',2,'/',1,20),(@superior,@universitaria,'ciclos-complementacion-curricular','Ciclos de Complementación Curricular',2,'/',1,30),
(@superior,@posgrado,'especializaciones','Especializaciones',2,'/',1,10),(@superior,@posgrado,'maestrias','Maestrías',2,'/',1,20),(@superior,@posgrado,'doctorados','Doctorados',2,'/',1,30),
(@superior,@continua,'cursos-capacitaciones','Cursos y Capacitaciones',2,'/',1,10),(@superior,@continua,'diplomaturas','Diplomaturas',2,'/',1,20),(@superior,@continua,'trayectos-formativos','Trayectos Formativos',2,'/',1,30)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), nivel_id=VALUES(nivel_id), parent_id=VALUES(parent_id), admite_ofertas=VALUES(admite_ofertas), orden=VALUES(orden), activo=1;

SET @catalogo_movimiento_permitido=1;
UPDATE catalogo_categorias SET profundidad=1,ruta=CONCAT('/',id,'/') WHERE parent_id IS NULL;
UPDATE catalogo_categorias c JOIN catalogo_categorias p ON p.id=c.parent_id SET c.profundidad=p.profundidad+1,c.ruta=CONCAT(p.ruta,c.id,'/');
SET @catalogo_movimiento_permitido=NULL;

-- Ofertas DEMO: no representan información académica oficial.
INSERT INTO ofertas (categoria_id,unidad_academica_id,modalidad_id,slug,titulo,resumen,descripcion,destacada,estado_oferta,estado_publicacion,publicada_at,orden)
SELECT c.id,u.id,m.id,'demo-profesorado-en-ingles','Profesorado en Inglés','Formación para la enseñanza del inglés en nivel primario y secundario.','Contenido DEMO pendiente de validación institucional.',1,'inscripcion_abierta','publicada',NOW(),10
FROM catalogo_categorias c JOIN unidades_academicas u ON u.slug='nivel-superior' JOIN modalidades m ON m.codigo='presencial' WHERE c.slug='profesorados'
ON DUPLICATE KEY UPDATE categoria_id=VALUES(categoria_id),unidad_academica_id=VALUES(unidad_academica_id),modalidad_id=VALUES(modalidad_id),titulo=VALUES(titulo),resumen=VALUES(resumen),estado_oferta=VALUES(estado_oferta),estado_publicacion='publicada';

INSERT INTO ofertas (categoria_id,unidad_academica_id,modalidad_id,slug,titulo,resumen,descripcion,estado_oferta,estado_publicacion,publicada_at,orden)
SELECT c.id,u.id,m.id,'demo-orientacion-humanidades-ciencias-sociales','Orientación en Humanidades y Ciencias Sociales','Ciclo orientado del Departamento de Aplicación de Nivel Secundario.','Contenido DEMO pendiente de validación institucional.','en_curso','publicada',NOW(),20
FROM catalogo_categorias c JOIN unidades_academicas u ON u.slug='departamento-aplicacion-secundario' JOIN modalidades m ON m.codigo='presencial' WHERE c.slug='ciclo-orientado'
ON DUPLICATE KEY UPDATE categoria_id=VALUES(categoria_id),unidad_academica_id=VALUES(unidad_academica_id),modalidad_id=VALUES(modalidad_id),titulo=VALUES(titulo),resumen=VALUES(resumen),estado_oferta=VALUES(estado_oferta),estado_publicacion='publicada';

INSERT INTO ofertas (categoria_id,unidad_academica_id,modalidad_id,slug,titulo,resumen,descripcion,estado_oferta,estado_publicacion,publicada_at,orden)
SELECT c.id,u.id,m.id,'demo-departamento-aplicacion-nivel-primario','Departamento de Aplicación de Nivel Primario','Escuela primaria anexa al instituto, con espacios de práctica docente.','Contenido DEMO pendiente de validación institucional.','en_curso','publicada',NOW(),30
FROM catalogo_categorias c JOIN unidades_academicas u ON u.slug='departamento-aplicacion-primario' JOIN modalidades m ON m.codigo='presencial' WHERE c.slug='nivel-primario'
ON DUPLICATE KEY UPDATE categoria_id=VALUES(categoria_id),unidad_academica_id=VALUES(unidad_academica_id),modalidad_id=VALUES(modalidad_id),titulo=VALUES(titulo),resumen=VALUES(resumen),estado_oferta=VALUES(estado_oferta),estado_publicacion='publicada';

INSERT IGNORE INTO oferta_turno (oferta_id,turno_id) SELECT o.id,t.id FROM ofertas o JOIN turnos t ON t.codigo='noche' WHERE o.slug='demo-profesorado-en-ingles';
INSERT IGNORE INTO oferta_turno (oferta_id,turno_id) SELECT o.id,t.id FROM ofertas o JOIN turnos t ON t.codigo='manana' WHERE o.slug='demo-orientacion-humanidades-ciencias-sociales';
INSERT IGNORE INTO oferta_turno (oferta_id,turno_id) SELECT o.id,t.id FROM ofertas o JOIN turnos t ON t.codigo='tarde' WHERE o.slug='demo-departamento-aplicacion-nivel-primario';
INSERT IGNORE INTO oferta_area (oferta_id,area_id) SELECT o.id,a.id FROM ofertas o JOIN areas_conocimiento a ON a.slug IN ('idiomas','educacion-docencia') WHERE o.slug='demo-profesorado-en-ingles';
INSERT IGNORE INTO oferta_area (oferta_id,area_id) SELECT o.id,a.id FROM ofertas o JOIN areas_conocimiento a ON a.slug='ciencias-sociales-humanidades' WHERE o.slug='demo-orientacion-humanidades-ciencias-sociales';
INSERT IGNORE INTO oferta_area (oferta_id,area_id) SELECT o.id,a.id FROM ofertas o JOIN areas_conocimiento a ON a.slug='educacion-docencia' WHERE o.slug='demo-departamento-aplicacion-nivel-primario';
INSERT INTO oferta_convocatorias (oferta_id,anio,edicion,inscripcion_desde,inscripcion_hasta,estado,observaciones)
SELECT id,YEAR(CURDATE()),'',DATE_FORMAT(CURDATE(),'%Y-01-01'),DATE_FORMAT(CURDATE(),'%Y-12-31'),'abierta','Convocatoria DEMO pendiente de validación institucional.' FROM ofertas WHERE slug='demo-profesorado-en-ingles'
ON DUPLICATE KEY UPDATE estado=VALUES(estado),observaciones=VALUES(observaciones);

-- Conserva las consultas existentes y las vincula con las ofertas migradas.
ALTER TABLE portal_consultas DROP FOREIGN KEY fk_consulta_oferta;
UPDATE portal_consultas c
JOIN portal_ofertas_academicas antigua ON antigua.id=c.oferta_id
JOIN ofertas nueva ON nueva.slug=CONCAT('demo-',antigua.slug)
SET c.oferta_id=nueva.id;
ALTER TABLE portal_consultas MODIFY oferta_id BIGINT UNSIGNED NULL, ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;
ALTER TABLE portal_consultas ADD CONSTRAINT fk_consulta_oferta FOREIGN KEY (oferta_id) REFERENCES ofertas(id) ON DELETE SET NULL;

CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW v_oferta_convocatorias_priorizadas AS
SELECT c.*,
       ROW_NUMBER() OVER (PARTITION BY c.oferta_id ORDER BY
           CASE c.estado WHEN 'abierta' THEN 0 ELSE 1 END,
           CASE WHEN c.estado='abierta' THEN c.inscripcion_desde END DESC,
           CASE WHEN c.estado='programada' THEN COALESCE(c.inscripcion_desde,'9999-12-31') END ASC,
           c.id DESC) prioridad
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

SET @catalogo_migracion=NULL;
