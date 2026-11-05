<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
require __DIR__.'/adjuntos_soporte.php';
$usuario=app_require_permission('noticias');
app_verify_csrf();
$db=app_db();$id=app_int('noticia_id');$accion=app_post('action');
app_require_permission('noticias',in_array($accion,['file_remove','video_remove'],true)?'B':'M');
try {
    if ($accion==='upload') {
        $archivos=noticia_archivos_validar($_FILES['archivos']??[],app_post('tipo'));
        noticia_adjuntos_guardar($db,$id,$archivos,(int)$usuario['id']);
    } else {
        $db->transaction(function () use ($db,$id,$accion,$usuario): void {
            if (!$db->one('SELECT id FROM noticias WHERE id=? AND deleted_at IS NULL FOR UPDATE','i',[$id])) throw new DomainException('Primero guardá el texto de una noticia disponible.');
            if ($accion==='file_remove') {
                $cambios=$db->execute('UPDATE noticia_archivos SET deleted_at=NOW() WHERE id=? AND noticia_id=? AND deleted_at IS NULL','ii',[app_int('archivo_id'),$id]);
                if (!$cambios) throw new DomainException('El archivo no pertenece a esta noticia o ya fue retirado.');
            } elseif (in_array($accion,['video_save','video_remove'],true)) {
                $codigo=$accion==='video_remove'?null:noticia_youtube(app_post('youtube'));
                if ($accion==='video_save' && !$codigo) throw new DomainException('Ingresá una URL o código válido de YouTube.');
                $db->execute('UPDATE noticias SET youtube_codigo=? WHERE id=?','si',[$codigo,$id]);
            } else throw new DomainException('Acción inválida.');
            $db->execute('UPDATE noticias SET actualizado_por=? WHERE id=?','ii',[(int)$usuario['id'],$id]);
        });
    }
    app_log(in_array($accion,['file_remove','video_remove'],true)?'B:ADJUNTO':($accion==='upload'?'A:ADJUNTO':'M:VIDEO'),'Noticia '.$id.'; acción '.$accion.'; archivo #'.app_int('archivo_id'));
    app_json(['ok'=>true,'message'=>'Adjuntos actualizados.']);
} catch (DomainException $error) {
    app_json(['ok'=>false,'message'=>$error->getMessage()],422);
} catch (Throwable $error) {
    error_log('Adjuntos de noticias: '.$error->getMessage());
    app_json(['ok'=>false,'message'=>'No se pudo completar la operación. Revisá los límites del servidor e intentá nuevamente.'],500);
}
