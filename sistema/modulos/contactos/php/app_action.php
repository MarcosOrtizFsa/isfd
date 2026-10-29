<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';app_require_mode([0]);app_verify_csrf();$db=app_db();$id=app_int('id');$action=app_post('action');$user=app_user();
if(!$db->one('SELECT id FROM portal_consultas WHERE id=?','i',[$id]))app_json(['ok'=>false,'message'=>'Consulta inexistente.'],404);
if($action==='attend'){$db->execute("UPDATE portal_consultas SET estado='leida',atendida_at=COALESCE(atendida_at,NOW()),gestion_usuario_id=? WHERE id=?",'ii',[$user['id'],$id]);$message='Consulta atendida.';}elseif($action==='respond'){$db->execute("UPDATE portal_consultas SET estado='respondida',atendida_at=COALESCE(atendida_at,NOW()),respondida_at=NOW(),gestion_usuario_id=? WHERE id=?",'ii',[$user['id'],$id]);$message='Consulta respondida.';}elseif($action==='reopen'){$db->execute("UPDATE portal_consultas SET estado='nueva',respondida_at=NULL,gestion_usuario_id=? WHERE id=?",'ii',[$user['id'],$id]);$message='Consulta reabierta.';}else app_json(['ok'=>false,'message'=>'Acción inválida.'],400);
app_log('CONTACTO_GESTION','Consulta '.$id.' '.$action,'OK');app_json(['ok'=>true,'message'=>$message]);
