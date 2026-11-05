<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
app_require_mode([0]);
require __DIR__.'/../../../../lib/auditoria.php';
header('Cache-Control: no-store');
$db=app_db();
try {
    $resultado=(new Auditoria($db))->consultar($_POST);
} catch (InvalidArgumentException $error) {
    http_response_code(422);
    exit(app_escape($error->getMessage()));
}
$f=$resultado['filtros'];
$usuarios=$db->all('SELECT DISTINCT l.rela_system_03 id,u.system_03_usuario usuario FROM system_05_logistica l LEFT JOIN system_03_usuarios u ON u.id_system_03=l.rela_system_03 ORDER BY usuario,id');
$modulos=$db->all('SELECT id_system_01,system_01_modulo FROM system_01_modulos ORDER BY system_01_modulo');
?>
<section class="card admin-card mb-4" aria-labelledby="audit-title"><div class="card-body">
<div class="d-flex justify-content-between gap-3"><div><h2 id="audit-title" class="h4"><i class="bi bi-clock-history me-2"></i>Registro de acciones</h2><p class="text-secondary">Consulta exclusiva de Root. El historial no se modifica ni se elimina desde el panel.</p></div><button class="btn-close" aria-label="Cerrar historial" onclick="$('#root-panel').empty()"></button></div>
<form id="audit-filter" class="row g-3 mb-4">
<div class="col-sm-6 col-lg-2"><label for="audit-from" class="form-label">Desde</label><input id="audit-from" class="form-control" type="date" name="desde" value="<?=app_escape($f['desde'])?>"></div>
<div class="col-sm-6 col-lg-2"><label for="audit-to" class="form-label">Hasta</label><input id="audit-to" class="form-control" type="date" name="hasta" value="<?=app_escape($f['hasta'])?>"></div>
<div class="col-sm-6 col-lg-3"><label for="audit-user" class="form-label">Administrador</label><select id="audit-user" name="usuario" class="form-select"><option value="0">Todos</option><?php foreach($usuarios as $u): ?><option value="<?=(int)$u['id']?>" <?=$f['usuario']===(int)$u['id']?'selected':''?>><?=app_escape(($u['usuario']?:'Cuenta no disponible').' · #'.$u['id'])?></option><?php endforeach; ?></select></div>
<div class="col-sm-6 col-lg-3"><label for="audit-module" class="form-label">Módulo</label><select id="audit-module" name="modulo" class="form-select"><option value="0">Todos</option><?php foreach($modulos as $m): ?><option value="<?=(int)$m['id_system_01']?>" <?=$f['modulo']===(int)$m['id_system_01']?'selected':''?>><?=app_escape($m['system_01_modulo'])?></option><?php endforeach; ?></select></div>
<div class="col-sm-6 col-lg-2"><label for="audit-type" class="form-label">Operación</label><select id="audit-type" name="tipo" class="form-select"><option value="">Todas</option><?php foreach(Auditoria::TIPOS as $k=>$v): ?><option value="<?=$k?>" <?=$f['tipo']===$k?'selected':''?>><?=$k?> · <?=$v?></option><?php endforeach; ?></select></div>
<div class="col-sm-9"><label for="audit-q" class="form-label">Buscar en acción, detalle o resultado</label><input id="audit-q" class="form-control" name="q" maxlength="120" value="<?=app_escape($f['q'])?>" placeholder="Ej.: Noticia 25, USUARIO, permiso"></div>
<div class="col-sm-3 d-flex align-items-end"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-search me-2"></i>Filtrar</button></div>
</form>
<p class="small text-secondary">Los registros anteriores conservan su descripción original. El filtro A/B/M/E clasifica las acciones nuevas. Dejá las fechas vacías para consultar todo el historial.</p>
<div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Fecha / hora</th><th>Administrador</th><th>Módulo</th><th>Acción</th><th>Detalle</th><th>Resultado</th></tr></thead><tbody>
<?php if (!$resultado['filas']): ?><tr><td colspan="6" class="text-center py-4 text-secondary">No hay acciones para estos filtros.</td></tr><?php endif; ?>
<?php foreach ($resultado['filas'] as $fila): ?>
<tr><td class="text-nowrap"><?=app_escape($fila['system_05_fecha'])?><br><?=app_escape($fila['system_05_hora'])?><small class="d-block text-secondary">#<?=(int)$fila['id_system_05']?></small></td>
<td><?=app_escape($fila['administrador']?:($fila['system_03_usuario']?:'Cuenta no disponible'))?><small class="d-block text-secondary">Usuario #<?=(int)$fila['rela_system_03']?> · <?=app_escape($fila['rol']?:'Rol #'.$fila['rela_system_07'])?></small></td>
<td><?=app_escape($fila['modulo']?:($fila['rela_system_01']?'Módulo #'.$fila['rela_system_01']:'Sistema / histórico'))?></td>
<td><span class="badge text-bg-light"><?=app_escape($fila['system_05_accion'])?></span></td>
<td style="min-width:220px;max-width:420px"><details><summary><?=app_escape(mb_strimwidth($fila['system_05_detalles'],0,85,'…'))?></summary><div class="text-break mt-2" style="white-space:pre-wrap"><?=app_escape($fila['system_05_detalles'])?></div></details></td>
<td><?=app_escape($fila['system_05_mensaje'])?></td></tr>
<?php endforeach; ?></tbody></table></div>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2"><span><?=$resultado['total']?> registros · Página <?=$resultado['pagina']?> de <?=$resultado['paginas']?></span><div class="btn-group"><button class="btn btn-outline-secondary" <?=$resultado['pagina']<=1?'disabled':''?> onclick="auditPage(<?=$resultado['pagina']-1?>)">Anterior</button><button class="btn btn-outline-secondary" <?=$resultado['pagina']>=$resultado['paginas']?'disabled':''?> onclick="auditPage(<?=$resultado['pagina']+1?>)">Siguiente</button></div></div>
</div></section>
<script>
function auditPage(pagina) {
    cargar_post('modulos/root/php/app_auditoria.php','root-panel',$('#audit-filter').serialize()+'&pagina='+pagina);
}
$('#audit-filter').on('submit',function(event){event.preventDefault();auditPage(1);});
</script>
