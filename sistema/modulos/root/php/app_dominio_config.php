<?php
declare(strict_types=1);
require __DIR__ . '/../../../../lib/app.php';
app_require_mode([0]);
require_once __DIR__ . '/dominio_campos.php';
$record = app_db()->one('SELECT * FROM system_08_configuracion ORDER BY id_system_08 LIMIT 1');
if (!$record) {
    echo '<div class="alert alert-warning">No existe un registro de configuración para editar.</div>';
    exit;
}
?>
<div class="card admin-card mb-4"><div class="card-body p-3 p-lg-4">
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
        <div><h2 class="h4 mb-1"><i class="bi bi-gear me-2"></i>Configuración de dominio</h2><p class="text-secondary mb-0">Edición de los datos generales del portal.</p></div>
        <button type="button" class="btn btn-outline-secondary align-self-start" onclick="$('#root-panel').empty()">Volver a Root</button>
    </div>
    <div class="alert alert-info">Esta parte administra dominio, metadatos y redes. La identidad, los contactos públicos, los horarios y las autoridades se editan más abajo, en Contenido institucional e historial.</div>
    <form id="domain-config-form">
        <input type="hidden" name="id" value="<?=(int) $record['id_system_08']?>">
        <?php foreach (dominio_campos() as $section => $fields): ?>
        <fieldset class="mb-4">
            <legend class="h6 border-bottom pb-2 mb-3"><?=app_escape($section)?></legend>
            <div class="row g-3">
            <?php foreach ($fields as $key => [$label, $type, $limit, $width]):
                $value = (string) ($record['system_08_' . $key] ?? ''); ?>
                <div class="col-12 col-md-<?=$width?>">
                    <label class="form-label" for="domain-<?=$key?>"><?=app_escape($label)?></label>
                    <?php if ($type === 'textarea'): ?>
                        <textarea class="form-control" id="domain-<?=$key?>" name="<?=$key?>" rows="3" maxlength="<?=$limit?>"><?=app_escape($value)?></textarea>
                    <?php else: ?>
                        <input class="form-control" id="domain-<?=$key?>" name="<?=$key?>" type="<?=$type?>" value="<?=app_escape($value)?>" <?=$type === 'number' ? 'min="0" max="2147483647" step="1"' : 'maxlength="'.$limit.'"'?> <?=$key === 'titulo_site' ? 'required' : ''?>>
                    <?php endif; ?>
                    <?php if ($key === 'dominio'): ?><div class="form-text">URL completa, incluyendo https:// y el subdirectorio si corresponde.</div><?php endif; ?>
                    <?php if ($key === 'whatsapp'): ?><div class="form-text">Número internacional, por ejemplo 5493704506262.</div><?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
        </fieldset>
        <?php endforeach; ?>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" onclick="$('#root-panel').empty()">Cancelar</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Guardar cambios</button>
        </div>
    </form>
</div></div>
<script>
$('#domain-config-form').on('submit', function (event) {
    event.preventDefault();
    var button = $(this).find('[type="submit"]').prop('disabled', true);
    appAction('modulos/root/php/app_dominio_action.php', $(this).serialize(), function () {
        cargar_post('modulos/root/php/app_dominio_config.php', 'root-panel', '');
    }).always(function () { button.prop('disabled', false); });
});
</script>
<div id="institutional-panel"><?php include __DIR__.'/app_institucional_form.php'; ?></div>
<div id="authorities-panel"><?php include __DIR__.'/app_autoridades.php'; ?></div>
