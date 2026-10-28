<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';require_once __DIR__.'/../../noticias/php/neuria_v1_config.php';app_require_mode([0]);neuria_v1_cargar_env();
$record=app_db()->one("SELECT * FROM integracion_credenciales WHERE servicio='neuria' LIMIT 1");$keyReady=strlen((string)getenv('ISFD_CREDENTIAL_KEY'))>=32;
?>
<div class="card admin-card mb-4"><div class="card-body p-4">
<div class="d-flex justify-content-between"><div><h2 class="h5 mb-1">Credenciales de Neuria</h2><p class="small text-secondary mb-0">Configuración exclusiva del desarrollador root. El token se almacena cifrado.</p></div><button class="btn-close" onclick="$('#root-panel').empty()" aria-label="Cerrar"></button></div>
<?php if(!$keyReady):?><div class="alert alert-warning mt-3 mb-0">Antes de guardar un token, configurá <code>ISFD_CREDENTIAL_KEY</code> con al menos 32 caracteres en el archivo privado <code>.env</code>.</div><?php endif;?>
<form id="neuria-config-form" class="row g-3 mt-1"><input type="hidden" name="action" value="save_neuria">
<div class="col-md-7"><label class="form-label required">URL base API v1</label><input class="form-control" type="url" name="base_url" required value="<?=app_escape($record['base_url']??'http://127.0.0.1:8000')?>" placeholder="https://neuriar.com.ar/api/v1"></div>
<div class="col-md-5"><label class="form-label">Token privado</label><input class="form-control" type="password" name="token" autocomplete="new-password" placeholder="<?=!empty($record['token_hint'])?'Configurado: '.app_escape($record['token_hint']).' · dejar vacío para conservar':'Ingresar token'?>"><div class="form-text">Nunca se muestra el token guardado.</div></div>
<div class="col-md-3"><label class="form-label">Timeout</label><input class="form-control" type="number" name="timeout" min="3" max="60" value="<?=(int)($record['timeout_segundos']??30)?>"></div>
<div class="col-md-3"><label class="form-label">Límite por página</label><input class="form-control" type="number" name="limit" min="1" max="100" value="<?=(int)($record['sync_limite']??50)?>"></div>
<div class="col-md-3 d-flex align-items-end"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="active" value="1" id="neuria-active" <?=($record['activa']??1)?'checked':''?>><label class="form-check-label" for="neuria-active">Integración activa</label></div></div>
<div class="col-md-3"><label class="form-label">Última verificación</label><div class="form-control bg-light"><?=app_escape($record['verificada_at']??'Nunca')?></div></div>
<?php if(!empty($record['ultimo_error'])):?><div class="col-12"><div class="alert alert-danger mb-0"><?=app_escape($record['ultimo_error'])?></div></div><?php endif;?>
<div class="col-12 d-flex justify-content-end gap-2"><button class="btn btn-outline-secondary" type="button" onclick="rootNeuriaVerify()" <?=$record?'':'disabled'?>>Probar conexión</button><button class="btn btn-light" type="button" onclick="$('#root-panel').empty()">Cancelar</button><button class="btn btn-primary" type="submit" <?=$keyReady?'':'disabled'?>>Guardar configuración</button></div>
</form></div></div>
<script>$('#neuria-config-form').on('submit',function(e){e.preventDefault();appAction('modulos/root/php/app_neuria_action.php',$(this).serialize(),rootReload)});function rootNeuriaVerify(){appAction('modulos/root/php/app_neuria_action.php',{action:'verify_neuria'},function(){cargar_post('modulos/root/php/app_neuria_config.php','root-panel','')})}</script>
