<?php
declare(strict_types=1);
require __DIR__.'/../sistema/modulos/noticias/php/neuria_sync.php';
function nv1(bool $ok,string $message):void{if(!$ok){fwrite(STDERR,"FALLO: $message\n");exit(1);}echo "OK: $message\n";}
function nv1client(string $token='valid'):NeuriaV1Cliente{return new NeuriaV1Cliente(['url'=>'http://127.0.0.1:18087','token'=>$token,'timeout'=>2,'limit'=>50,'max_retries'=>2,'configurada'=>true]);}
$db=app_db();$uuid='11111111-1111-4111-8111-111111111111';$counter=sys_get_temp_dir().DIRECTORY_SEPARATOR.'neuria_mock_transient.count';@unlink($counter);
$db->execute('DELETE FROM noticias WHERE neuria_uuid=?','s',[$uuid]);$db->execute("UPDATE neuria_sincronizaciones SET cursor_valor=NULL,ultimo_error=NULL,recibidos=0,importados=0,omitidos=0,errores=0");
try{
    $me=nv1client()->identidad();nv1($me['ok']&&in_array('content:read',$me['data']['permissions']??[],true),'identidad y permiso content:read');
    $bad=nv1client('unauthorized')->identidad();nv1(!$bad['ok']&&$bad['error']==='invalid_credentials','credencial inválida rechazada');
    $limited=nv1client('limited')->contenidos();nv1(!$limited['ok']&&$limited['attempts']===3,'429 reintentado de forma limitada');
    $transient=nv1client('transient')->contenidos();nv1($transient['ok']&&$transient['attempts']===2,'recuperación después de error temporal');
    $sync=new NeuriaSincronizador($db,nv1client());$first=$sync->sincronizarContenidos();nv1($first['ok']&&$first['importados']===1,'noticia nueva importada como borrador');
    $row=$db->one('SELECT * FROM noticias WHERE neuria_uuid=?','s',[$uuid]);nv1($row&&$row['estado']==='pendiente'&&$row['titulo']==='Noticia sincronizada','contenido local inicial persistido');$newsId=(int)$row['id'];
    nv1($row['contenido']==="Primer párrafo.\n\nSegundo párrafo."&&!str_contains($row['contenido'],'<p>'),'body almacenado directamente como texto plano con párrafos');
    nv1(neuria_texto_plano_editor('<p>Uno</p><p>Dos<br>Tres</p>')==="Uno\n\nDos\nTres",'HTML histórico convertido a texto plano para editar');
    $db->execute("UPDATE noticias SET titulo='Edición local ISFD',contenido='<p>Edición local</p>' WHERE id=?",'i',[$newsId]);$second=$sync->sincronizarContenidos();$row=$db->one('SELECT * FROM noticias WHERE id=?','i',[$newsId]);nv1($second['omitidos']===1&&$row['titulo']==='Edición local ISFD'&&$row['neuria_titulo']==='Noticia sincronizada','duplicado omitido y edición local conservada');
    $cursor=$db->one("SELECT cursor_valor FROM neuria_sincronizaciones WHERE flujo='contenidos'");nv1($cursor['cursor_valor']==='cursor-content-1','cursor persistido después de la página');
    (new NeuriaSincronizador($db,nv1client('suspend')))->sincronizarCambios();$row=$db->one('SELECT estado,neuria_estado FROM noticias WHERE id=?','i',[$newsId]);nv1($row['estado']==='suspendida'&&$row['neuria_estado']==='suspended','suspensión aplicada');
    (new NeuriaSincronizador($db,nv1client('reactivate')))->sincronizarCambios();$row=$db->one('SELECT estado,neuria_estado FROM noticias WHERE id=?','i',[$newsId]);nv1($row['estado']==='pendiente'&&$row['neuria_estado']==='available','reactivación sin publicación automática');
    (new NeuriaSincronizador($db,nv1client('withdraw')))->sincronizarCambios();$row=$db->one('SELECT estado,neuria_estado FROM noticias WHERE id=?','i',[$newsId]);nv1($row['estado']==='suspendida'&&$row['neuria_estado']==='withdrawn','retiro conservado y fuera de publicación');
    $db->execute("UPDATE neuria_sincronizaciones SET cursor_valor='cursor-seguro' WHERE flujo='cambios'");$failed=(new NeuriaSincronizador($db,nv1client('bad-change')))->sincronizarCambios();$cursor=$db->one("SELECT cursor_valor FROM neuria_sincronizaciones WHERE flujo='cambios'");nv1(!$failed['ok']&&$cursor['cursor_valor']==='cursor-seguro','cursor anterior conservado ante error de página');
    nv1(!str_contains(json_encode($bad),'unauthorized'),'token ausente de respuestas');
}finally{$db->execute('DELETE FROM noticias WHERE neuria_uuid=?','s',[$uuid]);$db->execute("UPDATE neuria_sincronizaciones SET cursor_valor=NULL,ultimo_error=NULL,recibidos=0,importados=0,omitidos=0,errores=0");@unlink($counter);}
