<?php
declare(strict_types=1);
require __DIR__ . '/../../../../lib/app.php';
require_once __DIR__ . '/../../home/php/neuria_sanitizador.php';
app_require_mode([0,1,2]);app_verify_csrf();
$db=app_db();$action=app_post('action');$id=app_int('id');$user=app_user();
$moduleId=(int)($db->one("SELECT id_system_01 FROM system_01_modulos WHERE system_01_path_home='noticias' LIMIT 1")['id_system_01']??0);

if($action==='file_remove'){
    $file=$db->one('SELECT id,noticia_id FROM noticia_archivos WHERE id=? AND deleted_at IS NULL','i',[$id]);if(!$file)app_json(['ok'=>false,'message'=>'Archivo inexistente.'],404);
    $db->execute('UPDATE noticia_archivos SET deleted_at=NOW() WHERE id=?','i',[$id]);app_log('NOTICIA_ARCHIVO','Archivo '.$id,'OK',$moduleId);app_json(['ok'=>true,'message'=>'Archivo retirado de la noticia.']);
}
if(in_array($action,['cycle','archive','restore'],true)){
    $row=$db->one('SELECT id,estado,deleted_at,neuria_bloqueada FROM noticias WHERE id=?','i',[$id]);if(!$row)app_json(['ok'=>false,'message'=>'Noticia inexistente.'],404);
    if($action==='cycle'&&(int)$row['neuria_bloqueada']===1)app_json(['ok'=>false,'message'=>'Neuria suspendió o retiró este contenido. No puede publicarse.'],422);
    if($action==='cycle'){$states=['pendiente'=>'activa','activa'=>'suspendida','suspendida'=>'pendiente'];$next=$states[$row['estado']]??'pendiente';$db->execute("UPDATE noticias SET estado=?,estado_revision=IF(origen='neuria' AND ?='activa','revisada',estado_revision),publicacion_at=CASE WHEN ?='activa' THEN COALESCE(publicacion_at,NOW()) ELSE publicacion_at END,actualizado_por=? WHERE id=?",'sssii',[$next,$next,$next,$user['id'],$id]);$message='Estado actualizado a '.$next.'.';}
    elseif($action==='archive'){$db->execute("UPDATE noticias SET deleted_at=NOW(),estado_revision=IF(origen='neuria','rechazada',estado_revision),actualizado_por=? WHERE id=?",'ii',[$user['id'],$id]);$message='Noticia eliminada. Podés restaurarla desde el filtro de eliminados.';}
    else{$db->execute("UPDATE noticias SET deleted_at=NULL,estado='pendiente',actualizado_por=? WHERE id=?",'ii',[$user['id'],$id]);$message='Noticia restaurada como pendiente.';}
    app_log('NOTICIA_ESTADO','Noticia '.$id,'OK',$moduleId);app_json(['ok'=>true,'message'=>$message]);
}
if($action!=='save')app_json(['ok'=>false,'message'=>'Acción inválida.'],400);

function noticia_youtube(string $value): ?string
{
    $value=trim($value);if($value==='')return null;
    if(preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{11})~',$value,$match))return $match[1];
    return preg_match('/^[A-Za-z0-9_-]{11}$/',$value)?$value:null;
}
function noticia_archivos_recibidos(): array
{
    if(empty($_FILES['archivos'])||!is_array($_FILES['archivos']['name']))return [];
    $allowed=[
        'image/jpeg'=>['jpg','imagen',8388608],'image/png'=>['png','imagen',8388608],'image/webp'=>['webp','imagen',8388608],'image/gif'=>['gif','imagen',8388608],
        'application/pdf'=>['pdf','adjunto',15728640],'application/msword'=>['doc','adjunto',15728640],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>['docx','adjunto',15728640],
        'application/vnd.ms-excel'=>['xls','adjunto',15728640],'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>['xlsx','adjunto',15728640]
    ];
    $result=[];$finfo=new finfo(FILEINFO_MIME_TYPE);
    foreach($_FILES['archivos']['name'] as $index=>$name){$error=(int)$_FILES['archivos']['error'][$index];if($error===UPLOAD_ERR_NO_FILE)continue;if($error!==UPLOAD_ERR_OK)throw new RuntimeException('Uno de los archivos no pudo cargarse.');$tmp=(string)$_FILES['archivos']['tmp_name'][$index];$size=(int)$_FILES['archivos']['size'][$index];$mime=(string)$finfo->file($tmp);if(!isset($allowed[$mime]))throw new RuntimeException('Formato no permitido: '.basename((string)$name));[$extension,$type,$limit]=$allowed[$mime];if($size<1||$size>$limit)throw new RuntimeException('El archivo supera el tamaño permitido: '.basename((string)$name));$result[]=['tmp'=>$tmp,'original'=>mb_substr(basename((string)$name),0,255),'mime'=>$mime,'size'=>$size,'extension'=>$extension,'type'=>$type];}
    return $result;
}

