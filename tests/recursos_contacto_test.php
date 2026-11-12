<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit;
ob_start();
require __DIR__.'/../lib/app.php';
require __DIR__.'/../lib/recursos_contacto.php';
function resource_check(bool $ok,string $message): void {
    if (!$ok) throw new RuntimeException($message);
    echo 'OK: '.$message.PHP_EOL;
}
$db = app_db();
$root = $db->one('SELECT id_system_03 FROM system_03_usuarios WHERE system_03_modo=0 AND system_03_estado=1 LIMIT 1');
if (!$root) throw new RuntimeException('Se necesita Root local activo.');
$marker = 'TEST-RECURSO-'.bin2hex(random_bytes(8));
$ids = [];
$files = [];
$fixture = tempnam(sys_get_temp_dir(),'isfd-resource-');
$image = imagecreatetruecolor(64,64);
imagefill($image,0,0,imagecolorallocate($image,255,255,255));
imagepng($image,$fixture);
imagedestroy($image);
session_write_close();
session_id(bin2hex(random_bytes(20)));
session_start();
$_SESSION = ['user_id'=>(int)$root['id_system_03'],'user_name'=>'Prueba recursos'];
$token = app_csrf_token();
$sid = session_id();
session_write_close();
$request = function (string $path,array $data,bool $auth=true) use ($sid): array {
    $curl = curl_init('http://localhost/isfd.com.ar/'.$path);
    curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$data]);
    if ($auth) curl_setopt($curl,CURLOPT_COOKIE,'ISFDSESSID='.$sid);
    $body = curl_exec($curl);
    $code = curl_getinfo($curl,CURLINFO_HTTP_CODE);
    if ($body === false) throw new RuntimeException(curl_error($curl));
    curl_close($curl);
    return ['code'=>$code,'body'=>$body,'json'=>json_decode($body,true)];
};
$endpoint = 'sistema/modulos/root/php/app_recurso_contacto_action.php';
try {
    resource_check($request($endpoint,[],false)['code'] === 401,'Guardado sin sesión bloqueado');
    resource_check($request($endpoint,['action'=>'save'])['code'] === 403,'CSRF obligatorio');
    $base = ['action'=>'save','tema'=>$marker.' <prueba>','url'=>'https://example.org/chat?x=1&y=2','orden'=>2,'visible'=>1,'csrf_token'=>$token];
    resource_check($request($endpoint,$base)['code'] === 422,'Imagen obligatoria al crear');
    resource_check($request($endpoint,array_replace($base,['url'=>'javascript:alert(1)']))['code'] === 422,'URL ejecutable rechazada');
    foreach ([2,1] as $order) {
        $response = $request($endpoint,array_replace($base,['tema'=>$marker.' '.$order.' <prueba>','orden'=>$order,'imagen'=>new CURLFile($fixture,'image/png','qr.png')]));
        resource_check($response['json']['ok'] ?? false,'Carga multipart de imagen '.$order);
        $ids[] = (int)$response['json']['id'];
        $row = $db->one('SELECT * FROM institucional_recursos_contacto WHERE id=?','i',[end($ids)]);
        $path = dirname(__DIR__).'/'.substr($row['imagen_url'],3);
        $files[] = $path;
        $size = getimagesize($path);
        resource_check($size[0] === 64 && $size[1] === 64 && $size['mime'] === 'image/png','Imagen conserva dimensiones y formato seguro');
    }
    $html = recursos_contacto_html($db);
    resource_check(strpos($html,$marker.' 1') < strpos($html,$marker.' 2'),'Orden configurado en el pasador');
    resource_check(str_contains($html,'&lt;prueba&gt;') && str_contains($html,'x=1&amp;y=2') && str_contains($html,'data-bs-slide="next"'),'Texto escapado y controles de paginación');
    resource_check(str_contains($html,'data-contact-resource') && str_contains($html,'rel="noopener noreferrer"'),'Enlace independiente del sitio');
    $edit = array_replace($base,['id'=>$ids[0],'revision'=>1]);
    unset($edit['visible']);
    resource_check($request($endpoint,$edit)['json']['ok'] ?? false,'Edición conserva imagen y permite ocultar');
    resource_check(!str_contains(recursos_contacto_html($db),$marker.' &lt;prueba&gt;'),'Recurso oculto fuera de portada');
    resource_check($request($endpoint,$edit)['code'] === 422,'Revisión antigua rechazada');
    resource_check($request($endpoint,['action'=>'delete','id'=>$ids[1],'revision'=>1,'csrf_token'=>$token])['json']['ok'] ?? false,'Baja lógica');
    resource_check(!str_contains(recursos_contacto_html($db),$marker),'No publica recursos eliminados');
    $public = $request('sistema/modulos/home/php/home.php',[],false);
    resource_check($public['code'] === 200 && !str_contains($public['body'],'{RECURSOS_CONTACTO}'),'Integración de portada');
} finally {
    foreach ($ids as $id) {
        $db->execute('DELETE FROM institucional_recursos_contacto WHERE id=?','i',[$id]);
        $db->execute("DELETE FROM system_05_logistica WHERE system_05_accion IN ('A:RECURSO','M:RECURSO','E:RECURSO','B:RECURSO') AND system_05_detalles LIKE ?",'s',['Recurso '.$id.';%']);
    }
    foreach ($files as $path) if (is_file($path)) unlink($path);
    if (is_file($fixture)) unlink($fixture);
    session_id($sid);
    session_start();
    session_destroy();
}
ob_end_flush();
