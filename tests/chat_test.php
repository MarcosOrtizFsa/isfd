<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') exit;
require __DIR__.'/../chat/lib/actions.php';
require __DIR__.'/../lib/mysql_conect.php';

function check_chat(bool $ok,string $message):void {
    if (!$ok) throw new RuntimeException('FALLO: '.$message);
    echo 'OK: '.$message.PHP_EOL;
}
function reject_chat(callable $operation,int $status,string $message):void {
    try { $operation(); } catch (ChatError $e) { check_chat($e->status===$status,$message); return; }
    throw new RuntimeException('FALLO: '.$message);
}

// Base efímera aislada: nunca usa ni borra tablas de la aplicación.
$name='isfd_chat_test_'.bin2hex(random_bytes(6));
$connection=new mysqli(HOST,USU,CLA);
$connection->query('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
try {
    $db=new AppDatabase(HOST,USU,CLA,$name);
    $sql=preg_replace('/^--.*$/m','',file_get_contents(__DIR__.'/../database/migrations/018_chat_institucional_up.sql'));
    foreach(explode(';',$sql) as $statement) if(trim($statement)!=='') $db->execute($statement);
    // Repetir la migración debe conservar los datos y evitar duplicar salas/avatares.
    foreach(explode(';',$sql) as $statement) if(trim($statement)!=='') $db->execute($statement);
    $websocketSql=file_get_contents(__DIR__.'/../database/migrations/019_chat_websocket_up.sql');
    foreach(explode(';',$websocketSql) as $statement) if(trim($statement)!=='') $db->execute($statement);
    $chat=new Chat($db);
    check_chat(count($chat->salas())===5 && count($chat->avatares())===512,'migración repetible: cinco salas y 512 avatares');
    $a=$chat->registrar(['usuario'=>'alicia','email'=>'alicia@example.test','password'=>'Prueba-segura-123','reglas'=>'1']);
    $b=$chat->registrar(['usuario'=>'bruno','email'=>'bruno@example.test','password'=>'Prueba-segura-123','reglas'=>'1']);
    reject_chat(fn()=>$chat->registrar(['usuario'=>'alicia','email'=>'otra@example.test','password'=>'Prueba-segura-123','reglas'=>'1']),409,'usuario único');
    reject_chat(fn()=>$chat->registrar(['usuario'=>'carla','email'=>'carla@example.test','password'=>'Prueba-segura-123']),422,'aceptación de reglas obligatoria');
    reject_chat(fn()=>$chat->ingresar('alicia','incorrecta'),401,'contraseña incorrecta rechazada');
    check_chat($chat->ingresar('alicia','Prueba-segura-123')===$a,'inicio de sesión con contraseña cifrada');
    check_chat(!isset($chat->usuario($a)['password_hash']) && !isset($chat->usuario($a)['email']),'perfil público sin correo ni hash');
    $chat->avatar($a,1); $chat->avatar($b,2);
    reject_chat(fn()=>$chat->avatar($b,1),409,'reserva exclusiva de avatar protegida por BD');
    $chat->presencia($a,1); $chat->presencia($b,1);
    check_chat(count($chat->conectados(1))===2,'presencia de participantes');
    $first=$chat->enviar($a,1,'Hola @bruno, ¿cómo estás?',0,'test-message-00000001');
    $again=$chat->enviar($a,1,'Hola @bruno, ¿cómo estás?',0,'test-message-00000001');
    check_chat($first['id']===$again['id'] && (int)$db->one('SELECT COUNT(*) n FROM chat_mensajes')['n']===1,'reintento idempotente sin duplicados');
    check_chat(count($chat->notificaciones($b))===1,'notificación por mención');
    reject_chat(fn()=>$chat->enviar($b,2,'Respuesta en otra sala',$first['id'],'test-message-00000002'),422,'respuesta cruzada entre salas bloqueada');
    $second=$chat->enviar($b,1,'¡Hola!',$first['id'],'test-message-00000003');
    check_chat(count($chat->notificaciones($a))===1,'notificación de respuesta');
    $history=$chat->mensajes(1,0,0);
    check_chat(count($history)===2 && $history[1]['respuesta_texto']==='Hola @bruno, ¿cómo estás?','historial ordenado y contexto de respuesta');
    check_chat(count($chat->mensajes(1,$first['id'],0))===1 && count($chat->mensajes(1,0,$second['id']))===1,'cursores de mensajes nuevos y anteriores');
    check_chat(Chat::moderar('Analizamos la frase te voy a matar en un ejemplo educativo')===null && Chat::moderar('La palabra idiota')===null,'contexto educativo y palabras aisladas no sancionados');
    $db->execute('UPDATE chat_mensajes SET created_at=DATE_SUB(NOW(),INTERVAL 2 MINUTE)');
    for($i=0;$i<3;$i++) reject_chat(fn()=>$chat->enviar($a,1,'Te voy a matar',0,'test-moderation-000'.$i),422,'amenaza no publicada y registrada');
    check_chat((int)$db->one('SELECT COUNT(*) n FROM chat_reportes')['n']===3,'advertencias persistentes pese al rechazo del envío');
    reject_chat(fn()=>$chat->enviar($a,1,'Otro mensaje',0,'test-message-00000004'),403,'pausa progresiva después de tres detecciones');
    $chat->moderacion(1,$a,0,'rehabilitar','Revisión de contexto favorable');
    $chat->enviar($a,1,'Mensaje permitido',0,'test-message-00000005');
    $chat->reportar($a,$second['id'],'Mensaje fuera de contexto');
    $chat->reportar($a,$second['id'],'Mensaje repetido en reporte');
    check_chat((int)$db->one("SELECT COUNT(*) n FROM chat_reportes WHERE origen='participante'")['n']===1,'reporte duplicado evitado');
    $report=$db->one("SELECT id FROM chat_reportes WHERE origen='participante'")['id'];
    $chat->moderacion(1,$b,(int)$report,'ocultar','Mensaje retirado tras revisión');
    check_chat($chat->mensajes(1,$first['id'],0)[0]['texto']==='Mensaje retirado por moderación.','moderación retira texto público');
    $chat->moderacion(1,$b,0,'expulsar','Incumplimiento revisado');
    reject_chat(fn()=>$chat->ingresar('bruno','Prueba-segura-123'),403,'cuenta expulsada sin acceso');
    $chat->moderacion(1,$b,0,'rehabilitar','Revisión de la expulsión');
    check_chat($chat->ingresar('bruno','Prueba-segura-123')===$b,'expulsión reversible');
    check_chat((int)$db->one('SELECT COUNT(*) n FROM chat_auditoria')['n']===4,'decisiones administrativas auditadas');
    $chat->limitar('prueba',1,60);
    reject_chat(fn()=>$chat->limitar('prueba',1,60),429,'límite persistente de frecuencia');
    $realtime=new ChatRealtime($db);
    $token=$realtime->emitir($a,'session-test');
    $credential=$realtime->consumir($token);
    check_chat($credential['user']===$a && $realtime->vigente($credential['hash'],$a),'credencial WebSocket válida ligada al usuario');
    reject_chat(fn()=>$realtime->consumir($token),401,'credencial WebSocket no reutilizable');
    $realtime->revocar('session-test');
    check_chat(!$realtime->vigente($credential['hash'],$a),'logout revoca conexiones existentes');
    $expired=$realtime->emitir($a,'session-expired');
    $db->execute('UPDATE chat_ws_tokens SET expires_at=DATE_SUB(NOW(),INTERVAL 1 SECOND)');
    reject_chat(fn()=>$realtime->consumir($expired),401,'credencial vencida rechazada');
    $db->execute('UPDATE chat_mensajes SET created_at=DATE_SUB(NOW(),INTERVAL 2 MINUTE)');
    $actions=new ChatActions($db);
    $input=['room'=>1,'text'=>'Cambio de transporte','client_id'=>'transport-message-000001','usuario_id'=>$b];
    $one=$actions->ejecutar($a,'send',$input);
    $two=$actions->ejecutar($a,'send',$input);
    check_chat($one['id']===$two['id'],'reintento entre transportes sin duplicar');
    check_chat((int)$db->one('SELECT usuario_id FROM chat_mensajes WHERE id=?','i',[$one['id']])['usuario_id']===$a,'identidad del transporte prevalece sobre datos del cliente');
    check_chat($realtime->revision()>0,'mutaciones notifican al servidor WebSocket');
    echo "Pruebas del chat completadas.\n";
} finally {
    $connection->query('DROP DATABASE `'.$name.'`');
}
