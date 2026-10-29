<?php
declare(strict_types=1);
require __DIR__.'/../lib/videos_institucionales.php';
function check_videos(bool $value): void { if(!$value) throw new RuntimeException('Falló la verificación de videos institucionales.'); }
check_videos(institucional_videos_publicos('[]')===[]);
$items=[
    ['titulo'=>'Primero','url'=>'https://youtu.be/abcdefghijk','visible'=>true],
    ['titulo'=>'Oculto','url'=>'https://vimeo.com/123456','visible'=>false],
    ['titulo'=>'Tercero','url'=>'https://app.heygen.com/embeds/9a6414515fd245c388ca01c688afff88','visible'=>true],
];
$public=institucional_videos_publicos(json_encode($items));
check_videos(count($public)===2 && $public[0]['titulo']==='Primero' && $public[1]['titulo']==='Tercero');
check_videos($public[0]['url']==='https://www.youtube-nocookie.com/embed/abcdefghijk');
foreach(['bad','{}','[{"titulo":"","url":"https://vimeo.com/123"}]','[{"titulo":"Test","url":"javascript:alert(1)"}]'] as $invalid) {
    try { institucional_videos_validar($invalid); throw new RuntimeException('Se aceptó una lista inválida.'); } catch(InvalidArgumentException $error) {}
}
echo "OK: lista vacía, proveedores, orden, visibilidad y validación.\n";
