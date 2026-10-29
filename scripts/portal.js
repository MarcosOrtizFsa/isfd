(function(){'use strict';
  var timer,page=1,lastPage=1,level='',currentOfferId=0;
  function one(s){return document.querySelector(s)}
  function escapeHtml(v){var d=document.createElement('div');d.textContent=v||'';return d.innerHTML}
  function csrf(){var node=one('meta[name="csrf-token"]');return node?node.content:''}
  function refreshCaptcha(scope){var image=(scope||document).querySelector('[data-captcha-context]');if(image)image.src='../api/captcha?context='+encodeURIComponent(image.dataset.captchaContext)+'&_='+(Date.now());var input=(scope||document).querySelector('[name="captcha"]');if(input)input.value=''}
  function sharePublication(network, scope) {
    var canonical = one('link[rel="canonical"]');
    var heading = one('.isfd-site h1');
    var url = canonical ? canonical.href : window.location.href.split('#')[0];
    var title = heading ? heading.textContent.trim() : document.title;
    var status = (scope || document).querySelector('.isfd-share-status');
    var target = '';

    // La vista previa se obtiene del Open Graph del servidor, no del texto enviado.
    if (network === 'facebook') target = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
    if (network === 'whatsapp') target = 'https://wa.me/?text=' + encodeURIComponent(title + '\n\n' + url);
    if (target) {
      window.open(target, '_blank', 'noopener,noreferrer');
      return;
    }
    if (network === 'instagram') {
      var copyLink = function () {
        var copied = navigator.clipboard && window.isSecureContext
          ? navigator.clipboard.writeText(url) : Promise.reject();
        copied.then(function () {
          if (status) status.textContent = 'Enlace copiado. Pegalo en Instagram para compartirlo.';
        }).catch(function () {
          if (status) status.textContent = 'Copiá este enlace para compartirlo en Instagram: ' + url;
        });
      };
      // El selector nativo muestra las aplicaciones disponibles en el dispositivo.
      if (navigator.share) {
        navigator.share({title: title, url: url}).catch(function (error) {
          if (error.name !== 'AbortError') copyLink();
        });
      } else {
        copyLink();
      }
    }
  }
  function filters(){var form=one('#offer-filters');if(!form)return {};var data=new FormData(form);return{q:data.get('q')||'',tipo:data.get('tipo')||'',estado:data.get('estado')||'',nivel:level,page:page,per_page:6}}
  function syncUrl(values){var url=new URL(window.location.href);['q','tipo','estado','nivel'].forEach(function(k){values[k]?url.searchParams.set(k,values[k]):url.searchParams.delete(k)});url.searchParams.delete('turno');history.replaceState(null,'',url)}
  function skeleton(){var grid=one('#offer-grid');if(grid)grid.innerHTML='<div class="col-md-6 col-lg-4"><div class="offer-skeleton"></div></div>'.repeat(3)}
  function render(items,append){var grid=one('#offer-grid');if(!grid)return;if(!append)grid.innerHTML='';items.forEach(function(item){var col=document.createElement('div');col.className='col-md-6 col-lg-4';col.innerHTML='<article class="offer-card"><div class="offer-image">'+(item.imagen_url?'<img src="'+escapeHtml(item.imagen_url)+'" alt="">':'')+'<span class="offer-level">'+escapeHtml(item.nivel_nombre)+'</span></div><div class="offer-body"><h3>'+escapeHtml(item.titulo)+'</h3><p>'+escapeHtml(item.resumen)+'</p><div class="offer-footer"><span>'+escapeHtml(item.modalidad_nombre||'Oferta académica')+'</span><a href="../oferta/'+escapeHtml(item.slug)+'">Ver propuesta →</a></div></div></article>';grid.appendChild(col)})}
  async function loadOffers(append){var status=one('#offer-status-message'),more=one('#offers-more');if(!append)skeleton();if(status)status.textContent='Cargando ofertas…';var values=filters();syncUrl(values);try{var response=await fetch('../api/ofertas-academicas?'+new URLSearchParams(values),{headers:{Accept:'application/json'}});var json=await response.json();if(!response.ok||!json.ok)throw new Error();render(json.data,append);lastPage=json.meta.last_page;if(status)status.innerHTML=json.data.length?'':'<p>No encontramos propuestas con esos filtros.</p><button class="btn btn-outline-dark" id="clear-offers">Limpiar filtros</button>';if(more)more.classList.toggle('d-none',page>=lastPage)}catch(error){if(one('#offer-grid'))one('#offer-grid').innerHTML='';if(status)status.innerHTML='<p>No pudimos cargar las ofertas.</p><button class="btn btn-outline-dark" id="retry-offers">Reintentar</button>';if(more)more.classList.add('d-none')}}
  function init(){var site=one('.isfd-site'),form=one('#offer-filters');if(!site||!form||site.dataset.portalReady)return;site.dataset.portalReady='1';var query=new URLSearchParams(location.search);['q','tipo','estado'].forEach(function(k){var input=form.elements[k];if(input&&query.get(k))input.value=query.get(k)});level=query.get('nivel')||'';document.querySelectorAll('.isfd-chips button').forEach(function(b){b.classList.toggle('active',b.dataset.level===level)});loadOffers(false);document.querySelectorAll('.isfd-reveal').forEach(function(el){if(!('IntersectionObserver'in window))return el.classList.add('is-visible');new IntersectionObserver(function(entries,observer){entries.forEach(function(entry){if(entry.isIntersecting){entry.target.classList.add('is-visible');observer.unobserve(entry.target)}})},{threshold:.15}).observe(el)})}
  document.addEventListener('input',function(e){if(e.target.matches('#offer-q')){clearTimeout(timer);timer=setTimeout(function(){page=1;loadOffers(false)},350)}});document.addEventListener('change',function(e){if(e.target.closest('#offer-filters')){page=1;loadOffers(false)}});document.addEventListener('submit',function(e){if(e.target.matches('#offer-filters')){e.preventDefault();page=1;loadOffers(false)}if(e.target.matches('#newsletter-form')){e.preventDefault();e.target.reset();window.alert('La suscripción estará disponible próximamente.')}if(e.target.matches('#contact-form')){e.preventDefault();var form=e.target,result=one('#contact-result');if(result)result.textContent='Enviando…';var data=new FormData(form);data.append('csrf_token',csrf());fetch('../api/contacto',{method:'POST',body:data}).then(function(r){return r.json().then(function(j){return{ok:r.ok,json:j}})}).then(function(x){if(!x.ok)throw x.json;if(result)result.textContent=x.json.message;form.reset()}).catch(function(err){if(result)result.textContent=err.message||'Revisá los datos e intentá nuevamente.'}).finally(function(){refreshCaptcha(form)})}});
  document.addEventListener('input',function(e){if(e.target.matches('.js-cellphone')){var digits=e.target.value.replace(/\D/g,'').slice(0,10);e.target.value=digits.length>4?digits.slice(0,4)+' '+digits.slice(4):digits}});
  document.addEventListener('click',function(e){var button=e.target.closest('.js-captcha-refresh');if(button)refreshCaptcha(button.closest('form')||document)});
  document.addEventListener('click',function(e){var internal=e.target.closest('.isfd-site a[href^="../#"]');if(internal&&internal.target!=='_blank'){var id=internal.getAttribute('href').slice(3),target=document.getElementById(id);if(target){e.preventDefault();target.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth'});history.replaceState(null,'','../#'+id);var menu=one('#portal-menu');if(menu&&menu.classList.contains('show')&&window.bootstrap)bootstrap.Collapse.getOrCreateInstance(menu).hide()}}var chip=e.target.closest('.isfd-chips button');if(chip){level=chip.dataset.level;page=1;document.querySelectorAll('.isfd-chips button').forEach(function(b){b.classList.toggle('active',b===chip)});loadOffers(false)}var link=e.target.closest('.js-nivel-link');if(link){level=link.dataset.nivel;page=1;document.querySelectorAll('.isfd-chips button').forEach(function(b){b.classList.toggle('active',b.dataset.level===level)});setTimeout(function(){loadOffers(false)},0)}if(e.target.closest('#offers-more')){page++;loadOffers(true)}if(e.target.closest('#clear-offers')){one('#offer-filters').reset();level='';page=1;loadOffers(false)}if(e.target.closest('#retry-offers'))loadOffers(false);var detail=e.target.closest('.js-offer-detail');if(detail)offerDetail(detail.dataset.slug)});
  function showOfferStep(question){var detail=one('#modal-oferta-detalle'),form=one('#offer-question-form');if(detail)detail.classList.toggle('d-none',question);if(form)form.classList.toggle('d-none',!question);if(question){var result=one('#offer-question-result');if(result)result.textContent='';var name=one('#offer-question-name');if(name)setTimeout(function(){name.focus()},150)}}
  async function offerDetail(slug){var modalNode=one('#modal-oferta'),status=one('#modal-oferta-estado'),detail=one('#modal-oferta-detalle');if(!modalNode)return;if(window.bootstrap)bootstrap.Modal.getOrCreateInstance(modalNode).show();currentOfferId=0;showOfferStep(false);if(detail)detail.classList.add('d-none');status.className='alert alert-light';status.textContent='Cargando información…';try{var response=await fetch('../api/ofertas-academicas/'+encodeURIComponent(slug),{headers:{Accept:'application/json'}});var json=await response.json();if(!response.ok||!json.ok)throw new Error();var o=json.data;currentOfferId=Number(o.id)||0;one('#offer-question-id').value=currentOfferId;one('#modal-oferta-titulo').textContent=o.titulo;one('#modal-oferta-categoria').textContent=o.tipo_nombre;one('#modal-oferta-meta').innerHTML='<span>'+escapeHtml(o.nivel_nombre)+'</span><span>'+escapeHtml(o.modalidad_nombre)+'</span><span>'+escapeHtml(o.estado_nombre)+'</span>';one('#modal-oferta-resumen').textContent=o.resumen||'';one('#modal-oferta-descripcion').textContent=o.descripcion||'La información detallada se encuentra en preparación.';one('#modal-oferta-video').innerHTML=o.youtube_codigo?'<div class="ratio ratio-16x9 mt-4"><iframe src="https://www.youtube-nocookie.com/embed/'+escapeHtml(o.youtube_codigo)+'" title="Video de la oferta" loading="lazy" allowfullscreen></iframe></div>':'';var facts=[];if(o.titulo_otorgado)facts.push(['Título otorgado',o.titulo_otorgado]);if(o.perfil_egresado)facts.push(['A quién está dirigido',o.perfil_egresado]);if(o.unidad_academica_nombre)facts.push(['Unidad académica',o.unidad_academica_nombre]);if(o.duracion_valor)facts.push(['Duración',o.duracion_valor+' '+(o.duracion_unidad||'')]);if(o.convocatoria_estado)facts.push(['Convocatoria',o.convocatoria_estado+(o.inscripcion_hasta?' · hasta '+o.inscripcion_hasta:'')]);one('#modal-oferta-datos').innerHTML=facts.map(function(f){return '<dt class="col-sm-4">'+escapeHtml(f[0])+'</dt><dd class="col-sm-8">'+escapeHtml(f[1])+'</dd>'}).join('');status.textContent='';status.className='d-none';detail.classList.remove('d-none')}catch(error){status.className='alert alert-danger';status.textContent='No pudimos cargar el detalle de la oferta. Intentá nuevamente.'}}
  async function sendOfferQuestion(form){var result=form.querySelector('#offer-question-result')||one('#offer-question-result'),button=form.querySelector('button[type="submit"]'),field=form.querySelector('[name="oferta_id"]'),offerId=Number(field?field.value:currentOfferId)||currentOfferId;if(!offerId)return;if(result){result.className='col-12 text-secondary';result.textContent='Enviando consulta…'}if(button)button.disabled=true;var data=new FormData(form);data.delete('oferta_id');data.append('csrf_token',csrf());try{var response=await fetch('../api/ofertas-academicas/'+offerId+'/consultas',{method:'POST',body:data,headers:{Accept:'application/json'}});var json=await response.json();if(!response.ok||!json.ok)throw json;if(result){result.className='col-12 alert alert-success';result.textContent=json.message}form.reset();if(field)field.value=offerId}catch(error){if(result){result.className='col-12 alert alert-danger';result.textContent=error.message||'No pudimos enviar la consulta. Revisá los datos e intentá nuevamente.'}}finally{refreshCaptcha(form);if(button)button.disabled=false}}
  document.addEventListener('submit',function(e){if(e.target.matches('#offer-question-form,#offer-page-question')){e.preventDefault();sendOfferQuestion(e.target)}});
  document.addEventListener('click',function(e){if(e.target.closest('#offer-question-open'))showOfferStep(true);if(e.target.closest('#offer-question-cancel'))showOfferStep(false)});
  document.addEventListener('click',function(e){var button=e.target.closest('[data-offer-target]');if(!button)return;var target=document.getElementById(button.dataset.offerTarget);if(!target)return;e.preventDefault();target.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'auto':'smooth',block:'start'});document.querySelectorAll('[data-offer-target]').forEach(function(item){item.classList.toggle('active',item===button)});});
  document.addEventListener('click',function(e){var button=e.target.closest('[data-share-network]');if(button)sharePublication(button.dataset.shareNetwork,button.closest('.isfd-share'))});
  window.addEventListener('scroll',function(){var h=one('#isfd-header');if(h)h.classList.toggle('is-scrolled',window.scrollY>80)},{passive:true});
  // Se crea el iframe al solicitar el saludo y se retira al cerrar para detener el audio.
  document.addEventListener('click', function(event) {
    var button = event.target.closest('[data-institutional-step]');
    if (!button) return;
    var gallery = button.closest('[data-institutional-videos]'), items;
    try { items = JSON.parse(gallery.dataset.institutionalVideos); } catch (error) { return; }
    if (!Array.isArray(items) || !items.length) return;
    var index = (Number(gallery.dataset.index) + Number(button.dataset.institutionalStep) + items.length) % items.length;
    var item = items[index], url;
    try { url = new URL(item.url); } catch (error) { return; }
    var allowed = (url.origin === 'https://www.youtube-nocookie.com' && /^\/embed\/[A-Za-z0-9_-]{11}$/.test(url.pathname))
      || (url.origin === 'https://app.heygen.com' && /^\/embeds\/[a-f0-9]{32}$/i.test(url.pathname))
      || (url.origin === 'https://player.vimeo.com' && /^\/video\/\d+$/.test(url.pathname));
    if (!allowed) return;
    var frame = document.createElement('iframe');
    frame.src = url.href;
    frame.title = item.titulo;
    frame.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
    frame.allowFullscreen = true;
    // Reemplazar, no ocultar: el reproductor anterior deja de emitir audio.
    gallery.querySelector('[data-institutional-player]').replaceChildren(frame);
    gallery.querySelector('[data-institutional-title]').textContent = item.titulo;
    gallery.querySelector('[data-institutional-external]').href = url.href;
    gallery.querySelector('[data-institutional-counter]').textContent = (index + 1) + ' / ' + items.length;
    gallery.dataset.index = String(index);
  });
  document.addEventListener('click', function(event) {
    var button = event.target.closest('[data-authority-video]');
    if (!button || !window.bootstrap) return;
    var video;
    try { video = new URL(button.dataset.authorityVideo); } catch (error) { return; }
    var youtube = video.origin === 'https://www.youtube-nocookie.com' && /^\/embed\/[A-Za-z0-9_-]{11}$/.test(video.pathname);
    var heygen = video.origin === 'https://app.heygen.com' && /^\/embeds\/[a-f0-9]{32}$/i.test(video.pathname);
    var vimeo = video.origin === 'https://player.vimeo.com' && /^\/video\/\d+$/.test(video.pathname);
    if (!youtube && !heygen && !vimeo) return;
    var modal = one('#authority-video-modal'), player = one('#authority-video-player');
    if (!modal || !player) return;
    var title = 'Saludo de ' + button.dataset.authorityName;
    one('#authority-video-title').textContent = title;
    one('#authority-video-external').href = youtube ? 'https://www.youtube.com/watch?v=' + video.pathname.split('/').pop() : video.href;
    one('#authority-video-external').textContent = 'Ver en ' + (youtube ? 'YouTube' : heygen ? 'HeyGen' : 'Vimeo');
    var frame = document.createElement('iframe');
    if (youtube || vimeo) video.searchParams.set('autoplay', '1');
    if (youtube) video.searchParams.set('rel', '0');
    frame.src = video.href;
    frame.title = title;
    frame.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
    frame.allowFullscreen = true;
    player.replaceChildren(frame);
    bootstrap.Modal.getOrCreateInstance(modal).show();
  });
  document.addEventListener('hidden.bs.modal', function(event) {
    if (event.target.id === 'authority-video-modal') {
      var player = one('#authority-video-player');
      if (player) player.replaceChildren();
    }
  });
  var observer=new MutationObserver(function(){init()});observer.observe(document.documentElement,{childList:true,subtree:true});document.addEventListener('DOMContentLoaded',init);
}());
