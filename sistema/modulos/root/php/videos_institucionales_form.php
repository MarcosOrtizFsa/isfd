<?php
// Fragmento incluido por el formulario root, nunca accesible sin sesión.
require_once __DIR__.'/../../../../lib/app.php';
app_require_mode([0]);
?>
<div class="col-12" id="institutional-video-editor">
    <p class="small text-secondary">Un video visible por vez en la portada. Admite YouTube, HeyGen y Vimeo. Usá las flechas para ordenar. Quitar un video lo retira de la lista actual, pero las versiones anteriores se conservan.</p>
    <input type="hidden" name="valores[videos_json]" value="<?=app_escape($current['videos_json']??'[]')?>">
    <div data-video-rows></div>
    <button type="button" class="btn btn-outline-primary mt-2" data-video-add>Agregar video</button>
</div>
<script>
(function(){
    var editor=document.getElementById('institutional-video-editor');
    var input=editor.querySelector('input'),rows=editor.querySelector('[data-video-rows]');
    var items=[];
    try{items=JSON.parse(input.value)||[];}catch(error){}
    function add(item){
        var row=document.createElement('div');
        row.className='row g-2 border rounded p-2 mb-2';
        row.innerHTML='<div class="col-md-4"><label class="d-block">Título<input class="form-control" data-video-title maxlength="160" required></label></div><div class="col-md-5"><label class="d-block">Enlace del video<input class="form-control" data-video-url maxlength="500" required placeholder="https://..."></label></div><div class="col-md-3"><label class="d-block mb-2"><input type="checkbox" data-video-visible> Visible</label><div class="btn-group btn-group-sm"><button type="button" class="btn btn-outline-secondary" data-video-up aria-label="Subir video">↑</button><button type="button" class="btn btn-outline-secondary" data-video-down aria-label="Bajar video">↓</button><button type="button" class="btn btn-outline-danger" data-video-remove>Quitar</button></div></div>';
        row.querySelector('[data-video-title]').value=item.titulo||'';
        row.querySelector('[data-video-url]').value=item.url||'';
        row.querySelector('[data-video-visible]').checked=!!item.visible;
        rows.appendChild(row);
        sync();
    }
    function sync(){
        input.value=JSON.stringify(Array.from(rows.children).map(function(row){return{titulo:row.querySelector('[data-video-title]').value,url:row.querySelector('[data-video-url]').value,visible:row.querySelector('[data-video-visible]').checked};}));
        Array.from(rows.children).forEach(function(row,index){row.querySelector('[data-video-up]').disabled=index===0;row.querySelector('[data-video-down]').disabled=index===rows.children.length-1;});
    }
    items.forEach(add);
    editor.addEventListener('input',sync);
    editor.addEventListener('change',sync);
    editor.addEventListener('click',function(event){
        var button=event.target.closest('button');if(!button)return;
        if(button.hasAttribute('data-video-add')){if(rows.children.length<30)add({visible:true});return;}
        var row=button.closest('.row');if(!row)return;
        if(button.hasAttribute('data-video-remove'))row.remove();
        if(button.hasAttribute('data-video-up')&&row.previousElementSibling)rows.insertBefore(row,row.previousElementSibling);
        if(button.hasAttribute('data-video-down')&&row.nextElementSibling)rows.insertBefore(row.nextElementSibling,row);
        sync();
    });
})();
</script>
