<?php
header('Content-Type: application/json; charset=utf-8');
$authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = preg_replace('/^Bearer\s+/i', '', $authorization);
$path=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH);

if(str_starts_with((string)$path,'/api/v1/')){
    if($token==='unauthorized'){http_response_code(401);echo json_encode(['message'=>'Unauthorized']);return;}
    if($token==='limited'){http_response_code(429);echo json_encode(['message'=>'Too many requests']);return;}
    if($token==='transient'){$counter=sys_get_temp_dir().DIRECTORY_SEPARATOR.'neuria_mock_transient.count';$count=is_file($counter)?(int)file_get_contents($counter):0;file_put_contents($counter,(string)($count+1));if($count===0){http_response_code(503);echo json_encode(['message'=>'Temporary']);return;}}
    if($path==='/api/v1/me'){echo json_encode(['data'=>['name'=>'ISFD','organization'=>'Formosa','permissions'=>['content:read'],'expires_at'=>'2030-01-01T00:00:00Z']]);return;}
    $content=['uuid'=>'11111111-1111-4111-8111-111111111111','title'=>'Noticia sincronizada','summary'=>'Resumen original','body'=>'<p>Contenido original</p>','category'=>'Educación','tags'=>['docencia'],'sources'=>[['name'=>'Neuria']],'channel_id'=>'isfd','generated_at'=>'2026-10-20T10:00:00-03:00','available_from'=>'2026-10-20T10:30:00-03:00','status'=>'available'];
    if($path==='/api/v1/requests'&&($_SERVER['REQUEST_METHOD']??'GET')==='POST'){$input=json_decode((string)file_get_contents('php://input'),true);if(!is_array($input)||empty($input['title'])){http_response_code(422);echo json_encode(['message'=>'Invalid']);return;}http_response_code(202);echo json_encode(['data'=>['id'=>'22222222-2222-4222-8222-222222222222','title'=>$input['title'],'status'=>'PENDIENTE','progress'=>5,'destinations'=>[]]]);return;}
    if($path==='/api/v1/requests/22222222-2222-4222-8222-222222222222'){echo json_encode(['data'=>['id'=>'22222222-2222-4222-8222-222222222222','title'=>'Tema solicitado','status'=>'COMPLETADA','progress'=>100,'destinations'=>[['status'=>'COMPLETADA','content_id'=>$content['uuid'],'content_status'=>'DISPONIBLE']]]]);return;}
    if($path==='/api/v1/contents'){echo json_encode(['data'=>[$content],'meta'=>['next_cursor'=>'cursor-content-1','limit'=>50,'has_more'=>false]]);return;}
    if(str_starts_with((string)$path,'/api/v1/contents/')){echo json_encode(['data'=>$content]);return;}
    if($path==='/api/v1/changes'){$types=['suspend'=>'CONTENT_SUSPENDED','reactivate'=>'CONTENT_REACTIVATED','withdraw'=>'CONTENT_WITHDRAWN'];$event=['type'=>$types[$token]??'CONTENT_AVAILABLE','content_uuid'=>$content['uuid']];if(($types[$token]??'')==='')$event['content']=$content;$events=$token==='bad-change'?[$event,['type'=>'CONTENT_SUSPENDED']]:[$event];echo json_encode(['data'=>$events,'meta'=>['next_cursor'=>'cursor-change-'.$token,'limit'=>50,'has_more'=>false]]);return;}
    http_response_code(404);echo json_encode(['message'=>'Not found']);return;
}

if ($token === 'unauthorized') {
    http_response_code(401);
    echo json_encode(array('ok' => false, 'error' => 'invalid_credentials'));
    return;
}
if ($token === 'limited') {
    http_response_code(429);
    echo json_encode(array('ok' => false, 'error' => 'rate_limit_exceeded'));
    return;
}
if ($token === 'invalid-json') {
    echo 'respuesta-invalida';
    return;
}
if ($token === 'server-error') {
    http_response_code(503);
    echo json_encode(array('ok' => false, 'error' => 'temporary_error'));
    return;
}

$items = $token === 'empty' ? array() : array(array(
    'id' => 123,
    'titulo' => 'Contenido de prueba',
    'bajada' => 'Resumen de prueba',
    'contenido' => '<p>Texto <strong>seguro</strong>.</p><script>alert(1)</script><a href="javascript:alert(2)" onclick="alert(3)">enlace</a>',
    'tipo' => 'NOTICIA',
    'perfil' => 'INFORMATIVO',
    'actualizado' => '2026-10-05 13:45:00',
    'aprobado' => '2026-10-05 13:44:00'
));

echo json_encode(array('ok' => true, 'client_id' => 'cliente-prueba', 'count' => count($items), 'items' => $items));
?>
