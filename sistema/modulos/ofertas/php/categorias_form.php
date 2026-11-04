<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
app_require_mode([0,1,2]);
$db = app_db();
$id = app_int('id');
$categoria = $id ? $db->one('SELECT * FROM catalogo_categorias WHERE id=?', 'i', [$id]) : null;
if ($id && !$categoria) { http_response_code(404); exit('Categoría inexistente.'); }
$niveles = $db->all('SELECT id,nombre FROM catalogo_niveles ORDER BY orden,nombre');
$padres = $db->all('SELECT id,nivel_id,nombre FROM catalogo_categorias WHERE admite_ofertas=0 AND activo=1 AND profundidad<3 ORDER BY nombre');
?>
<div class="card admin-card mb-4"><div class="card-body p-4">
    <div class="d-flex justify-content-between"><h2 class="h5"><?=$id ? 'Editar categoría' : 'Nueva categoría'?></h2><button type="button" class="btn-close" aria-label="Cerrar" onclick="$('#categories-panel').empty()"></button></div>
    <form id="category-admin-form">
        <input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=$id?>">
        <div class="row g-3">
            <div class="col-md-8"><label for="category-name" class="form-label">Nombre *</label><input id="category-name" class="form-control" name="nombre" maxlength="150" required value="<?=app_escape($categoria['nombre'] ?? '')?>"></div>
            <div class="col-md-4"><label for="category-order" class="form-label">Orden</label><input id="category-order" class="form-control" name="orden" type="number" min="0" max="65535" required value="<?=(int)($categoria['orden'] ?? 0)?>"></div>
            <?php if (!$id): ?>
                <div class="col-md-6"><label for="category-level" class="form-label">Nivel educativo *</label><select id="category-level" class="form-select" name="nivel_id" required><?php foreach ($niveles as $nivel): ?><option value="<?=(int)$nivel['id']?>"><?=app_escape($nivel['nombre'])?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label for="category-parent" class="form-label">Categoría superior</label><select id="category-parent" class="form-select" name="parent_id"><option value="0">Ninguna (categoría principal)</option><?php foreach ($padres as $padre): ?><option data-level="<?=(int)$padre['nivel_id']?>" value="<?=(int)$padre['id']?>"><?=app_escape($padre['nombre'])?></option><?php endforeach; ?></select></div>
            <?php else: ?>
                <div class="col-12"><p class="text-secondary small mb-0">La ubicación jerárquica y el identificador del enlace se conservan para mantener las relaciones existentes.</p></div>
            <?php endif; ?>
            <div class="col-12"><label for="category-description" class="form-label">Descripción</label><textarea id="category-description" class="form-control" name="descripcion" rows="3"><?=app_escape($categoria['descripcion'] ?? '')?></textarea></div>
            <div class="col-md-6"><div class="form-check"><input id="category-active" class="form-check-input" type="checkbox" name="activo" value="1" <?=($categoria['activo'] ?? 1) ? 'checked' : ''?>><label for="category-active" class="form-check-label">Activa</label></div></div>
            <div class="col-md-6"><div class="form-check"><input id="category-offers" class="form-check-input" type="checkbox" name="admite_ofertas" value="1" <?=($categoria['admite_ofertas'] ?? 1) ? 'checked' : ''?>><label for="category-offers" class="form-check-label">Admite ofertas directamente</label></div><small class="text-secondary">Desmarcar para agrupar subcategorías. No puede tener ambos usos.</small></div>
            <div class="col-12 text-end"><button type="submit" class="btn btn-primary">Guardar categoría</button></div>
        </div>
    </form>
</div></div>
<script>
$('#category-level').on('change', function () {
    var nivel = $(this).val();
    $('#category-parent').val('0').find('option[data-level]').each(function () {
        var visible = $(this).attr('data-level') === nivel;
        $(this).prop('disabled', !visible).prop('hidden', !visible);
    });
}).trigger('change');
$('#category-admin-form').on('submit', function (event) {
    event.preventDefault();
    var boton = $(this).find('[type="submit"]').prop('disabled', true);
    appAction('modulos/ofertas/php/categorias_action.php', $(this).serialize(), categoriasRecargar)
        .always(function () { boton.prop('disabled', false); });
});
</script>
