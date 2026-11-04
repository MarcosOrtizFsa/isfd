<?php
declare(strict_types=1);
require __DIR__.'/lib/realtime.php';
$admin=app_require_mode([0]);
header('Cache-Control: no-store');
$chat=new Chat(app_db());
if ($_SERVER['REQUEST_METHOD']==='POST') {
    app_verify_csrf();
    try {
        $chat->moderacion($admin['id'],app_int('usuario_id'),app_int('reporte_id'),app_post('accion'),app_post('motivo'));
        (new ChatRealtime(app_db()))->notificar();
        app_json(['ok'=>true,'message'=>'Decisión guardada en el historial.']);
    } catch (ChatError $e) { app_json(['ok'=>false,'message'=>$e->getMessage()],$e->status); }
    catch (Throwable $e) { error_log('Chat moderación: '.$e->getMessage()); app_json(['ok'=>false,'message'=>'No se pudo guardar la decisión.'],500); }
}
$page=max(1,(int)($_GET['page']??1)); $offset=min(100000,($page-1)*30);
$search=mb_substr(trim((string)($_GET['q']??'')),0,24);
$reports=app_db()->all("SELECT r.*,u.usuario,d.usuario denunciante FROM chat_reportes r JOIN chat_usuarios u ON u.id=r.usuario_id LEFT JOIN chat_usuarios d ON d.id=r.denunciante_id WHERE r.estado='pendiente' ORDER BY r.id DESC LIMIT 30 OFFSET $offset");
$users=app_db()->all("SELECT id,usuario,avatar_id,bloqueado,suspendido_hasta,created_at FROM chat_usuarios WHERE usuario LIKE ? ORDER BY id DESC LIMIT 30 OFFSET $offset",'s',['%'.$search.'%']);
$audit=app_db()->all('SELECT a.*,u.usuario FROM chat_auditoria a JOIN chat_usuarios u ON u.id=a.usuario_id ORDER BY a.id DESC LIMIT 30');
$pending=(int)app_db()->one("SELECT COUNT(*) n FROM chat_reportes WHERE estado='pendiente'")['n'];
function chat_admin_actions(int $user, int $report=0): void { ?>
    <form class="moderation-form row g-2">
        <input type="hidden" name="usuario_id" value="<?=$user?>"><input type="hidden" name="reporte_id" value="<?=$report?>">
        <div class="col-md-4"><label class="visually-hidden">Acción</label><select name="accion" class="form-select form-select-sm" aria-label="Acción de moderación">
            <option value="advertir">Advertencia escrita</option><option value="suspender">Pausa de 24 horas</option><option value="expulsar">Expulsar (reversible)</option><option value="rehabilitar">Rehabilitar</option>
            <?php if($report):?><option value="ocultar">Retirar mensaje</option><option value="descartar">Descartar reporte</option><?php endif;?>
        </select></div>
        <div class="col-md-5"><input class="form-control form-control-sm" name="motivo" minlength="5" maxlength="500" placeholder="Motivo de la decisión" aria-label="Motivo de la decisión" required></div>
        <div class="col-md-3"><button class="btn btn-sm btn-outline-primary w-100" type="submit">Aplicar</button></div>
    </form>
<?php } ?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="<?=app_escape(app_csrf_token())?>"><title>Administrar chat · ISFD</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../css/app_admin.css" rel="stylesheet"></head>
<body class="bg-light"><main class="container py-4">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3">Chat institucional</h1><p class="text-secondary mb-0">Moderación y participantes · acceso exclusivo root</p></div><a class="btn btn-outline-secondary" href="../mipanel">Volver a mipanel</a></div>
    <div id="admin-status" role="status" aria-live="polite"></div>
    <div class="alert alert-info">Revisá el contexto antes de sancionar. Los filtros automáticos son orientativos. Las pausas automáticas duran 10 minutos; podés revertirlas con <strong>Rehabilitar</strong>. Una expulsión siempre requiere tu decisión y queda registrada.</div>
    <div id="admin-content">
    <h2 class="h5">Reportes por revisar <span class="badge text-bg-secondary"><?=$pending?></span></h2>
    <?php if(!$reports):?><p class="text-secondary">No hay reportes pendientes en esta página.</p><?php endif;?>
    <?php foreach($reports as $row):?><article class="card mb-3"><div class="card-body">
        <div class="d-flex justify-content-between gap-2"><strong>@<?=app_escape($row['usuario'])?></strong><small><?=app_escape($row['created_at'])?> · <?=app_escape($row['origen'])?></small></div>
        <p class="text-break mt-2" style="white-space:pre-wrap"><?=app_escape($row['texto'])?></p>
        <p class="small text-secondary">Motivo: <?=app_escape($row['motivo'])?><?php if($row['denunciante']):?> · Reportado por @<?=app_escape($row['denunciante'])?><?php endif;?></p>
        <?php if($row['mensaje_id']): $context=app_db()->all('SELECT m.texto,m.created_at,u.usuario FROM chat_mensajes m JOIN chat_usuarios u ON u.id=m.usuario_id WHERE m.sala_id=(SELECT sala_id FROM chat_mensajes WHERE id=?) AND m.id<=? ORDER BY m.id DESC LIMIT 5','ii',[$row['mensaje_id'],$row['mensaje_id']]); ?>
        <details class="mb-3"><summary>Ver contexto previo</summary><?php foreach(array_reverse($context) as $message):?><p class="small text-break mb-1"><strong>@<?=app_escape($message['usuario'])?>:</strong> <?=app_escape($message['texto'])?></p><?php endforeach;?></details><?php endif;?>
        <?php chat_admin_actions((int)$row['usuario_id'],(int)$row['id']); ?>
    </div></article><?php endforeach;?>
    <h2 class="h5 mt-4">Participantes</h2><form class="d-flex gap-2 mb-3" method="get"><input name="q" class="form-control" placeholder="Buscar por usuario" value="<?=app_escape($search)?>" maxlength="24" aria-label="Buscar por usuario"><button class="btn btn-outline-secondary">Buscar</button></form>
    <?php foreach($users as $row):?><article class="card mb-2"><div class="card-body"><div class="d-flex align-items-center gap-2 mb-3"><img src="avatar.php?id=<?=(int)$row['avatar_id']?>" width="36" height="36" alt=""><strong>@<?=app_escape($row['usuario'])?></strong><span class="badge text-bg-light"><?=$row['bloqueado']?'Expulsado':($row['suspendido_hasta'] && strtotime($row['suspendido_hasta'])>time()?'Pausa hasta '.app_escape($row['suspendido_hasta']):'Activo')?></span></div><?php chat_admin_actions((int)$row['id']);?></div></article><?php endforeach;?>
    <nav class="d-flex justify-content-between my-3" aria-label="Paginación"><a class="btn btn-sm btn-outline-secondary" href="?page=<?=max(1,$page-1)?>&amp;q=<?=urlencode($search)?>">Anterior</a><span>Página <?=$page?></span><?php if(count($reports)===30 || count($users)===30):?><a class="btn btn-sm btn-outline-secondary" href="?page=<?=$page+1?>&amp;q=<?=urlencode($search)?>">Siguiente</a><?php endif;?></nav>
    <h2 class="h5 mt-4">Últimas decisiones</h2><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Fecha</th><th>Participante</th><th>Acción</th><th>Motivo</th><th>Admin.</th></tr></thead><tbody><?php foreach($audit as $row):?><tr><td><?=app_escape($row['created_at'])?></td><td>@<?=app_escape($row['usuario'])?></td><td><?=app_escape($row['accion'])?></td><td><?=app_escape($row['motivo'])?></td><td><?=(int)$row['administrador_id']?></td></tr><?php endforeach;?></tbody></table></div>
    </div>
</main><script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script><script>
$(document).on('submit','.moderation-form',function(e){
    e.preventDefault();var form=$(this),button=form.find('[type=submit]'),data=form.serializeArray();
    if(form.find('[name=accion]').val()==='expulsar'&&!confirm('¿Expulsar esta cuenta? La decisión queda registrada y puede revertirse.'))return;
    data.push({name:'csrf_token',value:$('meta[name=csrf-token]').attr('content')});button.prop('disabled',true);
    $.post('admin.php',$.param(data),null,'json').done(function(r){$('#admin-status').attr('class','alert alert-success').text(r.message);$('#admin-content').load(location.href+' #admin-content > *');}).fail(function(x){$('#admin-status').attr('class','alert alert-danger').text(x.responseJSON?.message||'No se pudo guardar. Recargá e intentá nuevamente.');}).always(function(){button.prop('disabled',false);});
});
</script></body></html>
