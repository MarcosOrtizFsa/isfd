<?php
declare(strict_types=1);
require __DIR__ . '/../../../../lib/app.php';
app_require_mode([0]); app_verify_csrf(); $db=app_db(); $action=app_post('action'); $id=app_int('id');
if($action==='toggle'){$row=$db->one('SELECT system_01_estado FROM system_01_modulos WHERE id_system_01=?','i',[$id]);if(!$row)app_json(['ok'=>false,'message'=>'Modulo inexistente.'],404);$db->execute('UPDATE system_01_modulos SET system_01_estado=? WHERE id_system_01=?','ii',[(int)$row['system_01_estado']===1?0:1,$id]);app_log('MODULO_ESTADO','Modulo '.$id,'OK',1);app_json(['ok'=>true,'message'=>'Estado actualizado.']);}
if($action!=='save')app_json(['ok'=>false,'message'=>'Accion invalida.'],400);
$name=app_post('name');$path=app_post('path');$type=app_post('type');$sort=app_int('sort');$visible=app_post('visible');
if($name===''||!in_array($path,['home','control','ofertas','noticias','root'],true)||!in_array($type,['public','acces','root'],true)||!in_array($visible,['on','off'],true))app_json(['ok'=>false,'message'=>'Revisa los datos del modulo.'],422);
if($id)$db->execute('UPDATE system_01_modulos SET system_01_modulo=?,system_01_tipo=?,system_01_path_home=?,system_01_onoff=?,system_01_orden=? WHERE id_system_01=?','ssssii',[$name,$type,$path,$visible,$sort,$id]);
else $id=$db->insert('INSERT INTO system_01_modulos (system_01_modulo,system_01_tipo,system_01_path_home,system_01_onoff,system_01_orden,system_01_estado) VALUES (?,?,?,?,?,1)','ssssi',[$name,$type,$path,$visible,$sort]);
app_log('MODULO_GUARDAR','Modulo '.$id,'OK',1);app_json(['ok'=>true,'message'=>'Modulo guardado.']);

