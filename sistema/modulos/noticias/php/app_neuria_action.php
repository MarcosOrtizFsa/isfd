<?php
declare(strict_types=1);
require __DIR__.'/neuria_requests.php';$user=app_require_mode([0,1,2]);app_verify_csrf();$action=app_post('action');$service=new NeuriaSolicitudes(app_db(),new NeuriaV1Cliente());
try{
    if($action==='create'){$urls=preg_split('/\R+/',app_post('initial_urls'))?:[];$result=$service->crear(['title'=>app_post('title'),'description'=>app_post('description'),'specific_instructions'=>app_post('specific_instructions'),'category'=>app_post('category'),'priority'=>app_post('priority','NORMAL'),'web_search_enabled'=>isset($_POST['web_search_enabled']),'initial_urls'=>$urls],(int)$user['id']);if(!$result['ok'])app_json(['ok'=>false,'message'=>'Neuria rechazó la solicitud: '.$result['error']],422);app_json(['ok'=>true,'message'=>$result['duplicada']?'La solicitud ya había sido enviada.':'Solicitud enviada a Neuria.']);}
    if($action==='sync'){$result=$service->sincronizarPendientes();app_json(['ok'=>$result['ok'],'message'=>'Sincronización finalizada: '.$result['actualizadas'].' actualizadas, '.$result['importadas'].' importadas, '.$result['errores'].' errores.','stats'=>$result],$result['ok']?200:207);}
    app_json(['ok'=>false,'message'=>'Acción inválida.'],400);
}catch(InvalidArgumentException $e){app_json(['ok'=>false,'message'=>$e->getMessage()],422);}catch(Throwable $e){app_json(['ok'=>false,'message'=>'No fue posible comunicarse con Neuria.'],503);}
