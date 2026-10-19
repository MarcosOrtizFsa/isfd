SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS noticias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    neuria_id BIGINT UNSIGNED NULL,
    origen ENUM('local','neuria') NOT NULL DEFAULT 'local',
    titulo VARCHAR(240) NOT NULL,
    bajada VARCHAR(600) NULL,
    contenido MEDIUMTEXT NOT NULL,
    volanta VARCHAR(160) NULL,
    seccion VARCHAR(120) NULL,
    youtube_codigo VARCHAR(30) NULL,
    estado ENUM('pendiente','activa','suspendida') NOT NULL DEFAULT 'pendiente',
    publicacion_at DATETIME NULL,
    creado_por BIGINT UNSIGNED NULL,
    actualizado_por BIGINT UNSIGNED NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_noticias_neuria (neuria_id),
    KEY idx_noticias_estado (estado,publicacion_at),
    KEY idx_noticias_seccion (seccion),
    KEY idx_noticias_eliminadas (deleted_at),
    FULLTEXT KEY ft_noticias_busqueda (titulo,bajada,contenido,volanta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS noticia_archivos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    noticia_id BIGINT UNSIGNED NOT NULL,
    tipo ENUM('imagen','adjunto') NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    nombre_guardado VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    tamano_bytes BIGINT UNSIGNED NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_noticia_archivos_noticia (noticia_id,tipo,orden),
    CONSTRAINT fk_noticia_archivos_noticia FOREIGN KEY (noticia_id) REFERENCES noticias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO system_01_modulos (system_01_modulo,system_01_tipo,system_01_path_home,system_01_onoff,system_01_orden,system_01_estado)
SELECT 'Noticias','acces','noticias','on',4,1
WHERE NOT EXISTS (SELECT 1 FROM system_01_modulos WHERE system_01_path_home='noticias');
UPDATE system_01_modulos SET system_01_modulo='Noticias',system_01_tipo='acces',system_01_onoff='on',system_01_orden=4,system_01_estado=1 WHERE system_01_path_home='noticias';
SET @modulo_noticias=(SELECT id_system_01 FROM system_01_modulos WHERE system_01_path_home='noticias' ORDER BY id_system_01 LIMIT 1);
INSERT INTO system_02_permisos (rela_system_03,rela_system_01,system_02_A,system_02_B,system_02_M,system_02_E,system_02_V,system_02_S,system_02_D,system_02_I,system_02_C)
SELECT u.id_system_03,@modulo_noticias,1,1,1,1,1,1,1,0,0 FROM system_03_usuarios u
WHERE u.system_03_modo IN (0,1,2) AND NOT EXISTS (SELECT 1 FROM system_02_permisos p WHERE p.rela_system_03=u.id_system_03 AND p.rela_system_01=@modulo_noticias);
