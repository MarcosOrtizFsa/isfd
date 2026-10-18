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

$title=app_post('titulo');$slug=oferta_slug(app_post('slug')?:$title);$categoryId=app_int('categoria_id');$unitId=app_int('unidad_academica_id');$modalityId=app_int('modalidad_id');
$summary=app_post('resumen');$description=app_post('descripcion');$graduateTitle=app_post('titulo_otorgado');$profile=app_post('perfil_egresado');$requirements=app_post('requisitos_ingreso');
$durationRaw=app_post('duracion_valor');$duration=$durationRaw===''?null:(float)$durationRaw;$durationUnit=app_post('duracion_unidad')?:null;$hoursRaw=app_post('carga_horaria');$hours=$hoursRaw===''?null:(int)$hoursRaw;
$approval=app_post('norma_aprobacion');$imageUrl=app_post('imagen_url');$featured=isset($_POST['destacada'])?1:0;$offerState=app_post('estado_oferta');$publication=app_post('estado_publicacion');$order=app_int('orden');
$shiftIds=oferta_ids('turnos');$areaIds=oferta_ids('areas');
$offerStates=['proximamente','inscripcion_abierta','en_curso','a_termino','cerrada'];$publicationStates=['borrador','publicada','archivada'];$durationUnits=['anios','cuatrimestres','meses','semanas','horas'];
if($title===''||mb_strlen($title)>200||$slug===''||mb_strlen($slug)>160||$categoryId<1||$modalityId<1)app_json(['ok'=>false,'message'=>'Completá título, categoría y modalidad.'],422);
if(!in_array($offerState,$offerStates,true)||!in_array($publication,$publicationStates,true)||($durationUnit!==null&&!in_array($durationUnit,$durationUnits,true)))app_json(['ok'=>false,'message'=>'Uno de los estados o la duración no es válido.'],422);
if($publication==='publicada'&&($summary===''||!$shiftIds))app_json(['ok'=>false,'message'=>'Para publicar, completá el resumen y al menos un turno.'],422);
if($imageUrl!==''&&!filter_var($imageUrl,FILTER_VALIDATE_URL))app_json(['ok'=>false,'message'=>'La URL de imagen no es válida.'],422);
$category=$db->one('SELECT id FROM catalogo_categorias WHERE id=? AND activo=1 AND admite_ofertas=1','i',[$categoryId]);
$modality=$db->one('SELECT id FROM modalidades WHERE id=? AND activo=1','i',[$modalityId]);
if(!$category||!$modality)app_json(['ok'=>false,'message'=>'La categoría o modalidad seleccionada no está disponible.'],422);
if($unitId>0&&!$db->one('SELECT id FROM unidades_academicas WHERE id=? AND activo=1','i',[$unitId]))app_json(['ok'=>false,'message'=>'La unidad académica no está disponible.'],422);
foreach([['turnos',$shiftIds],['areas_conocimiento',$areaIds]] as [$table,$ids])if($ids){$marks=implode(',',array_fill(0,count($ids),'?'));if(count($db->all("SELECT id FROM $table WHERE id IN ($marks) AND activo=1",str_repeat('i',count($ids)),$ids))!==count($ids))app_json(['ok'=>false,'message'=>'Hay clasificaciones seleccionadas que ya no están disponibles.'],422);}
if($id&&!$db->one('SELECT id FROM ofertas WHERE id=?','i',[$id]))app_json(['ok'=>false,'message'=>'Oferta inexistente.'],404);
$duplicate=$db->one('SELECT id FROM ofertas WHERE slug=? AND id<>?','si',[$slug,$id]);if($duplicate)app_json(['ok'=>false,'message'=>'Ya existe una oferta con ese slug.'],409);

$callId=app_int('convocatoria_id');$callYear=app_int('convocatoria_anio');$callEdition=app_post('convocatoria_edicion');$callFrom=oferta_fecha(app_post('inscripcion_desde'));$callTo=oferta_fecha(app_post('inscripcion_hasta'));$callState=app_post('convocatoria_estado')?:'programada';$callCapacityRaw=app_post('convocatoria_cupo');$callCapacity=$callCapacityRaw===''?null:(int)$callCapacityRaw;$preUrl=app_post('url_preinscripcion');
if($callYear>0&&($callYear<2000||$callYear>2100||!in_array($callState,['programada','abierta','cerrada','finalizada'],true)))app_json(['ok'=>false,'message'=>'Revisá los datos de la convocatoria.'],422);
if(app_post('inscripcion_desde')!==''&&$callFrom===null||app_post('inscripcion_hasta')!==''&&$callTo===null)app_json(['ok'=>false,'message'=>'Las fechas de inscripción no son válidas.'],422);
if($callFrom&&$callTo&&$callFrom>$callTo)app_json(['ok'=>false,'message'=>'La fecha final de inscripción debe ser posterior a la inicial.'],422);
if($preUrl!==''&&!filter_var($preUrl,FILTER_VALIDATE_URL))app_json(['ok'=>false,'message'=>'La URL de preinscripción no es válida.'],422);

