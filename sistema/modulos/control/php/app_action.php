<?php
declare(strict_types=1);
require __DIR__ . '/../../../../lib/app.php';
$current = app_require_mode([0, 1, 2]);
app_verify_csrf();
$db = app_db(); $action = app_post('action'); $id = app_int('id');
if ($action === 'toggle') {
    if ($id < 1 || $id === $current['id']) app_json(['ok'=>false,'message'=>'No podes desactivar tu propia cuenta.'],422);
    $target=$db->one('SELECT system_03_modo,system_03_estado FROM system_03_usuarios WHERE id_system_03=?','i',[$id]);
    if(!$target || ($current['mode']!==0 && (int)$target['system_03_modo'] <= $current['mode'])) app_json(['ok'=>false,'message'=>'No tenes permisos sobre esa cuenta.'],403);
    $db->execute('UPDATE system_03_usuarios SET system_03_estado=? WHERE id_system_03=?','ii',[(int)$target['system_03_estado']===1?2:1,$id]);
    app_log('USUARIO_ESTADO','Usuario '.$id,'OK',2); app_json(['ok'=>true,'message'=>'Estado actualizado.']);
}
if ($action !== 'save') app_json(['ok'=>false,'message'=>'Accion invalida.'],400);
$name=app_post('nombre'); $surname=app_post('apellido'); $username=preg_replace('/\D+/','',app_post('usuario')); $email=app_post('email'); $phone=app_post('celular'); $password=app_post('password'); $roleId=app_int('role_id');
if($name===''||$surname===''||$username===''||$roleId<1||(!$id&&strlen($password)<10)) app_json(['ok'=>false,'message'=>'Completa los campos obligatorios. La clave debe tener al menos 10 caracteres.'],422);
if($email!==''&&!filter_var($email,FILTER_VALIDATE_EMAIL)) app_json(['ok'=>false,'message'=>'El email no es valido.'],422);
$role=$db->one('SELECT id_system_07 FROM system_07_privilegios WHERE id_system_07=?','i',[$roleId]); if(!$role||($roleId===1&&$current['mode']!==0)) app_json(['ok'=>false,'message'=>'Rol invalido.'],403);
$mode=$roleId===1?0:($roleId===2?1:($roleId<=4?2:3));
$duplicate=$db->one('SELECT id_system_03 FROM system_03_usuarios WHERE system_03_usuario=? AND id_system_03<>?','si',[$username,$id]); if($duplicate) app_json(['ok'=>false,'message'=>'Ese usuario ya existe.'],409);
if($id){
    $target=$db->one('SELECT system_03_modo FROM system_03_usuarios WHERE id_system_03=?','i',[$id]); if(!$target||($current['mode']!==0&&(int)$target['system_03_modo']<=$current['mode'])) app_json(['ok'=>false,'message'=>'No tenes permisos sobre esa cuenta.'],403);
    $db->execute('UPDATE system_03_usuarios SET rela_system_07=?,system_03_usuario=?,system_03_modo=? WHERE id_system_03=?','isii',[$roleId,$username,$mode,$id]);
    if($password!==''){if(strlen($password)<10)app_json(['ok'=>false,'message'=>'La clave debe tener al menos 10 caracteres.'],422);$db->execute('UPDATE system_03_usuarios SET system_03_clave=? WHERE id_system_03=?','si',[password_hash($password,PASSWORD_DEFAULT),$id]);}
    $exists=$db->one('SELECT id_system_04 FROM system_04_perfil WHERE rela_system_03=?','i',[$id]);
    if($exists)$db->execute('UPDATE system_04_perfil SET system_04_nombre=?,system_04_apellido=?,system_04_dni=?,system_04_email=?,system_04_celular=? WHERE rela_system_03=?','sssssi',[$name,$surname,substr($username,0,8),$email,$phone,$id]);
    else $db->insert("INSERT INTO system_04_perfil (rela_system_03,system_04_nombre,system_04_apellido,system_04_dni,system_04_cuil,system_04_provincia,system_04_localidad,system_04_barrio,system_04_direccion,system_04_maps,system_04_email,system_04_telefono,system_04_celular,system_04_detalles) VALUES (?,?,?,?,0,'','','','','',?,'',?,'')",'isssss',[$id,$name,$surname,substr($username,0,8),$email,$phone]);
}else{
    $id=$db->insert('INSERT INTO system_03_usuarios (rela_system_07,rela_system_06,system_03_usuario,system_03_clave,system_03_cuir,system_03_modo,system_03_estado,system_03_fecha,system_03_hora) VALUES (?,1,?,?,0,?,1,CURDATE(),CURTIME())','issi',[$roleId,$username,password_hash($password,PASSWORD_DEFAULT),$mode]);
    $db->insert("INSERT INTO system_04_perfil (rela_system_03,system_04_nombre,system_04_apellido,system_04_dni,system_04_cuil,system_04_provincia,system_04_localidad,system_04_barrio,system_04_direccion,system_04_maps,system_04_email,system_04_telefono,system_04_celular,system_04_detalles) VALUES (?,?,?,?,0,'','','','','',?,'',?,'')",'isssss',[$id,$name,$surname,substr($username,0,8),$email,$phone]);
}
app_log('USUARIO_GUARDAR','Usuario '.$id,'OK',2); app_json(['ok'=>true,'message'=>'Usuario guardado.']);

