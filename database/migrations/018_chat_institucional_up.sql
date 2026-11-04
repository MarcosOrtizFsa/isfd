-- Importar en la base del sitio. No borra datos ni requiere DEFINER o procedimientos.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_avatares (
    id INT UNSIGNED PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 512 combinaciones visuales, reservadas de forma exclusiva mediante UNIQUE.
INSERT IGNORE INTO chat_avatares (id)
SELECT 1 + a.n + b.n*8 + c.n*64
FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7) a
CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7) b
CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7) c;

CREATE TABLE IF NOT EXISTS chat_usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    usuario VARCHAR(24) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar_id INT UNSIGNED NULL UNIQUE,
    reglas_version VARCHAR(20) NOT NULL,
    reglas_aceptadas_at DATETIME NOT NULL,
    bloqueado TINYINT NOT NULL DEFAULT 0,
    suspendido_hasta DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_chat_avatar FOREIGN KEY (avatar_id) REFERENCES chat_avatares(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_salas (
    id INT UNSIGNED PRIMARY KEY,
    nombre VARCHAR(180) NOT NULL,
    descripcion VARCHAR(500) NOT NULL,
    mascota VARCHAR(30) NOT NULL,
    activa TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO chat_salas (id,nombre,descripcion,mascota) VALUES
(1,'Profesorado en Inglés','Compartí recursos, experiencias y preguntas sobre el inglés y la formación docente.','owl'),
(2,'Desarrollo de Software','Programación, aplicaciones y proyectos. Un espacio para construir ideas en equipo.','code'),
(3,'Administración de Sistemas y Redes','Conectividad, servidores y soporte. Conectá con quienes comparten tus intereses.','network'),
(4,'Administración de Empresas · Pymes','Emprendimientos, gestión y nuevas ideas para pequeñas y medianas empresas.','business'),
(5,'Auxiliar Profesional Inicial en Inteligencia Artificial','Explorá herramientas, aplicaciones y posibilidades de la inteligencia artificial.','ai');

CREATE TABLE IF NOT EXISTS chat_presencias (
    usuario_id BIGINT UNSIGNED PRIMARY KEY,
    sala_id INT UNSIGNED NULL,
    visto_at DATETIME NOT NULL,
    KEY idx_chat_presencia (sala_id,visto_at),
    CONSTRAINT fk_chat_presencia_usuario FOREIGN KEY (usuario_id) REFERENCES chat_usuarios(id),
    CONSTRAINT fk_chat_presencia_sala FOREIGN KEY (sala_id) REFERENCES chat_salas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_mensajes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sala_id INT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    respuesta_id BIGINT UNSIGNED NULL,
    texto TEXT NOT NULL,
    client_id CHAR(36) NOT NULL,
    oculto TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_chat_envio (usuario_id,client_id),
    KEY idx_chat_historial (sala_id,id),
    KEY idx_chat_frecuencia (usuario_id,created_at),
    CONSTRAINT fk_chat_mensaje_sala FOREIGN KEY (sala_id) REFERENCES chat_salas(id),
    CONSTRAINT fk_chat_mensaje_usuario FOREIGN KEY (usuario_id) REFERENCES chat_usuarios(id),
    CONSTRAINT fk_chat_respuesta FOREIGN KEY (respuesta_id) REFERENCES chat_mensajes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_notificaciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    mensaje_id BIGINT UNSIGNED NOT NULL,
    leida TINYINT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_chat_mencion (usuario_id,mensaje_id),
    KEY idx_chat_notificaciones (usuario_id,leida,id),
    CONSTRAINT fk_chat_notificacion_usuario FOREIGN KEY (usuario_id) REFERENCES chat_usuarios(id),
    CONSTRAINT fk_chat_notificacion_mensaje FOREIGN KEY (mensaje_id) REFERENCES chat_mensajes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_reportes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mensaje_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    denunciante_id BIGINT UNSIGNED NULL,
    texto TEXT NOT NULL,
    motivo VARCHAR(500) NOT NULL,
    origen ENUM('automatico','participante') NOT NULL,
    estado ENUM('pendiente','revisado','descartado') NOT NULL DEFAULT 'pendiente',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_chat_reporte (mensaje_id,denunciante_id),
    KEY idx_chat_revision (estado,id),
    CONSTRAINT fk_chat_reporte_usuario FOREIGN KEY (usuario_id) REFERENCES chat_usuarios(id),
    CONSTRAINT fk_chat_reporte_mensaje FOREIGN KEY (mensaje_id) REFERENCES chat_mensajes(id),
    CONSTRAINT fk_chat_denunciante FOREIGN KEY (denunciante_id) REFERENCES chat_usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    administrador_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    reporte_id BIGINT UNSIGNED NULL,
    accion VARCHAR(30) NOT NULL,
    motivo VARCHAR(500) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_limites (
    clave CHAR(64) PRIMARY KEY,
    intentos INT UNSIGNED NOT NULL DEFAULT 1,
    vence_at DATETIME NOT NULL,
    KEY idx_chat_limite_expira (vence_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
