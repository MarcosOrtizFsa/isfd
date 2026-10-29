<?php
declare(strict_types=1);
require __DIR__.'/../lib/app.php';
require_once __DIR__.'/../lib/autoridades.php';
function check_authority(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
foreach (['https://youtu.be/abcdefghijk','https://www.youtube.com/shorts/abcdefghijk','https://www.youtube.com/watch?list=1&v=abcdefghijk','abcdefghijk'] as $url) check_authority(autoridad_youtube($url)==='abcdefghijk','URL válida rechazada.');
foreach (['https://evil.example/youtube.com/watch?v=abcdefghijk','javascript:alert(1)','https://youtube.com/watch?v=bad'] as $url) {
    try { autoridad_youtube($url); throw new RuntimeException('URL inválida aceptada.'); } catch (InvalidArgumentException $error) {}
}
$db=app_db();
// Tablas temporales de esta conexión: no se alteran fichas ni historial reales.
$schema=file_get_contents(__DIR__.'/../database/migrations/015_autoridades_up.sql');
$schema=str_replace('CREATE TABLE IF NOT EXISTS','CREATE TEMPORARY TABLE',$schema);
// MariaDB no permite FK en tablas temporales; las columnas y los índices se conservan.
$schema=preg_replace('/^\s*CONSTRAINT .*$/m','',$schema);
$schema=preg_replace('/,\s*\)/',"\n)",$schema);
foreach(explode(';',$schema) as $statement) if(trim($statement)!=='') $db->execute($statement);
$db->execute("ALTER TABLE institucional_autoridades ADD COLUMN video_url VARCHAR(500) NOT NULL DEFAULT ''");
check_authority(autoridad_video('https://app.heygen.com/embeds/9a6414515fd245c388ca01c688afff88')==='https://app.heygen.com/embeds/9a6414515fd245c388ca01c688afff88','HeyGen rechazado.');
check_authority(autoridad_video('https://vimeo.com/123456')==='https://player.vimeo.com/video/123456','Vimeo rechazado.');
foreach (['https://app.heygen.com.evil.example/embeds/9a6414515fd245c388ca01c688afff88','https://example.com/video','javascript:alert(1)'] as $badVideo) {
    try { autoridad_video($badVideo); throw new RuntimeException('Proveedor inválido aceptado.'); } catch (InvalidArgumentException $error) {}
}
$data=autoridad_validar(['nombre'=>'Ana','apellido'=>'Pérez','cargo'=>'Directora','profesion'=>'Profesora','descripcion'=>'Presentación','anio'=>'2026','orden'=>'1','youtube'=>'https://youtu.be/abcdefghijk','visible'=>'1']);
$id=autoridad_guardar($db,0,0,$data,'../uploads/autoridades/test.jpg',false,1,'Alta de prueba');
$data['visible']=0;
$data['video_url']=autoridad_video('https://app.heygen.com/embeds/9a6414515fd245c388ca01c688afff88');
$data['youtube_codigo']='';
autoridad_guardar($db,$id,1,$data,null,true,1,'Fin del período');
$current=$db->one('SELECT * FROM institucional_autoridades WHERE id=?','i',[$id]);
check_authority((int)$current['revision']===2 && (int)$current['visible']===0 && $current['imagen_url']==='','No se actualizó la ficha.');
check_authority($current['video_url']===$data['video_url'],'No se guardó el video HeyGen.');
$history=$db->all('SELECT * FROM institucional_autoridades_historial WHERE autoridad_id=? ORDER BY revision','i',[$id]);
check_authority(count($history)===2,'Faltan revisiones.');
$first=json_decode($history[0]['datos_json'],true);
check_authority((int)$first['visible']===1 && $first['imagen_url']==='../uploads/autoridades/test.jpg','Se perdió la foto histórica.');
try { autoridad_guardar($db,$id,1,$data,null,false,1,'Conflicto'); throw new RuntimeException('Se aceptó una edición obsoleta.'); } catch (UnexpectedValueException $error) {}
check_authority((int)$db->one('SELECT COUNT(*) n FROM institucional_autoridades_historial')['n']===2,'El conflicto creó una revisión.');
echo "OK: YouTube, alta, edición, visibilidad, foto histórica y concurrencia. Sin cambios en las tablas reales.\n";
