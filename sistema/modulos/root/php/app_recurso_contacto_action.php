<?php
declare(strict_types=1);
require_once __DIR__.'/../../../../lib/app.php';
require_once __DIR__.'/../../../../lib/recursos_contacto.php';
app_require_mode([0]);
app_verify_csrf();
$stored = null;
try {
    $action = app_post('action');
    if (!in_array($action,['save','delete'],true)) throw new InvalidArgumentException('Acción inválida.');
    $id = app_int('id');
    $revision = app_int('revision');
    $data = $action === 'save' ? recurso_contacto_validar($_POST) : [];
    if ($action === 'save' && isset($_FILES['imagen']) && (int)$_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        $stored = recurso_contacto_imagen($_FILES['imagen']);
    }
    $db = app_db();
    // DB y auditoría se confirman juntas. Si falla, se retira solo el archivo recién subido.
    $savedId = $db->transaction(function () use ($db,$id,$revision,$action,$data,$stored): int {
        $old = $id ? $db->one('SELECT * FROM institucional_recursos_contacto WHERE id=? AND deleted_at IS NULL FOR UPDATE','i',[$id]) : null;
        if ($id && (!$old || (int)$old['revision'] !== $revision)) throw new InvalidArgumentException('El recurso cambió o fue eliminado. Volvé a abrir el editor.');
        if ($action === 'delete') {
            if (!$old) throw new InvalidArgumentException('Seleccioná un recurso.');
            $db->execute('UPDATE institucional_recursos_contacto SET deleted_at=NOW(),visible=0,revision=revision+1 WHERE id=?','i',[$id]);
            app_log('B:RECURSO','Recurso '.$id.'; '.$old['tema'],'OK',1);
            return $id;
        }
        $image = $stored['url'] ?? ($old['imagen_url'] ?? '');
        if ($image === '') throw new InvalidArgumentException('Cargá una imagen o código QR.');
        $params = [$data['tema'],$data['url'],$image,$data['orden'],$data['visible']];
        if ($old) {
            $db->execute('UPDATE institucional_recursos_contacto SET tema=?,url=?,imagen_url=?,orden=?,visible=?,revision=revision+1 WHERE id=?','sssiii',[...$params,$id]);
            app_log('M:RECURSO','Recurso '.$id.'; '.$data['tema'],'OK',1);
            if ((int)$old['visible'] !== $data['visible']) app_log('E:RECURSO','Recurso '.$id.'; visible '.$data['visible'],'OK',1);
            return $id;
        }
        $newId = $db->insert('INSERT INTO institucional_recursos_contacto (tema,url,imagen_url,orden,visible) VALUES (?,?,?,?,?)','sssii',$params);
        app_log('A:RECURSO','Recurso '.$newId.'; '.$data['tema'],'OK',1);
        return $newId;
    });
    $stored = null;
    app_json(['ok'=>true,'id'=>$savedId,'message'=>$action === 'delete' ? 'Recurso eliminado.' : 'Recurso guardado.']);
} catch (Throwable $error) {
    if ($stored && is_file($stored['path'])) unlink($stored['path']);
    if ($error instanceof InvalidArgumentException) app_json(['ok'=>false,'message'=>$error->getMessage()],422);
    error_log('Guardar recurso de contacto: '.$error->getMessage());
    app_json(['ok'=>false,'message'=>'No se pudo guardar el recurso.'],500);
}
