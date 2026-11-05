<?php
declare(strict_types=1);
// Integración local: cuentas/noticia temporales; ningún llamado a NEURIA.
if (PHP_SAPI!=='cli') exit;
ob_start();
require __DIR__.'/../lib/app.php';
function permiso_check(bool $ok,string $mensaje): void {
    if (!$ok) throw new RuntimeException($mensaje);
    echo 'OK: '.$mensaje.PHP_EOL;
}
$db=app_db();$servicio=new PermisosModulos($db);
$root=$db->one('SELECT id_system_03 FROM system_03_usuarios WHERE system_03_modo=0 AND system_03_estado=1 LIMIT 1');
if (!$root) throw new RuntimeException('Se necesita Root local activo.');
$modulos=array_column($db->all('SELECT id_system_01,system_01_path_home FROM system_01_modulos'),'id_system_01','system_01_path_home');
$usuarioId=0;$noticiaId=0;$sesiones=[];
$crearSesion=function(int $id) use (&$sesiones): array {
    if (session_status()===PHP_SESSION_ACTIVE) session_write_close();
    session_id(bin2hex(random_bytes(20)));session_start();
    // Una sesión con modo obsoleto/suplantado no debe conferir privilegios.
    $_SESSION=['user_id'=>$id,'user_mode'=>0,'role_id'=>1,'site_id'=>1,'user_name'=>'Prueba permisos'];
    $token=app_csrf_token();$sid=session_id();$sesiones[]=$sid;session_write_close();
    return [$sid,$token];
};
$peticion=function(string $ruta,array $datos,array $sesion,bool $post=true): array {
    $curl=curl_init('http://localhost/isfd.com.ar/'.$ruta);
    curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIE=>'ISFDSESSID='.$sesion[0],CURLOPT_TIMEOUT=>20]);
    if($post)curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($datos+['csrf_token'=>$sesion[1]])]);
    $body=curl_exec($curl);$status=(int)curl_getinfo($curl,CURLINFO_HTTP_CODE);$error=curl_error($curl);curl_close($curl);
    if($body===false)throw new RuntimeException($error);
    return ['status'=>$status,'body'=>$body,'json'=>json_decode($body,true)];
};
$ruta=fn(string $mod,string $archivo='app_home.php'):string=>'sistema/modulos/'.$mod.'/php/'.$archivo;
try {
    $usuarioId=$db->insert("INSERT INTO system_03_usuarios (rela_system_07,rela_system_06,system_03_usuario,system_03_clave,system_03_cuir,system_03_modo,system_03_estado,system_03_fecha,system_03_hora) VALUES (2,1,?,?,0,1,1,CURDATE(),CURTIME())",'ss',['9'.random_int(100000000,999999999),password_hash(bin2hex(random_bytes(20)),PASSWORD_DEFAULT)]);
    $sesion=$crearSesion($usuarioId);$sesionRoot=$crearSesion((int)$root['id_system_03']);
    foreach ([1,2,3] as $modo) permiso_check(!$servicio->permite(['id'=>$usuarioId,'mode'=>$modo],'control'),'Sin asignación no hay acceso: modo '.$modo);
    permiso_check($servicio->permite(['id'=>$root['id_system_03'],'mode'=>0],'root','E'),'Root conserva acceso completo');
    foreach (['control','ofertas','noticias','contactos','root'] as $mod) permiso_check($peticion($ruta($mod),[],$sesion)['status']===403,'Acceso directo bloqueado a '.$mod);
    permiso_check($peticion('chat/admin.php',[],$sesion,false)['status']===403,'Chat requiere acceso a Contactos');
    permiso_check($peticion($ruta('control','permisos_action.php'),['id'=>$usuarioId,'permisos'=>[]],$sesion)['status']===403,'Director no puede otorgarse permisos');
    permiso_check($peticion($ruta('control','_interfaz.php'),[],$sesion)['status']===410,'Controlador antiguo retirado');
    $asignacion=[];
    foreach (['control','ofertas','noticias','contactos'] as $mod) $asignacion[$modulos[$mod]]=['acceso'=>1];
    $r=$peticion($ruta('control','permisos_action.php'),['id'=>$usuarioId,'permisos'=>$asignacion],$sesionRoot);
    permiso_check($r['status']===200 && !empty($r['json']['ok']),'Root guarda accesos desde el endpoint');
    permiso_check($peticion($ruta('control','permisos_action.php'),['id'=>$usuarioId,'csrf_token'=>'invalido'],$sesionRoot)['status']===403,'Asignación protegida por CSRF');
    permiso_check($peticion($ruta('control','permisos_action.php'),['id'=>$root['id_system_03']],$sesionRoot)['status']===422,'No se reemplazan los permisos implícitos de Root');
    foreach (['control','ofertas','noticias','contactos'] as $mod) permiso_check($peticion($ruta($mod),[],$sesion)['status']===200,'Lectura habilitada para '.$mod);
    foreach ([
        ['control','app_action.php',['action'=>'save']],['control','app_action.php',['action'=>'toggle','id'=>$root['id_system_03']]],
        ['ofertas','app_action.php',['action'=>'save']],['ofertas','app_action.php',['action'=>'archive','id'=>1]],
        ['ofertas','app_action.php',['action'=>'publication','id'=>1]],['ofertas','categorias_action.php',['action'=>'save']],
        ['ofertas','categorias_action.php',['action'=>'toggle','id'=>1]],['ofertas','categorias_action.php',['action'=>'delete','id'=>1]],
        ['noticias','app_action.php',['action'=>'save']],['noticias','app_action.php',['action'=>'cycle','id'=>1]],
        ['noticias','app_adjuntos_action.php',['action'=>'upload']],['noticias','app_adjuntos_action.php',['action'=>'file_remove']],
        ['noticias','app_neuria_action.php',['action'=>'create']],['noticias','app_neuria_action.php',['action'=>'sync']],
        ['contactos','app_action.php',['action'=>'attend','id'=>1]],['contactos','preinscripcion_action.php',['id'=>1]]
    ] as [$mod,$archivo,$datos]) permiso_check($peticion($ruta($mod,$archivo),$datos,$sesion)['status']===403,'Acción bloqueada: '.$mod.'/'.$archivo.' '.($datos['action']??'gestionar'));
    permiso_check($peticion('chat/admin.php',['accion'=>'suspender','usuario_id'=>1],$sesion)['status']===403,'Moderación bloqueada sin E');
    $nav=$peticion('mipanel',[],$sesion,false)['body'];
    permiso_check(str_contains($nav,'data-module="contactos"')&&!str_contains($nav,'data-module="root"'),'Navegación filtrada por acceso');
    $form=$peticion($ruta('control','permisos_form.php'),['id'=>$usuarioId],$sesionRoot);
    permiso_check($form['status']===200 && str_contains($form['body'],'permission-access'),'Formulario de asignación disponible para Root');
    $asignacion[$modulos['noticias']]+=['A'=>1,'M'=>1];
    $asignacion[$modulos['ofertas']]+=['A'=>1,'M'=>1];
    $asignacion[$modulos['control']]+=['A'=>1,'M'=>1,'E'=>1];
    $servicio->guardar($usuarioId,$asignacion);
    $texto=['action'=>'save','titulo'=>'Prueba permisos '.bin2hex(random_bytes(5)),'contenido'=>'Texto temporal.','estado'=>'pendiente'];
    $r=$peticion($ruta('noticias','app_action.php'),$texto,$sesion);$noticiaId=(int)($r['json']['id']??0);
    permiso_check($noticiaId>0,'A permite crear noticia pendiente');
    $r=$peticion($ruta('noticias','app_action.php'),array_replace($texto,['id'=>$noticiaId,'estado'=>'activa']),$sesion);
    permiso_check($r['status']===403 && $db->one('SELECT estado FROM noticias WHERE id=?','i',[$noticiaId])['estado']==='pendiente','M no permite publicar mediante save');
    $r=$peticion($ruta('noticias','app_action.php'),array_replace($texto,['id'=>$noticiaId,'contenido'=>'Edición permitida.']),$sesion);
    permiso_check(!empty($r['json']['ok']),'M permite editar texto conservando estado');
    permiso_check($peticion($ruta('ofertas','app_action.php'),['action'=>'save','estado_publicacion'=>'publicada'],$sesion)['status']===403,'A no publica ofertas sin E');
    $datos=['action'=>'save','nombre'=>'Prueba','apellido'=>'Permisos','usuario'=>'99999999999','password'=>'Temporal-no-se-usa','role_id'=>1];
    permiso_check($peticion($ruta('control','app_action.php'),$datos,$sesion)['status']===403,'Director no puede crear un Root');
    permiso_check($peticion($ruta('control','app_action.php'),['action'=>'toggle','id'=>$root['id_system_03']],$sesion)['status']===403,'Director no puede desactivar Root');
    $asignacion[$modulos['noticias']]['E']=1;
    $servicio->guardar($usuarioId,$asignacion);
    // Suspender una nota temporal no la publica en el portal.
    permiso_check(!empty($peticion($ruta('noticias','app_action.php'),array_replace($texto,['id'=>$noticiaId,'estado'=>'suspendida']),$sesion)['json']['ok']),'E permite cambiar estado');
    $servicio->guardar($usuarioId,[]);
    permiso_check($peticion($ruta('noticias'),[],$sesion)['status']===403,'Revocar acceso afecta sesión abierta');
    $db->execute('UPDATE system_03_usuarios SET system_03_estado=2 WHERE id_system_03=?','i',[$usuarioId]);
    permiso_check($peticion($ruta('control'),[],$sesion)['status']===401,'Cuenta desactivada invalida sesión abierta');
} finally {
    if($noticiaId)$db->execute('DELETE FROM noticias WHERE id=? AND creado_por=?','ii',[$noticiaId,$usuarioId]);
    if($usuarioId) {
        $db->execute('DELETE FROM system_02_permisos WHERE rela_system_03=?','i',[$usuarioId]);
        $db->execute('DELETE FROM system_05_logistica WHERE rela_system_03=?','i',[$usuarioId]);
        $db->execute('DELETE FROM system_03_usuarios WHERE id_system_03=?','i',[$usuarioId]);
    }
    foreach($sesiones as $sid) { session_id($sid);session_start();session_destroy(); }
    ob_end_flush();
}
