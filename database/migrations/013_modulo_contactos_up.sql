-- Seguimiento de la bandeja unificada de consultas.
ALTER TABLE portal_consultas
    ADD COLUMN atendida_at DATETIME NULL AFTER estado,
    ADD COLUMN respondida_at DATETIME NULL AFTER atendida_at,
    ADD COLUMN gestion_notas TEXT NULL AFTER respondida_at,
    ADD COLUMN gestion_usuario_id INT UNSIGNED NULL AFTER gestion_notas;

INSERT INTO system_01_modulos (system_01_modulo,system_01_tipo,system_01_path_home,system_01_onoff,system_01_orden,system_01_estado)
SELECT 'Contactos','acces','contactos','on',6,1
WHERE NOT EXISTS (SELECT 1 FROM system_01_modulos WHERE system_01_path_home='contactos');