$neuriaId=app_int('neuria_id');$title=app_post('titulo');$summary=app_post('bajada');$content=trim((string)($_POST['contenido']??''));$strapline=app_post('volanta');$section=app_post('seccion');$state=app_post('estado')?:'pendiente';$youtubeInput=app_post('youtube');$youtube=noticia_youtube($youtubeInput);$date=app_post('fecha_publicacion');$time=app_post('hora_publicacion');
if($title===''||mb_strlen($title)>240||$content==='')app_json(['ok'=>false,'message'=>'Título y texto completo son obligatorios.'],422);
if(mb_strlen($summary)>600||mb_strlen($strapline)>160||mb_strlen($section)>120||!in_array($state,['pendiente','activa','suspendida'],true))app_json(['ok'=>false,'message'=>'Revisá los campos editoriales.'],422);
if($youtubeInput!==''&&$youtube===null)app_json(['ok'=>false,'message'=>'Ingresá una URL o código válido de YouTube.'],422);
$publication=null;if($date!==''||$time!==''){$date=$date?:date('Y-m-d');$time=$time?:'00:00';$parsed=DateTime::createFromFormat('Y-m-d H:i',$date.' '.$time);if(!$parsed||$parsed->format('Y-m-d H:i')!==$date.' '.$time)app_json(['ok'=>false,'message'=>'La fecha u hora de publicación no es válida.'],422);$publication=$parsed->format('Y-m-d H:i:s');}
if($state==='activa'&&$publication===null)$publication=date('Y-m-d H:i:s');
if(!$id&&$neuriaId>0)$state='pendiente';
if($id&&$state==='activa'){$remote=$db->one('SELECT neuria_bloqueada FROM noticias WHERE id=?','i',[$id]);if((int)($remote['neuria_bloqueada']??0)===1)app_json(['ok'=>false,'message'=>'Neuria suspendió o retiró este contenido. No puede publicarse.'],422);}
$safeContent=neuria_sanitizar_html($content);if($safeContent==='')app_json(['ok'=>false,'message'=>'El texto completo quedó vacío después de validarlo.'],422);
try{$uploads=noticia_archivos_recibidos();}catch(RuntimeException $error){app_json(['ok'=>false,'message'=>$error->getMessage()],422);}
if($id&&!$db->one('SELECT id FROM noticias WHERE id=?','i',[$id]))app_json(['ok'=>false,'message'=>'Noticia inexistente.'],404);
if(!$id&&$neuriaId>0&&$db->one('SELECT id FROM noticias WHERE neuria_id=?','i',[$neuriaId]))app_json(['ok'=>false,'message'=>'Esta oferta de Neuria ya fue incorporada.'],409);
$moved=[];
try{
    $id=$db->transaction(function(AppDatabase $database)use($id,$neuriaId,$title,$summary,$safeContent,$strapline,$section,$youtube,$state,$publication,$user,$uploads,&$moved){
        if($id)$database->execute('UPDATE noticias SET titulo=?,bajada=?,contenido=?,volanta=?,seccion=?,youtube_codigo=?,estado=?,publicacion_at=?,actualizado_por=? WHERE id=?','ssssssssii',[$title,$summary,$safeContent,$strapline,$section,$youtube,$state,$publication,$user['id'],$id]);
        else $id=$database->insert('INSERT INTO noticias (neuria_id,origen,titulo,bajada,contenido,volanta,seccion,youtube_codigo,estado,publicacion_at,creado_por,actualizado_por) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)','isssssssssii',[$neuriaId>0?$neuriaId:null,$neuriaId>0?'neuria':'local',$title,$summary,$safeContent,$strapline,$section,$youtube,$state,$publication,$user['id'],$user['id']]);
        if($uploads){$relative='uploads/noticias/'.date('Y/m');$directory=dirname(__DIR__,4).DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$relative);if(!is_dir($directory)&&!mkdir($directory,0755,true)&&!is_dir($directory))throw new RuntimeException('No se pudo preparar la carpeta de archivos.');foreach($uploads as $index=>$file){$stored=bin2hex(random_bytes(18)).'.'.$file['extension'];$destination=$directory.DIRECTORY_SEPARATOR.$stored;if(!move_uploaded_file($file['tmp'],$destination))throw new RuntimeException('No se pudo guardar uno de los archivos.');$moved[]=$destination;$database->insert('INSERT INTO noticia_archivos (noticia_id,tipo,nombre_original,nombre_guardado,mime_type,tamano_bytes,ruta,orden) VALUES (?,?,?,?,?,?,?,?)','issssisi',[$id,$file['type'],$file['original'],$stored,$file['mime'],$file['size'],$relative.'/'.$stored,$index]);}}
        return $id;
    });
}catch(Throwable $error){foreach($moved as $path)if(is_file($path))@unlink($path);app_json(['ok'=>false,'message'=>$error instanceof mysqli_sql_exception?'No se pudo guardar porque la noticia ya existe.':$error->getMessage()],409);}
if($state==='activa')$db->execute("UPDATE noticias SET estado_revision=IF(origen='neuria','revisada',estado_revision) WHERE id=?",'i',[$id]);
app_log('NOTICIA_GUARDAR','Noticia '.$id,'OK',$moduleId);app_json(['ok'=>true,'id'=>$id,'message'=>$neuriaId>0?'Oferta incorporada como noticia pendiente.':'Noticia guardada correctamente.']);
