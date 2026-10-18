-- Registra el administrador de ofertas en la arquitectura modular existente.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO system_01_modulos
    (system_01_modulo,system_01_tipo,system_01_path_home,system_01_onoff,system_01_orden,system_01_estado)
SELECT 'Ofertas académicas','acces','ofertas','on',3,1
WHERE NOT EXISTS (SELECT 1 FROM system_01_modulos WHERE system_01_path_home='ofertas');

UPDATE system_01_modulos
SET system_01_modulo='Ofertas académicas',system_01_tipo='acces',system_01_onoff='on',system_01_orden=3,system_01_estado=1
WHERE system_01_path_home='ofertas';

SET @modulo_ofertas=(SELECT id_system_01 FROM system_01_modulos WHERE system_01_path_home='ofertas' ORDER BY id_system_01 LIMIT 1);
INSERT INTO system_02_permisos
    (rela_system_03,rela_system_01,system_02_A,system_02_B,system_02_M,system_02_E,system_02_V,system_02_S,system_02_D,system_02_I,system_02_C)
SELECT u.id_system_03,@modulo_ofertas,1,1,1,1,1,0,0,0,0
FROM system_03_usuarios u
WHERE u.system_03_modo IN (0,1,2)
AND NOT EXISTS (
    SELECT 1 FROM system_02_permisos p
    WHERE p.rela_system_03=u.id_system_03 AND p.rela_system_01=@modulo_ofertas
);
