<?php
declare(strict_types=1);
require __DIR__.'/neuria_coordinador.php';$user=app_require_permission('noticias');app_verify_csrf();$action=app_post('action');app_require_permission('noticias',$action==='sync'?['A','M','E']:'A');$service=new NeuriaSolicitudes(app_db(),new NeuriaV1Cliente());
try{
    if($action==='create'){$urls=preg_split('/\R+/',app_post('initial_urls'))?:[];$result=$service->crear(['title'=>app_post('title'),'description'=>app_post('description'),'specific_instructions'=>app_post('specific_instructions'),'category'=>app_post('category'),'priority'=>app_post('priority','NORMAL'),'web_search_enabled'=>isset($_POST['web_search_enabled']),'initial_urls'=>$urls],(int)$user['id']);if(!$result['ok'])app_json(['ok'=>false,'message'=>'Neuria rechazó la solicitud: '.$result['error']],422);app_log($result['duplicada']?'NEURIA_DUPLICADA':'A:NEURIA_SOLICITUD','Solicitud local #'.(int)($result['request']['id']??0));app_json(['ok'=>true,'message'=>$result['duplicada']?'La solicitud ya había sido enviada.':'Solicitud enviada a Neuria.']);}
    if($action==='sync'){
        $result=(new NeuriaCoordinador(app_db(),new NeuriaV1Cliente()))->sincronizar();
        app_log('M:NEURIA_SYNC','Solicitudes actualizadas: '.$result['actualizadas'].'; noticias importadas: '.$result['importadas'].'; errores: '.$result['errores'],$result['ok']?'OK':'PARCIAL');
        $fallidos=[];
        foreach(['solicitudes','contenidos','cambios'] as $flujo) if(!$result[$flujo]['ok']) $fallidos[]=$flujo;
        $message='Sincronización finalizada: '.$result['actualizadas'].' solicitudes actualizadas, '.$result['importadas'].' noticias importadas.';
        if($fallidos) $message.=' No se completaron los flujos: '.implode(', ',$fallidos).'. Revisá el estado de sincronización y volvé a intentar.';
        app_json(['ok'=>$result['ok'],'message'=>$message,'stats'=>$result],$result['ok']?200:207);
    }
    app_json(['ok'=>false,'message'=>'Acción inválida.'],400);
}catch(InvalidArgumentException $e){app_json(['ok'=>false,'message'=>$e->getMessage()],422);}catch(Throwable $e){app_json(['ok'=>false,'message'=>'No fue posible comunicarse con Neuria.'],503);}
