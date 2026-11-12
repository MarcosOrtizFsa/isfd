<?php
declare(strict_types=1);
// Integración local: término y sesión temporales, sin alterar cuentas existentes.
if (PHP_SAPI !== 'cli') exit;
ob_start();
require __DIR__.'/../lib/app.php';
require __DIR__.'/../lib/glosario.php';
function glossary_check(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException($message);
    echo 'OK: '.$message.PHP_EOL;
}
$db = app_db();
$root = $db->one('SELECT id_system_03 FROM system_03_usuarios WHERE system_03_modo=0 AND system_03_estado=1 LIMIT 1');
if (!$root) throw new RuntimeException('Se necesita Root activo en localhost.');
$marker = 'TEST-GLOSARIO-'.bin2hex(random_bytes(8));
session_write_close();
session_id(bin2hex(random_bytes(20)));
session_start();
$_SESSION = ['user_id'=>(int)$root['id_system_03'],'user_name'=>'Prueba glosario'];
$token = app_csrf_token();
$sid = session_id();
session_write_close();
$request = function (string $path, ?array $data = null, bool $authenticated = true) use ($sid): array {
    $curl = curl_init('http://localhost/isfd.com.ar/'.$path);
    curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20]);
    if ($authenticated) curl_setopt($curl,CURLOPT_COOKIE,'ISFDSESSID='.$sid);
    if ($data !== null) curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($data)]);
    $body = curl_exec($curl);
    $code = curl_getinfo($curl,CURLINFO_HTTP_CODE);
    if ($body === false) throw new RuntimeException(curl_error($curl));
    curl_close($curl);
    return ['code'=>$code,'body'=>$body,'json'=>json_decode($body,true)];
};
$endpoint = 'sistema/modulos/root/php/app_glosario_action.php';
try {
    glossary_check($request($endpoint,[],false)['code'] === 401,'Guardado sin sesión bloqueado');
    glossary_check($request($endpoint,['action'=>'save'])['code'] === 403,'Guardado sin CSRF bloqueado');
    $data = ['action'=>'save','palabra'=>$marker,'descripcion'=>"<script>alert(1)</script>\nSegundo párrafo",'visible'=>1,'csrf_token'=>$token];
    glossary_check($request($endpoint,$data+['id'=>0])['json']['ok'] ?? false,'Alta por endpoint Root');
    $row = $db->one('SELECT * FROM institucional_glosario WHERE palabra=?','s',[$marker]);
    $id = (int)$row['id'];
    $html = glosario_publico_html($db);
    glossary_check(str_contains($html,$marker) && str_contains($html,'&lt;script&gt;alert(1)&lt;/script&gt;') && str_contains($html,"<br />\nSegundo"),'Publicación con escape HTML y saltos de línea');
    $form = $request('sistema/modulos/root/php/app_glosario_form.php',['id'=>$id]);
    glossary_check($form['code'] === 200 && str_contains($form['body'],'&lt;script&gt;'),'Editor escapa el contenido');
    unset($data['visible']);
    $data['id'] = $id;
    $data['revision'] = 1;
    glossary_check($request($endpoint,$data)['json']['ok'] ?? false,'Modificar y ocultar');
    glossary_check(!str_contains(glosario_publico_html($db),$marker),'Término oculto fuera del listado público');
    glossary_check($request($endpoint,$data)['code'] === 422,'Revisión antigua rechazada');
    glossary_check($request($endpoint,['action'=>'delete','id'=>$id,'revision'=>2,'csrf_token'=>$token])['json']['ok'] ?? false,'Baja lógica');
    glossary_check($db->one('SELECT deleted_at FROM institucional_glosario WHERE id=?','i',[$id])['deleted_at'] !== null,'Baja persistida');
    glossary_check($request($endpoint,['action'=>'save','palabra'=>'','descripcion'=>'','csrf_token'=>$token])['code'] === 422,'Campos vacíos rechazados');
    $public = $request('sistema/modulos/home/php/home.php',[],false);
    glossary_check($public['code'] === 200 && str_contains($public['body'],'id="glossary-modal"') && !str_contains($public['body'],'{GLOSARIO}'),'Portada integra el modal');
} finally {
    $db->execute('DELETE FROM institucional_glosario WHERE palabra=?','s',[$marker]);
    if (isset($id)) $db->execute("DELETE FROM system_05_logistica WHERE system_05_accion IN ('A:GLOSARIO','B:GLOSARIO','M:GLOSARIO','E:GLOSARIO') AND (system_05_detalles LIKE ? OR system_05_detalles LIKE ?)",'ss',[$marker.'%','Término '.$id.';%']);
    session_id($sid);
    session_start();
    session_destroy();
}
ob_end_flush();
