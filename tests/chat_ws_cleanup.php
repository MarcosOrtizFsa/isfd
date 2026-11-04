<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') exit;
require __DIR__.'/../chat/lib/chat.php';
foreach (array_slice($argv,1) as $name) {
    if (!preg_match('/^wstest_[a-z0-9_]{3,20}$/D',$name)) throw new RuntimeException('Identificador de prueba inválido.');
    app_db()->transaction(function($db) use($name) {
        $row=$db->one('SELECT id FROM chat_usuarios WHERE usuario=? AND email=?','ss',[$name,$name.'@example.test']);
        if (!$row) return;
        $id=(int)$row['id'];
        $db->execute('DELETE FROM chat_ws_tokens WHERE usuario_id=?','i',[$id]);
        $db->execute('DELETE n FROM chat_notificaciones n JOIN chat_mensajes m ON m.id=n.mensaje_id WHERE n.usuario_id=? OR m.usuario_id=?','ii',[$id,$id]);
        $db->execute('DELETE FROM chat_reportes WHERE usuario_id=? OR denunciante_id=?','ii',[$id,$id]);
        $db->execute('DELETE FROM chat_auditoria WHERE usuario_id=?','i',[$id]);
        $db->execute('UPDATE chat_mensajes SET respuesta_id=NULL WHERE usuario_id=?','i',[$id]);
        $db->execute('DELETE FROM chat_mensajes WHERE usuario_id=?','i',[$id]);
        $db->execute('DELETE FROM chat_presencias WHERE usuario_id=?','i',[$id]);
        $db->execute('DELETE FROM chat_usuarios WHERE id=?','i',[$id]);
    });
}
