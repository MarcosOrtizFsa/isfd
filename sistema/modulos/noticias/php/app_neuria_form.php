<?php
declare(strict_types=1);
require __DIR__ . '/../../../../lib/app.php';
app_require_permission('noticias','A');

// Ayudas accesibles por teclado y toque, con el mismo estilo del administrador.
$ayuda = static function (string $campo, string $texto): string {
    return '<span class="field-help" tabindex="0" role="button" data-bs-toggle="tooltip" title="'
        . app_escape($texto) . '" aria-label="Ayuda: ' . app_escape($campo)
        . '"><i class="bi bi-question-circle" aria-hidden="true"></i></span>';
};
?>
<div class="card admin-card mb-4"><div class="card-body p-4">
<div class="d-flex justify-content-between"><div><h2 class="h5 mb-1">Agente IA de Neuria</h2><p class="small text-secondary mb-0">La nota generada ingresará como borrador pendiente de revisión.</p></div><button class="btn-close" onclick="$('#news-panel').empty()" aria-label="Cerrar"></button></div>
<form id="neuria-request-form" class="row g-3 mt-1">
<div class="col-12">
    <label class="form-label required" for="neuria-title">Tema o título</label>
    <?=$ayuda('Tema o título', 'Indicá el tema central de la nota. Es obligatorio y admite hasta 240 caracteres.')?>
    <input id="neuria-title" class="form-control" name="title" maxlength="240" placeholder="Ej.: Nuevas propuestas de formación docente en Formosa" required>
</div>
<div class="col-12">
    <label class="form-label required" for="neuria-description">Descripción de la investigación</label>
    <?=$ayuda('Descripción de la investigación', 'Explicá qué debe investigar, para qué público y qué aspectos debe abordar. Incluí los datos confirmados disponibles. Obligatorio, hasta 3000 caracteres.')?>
    <textarea id="neuria-description" class="form-control" name="description" rows="4" maxlength="3000" placeholder="Ej.: Elaborar una nota para futuros estudiantes sobre la formación docente en Formosa. Explicar las propuestas, a quiénes están dirigidas y dónde consultar los requisitos. Usar fuentes institucionales y señalar los datos pendientes de confirmar." required></textarea>
</div>
<div class="col-12">
    <label class="form-label" for="neuria-instructions">Instrucciones específicas</label>
    <?=$ayuda('Instrucciones específicas', 'Opcional. Definí el tono, la extensión y las indicaciones editoriales que debe seguir la IA. Admite hasta 3000 caracteres.')?>
    <textarea id="neuria-instructions" class="form-control" name="specific_instructions" rows="3" maxlength="3000" placeholder="Ej.: Usar un tono institucional y cercano. Incluir título, bajada y texto completo. Evitar tecnicismos y no inventar fechas, cifras ni testimonios."></textarea>
</div>
<div class="col-md-5">
    <label class="form-label" for="neuria-category">Categoría</label>
    <?=$ayuda('Categoría', 'Opcional. Indicá la temática editorial de la solicitud; por ejemplo, Educación o Vida institucional. Admite hasta 160 caracteres.')?>
    <input id="neuria-category" class="form-control" name="category" maxlength="160" placeholder="Ej.: Educación">
</div>
<div class="col-md-3">
    <label class="form-label" for="neuria-priority">Prioridad</label>
    <?=$ayuda('Prioridad', 'Indicá la urgencia de la solicitud para Neuria. Usá Normal para las notas habituales. La prioridad no garantiza un plazo de entrega.')?>
    <select id="neuria-priority" class="form-select" name="priority"><option value="BAJA">Baja</option><option value="NORMAL" selected>Normal</option><option value="ALTA">Alta</option><option value="URGENTE">Urgente</option></select>
</div>
<div class="col-md-4 d-flex align-items-end">
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="web_search_enabled" value="1" id="neuria-web">
        <label class="form-check-label" for="neuria-web">Activar búsqueda web</label>
        <?=$ayuda('Activar búsqueda web', 'Solicita a Neuria que use búsqueda web para investigar el tema. Revisá las fuentes y la información obtenida antes de publicar la nota.')?>
    </div>
</div>
<div class="col-12">
    <label class="form-label" for="neuria-urls">URLs iniciales</label>
    <?=$ayuda('URLs iniciales', 'Opcional. Agregá enlaces de referencia para orientar la investigación, preferentemente fuentes oficiales. Usá direcciones completas con https:// o http://, una por línea; hasta 10.')?>
    <textarea id="neuria-urls" class="form-control" name="initial_urls" rows="3" placeholder="https://isfd.com.ar/&#10;https://www.argentina.gob.ar/educacion" aria-describedby="neuria-urls-help"></textarea>
    <div id="neuria-urls-help" class="form-text">Opcional, hasta 10 direcciones. Una URL por línea.</div>
</div>
<div class="col-12 d-flex justify-content-end gap-2"><button class="btn btn-light" type="button" onclick="$('#news-panel').empty()">Cancelar</button><button class="btn btn-primary" type="submit"><i class="bi bi-stars me-2"></i>Solicitar a Neuria</button></div>
</form></div></div>
<script>
// Inicializar también cuando el formulario se inserta mediante AJAX.
document.querySelectorAll('#neuria-request-form [data-bs-toggle="tooltip"]').forEach(function (element) {
    bootstrap.Tooltip.getOrCreateInstance(element, {
        container: '#neuria-request-form',
        trigger: 'hover focus'
    });
});
</script>
<script>$('#neuria-request-form').on('submit',function(e){e.preventDefault();var form=this,button=$(form).find('[type=submit]'),data=$(form).serializeArray();data.push({name:'action',value:'create'},{name:'csrf_token',value:$('meta[name=csrf-token]').attr('content')});button.prop('disabled',true);$.post('modulos/noticias/php/app_neuria_action.php',$.param(data),null,'json').done(function(r){alert(r.message||'Solicitud enviada.');if(r.ok)newsReload()}).fail(function(x){alert(x.responseJSON?.message||x.responseText||'No se pudo crear la solicitud.')}).always(function(){button.prop('disabled',false)})});</script>
