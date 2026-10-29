<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
$user = app_require_mode([0]);
app_verify_csrf();
require_once __DIR__.'/../../../../lib/institucional_repositorio.php';
try {
    if (app_post('seccion') === 'autoridades') throw new InvalidArgumentException('Usá el administrador de Autoridades al final de Configuración de dominio.');
    (new InstitucionalRepositorio(app_db()))->guardar(app_post('seccion'), app_int('anio'), app_post('motivo'), is_array($_POST['valores'] ?? null) ? $_POST['valores'] : [], app_int('version'), $user['id']);
    app_json(['ok'=>true,'message'=>'Contenido publicado. La versión anterior queda en el historial.']);
} catch (InvalidArgumentException $error) {
    app_json(['ok'=>false,'message'=>$error->getMessage()],422);
} catch (UnexpectedValueException $error) {
    app_json(['ok'=>false,'message'=>$error->getMessage()],409);
} catch (Throwable $error) {
    error_log('Contenido institucional: '.$error->getMessage());
    app_json(['ok'=>false,'message'=>'No se pudo guardar el contenido.'],500);
}
