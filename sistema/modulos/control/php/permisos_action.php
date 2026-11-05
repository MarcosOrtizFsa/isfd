<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
app_require_mode([0]);
app_verify_csrf();
try {
    $permisos=$_POST['permisos']??[];
    if (!is_array($permisos)) throw new DomainException('Los permisos no tienen un formato válido.');
    (new PermisosModulos(app_db()))->guardar(app_int('id'),$permisos);
    app_log('USUARIO_PERMISOS','Permisos del usuario '.app_int('id'),'OK',2);
    app_json(['ok'=>true,'message'=>'Permisos actualizados.']);
} catch (DomainException $error) {
    app_json(['ok'=>false,'message'=>$error->getMessage()],422);
} catch (Throwable $error) {
    error_log('Permisos de usuarios: '.$error->getMessage());
    app_json(['ok'=>false,'message'=>'No se pudieron guardar los permisos. Verificá que la migración 021 esté instalada.'],500);
}
