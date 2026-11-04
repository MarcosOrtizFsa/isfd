<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
require __DIR__.'/categorias_servicio.php';
app_require_mode([0,1,2]);
app_verify_csrf();

try {
    $accion = app_post('action');
    $id = (new CategoriasServicio(app_db()))->ejecutar($accion, $_POST);
    app_log('CATEGORIA_OFERTA', 'Acción '.$accion.'; categoría '.$id);
    app_json(['ok'=>true, 'message'=>'Categoría actualizada.']);
} catch (DomainException $error) {
    app_json(['ok'=>false, 'message'=>$error->getMessage()]);
} catch (Throwable $error) {
    error_log('ABM categorías: '.$error->getMessage());
    app_json(['ok'=>false, 'message'=>'No se pudo guardar el cambio. Revisá las relaciones de la categoría e intentá nuevamente.']);
}
