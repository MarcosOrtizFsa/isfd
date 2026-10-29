<?php
declare(strict_types=1);
include __DIR__.'/../../../../lib/template.inc';
require_once __DIR__.'/../../../../lib/app.php';
require_once __DIR__.'/ofertas_repositorio.php';
include __DIR__.'/datos_institucionales.php';

function oferta_publica_escape($value):string{return htmlspecialchars((string)$value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
$slug=preg_replace('/[^a-z0-9-]/','',strtolower((string)($_POST['slug']??$_GET['slug']??'')));
$db=app_db();$offer=$slug!==''?(new OfertasRepositorio($db))->buscarPorSlug($slug):null;
$template=new _template(__DIR__.'/../templates');
$template->set_file(['page'=>'oferta_leer.html','plan'=>'oferta_plan_item.html']);
$template->set_var(['PLAN_ESTUDIOS'=>'']);

if($offer){
    $plan=$db->all('SELECT id,titulo,contenido FROM oferta_plan_estudios WHERE oferta_id=? ORDER BY orden,id','i',[(int)$offer['id']]);
    foreach($plan as $index=>$section){$template->set_var(['PLAN_ID'=>'plan-'.(int)$offer['id'].'-'.(int)$section['id'],'PLAN_TITULO'=>oferta_publica_escape($section['titulo']),'PLAN_CONTENIDO'=>nl2br(oferta_publica_escape($section['contenido'])),'PLAN_ABIERTO'=>$index===0?'show':'','PLAN_EXPANDIDO'=>$index===0?'true':'false','PLAN_COLAPSADO'=>$index===0?'':'collapsed']);$template->parse('PLAN_ESTUDIOS','plan',true);}
    $facts='';
    foreach([['Duración',trim((string)($offer['duracion_valor']??'').' '.(string)($offer['duracion_unidad']??''))],['Modalidad',$offer['modalidad_nombre']??''],['Título otorgado',$offer['titulo_otorgado']??''],['Unidad académica',$offer['unidad_academica_nombre']??'']] as [$label,$value])if(trim((string)$value)!=='')$facts.='<div><i class="bi bi-check2-circle"></i><span><small>'.oferta_publica_escape($label).'</small><strong>'.oferta_publica_escape($value).'</strong></span></div>';
    $video=preg_match('/^[A-Za-z0-9_-]{11}$/',(string)($offer['youtube_codigo']??''))?'<div class="ratio ratio-16x9 offer-page-video"><iframe src="https://www.youtube-nocookie.com/embed/'.oferta_publica_escape($offer['youtube_codigo']).'" title="Video de la oferta" loading="lazy" allowfullscreen></iframe></div>':'';
    $template->set_var(['PAGE_CLASS'=>'','STATE_CLASS'=>'d-none','STATE_MESSAGE'=>'','OFFER_ID'=>(int)$offer['id'],'CATEGORIA'=>oferta_publica_escape($offer['tipo_nombre']??'Oferta académica'),'TITULO'=>oferta_publica_escape($offer['titulo']),'RESUMEN'=>oferta_publica_escape($offer['resumen']),'DESCRIPCION'=>nl2br(oferta_publica_escape($offer['descripcion']?:'La información detallada se encuentra en preparación.')),'DIRIGIDO'=>nl2br(oferta_publica_escape($offer['perfil_egresado']?:'Consultar con la institución.')),'REQUISITOS'=>nl2br(oferta_publica_escape($offer['requisitos_ingreso']?:'Consultar con la institución.')),'IMAGEN'=>oferta_publica_escape($offer['imagen_url']??''),'IMAGEN_CLASS'=>empty($offer['imagen_url'])?'d-none':'','VIDEO'=>$video,'DATOS'=>$facts,'PLAN_CLASS'=>$plan?'':'d-none','PLAN_LINK_CLASS'=>$plan?'':'d-none']);
}else{$template->set_var(['PAGE_CLASS'=>'d-none','STATE_CLASS'=>'','STATE_MESSAGE'=>'La oferta solicitada no está disponible.','OFFER_ID'=>0,'CATEGORIA'=>'','TITULO'=>'','RESUMEN'=>'','DESCRIPCION'=>'','DIRIGIDO'=>'','REQUISITOS'=>'','IMAGEN'=>'','IMAGEN_CLASS'=>'d-none','VIDEO'=>'','DATOS'=>'','PLAN_CLASS'=>'d-none','PLAN_LINK_CLASS'=>'d-none']);}
$template->set_var(['INSTITUCION_CORTO'=>oferta_publica_escape($institucion['nombre_corto']),'INSTITUCION_SIGLA'=>oferta_publica_escape($institucion['sigla']),'TELEFONO'=>oferta_publica_escape($institucion['telefono']),'EMAIL'=>oferta_publica_escape($institucion['email']),'CAMPUS_URL'=>oferta_publica_escape($institucion['campus_url']),'PREINSCRIPCION_URL'=>oferta_publica_escape($institucion['preinscripcion_url']),'ANIO'=>date('Y')]);
$template->set_var(portal_institucional_variables($institucion));
$template->pparse('OUT','page');
?>
