<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
require __DIR__.'/../../../../preinscripcion/servicio.php';
app_require_mode([0]);
$db=app_db();
$anio=max(2000,min(2200,app_int('anio',(int)date('Y'))));
$oferta=app_int('oferta_id');
$estado=app_post('estado');
$q=app_post('q');
$pagina=max(1,app_int('pagina',1));
$where=['p.anio=?'];$types='i';$params=[$anio];
if ($oferta>0) {$where[]='p.oferta_id=?';$types.='i';$params[]=$oferta;}
if (isset(Preinscripciones::ESTADOS[$estado])) {$where[]='p.estado=?';$types.='s';$params[]=$estado;}
if ($q!=='') {$where[]='(p.nombre LIKE ? OR p.apellido LIKE ? OR p.dni LIKE ? OR p.email LIKE ?)';$types.='ssss';for($i=0;$i<4;$i++)$params[]='%'.$q.'%';}
$condicion=' WHERE '.implode(' AND ',$where);
$total=(int)$db->one('SELECT COUNT(*) total FROM portal_preinscripciones p'.$condicion,$types,$params)['total'];
$paginas=max(1,(int)ceil($total/30));$pagina=min($pagina,$paginas);
$filas=$db->all('SELECT p.* FROM portal_preinscripciones p'.$condicion.' ORDER BY p.created_at DESC,p.id DESC LIMIT 30 OFFSET '.(($pagina-1)*30),$types,$params);
$carreras=$db->all('SELECT id,titulo FROM ofertas ORDER BY titulo');
$resumen=$db->all("SELECT oferta_id,MAX(oferta_titulo) titulo,COUNT(*) total,
    SUM(estado='pendiente') pendientes,SUM(estado='formalizada') formalizadas,
    SUM(estado IN ('en_revision','documentacion_pendiente')) en_tramite,
    SUM(estado='cancelada') canceladas
    FROM portal_preinscripciones WHERE anio=? GROUP BY oferta_id ORDER BY total DESC,titulo",'i',[$anio]);
?>
<section class="admin-page"><div class="container-fluid px-3 px-lg-4">
<div class="d-flex flex-wrap justify-content-between gap-3 mb-4"><div><h1 class="h3">Preinscripciones</h1><p class="text-secondary mb-0">Seguimiento de solicitudes y formalización de la inscripción.</p></div><button class="btn btn-outline-secondary align-self-start" onclick="cargar_post('modulos/contactos/php/app_home.php','content_seccion','')">Volver a Contactos</button></div>
<div class="row g-3 mb-4"><?php foreach (['total'=>'Recibidas','pendientes'=>'Pendientes','en_tramite'=>'En trámite','formalizadas'=>'Formalizadas'] as $clave=>$etiqueta): ?><div class="col-6 col-lg-3"><div class="card admin-card h-100"><div class="card-body"><span class="text-secondary"><?=app_escape($etiqueta)?> · <?=$anio?></span><strong class="d-block fs-2"><?=(int)array_sum(array_column($resumen,$clave))?></strong></div></div></div><?php endforeach; ?></div>
<details class="card admin-card mb-4" open><summary class="card-header">Resumen por carrera · año de recepción <?=$anio?></summary><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Carrera</th><th>Total</th><th>Pendientes</th><th>En trámite</th><th>Formalizadas</th><th>Canceladas</th></tr></thead><tbody><?php foreach ($resumen as $r): ?><tr><td><button class="btn btn-link text-start p-0" onclick="$('#preinscripcion-filter [name=oferta_id]').val('<?=(int)$r['oferta_id']?>');preinscripcionesRecargar(1)"><?=app_escape($r['titulo'])?></button></td><?php foreach (['total','pendientes','en_tramite','formalizadas','canceladas'] as $clave): ?><td><?=(int)$r[$clave]?></td><?php endforeach; ?></tr><?php endforeach; ?><?php if (!$resumen): ?><tr><td colspan="6" class="text-center py-3">Todavía no hay preinscripciones este año.</td></tr><?php endif; ?></tbody></table></div></details>
<form id="preinscripcion-filter" class="card admin-card mb-4"><div class="card-body row g-3 align-items-end">
<div class="col-md-2"><label for="pre-year" class="form-label">Año de recepción</label><input id="pre-year" name="anio" type="number" min="2000" max="2200" value="<?=$anio?>" class="form-control"></div>
<div class="col-md-3"><label for="pre-offer" class="form-label">Carrera</label><select id="pre-offer" name="oferta_id" class="form-select"><option value="">Todas</option><?php foreach ($carreras as $c): ?><option value="<?=(int)$c['id']?>" <?=$oferta===(int)$c['id']?'selected':''?>><?=app_escape($c['titulo'])?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label for="pre-state" class="form-label">Estado</label><select id="pre-state" name="estado" class="form-select"><option value="">Todos</option><?php foreach (Preinscripciones::ESTADOS as $key=>$label): ?><option value="<?=$key?>" <?=$estado===$key?'selected':''?>><?=$label?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label for="pre-search" class="form-label">Nombre, DNI o correo</label><input id="pre-search" name="q" class="form-control" value="<?=app_escape($q)?>"></div><div class="col-md-1"><button class="btn btn-outline-primary" type="submit">Filtrar</button></div>
</div></form><div id="preinscripcion-panel"></div>
<div class="card admin-card"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Fecha</th><th>Postulante</th><th>Carrera</th><th>Contacto</th><th>Estado</th><th></th></tr></thead><tbody>
<?php foreach ($filas as $fila): ?><tr><td><?=app_escape(date('d/m/Y',strtotime($fila['created_at'])))?></td><td><strong><?=app_escape($fila['apellido'].', '.$fila['nombre'])?></strong><small class="d-block">DNI <?=app_escape($fila['dni'])?></small></td><td><?=app_escape($fila['oferta_titulo'])?></td><td><?=app_escape($fila['telefono'])?><small class="d-block"><?=app_escape($fila['email'])?></small></td><td><span class="badge <?=$fila['estado']==='formalizada'?'text-bg-success':'text-bg-light'?>"><?=app_escape(Preinscripciones::ESTADOS[$fila['estado']])?></span></td><td><button class="btn btn-sm btn-outline-primary" onclick="cargar_post('modulos/contactos/php/preinscripcion_form.php','preinscripcion-panel',{id:<?=(int)$fila['id']?>})">Gestionar</button></td></tr><?php endforeach; ?>
<?php if (!$filas): ?><tr><td colspan="6" class="text-center py-4">No hay resultados con estos filtros.</td></tr><?php endif; ?>
</tbody></table></div></div><div class="d-flex justify-content-between align-items-center mt-3"><button class="btn btn-outline-secondary" <?=$pagina<=1?'disabled':''?> onclick="preinscripcionesRecargar(<?=$pagina-1?>)">Anterior</button><span><?=$total?> registros · Página <?=$pagina?> de <?=$paginas?></span><button class="btn btn-outline-secondary" <?=$pagina>=$paginas?'disabled':''?> onclick="preinscripcionesRecargar(<?=$pagina+1?>)">Siguiente</button></div>
</div></section>
<script>
function preinscripcionesRecargar(pagina) {
    cargar_post('modulos/contactos/php/preinscripciones_home.php','content_seccion',$('#preinscripcion-filter').serialize()+'&pagina='+(pagina||<?=$pagina?>));
}
$('#preinscripcion-filter').on('submit',function(event){event.preventDefault();preinscripcionesRecargar(1);});
</script>
