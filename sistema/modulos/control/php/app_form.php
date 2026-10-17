<?php
declare(strict_types=1);
require __DIR__ . '/../../../../lib/app.php';
$current = app_require_mode([0, 1, 2]);
$id = app_int('id');
$db = app_db();
$record = $id ? $db->one('SELECT u.*, p.* FROM system_03_usuarios u LEFT JOIN system_04_perfil p ON p.rela_system_03=u.id_system_03 WHERE u.id_system_03=?', 'i', [$id]) : null;
if ($id && $record === null) { http_response_code(404); exit('Usuario inexistente.'); }
$roles = $db->all('SELECT id_system_07, system_07_nombre FROM system_07_privilegios WHERE id_system_07 <> 1 OR ? = 0 ORDER BY id_system_07', 'i', [$current['mode']]);
?>
<div class="card admin-card mb-4"><div class="card-body p-4"><div class="d-flex justify-content-between"><h2 class="h5"><?=$id?'Editar usuario':'Nuevo usuario'?></h2><button class="btn-close" type="button" onclick="$('#control-panel').empty()" aria-label="Cerrar"></button></div>
<form id="user-form" class="row g-3 mt-1">
<input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=$id?>">
<div class="col-md-6"><label class="form-label required">Nombre</label><input class="form-control" name="nombre" maxlength="240" required value="<?=app_escape($record['system_04_nombre']??'')?>"></div>
<div class="col-md-6"><label class="form-label required">Apellido</label><input class="form-control" name="apellido" maxlength="240" required value="<?=app_escape($record['system_04_apellido']??'')?>"></div>
<div class="col-md-4"><label class="form-label required">DNI / usuario</label><input class="form-control" name="usuario" inputmode="numeric" maxlength="11" required value="<?=app_escape($record['system_03_usuario']??'')?>"></div>
<div class="col-md-4"><label class="form-label required">Rol</label><select class="form-select" name="role_id" required><option value="">Seleccionar</option><?php foreach($roles as $role):?><option value="<?=(int)$role['id_system_07']?>" <?=((int)($record['rela_system_07']??0)===(int)$role['id_system_07'])?'selected':''?>><?=app_escape($role['system_07_nombre'])?></option><?php endforeach;?></select></div>
<div class="col-md-4"><label class="form-label <?=$id?'':'required'?>">Clave <?=$id?'<span class="text-secondary">(dejar vacia para conservar)</span>':''?></label><input class="form-control" type="password" name="password" minlength="10" maxlength="128" <?=$id?'':'required'?>></div>
<div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" maxlength="60" value="<?=app_escape($record['system_04_email']??'')?>"></div>
<div class="col-md-6"><label class="form-label">Celular</label><input class="form-control" name="celular" maxlength="16" value="<?=app_escape($record['system_04_celular']??'')?>"></div>
<div class="col-12 d-flex justify-content-end gap-2"><button class="btn btn-light" type="button" onclick="$('#control-panel').empty()">Cancelar</button><button class="btn btn-primary" type="submit">Guardar</button></div>
</form></div></div>
<script>$('#user-form').on('submit',function(e){e.preventDefault();appAction('modulos/control/php/app_action.php',$(this).serialize(),controlReload);});</script>

