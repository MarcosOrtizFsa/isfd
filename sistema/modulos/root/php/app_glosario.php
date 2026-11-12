<?php
declare(strict_types=1);
require_once __DIR__.'/../../../../lib/app.php';
app_require_mode([0]);
try {
    $items = app_db()->all('SELECT id,palabra,visible FROM institucional_glosario WHERE deleted_at IS NULL ORDER BY palabra,id');
} catch (Throwable $error) {
    error_log('Administrar glosario: '.$error->getMessage());
    echo '<div class="alert alert-warning">No se pudo cargar el glosario. Verificá la migración 022_glosario_up.sql y la conexión.</div>';
    return;
}
?>
<section class="card admin-card my-4"><div class="card-body p-3 p-lg-4">
<div class="d-flex flex-wrap justify-content-between gap-3 mb-3"><div><h2 class="h4">Glosario institucional</h2><p class="text-secondary mb-0">Cargá cada palabra y su descripción. Los términos visibles se muestran en orden alfabético.</p></div><button type="button" class="btn btn-primary" onclick="cargar_post('modulos/root/php/app_glosario_form.php','glossary-editor','')">Agregar palabra</button></div>
<div id="glossary-editor"></div>
<?php if (!$items): ?><p class="text-secondary">Todavía no hay palabras cargadas.</p><?php else: ?>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Palabra</th><th>Publicación</th><th>Gestión</th></tr></thead><tbody>
<?php foreach ($items as $item): ?>
<tr><td class="text-break"><?=app_escape($item['palabra'])?></td><td><?=$item['visible'] ? 'Visible' : 'Oculta'?></td><td><button type="button" class="btn btn-outline-primary btn-sm" onclick="cargar_post('modulos/root/php/app_glosario_form.php','glossary-editor',{id:<?=(int)$item['id']?>})">Editar</button></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</div></section>
