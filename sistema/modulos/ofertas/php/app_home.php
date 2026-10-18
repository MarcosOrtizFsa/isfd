<?php
declare(strict_types=1);
require __DIR__ . '/../../../../lib/app.php';
app_require_mode([0,1,2]);

$db=app_db();
$q=trim((string)($_POST['q']??''));
$publication=trim((string)($_POST['publicacion']??''));
$categoryId=filter_var($_POST['categoria_id']??null,FILTER_VALIDATE_INT)?:0;
$showArchived=($_POST['archivadas']??'0')==='1';
$where=[$showArchived?'o.deleted_at IS NOT NULL':'o.deleted_at IS NULL'];$types='';$params=[];
if($q!==''){$where[]='(o.titulo LIKE ? OR o.slug LIKE ?)';$term='%'.$q.'%';$types.='ss';array_push($params,$term,$term);}
if(in_array($publication,['borrador','publicada','archivada'],true)){$where[]='o.estado_publicacion=?';$types.='s';$params[]=$publication;}
if($categoryId>0){$where[]='o.categoria_id=?';$types.='i';$params[]=$categoryId;}
$offers=$db->all("SELECT o.id,o.slug,o.titulo,o.destacada,o.estado_oferta,o.estado_publicacion,o.deleted_at,c.nombre categoria,n.nombre nivel,m.nombre modalidad,
    COALESCE((SELECT GROUP_CONCAT(t.nombre ORDER BY t.orden SEPARATOR ', ') FROM oferta_turno ot JOIN turnos t ON t.id=ot.turno_id WHERE ot.oferta_id=o.id),'Sin turno') turnos
    FROM ofertas o JOIN catalogo_categorias c ON c.id=o.categoria_id JOIN catalogo_niveles n ON n.id=c.nivel_id JOIN modalidades m ON m.id=o.modalidad_id
    WHERE ".implode(' AND ',$where).' ORDER BY o.destacada DESC,o.orden,o.titulo',$types,$params);
$categories=$db->all('SELECT id,nombre FROM catalogo_categorias WHERE activo=1 AND admite_ofertas=1 ORDER BY nombre');
$labels=['proximamente'=>'Próximamente','inscripcion_abierta'=>'Inscripción abierta','en_curso'=>'En curso','a_termino'=>'A término','cerrada'=>'Cerrada'];
?>
<section class="admin-page"><div class="container-fluid px-3 px-lg-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 mb-1">Ofertas académicas</h1><p class="text-secondary mb-0">Carga, clasificación y publicación del catálogo institucional.</p></div><button class="btn btn-primary" onclick="cargar_post('modulos/ofertas/php/app_form.php','offers-panel','')"><i class="bi bi-plus-lg me-2"></i>Nueva oferta</button></div>
  <form id="offers-admin-filter" class="card admin-card mb-4"><div class="card-body"><div class="row g-2 align-items-end"><div class="col-lg-4"><label class="form-label" for="admin-offer-q">Buscar</label><input class="form-control" id="admin-offer-q" name="q" value="<?=app_escape($q)?>" placeholder="Título o slug"></div><div class="col-md-4 col-lg-3"><label class="form-label">Categoría</label><select class="form-select" name="categoria_id"><option value="">Todas</option><?php foreach($categories as $category):?><option value="<?=(int)$category['id']?>" <?=$categoryId===(int)$category['id']?'selected':''?>><?=app_escape($category['nombre'])?></option><?php endforeach;?></select></div><div class="col-md-4 col-lg-2"><label class="form-label">Publicación</label><select class="form-select" name="publicacion"><option value="">Todas</option><?php foreach(['borrador'=>'Borrador','publicada'=>'Publicada','archivada'=>'Archivada'] as $value=>$label):?><option value="<?=$value?>" <?=$publication===$value?'selected':''?>><?=$label?></option><?php endforeach;?></select></div><div class="col-md-4 col-lg-2"><label class="form-label">Registros</label><select class="form-select" name="archivadas"><option value="0" <?=$showArchived?'':'selected'?>>Activos</option><option value="1" <?=$showArchived?'selected':''?>>Eliminados</option></select></div><div class="col-lg-1"><button class="btn btn-outline-primary w-100" type="submit" title="Aplicar filtros"><i class="bi bi-search"></i></button></div></div></div></form>
  <div id="offers-panel"></div>
  <div class="card admin-card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle admin-table mb-0"><thead><tr><th>Oferta</th><th>Clasificación</th><th>Modalidad / turno</th><th>Estado</th><th>Publicación</th><th class="text-end">Acciones</th></tr></thead><tbody>
  <?php if(!$offers):?><tr><td colspan="6" class="text-center py-5 text-secondary">No se encontraron ofertas con estos filtros.</td></tr><?php endif;?>
  <?php foreach($offers as $offer):$deleted=$offer['deleted_at']!==null;$published=$offer['estado_publicacion']==='publicada';?>
    <tr><td data-label="Oferta"><strong><?=app_escape($offer['titulo'])?></strong><small class="d-block text-secondary"><?=app_escape($offer['slug'])?><?=$offer['destacada']?' · Destacada':''?></small></td><td data-label="Clasificación"><?=app_escape($offer['nivel'])?><small class="d-block text-secondary"><?=app_escape($offer['categoria'])?></small></td><td data-label="Modalidad / turno"><?=app_escape($offer['modalidad'])?><small class="d-block text-secondary"><?=app_escape($offer['turnos'])?></small></td><td data-label="Estado"><span class="badge text-bg-light"><?=app_escape($labels[$offer['estado_oferta']]??$offer['estado_oferta'])?></span></td><td data-label="Publicación"><span class="badge <?=$published?'text-bg-success':'text-bg-secondary'?>"><?=app_escape(ucfirst($offer['estado_publicacion']))?></span></td><td data-label="Acciones" class="text-end"><div class="btn-group btn-group-sm"><?php if(!$deleted):?><button class="btn btn-outline-primary" title="Editar" onclick="cargar_post('modulos/ofertas/php/app_form.php','offers-panel',{id:<?=(int)$offer['id']?>})"><i class="bi bi-pencil"></i></button><button class="btn btn-outline-secondary" title="<?=$published?'Pasar a borrador':'Publicar'?>" onclick="offersAdminAction('publication',<?=(int)$offer['id']?>)"><i class="bi <?=$published?'bi-eye-slash':'bi-eye'?>"></i></button><button class="btn btn-outline-danger" title="Eliminar" onclick="offersAdminAction('archive',<?=(int)$offer['id']?>)"><i class="bi bi-trash"></i></button><?php else:?><button class="btn btn-outline-success" title="Restaurar" onclick="offersAdminAction('restore',<?=(int)$offer['id']?>)"><i class="bi bi-arrow-counterclockwise"></i></button><?php endif;?></div></td></tr>
  <?php endforeach;?></tbody></table></div></div></div>
</div></section>
<script>
function offersAdminReload(){cargar_post('modulos/ofertas/php/app_home.php','content_seccion',$('#offers-admin-filter').serialize());}
function offersAdminAction(action,id){var text=action==='archive'?'¿Eliminar esta oferta? Podrás restaurarla después.':(action==='restore'?'¿Restaurar esta oferta?':'¿Cambiar su estado de publicación?');if(confirm(text))appAction('modulos/ofertas/php/app_action.php',{action:action,id:id},offersAdminReload);}
$('#offers-admin-filter').on('submit',function(e){e.preventDefault();offersAdminReload();});
</script>
