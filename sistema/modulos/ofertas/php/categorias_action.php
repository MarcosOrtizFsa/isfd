<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
require __DIR__.'/categorias_servicio.php';
app_require_permission('ofertas');
app_verify_csrf();

try {
    $accion = app_post('action');
    app_require_permission('ofertas',$accion==='save'?(app_int('id')?'M':'A'):($accion==='delete'?'B':'E'));
    if ($accion==='save') {
        $actual=app_db()->one('SELECT activo FROM catalogo_categorias WHERE id=?','i',[app_int('id')]);
        app_guard_state('ofertas','activo',$actual['activo']??1);
    }
    $id = (new CategoriasServicio(app_db()))->ejecutar($accion, $_POST);
    app_log($accion==='save'?(app_int('id')?'M:CATEGORIA':'A:CATEGORIA'):($accion==='delete'?'B:CATEGORIA':'E:CATEGORIA'), 'Acción '.$accion.'; categoría '.$id);
    if ($accion==='save' && app_int('id') && (string)$actual['activo']!==(string)($_POST['activo']??'0')) {
        app_log('E:CATEGORIA','Categoría '.$id.'; activa '.$actual['activo'].' → '.($_POST['activo']??'0'));
    }
    app_json(['ok'=>true, 'message'=>'Categoría actualizada.']);
} catch (DomainException $error) {
    app_json(['ok'=>false, 'message'=>$error->getMessage()]);
} catch (Throwable $error) {
    error_log('ABM categorías: '.$error->getMessage());
    app_json(['ok'=>false, 'message'=>'No se pudo guardar el cambio. Revisá las relaciones de la categoría e intentá nuevamente.']);
}
