<?php
declare(strict_types=1);
require __DIR__ . '/../../../../lib/app.php';
app_require_mode([0,1,2]);
app_verify_csrf();

$db=app_db();$action=app_post('action');$id=app_int('id');
$moduleId=(int)($db->one("SELECT id_system_01 FROM system_01_modulos WHERE system_01_path_home='ofertas' LIMIT 1")['id_system_01']??0);

if(in_array($action,['publication','archive','restore'],true)){
    $offer=$db->one('SELECT id,estado_publicacion,deleted_at FROM ofertas WHERE id=?','i',[$id]);
    if(!$offer)app_json(['ok'=>false,'message'=>'Oferta inexistente.'],404);
    if($action==='publication'){
        if($offer['deleted_at']!==null)app_json(['ok'=>false,'message'=>'Restaurá la oferta antes de publicarla.'],422);
        $next=$offer['estado_publicacion']==='publicada'?'borrador':'publicada';
        $db->execute("UPDATE ofertas SET estado_publicacion=?,publicada_at=CASE WHEN ?='publicada' THEN COALESCE(publicada_at,NOW()) ELSE publicada_at END WHERE id=?",'ssi',[$next,$next,$id]);
        $message=$next==='publicada'?'Oferta publicada.':'Oferta pasada a borrador.';
    }elseif($action==='archive'){
        $db->execute("UPDATE ofertas SET deleted_at=NOW(),estado_publicacion='archivada' WHERE id=?",'i',[$id]);$message='Oferta eliminada. Podés restaurarla desde el filtro de eliminados.';
    }else{
        $db->execute("UPDATE ofertas SET deleted_at=NULL,estado_publicacion='borrador' WHERE id=?",'i',[$id]);$message='Oferta restaurada como borrador.';
    }
    app_log('OFERTA_ESTADO','Oferta '.$id,'OK',$moduleId);app_json(['ok'=>true,'message'=>$message]);
}
if($action!=='save')app_json(['ok'=>false,'message'=>'Acción inválida.'],400);

