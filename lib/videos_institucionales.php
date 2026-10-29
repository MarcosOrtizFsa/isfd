<?php
declare(strict_types=1);
require_once __DIR__.'/autoridades.php';

/** Normaliza la lista antes de versionarla; nunca se almacena HTML de reproductores. */
function institucional_videos_validar(string $json): string
{
    $items=json_decode($json,true);
    if (!is_array(json_decode($json)) || !is_array($items) || !array_is_list($items) || count($items)>30) throw new InvalidArgumentException('Ingresá una lista de hasta 30 videos.');
    $result=[];
    foreach($items as $item) {
        if(!is_array($item) || !is_string($item['titulo']??null) || !is_string($item['url']??null)) throw new InvalidArgumentException('Revisá el título y enlace de cada video.');
        $title=trim($item['titulo']);
        if($title==='' || mb_strlen($title)>160) throw new InvalidArgumentException('Cada video necesita un título de hasta 160 caracteres.');
        $url=autoridad_video($item['url']);
        if($url==='') throw new InvalidArgumentException('Completá el enlace de cada video.');
        $result[]=['titulo'=>$title,'url'=>$url,'visible'=>!empty($item['visible'])];
    }
    return json_encode($result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
}

function institucional_videos_publicos(string $json): array
{
    try { $items=json_decode(institucional_videos_validar($json),true); }
    catch (InvalidArgumentException $error) { return []; }
    return array_values(array_filter($items,static fn($item)=>$item['visible']));
}
