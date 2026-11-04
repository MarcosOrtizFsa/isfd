<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
require __DIR__.'/../../../../preinscripcion/servicio.php';
app_require_mode([0]);
$id=app_int('id');$db=app_db();
$fila=$db->one('SELECT * FROM portal_preinscripciones WHERE id=?','i',[$id]);
if (!$fila) {http_response_code(404);exit('Preinscripción inexistente.');}
$historial=$db->all('SELECT * FROM portal_preinscripcion_historial WHERE preinscripcion_id=? ORDER BY id DESC','i',[$id]);
?>
<div class="card admin-card mb-4"><div class="card-body p-4"><div class="d-flex justify-content-between"><h2 class="h5">Solicitud #<?=$id?> · <?=app_escape($fila['nombre'].' '.$fila['apellido'])?></h2><button class="btn-close" aria-label="Cerrar" onclick="$('#preinscripcion-panel').empty()"></button></div>
<p><strong><?=app_escape($fila['oferta_titulo'])?></strong><br>DNI: <?=app_escape($fila['dni'])?><br><a href="mailto:<?=app_escape($fila['email'])?>"><?=app_escape($fila['email'])?></a> · <a href="tel:<?=app_escape(str_replace(' ','',$fila['telefono']))?>"><?=app_escape($fila['telefono'])?></a></p>
<p class="small text-secondary">Recibida: <?=app_escape($fila['created_at'])?> · Consentimiento registrado: <?=app_escape($fila['consentimiento_at'])?></p>
<form id="preinscripcion-management" class="row g-3"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="estado_anterior" value="<?=app_escape($fila['estado'])?>">
<div class="col-md-4"><label for="management-state" class="form-label">Estado del trámite</label><select id="management-state" name="estado" class="form-select"><?php foreach (Preinscripciones::ESTADOS as $key=>$label): ?><option value="<?=$key?>" <?=$fila['estado']===$key?'selected':''?>><?=$label?></option><?php endforeach; ?></select></div>
<div class="col-md-8"><label for="management-note" class="form-label">Nota interna</label><textarea id="management-note" name="nota" class="form-control" maxlength="3000" rows="3" placeholder="Ej.: Contactado por teléfono. Falta presentar certificado de estudios."></textarea></div><div class="col-12 text-end"><button class="btn btn-primary" type="submit">Guardar seguimiento</button></div></form>
<h3 class="h6 mt-4">Historial de seguimiento</h3><ul class="list-group list-group-flush"><?php foreach ($historial as $h): ?><li class="list-group-item px-0"><strong><?=app_escape(Preinscripciones::ESTADOS[$h['estado']]??$h['estado'])?></strong> · <?=app_escape($h['created_at'])?><small class="text-secondary"> · <?=$h['usuario_id']?'Administrador #'.(int)$h['usuario_id']:'Formulario público'?></small><p class="mb-0"><?=nl2br(app_escape($h['nota']))?></p></li><?php endforeach; ?></ul>
</div></div><script>
$('#preinscripcion-management').on('submit',function(event){
    event.preventDefault();var boton=$(this).find('[type=submit]').prop('disabled',true);
    appAction('modulos/contactos/php/preinscripcion_action.php',$(this).serialize(),function(){preinscripcionesRecargar();}).always(function(){boton.prop('disabled',false);});
});
</script>
