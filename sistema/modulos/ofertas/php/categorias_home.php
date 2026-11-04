<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
app_require_mode([0,1,2]);
$categorias = app_db()->all('SELECT c.*,n.nombre nivel,p.nombre padre,
    (SELECT COUNT(*) FROM ofertas o WHERE o.categoria_id=c.id) ofertas
    FROM catalogo_categorias c JOIN catalogo_niveles n ON n.id=c.nivel_id
    LEFT JOIN catalogo_categorias p ON p.id=c.parent_id ORDER BY n.orden,c.ruta,c.orden,c.nombre');
?>
<section class="admin-page"><div class="container-fluid px-3 px-lg-4">
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
        <div><h1 class="h3">Categorías de ofertas</h1><p class="text-secondary mb-0">El filtro público muestra categorías activas con ofertas publicadas. Desactivar una categoría oculta también su rama en la portada.</p></div>
        <div class="d-flex flex-wrap gap-2 align-items-start">
            <button class="btn btn-outline-secondary" onclick="cargar_post('modulos/ofertas/php/app_home.php','content_seccion','')">Volver a ofertas</button>
            <button class="btn btn-primary" onclick="categoriasEditar(0)"><i class="bi bi-plus-lg me-2"></i>Nueva categoría</button>
        </div>
    </div>
    <div id="categories-panel"></div>
    <div class="card admin-card"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Categoría</th><th>Nivel / superior</th><th>Uso</th><th>Orden</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            <?php if (!$categorias): ?><tr><td colspan="6" class="text-center py-4">No hay categorías.</td></tr><?php endif; ?>
            <?php foreach ($categorias as $categoria): $id=(int)$categoria['id']; ?>
                <tr>
                    <td><strong><?=app_escape($categoria['nombre'])?></strong><small class="d-block text-secondary"><?=app_escape($categoria['slug'])?></small></td>
                    <td><?=app_escape($categoria['nivel'])?><small class="d-block text-secondary"><?=app_escape($categoria['padre'] ?? 'Categoría principal')?></small></td>
                    <td><?=$categoria['admite_ofertas'] ? (int)$categoria['ofertas'].' ofertas' : 'Agrupa subcategorías'?></td>
                    <td><?=(int)$categoria['orden']?></td>
                    <td><span class="badge <?=$categoria['activo'] ? 'text-bg-success' : 'text-bg-secondary'?>"><?=$categoria['activo'] ? 'Activa' : 'Inactiva'?></span></td>
                    <td class="text-end"><div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="categoriasEditar(<?=$id?>)" title="Editar" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-outline-secondary" onclick="categoriasAccion('toggle',<?=$id?>)" title="Activar / desactivar" aria-label="Activar / desactivar"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-outline-danger" onclick="categoriasAccion('delete',<?=$id?>)" title="Eliminar" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>
</div></section>
<script>
function categoriasRecargar() { cargar_post('modulos/ofertas/php/categorias_home.php', 'content_seccion', ''); }
function categoriasEditar(id) { cargar_post('modulos/ofertas/php/categorias_form.php', 'categories-panel', {id: id}); }
function categoriasAccion(action, id) {
    var mensaje = action === 'delete' ? '¿Eliminar esta categoría? Solo se permite si no tiene ofertas ni subcategorías.' : '¿Cambiar el estado de esta categoría? Esto afecta la visibilidad pública de su rama.';
    if (confirm(mensaje)) appAction('modulos/ofertas/php/categorias_action.php', {action: action, id: id}, categoriasRecargar);
}
</script>
