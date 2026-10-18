SET @modulo_ofertas=(SELECT id_system_01 FROM system_01_modulos WHERE system_01_path_home='ofertas' ORDER BY id_system_01 LIMIT 1);
DELETE FROM system_02_permisos WHERE rela_system_01=@modulo_ofertas;
DELETE FROM system_01_modulos WHERE id_system_01=@modulo_ofertas;
