<?php
declare(strict_types=1);
require_once __DIR__.'/../../../../lib/app.php';
app_require_mode([0]);
$id = app_int('id');
$row = $id ? app_db()->one('SELECT * FROM institucional_recursos_contacto WHERE id=? AND deleted_at IS NULL','i',[$id]) : [];
if ($id && !$row) { http_response_code(404); exit('Recurso no encontrado.'); }
?>
<form id="contact-resource-form" class="row g-3 admin-editor admin-form-section mb-4" enctype="multipart/form-data">
<div class="col-12 d-flex justify-content-between"><h3 class="h5"><?=$id ? 'Editar recurso' : 'Nuevo recurso'?></h3><button type="button" class="btn-close" data-resource-close aria-label="Cerrar editor"></button></div>
<input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="revision" value="<?=(int)($row['revision'] ?? 0)?>">
<div class="col-md-8"><label for="resource-topic" class="form-label">Tema *</label><input id="resource-topic" class="form-control" name="tema" maxlength="160" required placeholder="Ejemplo: Asistente de orientación académica" value="<?=app_escape($row['tema'] ?? '')?>"></div>
<div class="col-md-4"><label for="resource-order" class="form-label">Orden</label><input id="resource-order" class="form-control" name="orden" type="number" min="0" max="65535" required value="<?=(int)($row['orden'] ?? 0)?>"></div>
<div class="col-12"><label for="resource-url" class="form-label">URL de destino *</label><input id="resource-url" class="form-control" name="url" type="url" maxlength="1000" required placeholder="https://..." value="<?=app_escape($row['url'] ?? '')?>"><div class="form-text">El botón y la imagen abren este enlace en una ventana aparte. Si subís un QR, comprobá que contenga el mismo destino: cambiar esta URL no modifica el QR.</div></div>
<div class="col-md-8"><label for="resource-image" class="form-label">Imagen o código QR *</label><input id="resource-image" class="form-control" type="file" name="imagen" accept="image/png,image/jpeg,image/webp" <?=$id ? '' : 'required'?>><div class="form-text">PNG, JPG o WebP; hasta 5 MB y 12 megapíxeles. Para QR, preferí PNG con fondo claro y margen blanco. La imagen se muestra completa, sin recortes. Para reemplazarla, seleccioná otro archivo.</div></div>
<div class="col-md-4"><img id="resource-preview" class="img-fluid border rounded p-2 <?=empty($row['imagen_url']) ? 'd-none' : ''?>" src="<?=app_escape($row['imagen_url'] ?? '')?>" alt="Vista previa del recurso" style="max-height:180px;object-fit:contain;background:white"></div>
<div class="col-12"><div class="form-check"><input id="resource-visible" class="form-check-input" name="visible" type="checkbox" <?=($row['visible'] ?? 1) ? 'checked' : ''?>><label for="resource-visible" class="form-check-label">Visible en la portada</label></div></div>
<div class="col-12"><div id="resource-result" class="mb-2" role="status"></div><div class="d-flex flex-wrap gap-2"><button type="submit" class="btn btn-primary">Guardar recurso</button><button type="button" class="btn btn-outline-secondary" data-resource-close>Cancelar</button><?php if ($id): ?><button type="button" id="resource-delete" class="btn btn-outline-danger ms-auto">Eliminar recurso</button><?php endif; ?></div></div>
</form>
<script>
(function () {
    var previewUrl = null;
    function releasePreview() { if (previewUrl) { URL.revokeObjectURL(previewUrl); previewUrl = null; } }
    $('#resource-image').on('change', function () {
        releasePreview();
        if (this.files[0]) {
            previewUrl = URL.createObjectURL(this.files[0]);
            $('#resource-preview').attr('src',previewUrl).removeClass('d-none');
        }
    });
    $('[data-resource-close]').on('click',function () { releasePreview(); $('#contact-resource-editor').empty(); });
    function save(action) {
        var form = document.getElementById('contact-resource-form'), data = new FormData(form);
        var buttons = $(form).find('button').prop('disabled',true);
        data.append('action',action);
        data.append('csrf_token',$('meta[name="csrf-token"]').attr('content'));
        $.ajax({url:'modulos/root/php/app_recurso_contacto_action.php',method:'POST',data:data,processData:false,contentType:false,dataType:'json'})
          .done(function (result) {
              if (result.ok) { releasePreview(); cargar_post('modulos/root/php/app_recursos_contacto.php','contact-resources-panel',''); }
              else $('#resource-result').text(result.message);
          })
          .fail(function (xhr) { $('#resource-result').text(xhr.responseJSON ? xhr.responseJSON.message : 'No se pudo guardar. Verificá el tamaño de la imagen e intentá nuevamente.'); })
          .always(function () { buttons.prop('disabled',false); });
    }
    $('#contact-resource-form').on('submit',function (event) { event.preventDefault(); save('save'); });
    $('#resource-delete').on('click',function () { if (window.confirm('¿Eliminar este recurso de la portada?')) save('delete'); });
})();
</script>
