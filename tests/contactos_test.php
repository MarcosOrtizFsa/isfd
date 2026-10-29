<?php
declare(strict_types=1);
require __DIR__.'/../lib/app.php';
function contactos_ok(bool $condition,string $message):void{if(!$condition){fwrite(STDERR,"FALLO: $message\n");exit(1);}echo "OK: $message\n";}
$db=app_db();
$module=$db->one("SELECT id_system_01 FROM system_01_modulos WHERE system_01_path_home='contactos' AND system_01_estado=1 AND system_01_onoff='on'");
contactos_ok($module!==null,'módulo Contactos activo');
$columns=$db->all("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME='portal_consultas' AND COLUMN_NAME IN ('atendida_at','respondida_at','gestion_notas','gestion_usuario_id')",'s',[BD]);
contactos_ok(count($columns)===4,'campos de seguimiento disponibles');
$types=$db->all("SELECT DISTINCT tipo FROM portal_consultas WHERE tipo NOT IN ('contacto','oferta')");
contactos_ok(count($types)===0,'bandeja limitada a consultas generales y de ofertas');
