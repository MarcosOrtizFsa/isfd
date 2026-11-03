<?php
declare(strict_types=1);
require __DIR__.'/../sistema/modulos/noticias/php/neuria_requests.php';
function nr(bool $ok,string $message):void{if(!$ok){fwrite(STDERR,"FALLO: $message\n");exit(1);}echo "OK: $message\n";}
$db=app_db();$requestId='22222222-2222-4222-8222-222222222222';$contentId='11111111-1111-4111-8111-111111111111';$client=new NeuriaV1Cliente(['url'=>'http://127.0.0.1:18087','token'=>'valid','timeout'=>2,'limit'=>50,'max_retries'=>2,'configurada'=>true]);$service=new NeuriaSolicitudes($db,$client);
// Tablas temporales: las pruebas no alteran noticias ni solicitudes reales.
foreach (['noticias', 'neuria_requests'] as $table) {
    $schema = $db->one('SHOW CREATE TABLE `' . $table . '`')['Create Table'];
    $schema = preg_replace('/^CREATE TABLE/', 'CREATE TEMPORARY TABLE', $schema);
    // InnoDB temporal no admite claves foráneas ni índices FULLTEXT.
    $schema = preg_replace('/,\n\s*(?:CONSTRAINT|FULLTEXT KEY)[^\n]+/', '', $schema);
    $db->execute($schema);
}
try{
    $input=['title'=>'Tema solicitado','description'=>'Investigar información comprobable.','specific_instructions'=>'Lenguaje claro.','category'=>'Educación','priority'=>'NORMAL','web_search_enabled'=>true,'initial_urls'=>['https://example.com/fuente']];
    $created=$service->crear($input,1);nr($created['ok']&&!$created['duplicada']&&$created['request']['status']==='PENDIENTE','solicitud creada y UUID guardado');
    $duplicate=$service->crear($input,1);nr($duplicate['ok']&&$duplicate['duplicada'],'doble envío evitado');
    $sync=$service->sincronizarPendientes();nr($sync['ok']&&$sync['importadas']===1,'solicitud completada e importada');
    $news=$db->one('SELECT * FROM noticias WHERE neuria_uuid=?','s',[$contentId]);nr($news&&$news['estado']==='pendiente'&&$news['estado_revision']==='pendiente'&&$news['neuria_request_id']===$requestId,'nota incorporada como borrador pendiente de revisión');
    nr($news['contenido']==="Primer párrafo.\n\nSegundo párrafo.",'solicitud importada sin envolver el body con HTML');
    $again=$service->sincronizarPendientes();nr($again['importadas']===0,'solicitud terminal no se importa nuevamente');
    foreach(['retry-pending'=>['PENDIENTE',0],'retry-research'=>['INVESTIGANDO',30],'retry-generating'=>['GENERANDO',70],'retry-error'=>['ERROR',10],'valid'=>['COMPLETADA',100]] as $token=>[$status,$progress]){
        $db->execute("UPDATE neuria_requests SET status='ERROR',error_message='Error anterior',completed_at=NOW()");
        $retryClient=new NeuriaV1Cliente(['url'=>'http://127.0.0.1:18087','token'=>$token,'timeout'=>2,'limit'=>50,'max_retries'=>0,'configurada'=>true]);
        $retry=(new NeuriaSolicitudes($db,$retryClient))->sincronizarPendientes();
        $row=$db->one('SELECT * FROM neuria_requests WHERE neuria_request_id=?','s',[$requestId]);
        nr($retry['ok']&&$retry['consultadas']===1&&$row['status']===$status&&(int)$row['progress']===$progress,'ERROR se vuelve a consultar: '.$status);
        nr($status==='ERROR'?$row['error_message']!==null:$row['error_message']===null,'error actualizado según estado remoto: '.$status);
        if(in_array($status,['PENDIENTE','INVESTIGANDO','GENERANDO'],true))nr($row['completed_at']===null,'fecha de cierre limpiada: '.$status);
        nr((int)$db->one('SELECT COUNT(*) total FROM noticias')['total']===1,'recuperación sin duplicar noticias: '.$status);
    }
    // Recuperación directa sin contenido previamente importado.
    $db->execute('DELETE FROM noticias');
    $db->execute("UPDATE neuria_requests SET status='ERROR',error_message='Error anterior'");
    $recovered=$service->sincronizarPendientes();
    nr($recovered['ok']&&$recovered['importadas']===1,'ERROR a COMPLETADA descarga contenido nuevo');
}finally{$db->execute('DELETE FROM noticias WHERE neuria_uuid=?','s',[$contentId]);$db->execute('DELETE FROM neuria_requests WHERE neuria_request_id=?','s',[$requestId]);}
