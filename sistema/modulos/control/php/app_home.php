<?php
declare(strict_types=1);
require __DIR__ . '/../../../../lib/app.php';
$current = app_require_mode([0, 1, 2]);
$users = app_db()->all("SELECT u.id_system_03, u.system_03_usuario, u.system_03_estado, u.system_03_modo, p.system_04_nombre, p.system_04_apellido, p.system_04_email, r.system_07_nombre FROM system_03_usuarios u LEFT JOIN system_04_perfil p ON p.rela_system_03=u.id_system_03 LEFT JOIN system_07_privilegios r ON r.id_system_07=u.rela_system_07 ORDER BY p.system_04_apellido, p.system_04_nombre, u.system_03_usuario");
?>
<section class="admin-page"><div class="container-fluid px-3 px-lg-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 mb-1">Usuarios</h1><p class="text-secondary mb-0">Accesos, perfiles y estado de las cuentas.</p></div><button class="btn btn-primary" onclick="cargar_post('modulos/control/php/app_form.php','control-panel','')"><i class="bi bi-person-plus me-2"></i>Nuevo usuario</button></div>
  <div id="control-panel"></div>
  <div class="card admin-card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle admin-table mb-0"><thead><tr><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Email</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody>
  <?php foreach ($users as $row): $active=(int)$row['system_03_estado']===1; ?>
    <tr><td data-label="Usuario"><?=app_escape($row['system_03_usuario'])?></td><td data-label="Nombre"><?=app_escape(trim($row['system_04_apellido'].' '.$row['system_04_nombre']))?></td><td data-label="Rol"><?=app_escape($row['system_07_nombre'] ?: 'Sin rol')?></td><td data-label="Email"><?=app_escape($row['system_04_email'])?></td><td data-label="Estado"><span class="badge <?=$active?'text-bg-success':'text-bg-secondary'?>"><?=$active?'Activo':'Inactivo'?></span></td><td data-label="Acciones" class="text-end"><div class="btn-group btn-group-sm"><button class="btn btn-outline-primary" title="Editar" onclick="cargar_post('modulos/control/php/app_form.php','control-panel',{id:<?=(int)$row['id_system_03']?>})"><i class="bi bi-pencil"></i></button><button class="btn btn-outline-secondary" title="Cambiar estado" onclick="controlToggle(<?=(int)$row['id_system_03']?>)"><i class="bi bi-power"></i></button></div></td></tr>
  <?php endforeach; ?>
  </tbody></table></div></div></div>
</div></section>
<script>function controlReload(){cargar_post('modulos/control/php/app_home.php','content_seccion','');} function controlToggle(id){if(confirm('¿Cambiar el estado de esta cuenta?'))appAction('modulos/control/php/app_action.php',{action:'toggle',id:id},controlReload);}</script>

