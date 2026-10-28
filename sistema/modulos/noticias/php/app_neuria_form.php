<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';app_require_mode([0,1,2]);
?>
<div class="card admin-card mb-4"><div class="card-body p-4">
<div class="d-flex justify-content-between"><div><h2 class="h5 mb-1">Agente IA de Neuria</h2><p class="small text-secondary mb-0">La nota generada ingresará como borrador pendiente de revisión.</p></div><button class="btn-close" onclick="$('#news-panel').empty()" aria-label="Cerrar"></button></div>
<form id="neuria-request-form" class="row g-3 mt-1">
<div class="col-12"><label class="form-label required">Tema o título</label><input class="form-control" name="title" maxlength="240" required></div>
<div class="col-12"><label class="form-label required">Descripción de la investigación</label><textarea class="form-control" name="description" rows="4" maxlength="3000" required></textarea></div>
<div class="col-12"><label class="form-label">Instrucciones específicas</label><textarea class="form-control" name="specific_instructions" rows="3" maxlength="3000"></textarea></div>
<div class="col-md-5"><label class="form-label">Categoría</label><input class="form-control" name="category" maxlength="160"></div>
<div class="col-md-3"><label class="form-label">Prioridad</label><select class="form-select" name="priority"><option value="BAJA">Baja</option><option value="NORMAL" selected>Normal</option><option value="ALTA">Alta</option><option value="URGENTE">Urgente</option></select></div>
<div class="col-md-4 d-flex align-items-end"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="web_search_enabled" value="1" id="neuria-web"><label class="form-check-label" for="neuria-web">Activar búsqueda web</label></div></div>
<div class="col-12"><label class="form-label">URLs iniciales</label><textarea class="form-control" name="initial_urls" rows="3" placeholder="Una URL por línea"></textarea><div class="form-text">Opcional, hasta 10 direcciones.</div></div>
<div class="col-12 d-flex justify-content-end gap-2"><button class="btn btn-light" type="button" onclick="$('#news-panel').empty()">Cancelar</button><button class="btn btn-primary" type="submit"><i class="bi bi-stars me-2"></i>Solicitar a Neuria</button></div>
</form></div></div>
<script>$('#neuria-request-form').on('submit',function(e){e.preventDefault();var form=this,button=$(form).find('[type=submit]'),data=$(form).serializeArray();data.push({name:'action',value:'create'},{name:'csrf_token',value:$('meta[name=csrf-token]').attr('content')});button.prop('disabled',true);$.post('modulos/noticias/php/app_neuria_action.php',$.param(data),null,'json').done(function(r){alert(r.message||'Solicitud enviada.');if(r.ok)newsReload()}).fail(function(x){alert(x.responseJSON?.message||x.responseText||'No se pudo crear la solicitud.')}).always(function(){button.prop('disabled',false)})});</script>
