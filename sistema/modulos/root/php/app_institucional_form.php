<?php
declare(strict_types=1);
require_once __DIR__.'/../../../../lib/app.php';
app_require_mode([0]);
require_once __DIR__.'/../../../../lib/institucional_repositorio.php';
$db = app_db();
$current = (new InstitucionalRepositorio($db))->actuales();
?>
<div class="card admin-card my-4"><div class="card-body p-4">
<h2 class="h4">Contenido institucional e historial</h2>
<p class="text-secondary">Cada sección se guarda por separado. El año identifica el período; guardar publica una nueva versión y conserva las anteriores.</p>
<?php foreach (institucional_campos() as $section => [$title, $fields]):
    if ($section === 'autoridades') continue; // Ahora se administra con fichas individuales.
    $record = $db->one('SELECT s.version_actual,v.anio FROM institucional_secciones s LEFT JOIN institucional_versiones v ON v.id=s.version_actual WHERE s.clave=?','s',[$section]);
    $history = $db->all('SELECT v.* FROM institucional_versiones v JOIN institucional_secciones s ON s.id=v.seccion_id WHERE s.clave=? ORDER BY v.id DESC','s',[$section]);
?>
<section class="border rounded p-3 mb-4">
<h3 class="h5"><?=app_escape($title)?></h3>
<form class="institutional-form row g-3">
<input type="hidden" name="seccion" value="<?=app_escape($section)?>">
<input type="hidden" name="version" value="<?=(int)($record['version_actual']??0)?>">
<div class="col-md-3"><label class="form-label" for="year-<?=$section?>">Año / período</label><input id="year-<?=$section?>" class="form-control" type="number" name="anio" min="1900" max="2200" required value="<?=(int)($record['anio']??date('Y'))?>"></div>
<div class="col-md-9"><label class="form-label" for="reason-<?=$section?>">Motivo del cambio</label><input id="reason-<?=$section?>" class="form-control" name="motivo" maxlength="250" required placeholder="Ej.: actualización de autoridades para el nuevo ciclo"></div>
<?php foreach ($fields as $key=>$label): ?>
<?php if ($key === 'videos_json') { include __DIR__.'/videos_institucionales_form.php'; continue; } ?>
<div class="col-12 col-lg-6"><label class="form-label" for="institutional-<?=$key?>"><?=app_escape($label)?></label><textarea id="institutional-<?=$key?>" class="form-control" name="valores[<?=$key?>]" maxlength="12000" rows="<?=str_contains($key,'texto')?6:2?>"><?=app_escape($current[$key]??'')?></textarea><?php if ($key === 'preinscripcion_url'): ?><div class="form-text">URL de destino de todos los enlaces de Preinscripción. Se abre en una pestaña nueva. Para un sitio externo, ingresá la dirección completa https://…</div><?php endif; ?></div>
<?php endforeach; ?>
<div class="col-12"><button class="btn btn-primary" type="submit">Guardar y publicar <?=app_escape(mb_strtolower($title))?></button></div>
</form>
<details class="mt-3"><summary>Historial (<?=count($history)?> versiones)</summary>
<?php foreach ($history as $revision): ?>
<details class="border-top mt-2 pt-2"><summary><?=app_escape($revision['anio'].' · '.$revision['created_at'].' · '.$revision['motivo'])?> <?=$revision['id']==($record['version_actual']??0)?'— Vigente':''?></summary>
<dl class="row mt-2">
<?php foreach ($db->all('SELECT campo,valor FROM institucional_valores WHERE version_id=?','i',[(int)$revision['id']]) as $value): ?>
<?php if ($value['campo']==='videos_json'): ?>
<dt class="col-md-4">Videos (en orden)</dt><dd class="col-md-8"><ol><?php foreach(json_decode($value['valor'],true)??[] as $oldVideo): ?><li><?=app_escape($oldVideo['titulo'].' · '.($oldVideo['visible']?'Visible':'Oculto').' · '.$oldVideo['url'])?></li><?php endforeach; ?></ol></dd>
<?php continue; endif; ?>
<dt class="col-md-4"><?=app_escape($fields[$value['campo']]??$value['campo'])?></dt><dd class="col-md-8" style="white-space:pre-wrap"><?=app_escape($value['valor'])?></dd>
<?php endforeach; ?></dl></details>
<?php endforeach; ?></details></section>
<?php endforeach; ?></div></div>
<script>
$('.institutional-form').on('submit', function(event) {
    event.preventDefault();
    var button = $(this).find('[type="submit"]').prop('disabled', true);
    appAction('modulos/root/php/app_institucional_action.php', $(this).serialize(), function() {
        cargar_post('modulos/root/php/app_institucional_form.php', 'institutional-panel', '');
    }).always(function() { button.prop('disabled', false); });
});
</script>
