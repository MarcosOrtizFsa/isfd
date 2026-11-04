<?php
declare(strict_types=1);
require __DIR__.'/../lib/app.php';
require __DIR__.'/servicio.php';
require __DIR__.'/../lib/institucional_repositorio.php';
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
$institucion=(new InstitucionalRepositorio(app_db()))->actuales();
$carreras=(new Preinscripciones(app_db()))->carreras();
$seleccion=(int)($_GET['oferta']??0);
$disponible=isset(array_column($carreras,null,'id')[$seleccion]);
function preinscripcion_detalle_carrera(array $carrera): string
{
    $unidad=['anios'=>'años','meses'=>'meses','horas'=>'horas','cuatrimestres'=>'cuatrimestres'][$carrera['duracion_unidad']??'']??($carrera['duracion_unidad']??'');
    $duracion=empty($carrera['duracion_valor'])?'':(string)(float)$carrera['duracion_valor'];
    return implode(' · ',array_filter([trim($duracion.' '.($duracion!==''?$unidad:'')),$carrera['modalidad']]));
}
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="<?=app_escape(app_csrf_token())?>">
<title>Preinscripción · <?=app_escape($institucion['nombre_corto']??'ISFD')?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/preinscripcion.css" rel="stylesheet"></head>
<body><main class="container py-4 py-lg-5">
<header class="d-flex align-items-center justify-content-between gap-3 mb-4"><a href="../" class="brand d-flex align-items-center gap-3 text-decoration-none"><img src="../image/logo.png" width="58" height="64" alt="Escudo institucional"><span><?=app_escape($institucion['nombre_corto']??'ISFD')?><small class="d-block">Preinscripción institucional</small></span></a><a class="btn btn-outline-secondary btn-sm" href="../">Ir al portal</a></header>
<div class="row g-4 align-items-start"><aside class="col-lg-4 order-2"><div class="card border-0 shadow-sm mb-4 overflow-hidden" id="career-summary"><img id="career-image" class="w-100 d-none" alt="Imagen de la carrera seleccionada" style="max-height:240px;object-fit:cover"><div class="card-body"><span class="eyebrow text-secondary">Tu elección</span><h2 id="career-title" class="h5 mt-2">Seleccioná una carrera</h2><p id="career-facts" class="small text-secondary mb-0">Aquí verás un resumen de la propuesta elegida.</p></div></div><div class="intro p-4"><span class="eyebrow">Tu próximo paso</span><h1 class="h2 mt-2">Comenzá tu camino con nosotros</h1><p>Elegí tu carrera y dejanos tus datos para iniciar el trámite.</p><ol class="ps-3"><li class="mb-3">Completá esta preinscripción.</li><li class="mb-3">El instituto revisará tu solicitud y se comunicará con vos.</li><li>Presentá la documentación requerida para formalizar la inscripción.</li></ol><p class="small mb-0">Este registro no garantiza una vacante ni reemplaza la inscripción definitiva.</p></div></aside>
<section class="col-lg-8 order-1"><div class="card border-0 shadow-sm p-3 p-md-4"><h2 class="h4">Preinscribirme a una carrera</h2><p class="text-secondary">Todos los campos del formulario son obligatorios.</p>
<?php if (!$carreras): ?><div class="alert alert-info">Por el momento no hay carreras con inscripción abierta. Consultá las novedades del portal.</div>
<?php else: ?>
<?php if ($seleccion && !$disponible): ?><div class="alert alert-warning">La carrera solicitada ya no tiene inscripción abierta. Podés elegir otra de las disponibles.</div><?php endif; ?>
<form id="preinscripcion-form" class="row g-3">
<div class="d-none" aria-hidden="true"><input name="sitio_web" tabindex="-1" autocomplete="off"></div>
<div class="col-12"><label for="carrera" class="form-label">Carrera</label><select id="carrera" name="oferta_id" class="form-select" required><option value="">Seleccioná una carrera</option><?php foreach ($carreras as $carrera): ?><option data-imagen="<?=app_escape($carrera['imagen_url']??'')?>" data-detalle="<?=app_escape(preinscripcion_detalle_carrera($carrera))?>" value="<?=(int)$carrera['id']?>" <?=(int)$carrera['id']===$seleccion?'selected':''?>><?=app_escape($carrera['titulo'])?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label for="nombre" class="form-label">Nombre</label><input id="nombre" name="nombre" class="form-control" maxlength="100" autocomplete="given-name" required></div>
<div class="col-md-6"><label for="apellido" class="form-label">Apellido</label><input id="apellido" name="apellido" class="form-control" maxlength="100" autocomplete="family-name" required></div>
<div class="col-md-6"><label for="dni" class="form-label">DNI</label><input id="dni" name="dni" class="form-control" inputmode="numeric" pattern="[0-9]{7,8}" maxlength="8" placeholder="Sin puntos" required></div>
<div class="col-md-6"><label for="telefono" class="form-label">Celular</label><input id="telefono" name="telefono" class="form-control" inputmode="numeric" autocomplete="tel-national" pattern="[0-9]{4} [0-9]{6}" maxlength="11" placeholder="0000 000000" required><small class="text-secondary">10 dígitos, con código de área, sin 0 ni 15.</small></div>
<div class="col-12"><label for="email" class="form-label">Correo electrónico</label><input id="email" name="email" type="email" class="form-control" maxlength="190" autocomplete="email" required></div>
<div class="col-12"><label for="captcha" class="form-label">Código de seguridad</label><div class="d-flex flex-wrap gap-2 align-items-center"><img id="captcha-image" src="../api/captcha?context=preinscripcion" alt="Código de seguridad" width="150" height="48"><button id="captcha-refresh" class="btn btn-outline-secondary btn-sm" type="button">Otro código</button><input id="captcha" name="captcha" class="form-control captcha-input" maxlength="5" autocomplete="off" required></div></div>
<div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" id="politica" name="acepta_politica" value="1" required><label for="politica" class="form-check-label small">Acepto que el instituto utilice estos datos para gestionar mi preinscripción y contactarme sobre el trámite.</label></div></div>
<div class="col-12"><button class="btn btn-primary btn-lg w-100" type="submit">Enviar preinscripción</button></div>
</form><?php endif; ?><div id="preinscripcion-result" class="mt-3" role="status" aria-live="polite" tabindex="-1"></div>
</div></section></div></main>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script><script src="assets/preinscripcion.js"></script></body></html>
