<?php
declare(strict_types=1);
require __DIR__.'/../sistema/modulos/noticias/php/neuria_coordinador.php';
function pilot_check(bool $ok,string $message): void {
    if (!$ok) throw new RuntimeException($message);
    echo 'OK: '.$message.PHP_EOL;
}
$db=app_db();
// Tablas temporales: no cambia noticias, solicitudes ni cursores reales.
foreach (['noticias','neuria_requests','neuria_sincronizaciones'] as $table) {
    $schema=$db->one('SHOW CREATE TABLE '.$table)['Create Table'];
    $schema=preg_replace('/^CREATE TABLE/','CREATE TEMPORARY TABLE',$schema);
    $schema=preg_replace('/,\n\s*(?:CONSTRAINT|FULLTEXT KEY)[^\n]+/','',$schema);
    $db->execute($schema);
}
$db->execute("INSERT INTO neuria_sincronizaciones (flujo) VALUES ('contenidos'),('cambios')");
$client=new NeuriaV1Cliente(['url'=>'http://127.0.0.1:18087','token'=>'autopilot','timeout'=>2,'limit'=>1,'max_retries'=>0,'configurada'=>true]);
$service=new NeuriaCoordinador($db,$client);
$first=$service->sincronizar();
pilot_check($first['ok'] && $first['solicitudes']['consultadas']===0 && $first['importadas']===2,'Importa dos páginas de Piloto Automático sin solicitudes locales');
$rows=$db->all('SELECT * FROM noticias ORDER BY id');
foreach ($rows as $row) {
    pilot_check($row['estado']==='pendiente' && $row['estado_revision']==='pendiente' && !$row['neuria_request_id'],'Noticia independiente queda pendiente de revisión');
}
$id=(int)$rows[0]['id'];
$db->execute("UPDATE noticias SET titulo='Edición del instituto',estado='activa',estado_revision='revisada' WHERE id=?",'i',[$id]);
$db->execute("UPDATE neuria_sincronizaciones SET cursor_valor=NULL WHERE flujo='contenidos'");
$again=$service->sincronizar();
$row=$db->one('SELECT * FROM noticias WHERE id=?','i',[$id]);
pilot_check($again['ok'] && $again['importadas']===0 && (int)$db->one('SELECT COUNT(*) n FROM noticias')['n']===2,'Relectura sin duplicados');
pilot_check($row['titulo']==='Edición del instituto' && $row['estado']==='activa' && $row['estado_revision']==='revisada','Conserva edición y decisión editorial local');
$empty=$service->sincronizar();
pilot_check($empty['ok'] && $empty['contenidos']['recibidos']===0,'Consulta incremental vacía conserva funcionamiento');
$lock=fopen(sys_get_temp_dir().DIRECTORY_SEPARATOR.'isfd_neuria_sync.lock','c');
flock($lock,LOCK_EX);
try {
    $blocked=false;
    try { $service->sincronizar(); } catch (RuntimeException) { $blocked=true; }
    pilot_check($blocked,'Impide sincronizaciones simultáneas');
} finally { flock($lock,LOCK_UN); fclose($lock); }
