<?php
declare(strict_types=1);
require_once __DIR__.'/../../../../lib/app.php';
app_require_permission('noticias');
$noticiaId=(int)($noticiaAdjuntosId??app_int('noticia_id'));
$noticia=app_db()->one('SELECT id,youtube_codigo FROM noticias WHERE id=? AND deleted_at IS NULL','i',[$noticiaId]);
if (!$noticia) {http_response_code(404);exit('Guardá primero una noticia disponible.');}
$archivos=app_db()->all('SELECT * FROM noticia_archivos WHERE noticia_id=? AND deleted_at IS NULL ORDER BY orden,id','i',[$noticiaId]);
$video=preg_match('/^[A-Za-z0-9_-]{11}$/D',(string)$noticia['youtube_codigo'])?(string)$noticia['youtube_codigo']:'';
?>
<div class="news-attachments" data-noticia-id="<?=$noticiaId?>">
    <h3 class="h5 mb-3">2. Adjuntos de la nota</h3>
    <p class="text-secondary small">Cada panel guarda sus cambios por separado. No es necesario volver a guardar el texto.</p>
    <div class="news-attachments-result" role="status" aria-live="polite"></div>
    <?php foreach (['imagen'=>'Imágenes','adjunto'=>'Archivos descargables'] as $tipo=>$titulo): ?>
    <section class="card mb-3"><div class="card-body">
        <h4 class="h6"><i class="bi <?=$tipo==='imagen'?'bi-images':'bi-paperclip'?> me-2"></i><?=$titulo?></h4>
        <form class="news-attachment-upload row g-3" enctype="multipart/form-data">
            <input type="hidden" name="tipo" value="<?=$tipo?>"><input type="hidden" name="action" value="upload">
            <div class="col-md-9"><label class="form-label" for="news-files-<?=$tipo?>">Seleccionar <?=$tipo==='imagen'?'imágenes':'documentos'?></label>
                <input id="news-files-<?=$tipo?>" class="form-control" type="file" name="archivos[]" multiple required accept="<?=$tipo==='imagen'?'image/jpeg,image/png,image/webp,image/gif':'.pdf,.doc,.docx,.xls,.xlsx'?>">
                <div class="form-text"><?=$tipo==='imagen'?'JPG, PNG, WebP o GIF. Hasta 8 MB por imagen. La primera imagen guardada se utiliza como portada.':'PDF, Word o Excel. Hasta 15 MB por archivo.'?> Máximo 10 archivos por carga; sujeto al límite del servidor.</div>
            </div><div class="col-md-3 d-flex align-items-start pt-md-4"><button <?=app_permission_attrs('noticias','M')?> class="btn btn-outline-primary w-100 mt-md-2" type="submit">Cargar <?=$tipo==='imagen'?'imágenes':'documentos'?></button></div>
            <div class="news-local-preview row g-2 m-0" aria-live="polite"></div>
        </form>
        <div class="row g-3 mt-1">
        <?php $cantidad=0; foreach ($archivos as $archivo): if ($archivo['tipo']!==$tipo) continue; $cantidad++; $url='../'.ltrim($archivo['ruta'],'/'); ?>
            <div class="<?=$tipo==='imagen'?'col-sm-6 col-xl-4':'col-12'?>"><div class="border rounded p-2 h-100">
                <?php if ($tipo==='imagen'): ?><a href="<?=app_escape($url)?>" target="_blank" rel="noopener"><img src="<?=app_escape($url)?>" alt="<?=app_escape($archivo['nombre_original'])?>" class="w-100 rounded mb-2" style="height:180px;object-fit:contain;background:#f8f9fa" loading="lazy"></a><?php endif; ?>
                <div class="d-flex justify-content-between align-items-center gap-2"><div class="text-break"><a href="<?=app_escape($url)?>" target="_blank" rel="noopener" <?=$tipo==='adjunto'?'download':''?>><?=app_escape($archivo['nombre_original'])?></a><small class="d-block text-secondary"><?=number_format((int)$archivo['tamano_bytes']/1024,0)?> KB</small></div>
                    <button <?=app_permission_attrs('noticias','B')?> class="btn btn-sm btn-outline-danger news-attachment-remove flex-shrink-0" type="button" data-file-id="<?=(int)$archivo['id']?>" aria-label="Quitar <?=app_escape($archivo['nombre_original'])?>"><i class="bi bi-trash"></i></button>
                </div>
            </div></div>
        <?php endforeach; ?>
        <?php if (!$cantidad): ?><p class="text-secondary small mb-0">Todavía no hay <?=$tipo==='imagen'?'imágenes':'documentos'?> cargados.</p><?php endif; ?>
        </div>
    </div></section>
    <?php endforeach; ?>
    <section class="card"><div class="card-body">
        <h4 class="h6"><i class="bi bi-youtube me-2"></i>Video de YouTube</h4>
        <form class="news-video-form row g-3">
            <input type="hidden" name="action" value="video_save">
            <div class="col-md-8"><label for="news-youtube" class="form-label">URL o código del video</label><input id="news-youtube" class="form-control" name="youtube" maxlength="500" value="<?=app_escape($video)?>" placeholder="https://www.youtube.com/watch?v=…" required></div>
            <div class="col-md-4 d-flex align-items-end gap-2"><button class="btn btn-outline-secondary news-video-preview-button" type="button">Vista previa</button><button <?=app_permission_attrs('noticias','M')?> class="btn btn-outline-primary" type="submit">Guardar video</button></div>
        </form>
        <div class="news-video-preview mt-3" style="max-width:720px"><?php if ($video): ?><div class="ratio ratio-16x9"><iframe src="https://www.youtube-nocookie.com/embed/<?=app_escape($video)?>" title="Video adjunto de la noticia" loading="lazy" allowfullscreen></iframe></div><?php endif; ?></div>
        <?php if ($video): ?><button <?=app_permission_attrs('noticias','B')?> class="btn btn-sm btn-outline-danger news-video-remove mt-2" type="button">Quitar video guardado</button><?php endif; ?>
    </div></section>
</div>
<script src="modulos/noticias/js/adjuntos.js"></script>
