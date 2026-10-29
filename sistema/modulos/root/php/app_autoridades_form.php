<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
app_require_mode([0]);
$id=app_int('id');
$db=app_db();
$row=$id?$db->one('SELECT * FROM institucional_autoridades WHERE id=?','i',[$id]):[];
if ($id && !$row) { http_response_code(404); exit('Autoridad no encontrada.'); }
?>
<form id="authority-form" class="row g-3 border rounded p-3 mb-4" enctype="multipart/form-data">
<div class="col-12 d-flex justify-content-between"><h3 class="h5"><?=$id?'Editar autoridad':'Nueva autoridad'?></h3><button type="button" class="btn-close" onclick="$('#authority-editor').empty()" aria-label="Cerrar"></button></div>
<input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="revision" value="<?=(int)($row['revision']??0)?>">
<?php foreach(['nombre'=>'Nombre','apellido'=>'Apellido','cargo'=>'Cargo','profesion'=>'Profesión / título'] as $key=>$label): ?>
<div class="col-md-6"><label class="form-label" for="authority-<?=$key?>"><?=$label?></label><input id="authority-<?=$key?>" class="form-control" name="<?=$key?>" maxlength="<?=in_array($key,['nombre','apellido'])?120:160?>" value="<?=app_escape($row[$key]??'')?>" <?=$key!=='profesion'?'required':''?>></div>
<?php endforeach; ?>
<div class="col-12"><label class="form-label" for="authority-description">Presentación breve</label><textarea id="authority-description" class="form-control" name="descripcion" maxlength="2000" rows="3"><?=app_escape($row['descripcion']??'')?></textarea></div>
<div class="col-md-6"><label class="form-label" for="authority-video-url">Enlace del video de saludo</label><input id="authority-video-url" class="form-control" name="video_url" maxlength="500" value="<?=app_escape($row['video_url']??(empty($row['youtube_codigo'])?'':'https://www.youtube.com/watch?v='.$row['youtube_codigo']))?>" placeholder="https://app.heygen.com/embeds/..."><div class="form-text">Admite YouTube (incluidos Shorts), HeyGen y Vimeo. Para HeyGen, pegá el enlace /embeds/ del video. El video debe permitir inserción.</div></div>
<div class="col-md-3"><label class="form-label" for="authority-year">Año / período</label><input id="authority-year" class="form-control" type="number" name="anio" min="1900" max="2200" required value="<?=(int)($row['anio']??date('Y'))?>"></div>
<div class="col-md-3"><label class="form-label" for="authority-order">Orden</label><input id="authority-order" class="form-control" type="number" name="orden" min="0" max="65535" required value="<?=(int)($row['orden']??0)?>"></div>
<div class="col-md-6"><label class="form-label" for="authority-image">Foto de perfil</label><input id="authority-image" class="form-control" type="file" name="imagen" accept="image/jpeg,image/png,image/webp"><div class="form-text">JPG, PNG o WebP; hasta 5 MB y 12 megapíxeles. Preferentemente cuadrada.</div><img id="authority-preview" src="<?=app_escape($row['imagen_url']??'')?>" alt="Vista previa" class="rounded mt-2 <?=empty($row['imagen_url'])?'d-none':''?>" style="width:140px;height:140px;object-fit:cover"><div class="form-check mt-2"><input id="authority-remove" class="form-check-input" type="checkbox" name="quitar_imagen"><label for="authority-remove" class="form-check-label">Quitar foto actual</label></div></div>
<div class="col-md-6"><label class="form-label" for="authority-reason">Motivo del cambio</label><input id="authority-reason" class="form-control" name="motivo" maxlength="250" required><div class="form-check mt-3"><input class="form-check-input" id="authority-visible" name="visible" type="checkbox" <?=!empty($row['visible'])?'checked':''?>><label for="authority-visible" class="form-check-label">Visible en la portada</label></div></div>
<div class="col-12"><div id="authority-result" role="status" class="mb-2"></div><button class="btn btn-primary" type="submit">Guardar autoridad</button></div>
</form>
<?php if($id): ?><details class="mb-4"><summary>Historial de esta autoridad</summary>
<?php foreach($db->all('SELECT * FROM institucional_autoridades_historial WHERE autoridad_id=? ORDER BY revision DESC','i',[$id]) as $entry): $snapshot=json_decode($entry['datos_json'],true)??[]; ?>
<details class="border rounded p-3 my-2"><summary><?=app_escape('Versión '.$entry['revision'].' · '.$entry['created_at'].' · '.$entry['motivo'])?></summary><dl class="row mt-3">
<?php foreach(['nombre'=>'Nombre','apellido'=>'Apellido','cargo'=>'Cargo','profesion'=>'Profesión','descripcion'=>'Presentación','anio'=>'Año','orden'=>'Orden','visible'=>'Visible','video_url'=>'Enlace del video','youtube_codigo'=>'Código YouTube (versiones anteriores)'] as $key=>$label): ?><dt class="col-md-3"><?=$label?></dt><dd class="col-md-9"><?=nl2br(app_escape($snapshot[$key]??''))?></dd><?php endforeach; ?>
</dl><?php if(!empty($snapshot['imagen_url'])): ?><img src="<?=app_escape($snapshot['imagen_url'])?>" alt="Foto de esta versión" width="120" loading="lazy"><?php endif; ?></details>
<?php endforeach; ?></details><?php endif; ?>
<script>
(function(){
  var previewUrl=null;
  $('#authority-image').on('change',function(){
    if(previewUrl) URL.revokeObjectURL(previewUrl);
    var file=this.files[0];
    if(!file)return;
    previewUrl=URL.createObjectURL(file);
    $('#authority-preview').attr('src',previewUrl).removeClass('d-none');
    $('#authority-remove').prop('checked',false);
  });
  $('#authority-form').on('submit',function(e){
    e.preventDefault();
    var data=new FormData(this),button=$(this).find('[type="submit"]').prop('disabled',true);
    data.append('csrf_token',$('meta[name="csrf-token"]').attr('content'));
    $.ajax({url:'modulos/root/php/app_autoridades_action.php',method:'POST',data:data,processData:false,contentType:false,dataType:'json'})
      .done(function(result){if(result.ok){if(previewUrl)URL.revokeObjectURL(previewUrl);cargar_post('modulos/root/php/app_autoridades.php','authorities-panel','');}else $('#authority-result').text(result.message);})
      .fail(function(xhr){$('#authority-result').text(xhr.responseJSON?xhr.responseJSON.message:'No se pudo guardar. Intentá nuevamente.');})
      .always(function(){button.prop('disabled',false);});
  });
})();
</script>