try{
    $id=$db->transaction(function(AppDatabase $database)use($id,$categoryId,$unitId,$modalityId,$slug,$title,$summary,$description,$graduateTitle,$profile,$requirements,$duration,$durationUnit,$hours,$approval,$imageUrl,$featured,$offerState,$publication,$order,$shiftIds,$areaIds,$callId,$callYear,$callEdition,$callFrom,$callTo,$callState,$callCapacity,$preUrl){
        $unit=$unitId>0?$unitId:null;
        if($id){
            $database->execute("UPDATE ofertas SET categoria_id=?,unidad_academica_id=?,modalidad_id=?,slug=?,titulo=?,resumen=?,descripcion=?,titulo_otorgado=?,perfil_egresado=?,requisitos_ingreso=?,duracion_valor=?,duracion_unidad=?,carga_horaria=?,norma_aprobacion=?,imagen_url=?,destacada=?,estado_oferta=?,estado_publicacion=?,publicada_at=CASE WHEN ?='publicada' THEN COALESCE(publicada_at,NOW()) ELSE publicada_at END,orden=? WHERE id=?",'iiisssssssdsiisssssii',[$categoryId,$unit,$modalityId,$slug,$title,$summary,$description,$graduateTitle,$profile,$requirements,$duration,$durationUnit,$hours,$approval,$imageUrl,$featured,$offerState,$publication,$publication,$order,$id]);
        }else{
            $id=$database->insert("INSERT INTO ofertas (categoria_id,unidad_academica_id,modalidad_id,slug,titulo,resumen,descripcion,titulo_otorgado,perfil_egresado,requisitos_ingreso,duracion_valor,duracion_unidad,carga_horaria,norma_aprobacion,imagen_url,destacada,estado_oferta,estado_publicacion,publicada_at,orden) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,IF(?='publicada',NOW(),NULL),?)",'iiisssssssdsiisssssi',[$categoryId,$unit,$modalityId,$slug,$title,$summary,$description,$graduateTitle,$profile,$requirements,$duration,$durationUnit,$hours,$approval,$imageUrl,$featured,$offerState,$publication,$publication,$order]);
        }
        $database->execute('DELETE FROM oferta_turno WHERE oferta_id=?','i',[$id]);
        foreach($shiftIds as $shiftId)$database->insert('INSERT INTO oferta_turno (oferta_id,turno_id) VALUES (?,?)','ii',[$id,$shiftId]);
        $database->execute('DELETE FROM oferta_area WHERE oferta_id=?','i',[$id]);
        foreach($areaIds as $areaId)$database->insert('INSERT INTO oferta_area (oferta_id,area_id) VALUES (?,?)','ii',[$id,$areaId]);
        if($callYear>0){
            if($callId>0&&$database->one('SELECT id FROM oferta_convocatorias WHERE id=? AND oferta_id=?','ii',[$callId,$id]))$database->execute('UPDATE oferta_convocatorias SET anio=?,edicion=?,inscripcion_desde=?,inscripcion_hasta=?,cupo=?,url_preinscripcion=?,estado=? WHERE id=?','isssissi',[$callYear,$callEdition,$callFrom,$callTo,$callCapacity,$preUrl,$callState,$callId]);
            else $database->insert('INSERT INTO oferta_convocatorias (oferta_id,anio,edicion,inscripcion_desde,inscripcion_hasta,cupo,url_preinscripcion,estado) VALUES (?,?,?,?,?,?,?,?)','iisssiss',[$id,$callYear,$callEdition,$callFrom,$callTo,$callCapacity,$preUrl,$callState]);
        }
        return $id;
    });
}catch(mysqli_sql_exception $error){app_json(['ok'=>false,'message'=>'No se pudo guardar. Revisá que el slug y la edición de la convocatoria no estén repetidos.'],409);}
app_log('OFERTA_GUARDAR','Oferta '.$id,'OK',$moduleId);app_json(['ok'=>true,'id'=>$id,'message'=>'Oferta guardada correctamente.']);
