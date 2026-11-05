<?php
declare(strict_types=1);
require __DIR__ . '/../../../../lib/app.php';
require_once __DIR__ . '/../../home/php/neuria_sanitizador.php';
app_require_permission('noticias');app_verify_csrf();
$db=app_db();$action=app_post('action');$id=app_int('id');
app_require_permission('noticias', $action === 'save' ? ($id ? 'M' : 'A') : ($action === 'archive' ? 'B' : 'E'));
$user=app_user();
$moduleId=(int)($db->one("SELECT id_system_01 FROM system_01_modulos WHERE system_01_path_home='noticias' LIMIT 1")['id_system_01']??0);

if(in_array($action,['cycle','archive','restore'],true)){
    $row=$db->one('SELECT id,estado,deleted_at,neuria_bloqueada FROM noticias WHERE id=?','i',[$id]);if(!$row)app_json(['ok'=>false,'message'=>'Noticia inexistente.'],404);
    if($action==='cycle'&&(int)$row['neuria_bloqueada']===1)app_json(['ok'=>false,'message'=>'Neuria suspendió o retiró este contenido. No puede publicarse.'],422);
    if($action==='cycle'){$states=['pendiente'=>'activa','activa'=>'suspendida','suspendida'=>'pendiente'];$next=$states[$row['estado']]??'pendiente';$db->execute("UPDATE noticias SET estado=?,estado_revision=IF(origen='neuria' AND ?='activa','revisada',estado_revision),publicacion_at=CASE WHEN ?='activa' THEN COALESCE(publicacion_at,NOW()) ELSE publicacion_at END,actualizado_por=? WHERE id=?",'sssii',[$next,$next,$next,$user['id'],$id]);$message='Estado actualizado a '.$next.'.';}
    elseif($action==='archive'){$db->execute("UPDATE noticias SET deleted_at=NOW(),estado_revision=IF(origen='neuria','rechazada',estado_revision),actualizado_por=? WHERE id=?",'ii',[$user['id'],$id]);$message='Noticia eliminada. Podés restaurarla desde el filtro de eliminados.';}
    else{$db->execute("UPDATE noticias SET deleted_at=NULL,estado='pendiente',actualizado_por=? WHERE id=?",'ii',[$user['id'],$id]);$message='Noticia restaurada como pendiente.';}
    app_log('NOTICIA_ESTADO','Noticia '.$id,'OK',$moduleId);app_json(['ok'=>true,'message'=>$message]);
}
if($action!=='save')app_json(['ok'=>false,'message'=>'Acción inválida.'],400);

$actual=$id?$db->one('SELECT estado FROM noticias WHERE id=?','i',[$id]):null;
app_guard_state('noticias','estado',$actual['estado']??'pendiente');
$neuriaId=app_int('neuria_id');$title=app_post('titulo');$summary=app_post('bajada');$content=trim((string)($_POST['contenido']??''));$strapline=app_post('volanta');$section=app_post('seccion');$state=app_post('estado')?:'pendiente';$date=app_post('fecha_publicacion');$time=app_post('hora_publicacion');
$existingRecord=$id?$db->one('SELECT id,origen,neuria_bloqueada FROM noticias WHERE id=?','i',[$id]):null;
if($id&&!$existingRecord)app_json(['ok'=>false,'message'=>'Noticia inexistente.'],404);
$isNeuria=($existingRecord['origen']??'')==='neuria'||(!$id&&$neuriaId>0);
if($title===''||mb_strlen($title)>240||$content==='')app_json(['ok'=>false,'message'=>'Título y texto completo son obligatorios.'],422);
if(mb_strlen($summary)>600||mb_strlen($strapline)>160||mb_strlen($section)>120||!in_array($state,['pendiente','activa','suspendida'],true))app_json(['ok'=>false,'message'=>'Revisá los campos editoriales.'],422);
$publication=null;if($date!==''||$time!==''){$date=$date?:date('Y-m-d');$time=$time?:'00:00';$parsed=DateTime::createFromFormat('Y-m-d H:i',$date.' '.$time);if(!$parsed||$parsed->format('Y-m-d H:i')!==$date.' '.$time)app_json(['ok'=>false,'message'=>'La fecha u hora de publicación no es válida.'],422);$publication=$parsed->format('Y-m-d H:i:s');}
if($state==='activa'&&$publication===null)$publication=date('Y-m-d H:i:s');
if(!$id&&$neuriaId>0)$state='pendiente';
if($id&&$state==='activa'&&(int)($existingRecord['neuria_bloqueada']??0)===1)app_json(['ok'=>false,'message'=>'Neuria suspendió o retiró este contenido. No puede publicarse.'],422);
// Las noticias de NEURIA se editan y almacenan como texto plano. Se conservan
// los saltos dobles entre párrafos y el HTML se genera recién en la portada.
$storedContent=$isNeuria?neuria_texto_plano_editor($content):neuria_sanitizar_html($content);
if($storedContent==='')app_json(['ok'=>false,'message'=>'El texto completo quedó vacío después de validarlo.'],422);
if(!$id&&$neuriaId>0&&$db->one('SELECT id FROM noticias WHERE neuria_id=?','i',[$neuriaId]))app_json(['ok'=>false,'message'=>'Esta oferta de Neuria ya fue incorporada.'],409);
// El contenido editorial se guarda sin modificar imágenes, documentos ni video.
try{
    $id=$db->transaction(function(AppDatabase $database)use($id,$neuriaId,$title,$summary,$storedContent,$strapline,$section,$state,$publication,$user){
        if($id)$database->execute('UPDATE noticias SET titulo=?,bajada=?,contenido=?,volanta=?,seccion=?,estado=?,publicacion_at=?,actualizado_por=? WHERE id=?','sssssssii',[$title,$summary,$storedContent,$strapline,$section,$state,$publication,$user['id'],$id]);
        else $id=$database->insert('INSERT INTO noticias (neuria_id,origen,titulo,bajada,contenido,volanta,seccion,estado,publicacion_at,creado_por,actualizado_por) VALUES (?,?,?,?,?,?,?,?,?,?,?)','issssssssii',[$neuriaId>0?$neuriaId:null,$neuriaId>0?'neuria':'local',$title,$summary,$storedContent,$strapline,$section,$state,$publication,$user['id'],$user['id']]);
        return $id;
    });
}catch(Throwable $error){app_json(['ok'=>false,'message'=>$error instanceof mysqli_sql_exception?'No se pudo guardar porque la noticia ya existe.':$error->getMessage()],409);}
if($state==='activa')$db->execute("UPDATE noticias SET estado_revision=IF(origen='neuria','revisada',estado_revision) WHERE id=?",'i',[$id]);
app_log('NOTICIA_GUARDAR','Noticia '.$id,'OK',$moduleId);app_json(['ok'=>true,'id'=>$id,'message'=>$neuriaId>0?'Oferta incorporada como noticia pendiente.':'Noticia guardada correctamente.']);
