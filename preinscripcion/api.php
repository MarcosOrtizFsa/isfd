<?php
declare(strict_types=1);
require __DIR__.'/../lib/app.php';
require __DIR__.'/servicio.php';
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD']!=='POST') app_json(['ok'=>false,'message'=>'Método no permitido.'],405);
app_verify_csrf();
try {
    if (app_post('sitio_web')!=='') throw new DomainException('No se pudo procesar el envío.');
    $desafio=$_SESSION['portal_captcha_preinscripcion']??null;
    unset($_SESSION['portal_captcha_preinscripcion']);
    if (!$desafio || $desafio['expires']<time() || !password_verify(strtoupper(app_post('captcha')),$desafio['hash'])) throw new DomainException('El código de seguridad es incorrecto o venció. Ingresá el nuevo código.');
    (new Preinscripciones(app_db()))->registrar($_POST,hash('sha256',(string)($_SERVER['REMOTE_ADDR']??'unknown')));
    app_json(['ok'=>true,'message'=>'Tu solicitud fue recibida. Si ya te preinscribiste en esta carrera este año, conservamos el registro anterior. El instituto se comunicará con vos para completar el trámite. La preinscripción no confirma la inscripción definitiva.']);
} catch (DomainException $error) {
    app_json(['ok'=>false,'message'=>$error->getMessage()],422);
} catch (Throwable $error) {
    error_log('Preinscripción: '.$error->getMessage());
    app_json(['ok'=>false,'message'=>'No se pudo guardar la solicitud. Intentá nuevamente más tarde.'],500);
}
