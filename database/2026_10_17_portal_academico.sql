-- Estructura escalable del portal institucional.
-- No modifica las tablas heredadas ni las utilizadas por NEURIA.

CREATE TABLE IF NOT EXISTS portal_catalogos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    grupo VARCHAR(40) NOT NULL,
    codigo VARCHAR(60) NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_portal_catalogo (grupo, codigo),
    KEY idx_portal_catalogo_lista (grupo, activo, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portal_ofertas_academicas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(180) NOT NULL,
    titulo VARCHAR(220) NOT NULL,
    nivel_id INT UNSIGNED NOT NULL,
    tipo_id INT UNSIGNED NOT NULL,
    turno_id INT UNSIGNED NOT NULL,
    estado_id INT UNSIGNED NOT NULL,
    resumen VARCHAR(500) NOT NULL DEFAULT '',
    descripcion MEDIUMTEXT NOT NULL,
    requisitos MEDIUMTEXT NOT NULL,
    duracion VARCHAR(120) NOT NULL DEFAULT '',
    plan_estudios_url VARCHAR(500) NULL,
    imagen_url VARCHAR(500) NULL,
    destacada TINYINT(1) NOT NULL DEFAULT 0,
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    publicada TINYINT(1) NOT NULL DEFAULT 0,
    es_demo TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_portal_oferta_slug (slug),
    KEY idx_portal_oferta_publica (publicada, orden),
    KEY idx_portal_oferta_filtros (nivel_id, tipo_id, turno_id, estado_id),
    CONSTRAINT fk_oferta_nivel FOREIGN KEY (nivel_id) REFERENCES portal_catalogos(id),
    CONSTRAINT fk_oferta_tipo FOREIGN KEY (tipo_id) REFERENCES portal_catalogos(id),
    CONSTRAINT fk_oferta_turno FOREIGN KEY (turno_id) REFERENCES portal_catalogos(id),
    CONSTRAINT fk_oferta_estado FOREIGN KEY (estado_id) REFERENCES portal_catalogos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portal_consultas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    oferta_id INT UNSIGNED NULL,
    tipo ENUM('oferta', 'contacto') NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    telefono VARCHAR(40) NULL,
    asunto VARCHAR(150) NOT NULL DEFAULT '',
    mensaje TEXT NOT NULL,
    estado ENUM('nueva', 'leida', 'respondida') NOT NULL DEFAULT 'nueva',
    ip_hash CHAR(64) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_portal_consulta_estado (estado, created_at),
    KEY idx_portal_consulta_frecuencia (ip_hash, created_at),
    CONSTRAINT fk_consulta_oferta FOREIGN KEY (oferta_id) REFERENCES portal_ofertas_academicas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portal_bandeja_salida (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    consulta_id BIGINT UNSIGNED NOT NULL,
    destinatario VARCHAR(190) NOT NULL,
    asunto VARCHAR(190) NOT NULL,
    cuerpo TEXT NOT NULL,
    estado ENUM('pendiente', 'enviando', 'enviado', 'fallido') NOT NULL DEFAULT 'pendiente',
    intentos TINYINT UNSIGNED NOT NULL DEFAULT 0,
    ultimo_error VARCHAR(500) NULL,
    disponible_desde DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    enviado_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_bandeja_pendiente (estado, disponible_desde),
    CONSTRAINT fk_bandeja_consulta FOREIGN KEY (consulta_id) REFERENCES portal_consultas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO portal_catalogos (grupo, codigo, nombre, orden) VALUES
('nivel','inicial','Nivel Inicial',10),('nivel','primario','Nivel Primario',20),('nivel','secundario','Nivel Secundario',30),('nivel','superior','Nivel Superior',40),
('tipo','profesorado','Profesorado',10),('tipo','tecnicatura','Tecnicatura',20),('tipo','orientacion','Orientacion / Ciclo',30),('tipo','otro','Otro',40),
('turno','manana','Mañana',10),('turno','tarde','Tarde',20),('turno','noche','Noche',30),('turno','manana_tarde','Mañana y tarde',40),
('estado','inscripcion_abierta','Inscripción abierta',10),('estado','en_curso','En curso',20),('estado','a_termino','A término',30);

-- DEMO: estos registros se eliminan fácilmente con WHERE es_demo = 1.
INSERT IGNORE INTO portal_ofertas_academicas
(slug,titulo,nivel_id,tipo_id,turno_id,estado_id,resumen,descripcion,requisitos,duracion,destacada,orden,publicada,es_demo)
SELECT 'profesorado-en-ingles','Profesorado en Inglés',n.id,t.id,tu.id,e.id,
'Formación para la enseñanza del inglés en nivel primario y secundario.','Contenido demostrativo pendiente de validación institucional.','','',1,10,1,1
FROM portal_catalogos n,portal_catalogos t,portal_catalogos tu,portal_catalogos e
WHERE n.grupo='nivel' AND n.codigo='superior' AND t.grupo='tipo' AND t.codigo='profesorado' AND tu.grupo='turno' AND tu.codigo='noche' AND e.grupo='estado' AND e.codigo='inscripcion_abierta';

INSERT IGNORE INTO portal_ofertas_academicas
(slug,titulo,nivel_id,tipo_id,turno_id,estado_id,resumen,descripcion,requisitos,duracion,destacada,orden,publicada,es_demo)
SELECT 'orientacion-humanidades-ciencias-sociales','Orientación en Humanidades y Ciencias Sociales',n.id,t.id,tu.id,e.id,
'Ciclo básico orientado del Departamento de Aplicación de Nivel Secundario.','Contenido demostrativo pendiente de validación institucional.','','',0,20,1,1
FROM portal_catalogos n,portal_catalogos t,portal_catalogos tu,portal_catalogos e
WHERE n.grupo='nivel' AND n.codigo='secundario' AND t.grupo='tipo' AND t.codigo='orientacion' AND tu.grupo='turno' AND tu.codigo='manana' AND e.grupo='estado' AND e.codigo='en_curso';

INSERT IGNORE INTO portal_ofertas_academicas
(slug,titulo,nivel_id,tipo_id,turno_id,estado_id,resumen,descripcion,requisitos,duracion,destacada,orden,publicada,es_demo)
SELECT 'departamento-aplicacion-nivel-primario','Departamento de Aplicación de Nivel Primario',n.id,t.id,tu.id,e.id,
'Escuela primaria anexa al instituto, con espacios de práctica docente.','Contenido demostrativo pendiente de validación institucional.','','',0,30,1,1
FROM portal_catalogos n,portal_catalogos t,portal_catalogos tu,portal_catalogos e
WHERE n.grupo='nivel' AND n.codigo='primario' AND t.grupo='tipo' AND t.codigo='otro' AND tu.grupo='turno' AND tu.codigo='tarde' AND e.grupo='estado' AND e.codigo='en_curso';

