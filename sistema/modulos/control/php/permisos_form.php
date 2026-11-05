<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
app_require_mode([0]);
$id=app_int('id');
$db=app_db();
$usuario=$db->one('SELECT u.id_system_03,u.system_03_usuario,u.system_03_modo,p.system_04_nombre,p.system_04_apellido FROM system_03_usuarios u LEFT JOIN system_04_perfil p ON p.rela_system_03=u.id_system_03 WHERE u.id_system_03=?','i',[$id]);
if (!$usuario) { http_response_code(404); exit('Usuario inexistente.'); }
if ((int)$usuario['system_03_modo']===0) { exit('Root tiene acceso completo a todos los módulos.'); }
$modulos=$db->all("SELECT * FROM system_01_modulos WHERE system_01_path_home IN ('control','ofertas','noticias','contactos','home') ORDER BY system_01_orden,system_01_modulo");
$asignaciones=[];
foreach ($db->all('SELECT * FROM system_02_permisos WHERE rela_system_03=? ORDER BY id_system_02','i',[$id]) as $fila) $asignaciones[(int)$fila['rela_system_01']]=$fila;
?>
<h3 class="h6"><?=app_escape(trim(($usuario['system_04_nombre']??'').' '.($usuario['system_04_apellido']??''))?:$usuario['system_03_usuario'])?></h3>
<p class="small text-secondary">El acceso permite ver el módulo. Las acciones se otorgan por separado. Solo Root administra estos permisos.</p>
<form id="user-permissions-form">
<input type="hidden" name="id" value="<?=$id?>">
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Módulo / acceso</th><?php foreach (PermisosModulos::ACCIONES as $letra=>$nombre): ?><th class="text-center"><abbr title="<?=$nombre?>"><?=$letra?></abbr></th><?php endforeach; ?></tr></thead><tbody>
<?php foreach ($modulos as $modulo): $mid=(int)$modulo['id_system_01'];$fila=$asignaciones[$mid]??[];$acceso=(int)($fila['system_02_acceso']??0)===1; ?>
<tr><td><label class="d-flex align-items-center gap-2"><input type="checkbox" class="form-check-input m-0 permission-access" name="permisos[<?=$mid?>][acceso]" value="1" <?=$acceso?'checked':''?>><?=app_escape($modulo['system_01_modulo'])?></label><?php if ((int)$modulo['system_01_estado']!==1 || $modulo['system_01_onoff']!=='on'): ?><small class="text-secondary">Módulo deshabilitado globalmente</small><?php endif; ?></td>
<?php foreach (PermisosModulos::ACCIONES as $letra=>$nombre): ?><td class="text-center"><?php if ($modulo['system_01_path_home']==='home'): ?><span title="La portada es pública">—</span><?php else: ?><input type="checkbox" class="form-check-input permission-action" name="permisos[<?=$mid?>][<?=$letra?>]" value="1" aria-label="<?=app_escape($nombre.' en '.$modulo['system_01_modulo'])?>" <?=$acceso && (int)($fila['system_02_'.$letra]??0)===1?'checked':''?> <?=$acceso?'':'disabled'?>><?php endif; ?></td><?php endforeach; ?></tr>
<?php endforeach; ?></tbody></table></div>
<p class="small">A: agregar · B: borrar · M: modificar · E: cambiar estado.<br>Se habilitan únicamente las acciones que existen en cada módulo. Home sigue siendo público. Módulos internos es exclusivo de Root.</p>
<div id="permissions-result" role="status" aria-live="polite"></div>
<div class="text-end"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Guardar permisos</button></div>
</form>
<script>
(function(){
    var form=$('#user-permissions-form');
    form.on('change','.permission-access',function(){
        var actions=$(this).closest('tr').find('.permission-action');
        actions.prop('disabled',!this.checked);
        if(!this.checked) actions.prop('checked',false);
    });
    form.on('submit',function(event){
        event.preventDefault();var button=form.find('[type=submit]').prop('disabled',true);
        appAction('modulos/control/php/permisos_action.php',form.serialize(),function(){
            $('#permissions-result').text('Permisos guardados. Se aplican desde la próxima solicitud del usuario.').addClass('alert alert-success');
        }).always(function(){button.prop('disabled',false);});
    });
})();
</script>
