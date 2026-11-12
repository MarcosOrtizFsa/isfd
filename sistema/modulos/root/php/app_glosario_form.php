<?php
declare(strict_types=1);
require_once __DIR__.'/../../../../lib/app.php';
app_require_mode([0]);
$id = app_int('id');
$row = $id ? app_db()->one('SELECT * FROM institucional_glosario WHERE id=? AND deleted_at IS NULL','i',[$id]) : [];
if ($id && !$row) { http_response_code(404); exit('Término no encontrado.'); }
?>
<form id="glossary-form" class="admin-editor admin-form-section row g-3 mb-4">
<div class="col-12 d-flex justify-content-between"><h3 class="h5"><?=$id ? 'Editar palabra' : 'Nueva palabra'?></h3><button type="button" class="btn-close" aria-label="Cerrar editor" onclick="$('#glossary-editor').empty()"></button></div>
<input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="revision" value="<?=(int)($row['revision'] ?? 0)?>">
<div class="col-12"><label class="form-label" for="glossary-word">Palabra o expresión *</label><input class="form-control" id="glossary-word" name="palabra" maxlength="150" required placeholder="Ejemplo: Correlatividad" value="<?=app_escape($row['palabra'] ?? '')?>"></div>
<div class="col-12"><label class="form-label" for="glossary-description">Descripción *</label><textarea class="form-control" id="glossary-description" name="descripcion" rows="5" maxlength="10000" required placeholder="Explicá el significado con palabras claras."><?=app_escape($row['descripcion'] ?? '')?></textarea><div class="form-text">Texto plano. Se conservan los saltos de línea.</div></div>
<div class="col-12"><div class="form-check"><input type="checkbox" class="form-check-input" id="glossary-visible" name="visible" <?=($row['visible'] ?? 1) ? 'checked' : ''?>><label class="form-check-label" for="glossary-visible">Visible en el glosario público</label></div></div>
<div class="col-12"><div id="glossary-result" role="status" class="mb-2"></div><div class="d-flex flex-wrap gap-2"><button class="btn btn-primary" type="submit">Guardar palabra</button><button class="btn btn-outline-secondary" type="button" onclick="$('#glossary-editor').empty()">Cancelar</button><?php if ($id): ?><button class="btn btn-outline-danger ms-auto" id="glossary-delete" type="button">Eliminar palabra</button><?php endif; ?></div></div>
</form>
<script>
(function () {
    function save(action) {
        var form = $('#glossary-form'), buttons = form.find('button').prop('disabled', true);
        var data = form.serializeArray();
        data.push({name:'action', value:action}, {name:'csrf_token', value:$('meta[name="csrf-token"]').attr('content')});
        $.ajax({url:'modulos/root/php/app_glosario_action.php', method:'POST', data:data, dataType:'json'})
            .done(function (result) {
                if (result.ok) cargar_post('modulos/root/php/app_glosario.php','glossary-panel','');
                else $('#glossary-result').text(result.message);
            })
            .fail(function (xhr) { $('#glossary-result').text(xhr.responseJSON ? xhr.responseJSON.message : 'No se pudo guardar. Intentá nuevamente.'); })
            .always(function () { buttons.prop('disabled',false); });
    }
    $('#glossary-form').on('submit',function (event) { event.preventDefault(); save('save'); });
    $('#glossary-delete').on('click',function () { if (window.confirm('¿Eliminar esta palabra del glosario?')) save('delete'); });
})();
</script>
