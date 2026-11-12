<?php
declare(strict_types=1);
require_once __DIR__.'/../../../../lib/app.php';
require_once __DIR__.'/../../../../lib/glosario.php';
app_require_mode([0]);
app_verify_csrf();
try {
    $action = app_post('action');
    if (!in_array($action, ['save','delete'], true)) throw new InvalidArgumentException('Acción inválida.');
    $id = app_int('id');
    $revision = app_int('revision');
    $data = $action === 'save' ? glosario_validar($_POST) : [];
    $db = app_db();
    // Bloqueo y revisión impiden sobrescribir una edición concurrente.
    $db->transaction(function () use ($db, $id, $revision, $action, $data) {
        $old = $id ? $db->one('SELECT * FROM institucional_glosario WHERE id=? AND deleted_at IS NULL FOR UPDATE','i',[$id]) : null;
        if ($id && !$old) throw new InvalidArgumentException('La palabra ya no está disponible.');
        if ($old && (int)$old['revision'] !== $revision) throw new InvalidArgumentException('La palabra fue modificada. Volvé a abrir el editor.');
        if ($action === 'delete') {
            if (!$old) throw new InvalidArgumentException('Seleccioná una palabra.');
            $db->execute('UPDATE institucional_glosario SET deleted_at=NOW(),visible=0,revision=revision+1 WHERE id=?','i',[$id]);
            app_log('B:GLOSARIO','Término '.$id.'; '.$old['palabra'],'OK',1);
        } elseif ($old) {
            $db->execute('UPDATE institucional_glosario SET palabra=?,descripcion=?,visible=?,revision=revision+1 WHERE id=?','ssii',[$data['palabra'],$data['descripcion'],$data['visible'],$id]);
            app_log('M:GLOSARIO','Término '.$id.'; '.$data['palabra'],'OK',1);
            if ((int)$old['visible'] !== $data['visible']) app_log('E:GLOSARIO','Término '.$id.'; visible '.$data['visible'],'OK',1);
        } else {
            $newId = $db->insert('INSERT INTO institucional_glosario (palabra,descripcion,visible) VALUES (?,?,?)','ssi',[$data['palabra'],$data['descripcion'],$data['visible']]);
            app_log('A:GLOSARIO','Término '.$newId.'; '.$data['palabra'],'OK',1);
        }
    });
    app_json(['ok'=>true,'message'=>$action === 'delete' ? 'Palabra eliminada.' : 'Palabra guardada.']);
} catch (InvalidArgumentException $error) {
    app_json(['ok'=>false,'message'=>$error->getMessage()],422);
} catch (Throwable $error) {
    error_log('Guardar glosario: '.$error->getMessage());
    app_json(['ok'=>false,'message'=>'No se pudo guardar el glosario.'],500);
}
