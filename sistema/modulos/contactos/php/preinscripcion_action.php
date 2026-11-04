<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
require __DIR__.'/../../../../preinscripcion/servicio.php';
$usuario=app_require_mode([0]);
app_verify_csrf();
try {
    (new Preinscripciones(app_db()))->gestionar(app_int('id'),app_post('estado'),app_post('nota'),(int)$usuario['id'],app_post('estado_anterior'));
    app_log('PREINSCRIPCION_GESTION','Solicitud '.app_int('id').' '.app_post('estado'));
    app_json(['ok'=>true,'message'=>'Seguimiento guardado.']);
} catch (DomainException $error) {
    app_json(['ok'=>false,'message'=>$error->getMessage()]);
} catch (Throwable $error) {
    error_log('Gestión de preinscripciones: '.$error->getMessage());
    app_json(['ok'=>false,'message'=>'No se pudo guardar el seguimiento.']);
}
