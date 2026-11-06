<?php
declare(strict_types=1);
require __DIR__ . '/../../../../lib/app.php';
app_require_mode([0]); $id=app_int('id'); $record=$id?app_db()->one('SELECT * FROM system_01_modulos WHERE id_system_01=?','i',[$id]):null;
if($id&&!$record){http_response_code(404);exit('Modulo inexistente.');}
?>
<div class="card admin-card admin-editor mb-4"><div class="card-body p-4"><div class="d-flex justify-content-between"><h2 class="h5"><?=$id?'Editar modulo':'Nuevo modulo'?></h2><button class="btn-close" onclick="$('#root-panel').empty()" aria-label="Cerrar"></button></div>
<form id="module-form" class="row g-3 mt-1"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=$id?>">
<fieldset class="col-12 admin-form-section"><legend><span class="admin-section-number">1</span>Identificación del módulo</legend><p class="admin-section-help">Definí el nombre que verá el equipo y el módulo del sistema al que corresponde.</p><div class="row g-3"><div class="col-md-4"><label class="form-label required">Nombre</label><input class="form-control" name="name" maxlength="100" required value="<?=app_escape($record['system_01_modulo']??'')?>"></div>
<div class="col-md-4"><label class="form-label required">Ruta</label><select class="form-select" name="path" required><?php foreach(['home'=>'Home pública','control'=>'Control de usuarios','ofertas'=>'Ofertas académicas','noticias'=>'Noticias','root'=>'Root'] as $value=>$label):?><option value="<?=$value?>" <?=($record['system_01_path_home']??'')===$value?'selected':''?>><?=$label?></option><?php endforeach;?></select></div>
<div class="col-md-4"><label class="form-label required">Tipo</label><select class="form-select" name="type"><?php foreach(['public','acces','root'] as $value):?><option value="<?=$value?>" <?=($record['system_01_tipo']??'')===$value?'selected':''?>><?=$value?></option><?php endforeach;?></select></div>
</div></fieldset>
<fieldset class="col-12 admin-form-section"><legend><span class="admin-section-number">2</span>Ubicación en el menú</legend><p class="admin-section-help">Los números menores aparecen primero. La visibilidad no reemplaza la asignación de permisos por usuario.</p><div class="row g-3"><div class="col-md-6"><label class="form-label required">Orden</label><input class="form-control" type="number" name="sort" min="0" max="99" value="<?=(int)($record['system_01_orden']??0)?>"></div>
<div class="col-md-6"><label class="form-label required">Menu</label><select class="form-select" name="visible"><option value="on" <?=($record['system_01_onoff']??'on')==='on'?'selected':''?>>Visible</option><option value="off" <?=($record['system_01_onoff']??'')==='off'?'selected':''?>>Oculto</option></select></div>
</div></fieldset>
<div class="col-12 admin-form-actions"><button class="btn btn-light" type="button" onclick="$('#root-panel').empty()">Cancelar</button><button class="btn btn-primary">Guardar módulo</button></div></form></div></div>
<script>$('#module-form').on('submit',function(e){e.preventDefault();appAction('modulos/root/php/app_action.php',$(this).serialize(),rootReload);});</script>

