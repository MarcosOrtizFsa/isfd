DELETE FROM system_01_modulos WHERE system_01_path_home='contactos';
ALTER TABLE portal_consultas DROP COLUMN gestion_usuario_id,DROP COLUMN gestion_notas,DROP COLUMN respondida_at,DROP COLUMN atendida_at;
