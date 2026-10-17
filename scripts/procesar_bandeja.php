<?php
declare(strict_types=1);
// Ejecutar por tarea programada: php scripts/procesar_bandeja.php
require __DIR__ . '/../lib/app.php';
$db=app_db();
$messages=$db->all("SELECT * FROM portal_bandeja_salida WHERE estado IN ('pendiente','fallido') AND disponible_desde<=NOW() AND intentos<5 ORDER BY id LIMIT 20");
foreach($messages as $message){
    $id=(int)$message['id'];
    $db->execute("UPDATE portal_bandeja_salida SET estado='enviando',intentos=intentos+1 WHERE id=?",'i',[$id]);
    $headers="MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nFrom: Portal ISFD <no-responder@localhost>";
    $sent=@mail($message['destinatario'],$message['asunto'],$message['cuerpo'],$headers);
    if($sent)$db->execute("UPDATE portal_bandeja_salida SET estado='enviado',enviado_at=NOW(),ultimo_error=NULL WHERE id=?",'i',[$id]);
    else $db->execute("UPDATE portal_bandeja_salida SET estado='fallido',ultimo_error='mail() no pudo entregar el mensaje',disponible_desde=DATE_ADD(NOW(),INTERVAL 15 MINUTE) WHERE id=?",'i',[$id]);
}

