-- Ejecutar primero, antes del código de permisos. No otorga accesos automáticamente.
-- Las asignaciones A/B/M/E anteriores se conservan, pero requieren acceso explícito de root.
SET @permiso_columna = (SELECT COUNT(*) FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='system_02_permisos' AND COLUMN_NAME='system_02_acceso');
SET @permiso_sql = IF(@permiso_columna=0,
 'ALTER TABLE system_02_permisos ADD COLUMN system_02_acceso TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE permiso_stmt FROM @permiso_sql;
EXECUTE permiso_stmt;
DEALLOCATE PREPARE permiso_stmt;

-- Índice no único: no elimina ni altera asignaciones históricas duplicadas.
SET @permiso_indice = (SELECT COUNT(*) FROM information_schema.STATISTICS
 WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='system_02_permisos' AND INDEX_NAME='idx_permiso_usuario_modulo');
SET @permiso_sql = IF(@permiso_indice=0,
 'ALTER TABLE system_02_permisos ADD INDEX idx_permiso_usuario_modulo (rela_system_03,rela_system_01)', 'SELECT 1');
PREPARE permiso_stmt FROM @permiso_sql;
EXECUTE permiso_stmt;
DEALLOCATE PREPARE permiso_stmt;