function oferta_slug(string $value): string
{
    $ascii=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',trim($value));
    $slug=strtolower((string)$ascii);
    return trim((string)preg_replace('/[^a-z0-9]+/','-',$slug),'-');
}
function oferta_fecha(string $value): ?string
{
    if($value==='')return null;
    $parts=explode('-',$value);
    return count($parts)===3&&checkdate((int)$parts[1],(int)$parts[2],(int)$parts[0])?$value:null;
}
function oferta_ids(string $key): array
{
    $values=$_POST[$key]??[];
    if(!is_array($values))return [];
    return array_values(array_unique(array_filter(array_map('intval',$values),fn(int $value):bool=>$value>0)));
}
function oferta_youtube(string $value): ?string
{
    $value=trim($value);if($value==='')return null;
    if(preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{11})~',$value,$match))return $match[1];
    return preg_match('/^[A-Za-z0-9_-]{11}$/',$value)?$value:null;
}
function oferta_imagen_recibida(): ?array
{
    if(empty($_FILES['imagen'])||(int)($_FILES['imagen']['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)return null;
    if((int)$_FILES['imagen']['error']!==UPLOAD_ERR_OK)throw new RuntimeException('La imagen no pudo cargarse.');
    $tmp=(string)$_FILES['imagen']['tmp_name'];$size=(int)$_FILES['imagen']['size'];$mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $permitidos=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if(!isset($permitidos[$mime]))throw new RuntimeException('La imagen debe ser JPG, PNG o WebP.');
    if($size<1||$size>8388608)throw new RuntimeException('La imagen no puede superar los 8 MB.');
    return ['tmp'=>$tmp,'extension'=>$permitidos[$mime]];
}
function oferta_eliminar_imagen_local(string $url): void
{
    if(!str_starts_with($url,'../uploads/ofertas/'))return;
    $root=str_replace('\\','/',dirname(__DIR__,4));
    $path=$root.'/'.substr($url,3);
    $real=is_file($path)?str_replace('\\','/',(string)realpath($path)):'';
    if($real!==''&&str_starts_with($real,$root.'/uploads/ofertas/'))@unlink($real);
}

$title=app_post('titulo');$slug=oferta_slug($title);$categoryId=app_int('categoria_id');$unitId=app_int('unidad_academica_id');$modalityId=app_int('modalidad_id');
$summary=app_post('resumen');$description=app_post('descripcion');$graduateTitle=app_post('titulo_otorgado');$profile=app_post('perfil_egresado');$requirements=app_post('requisitos_ingreso');
$durationRaw=app_post('duracion_valor');$duration=$durationRaw===''?null:(float)$durationRaw;$durationUnit=app_post('duracion_unidad')?:null;$hoursRaw=app_post('carga_horaria');$hours=$hoursRaw===''?null:(int)$hoursRaw;
$approval='';$featured=isset($_POST['destacada'])?1:0;$offerState=app_post('estado_oferta');$publication=app_post('estado_publicacion');$order=app_int('orden');
$youtubeInput=app_post('youtube');$youtube=oferta_youtube($youtubeInput);
$offerStates=['proximamente','inscripcion_abierta','en_curso','a_termino','cerrada'];$publicationStates=['borrador','publicada','archivada'];$durationUnits=['anios','cuatrimestres','meses','semanas','horas'];
if($title===''||mb_strlen($title)>200||$slug===''||mb_strlen($slug)>160||$categoryId<1||$modalityId<1)app_json(['ok'=>false,'message'=>'Completá título, categoría y modalidad.'],422);
if(!in_array($offerState,$offerStates,true)||!in_array($publication,$publicationStates,true)||($durationUnit!==null&&!in_array($durationUnit,$durationUnits,true)))app_json(['ok'=>false,'message'=>'Uno de los estados o la duración no es válido.'],422);
if($publication==='publicada'&&$summary==='')app_json(['ok'=>false,'message'=>'Para publicar, completá el resumen.'],422);
if($youtubeInput!==''&&$youtube===null)app_json(['ok'=>false,'message'=>'Ingresá una URL o código válido de YouTube.'],422);
$category=$db->one('SELECT id FROM catalogo_categorias WHERE id=? AND activo=1 AND admite_ofertas=1','i',[$categoryId]);
$modality=$db->one('SELECT id FROM modalidades WHERE id=? AND activo=1','i',[$modalityId]);
if(!$category||!$modality)app_json(['ok'=>false,'message'=>'La categoría o modalidad seleccionada no está disponible.'],422);
if($unitId>0&&!$db->one('SELECT id FROM unidades_academicas WHERE id=? AND activo=1','i',[$unitId]))app_json(['ok'=>false,'message'=>'La unidad académica no está disponible.'],422);
$existing=$id?$db->one('SELECT id,imagen_url FROM ofertas WHERE id=?','i',[$id]):null;
if($id&&!$existing)app_json(['ok'=>false,'message'=>'Oferta inexistente.'],404);
$duplicate=$db->one('SELECT id FROM ofertas WHERE slug=? AND id<>?','si',[$slug,$id]);if($duplicate)app_json(['ok'=>false,'message'=>'Ya existe una oferta con ese slug.'],409);

$callId=app_int('convocatoria_id');$callYear=app_int('convocatoria_anio');$callEdition=app_post('convocatoria_edicion');$callFrom=oferta_fecha(app_post('inscripcion_desde'));$callTo=oferta_fecha(app_post('inscripcion_hasta'));$callState=app_post('convocatoria_estado')?:'programada';$callCapacityRaw=app_post('convocatoria_cupo');$callCapacity=$callCapacityRaw===''?null:(int)$callCapacityRaw;$preUrl='';
if($callYear>0&&($callYear<2000||$callYear>2100||!in_array($callState,['programada','abierta','cerrada','finalizada'],true)))app_json(['ok'=>false,'message'=>'Revisá los datos de la convocatoria.'],422);
if(app_post('inscripcion_desde')!==''&&$callFrom===null||app_post('inscripcion_hasta')!==''&&$callTo===null)app_json(['ok'=>false,'message'=>'Las fechas de inscripción no son válidas.'],422);
if($callFrom&&$callTo&&$callFrom>$callTo)app_json(['ok'=>false,'message'=>'La fecha final de inscripción debe ser posterior a la inicial.'],422);

$oldImage=(string)($existing['imagen_url']??'');$imageUrl=!empty($_POST['quitar_imagen'])?'':$oldImage;$newImagePath=null;
try{$image=oferta_imagen_recibida();if($image){$relative='uploads/ofertas/'.date('Y/m');$directory=dirname(__DIR__,4).DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$relative);if(!is_dir($directory)&&!mkdir($directory,0755,true)&&!is_dir($directory))throw new RuntimeException('No se pudo preparar la carpeta de imágenes.');$stored=bin2hex(random_bytes(18)).'.'.$image['extension'];$newImagePath=$directory.DIRECTORY_SEPARATOR.$stored;if(!move_uploaded_file($image['tmp'],$newImagePath))throw new RuntimeException('No se pudo guardar la imagen.');$imageUrl='../'.$relative.'/'.$stored;}}catch(RuntimeException $error){app_json(['ok'=>false,'message'=>$error->getMessage()],422);}
$planTitles=is_array($_POST['plan_titulo']??null)?$_POST['plan_titulo']:[];$planContents=is_array($_POST['plan_contenido']??null)?$_POST['plan_contenido']:[];$plan=[];
foreach($planTitles as $index=>$planTitle){$planTitle=trim((string)$planTitle);$planContent=trim((string)($planContents[$index]??''));if($planTitle===''&&$planContent==='')continue;if($planTitle===''||$planContent===''||mb_strlen($planTitle)>150)app_json(['ok'=>false,'message'=>'Cada sección del plan debe tener título y contenido.'],422);$plan[]=['titulo'=>$planTitle,'contenido'=>$planContent];}

try{
    $id=$db->transaction(function(AppDatabase $database)use($id,$categoryId,$unitId,$modalityId,$slug,$title,$summary,$description,$graduateTitle,$profile,$requirements,$duration,$durationUnit,$hours,$approval,$imageUrl,$youtube,$featured,$offerState,$publication,$order,$callId,$callYear,$callEdition,$callFrom,$callTo,$callState,$callCapacity,$preUrl,$plan){
        $unit=$unitId>0?$unitId:null;
        if($id){
            $database->execute("UPDATE ofertas SET categoria_id=?,unidad_academica_id=?,modalidad_id=?,slug=?,titulo=?,resumen=?,descripcion=?,titulo_otorgado=?,perfil_egresado=?,requisitos_ingreso=?,duracion_valor=?,duracion_unidad=?,carga_horaria=?,norma_aprobacion=?,imagen_url=?,youtube_codigo=?,destacada=?,estado_oferta=?,estado_publicacion=?,publicada_at=CASE WHEN ?='publicada' THEN COALESCE(publicada_at,NOW()) ELSE publicada_at END,orden=? WHERE id=?",'iiisssssssdsisssisssii',[$categoryId,$unit,$modalityId,$slug,$title,$summary,$description,$graduateTitle,$profile,$requirements,$duration,$durationUnit,$hours,$approval,$imageUrl,$youtube,$featured,$offerState,$publication,$publication,$order,$id]);
        }else{
            $id=$database->insert("INSERT INTO ofertas (categoria_id,unidad_academica_id,modalidad_id,slug,titulo,resumen,descripcion,titulo_otorgado,perfil_egresado,requisitos_ingreso,duracion_valor,duracion_unidad,carga_horaria,norma_aprobacion,imagen_url,youtube_codigo,destacada,estado_oferta,estado_publicacion,publicada_at,orden) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,IF(?='publicada',NOW(),NULL),?)",'iiisssssssdsisssisssi',[$categoryId,$unit,$modalityId,$slug,$title,$summary,$description,$graduateTitle,$profile,$requirements,$duration,$durationUnit,$hours,$approval,$imageUrl,$youtube,$featured,$offerState,$publication,$publication,$order]);
        }
        $database->execute('DELETE FROM oferta_turno WHERE oferta_id=?','i',[$id]);
        $database->execute('DELETE FROM oferta_area WHERE oferta_id=?','i',[$id]);
        $database->execute('DELETE FROM oferta_plan_estudios WHERE oferta_id=?','i',[$id]);
        foreach($plan as $planOrder=>$section)$database->insert('INSERT INTO oferta_plan_estudios (oferta_id,titulo,contenido,orden) VALUES (?,?,?,?)','issi',[$id,$section['titulo'],$section['contenido'],$planOrder]);
        if($callYear>0){
            if($callId>0&&$database->one('SELECT id FROM oferta_convocatorias WHERE id=? AND oferta_id=?','ii',[$callId,$id]))$database->execute('UPDATE oferta_convocatorias SET anio=?,edicion=?,inscripcion_desde=?,inscripcion_hasta=?,cupo=?,url_preinscripcion=?,estado=? WHERE id=?','isssissi',[$callYear,$callEdition,$callFrom,$callTo,$callCapacity,$preUrl,$callState,$callId]);
            else $database->insert('INSERT INTO oferta_convocatorias (oferta_id,anio,edicion,inscripcion_desde,inscripcion_hasta,cupo,url_preinscripcion,estado) VALUES (?,?,?,?,?,?,?,?)','iisssiss',[$id,$callYear,$callEdition,$callFrom,$callTo,$callCapacity,$preUrl,$callState]);
        }
        return $id;
    });
}catch(mysqli_sql_exception $error){if($newImagePath&&is_file($newImagePath))@unlink($newImagePath);app_json(['ok'=>false,'message'=>'No se pudo guardar. Revisá que el título y la edición de la convocatoria no estén repetidos.'],409);}
if($oldImage!==''&&$oldImage!==$imageUrl)oferta_eliminar_imagen_local($oldImage);
app_log('OFERTA_GUARDAR','Oferta '.$id,'OK',$moduleId);app_json(['ok'=>true,'id'=>$id,'message'=>'Oferta guardada correctamente.']);
