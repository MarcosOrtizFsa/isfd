<?php
declare(strict_types=1);
require_once __DIR__.'/../../../../lib/app.php';
app_require_mode([0]);
try {
    $items = app_db()->all('SELECT * FROM institucional_recursos_contacto WHERE deleted_at IS NULL ORDER BY orden,id');
} catch (Throwable $error) {
    error_log('Administrar recursos de contacto: '.$error->getMessage());
    echo '<div class="alert alert-warning">No se pudieron cargar los recursos de contacto. Verificá la migración 023_recursos_contacto_up.sql y la conexión.</div>';
    return;
}
?>
<section class="card admin-card my-4"><div class="card-body p-3 p-lg-4">
<div class="d-flex flex-wrap justify-content-between gap-3 mb-3"><div><h2 class="h4">Recursos de contacto: QR y enlaces</h2><p class="text-secondary mb-0">Tema, imagen y enlace. Se muestra una tarjeta por vez debajo de los datos de contacto, ordenadas de menor a mayor.</p></div><button type="button" class="btn btn-primary" onclick="cargar_post('modulos/root/php/app_recurso_contacto_form.php','contact-resource-editor','')">Agregar recurso</button></div>
<div id="contact-resource-editor"></div>
<?php if (!$items): ?><p class="text-secondary">Todavía no hay recursos cargados. El bloque público aparecerá cuando publiques el primero.</p><?php else: ?>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Imagen</th><th>Tema</th><th>Orden</th><th>Publicación</th><th>Gestión</th></tr></thead><tbody>
<?php foreach ($items as $item): ?>
<tr><td><img src="<?=app_escape($item['imagen_url'])?>" alt="" width="64" height="64" style="object-fit:contain"></td><td class="text-break"><?=app_escape($item['tema'])?></td><td><?=(int)$item['orden']?></td><td><?=$item['visible'] ? 'Visible' : 'Oculto'?></td><td><button type="button" class="btn btn-outline-primary btn-sm" onclick="cargar_post('modulos/root/php/app_recurso_contacto_form.php','contact-resource-editor',{id:<?=(int)$item['id']?>})">Editar</button></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</div></section>
