<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
require __DIR__.'/../lib/app.php';
require_once __DIR__.'/../lib/autoridades.php';
$db=app_db();
$sql=preg_replace('/^--.*$/m','',file_get_contents(__DIR__.'/migrations/015_autoridades_up.sql'));
foreach(explode(';',$sql) as $statement) if(trim($statement)!=='') $db->execute($statement);
$db->transaction(function(AppDatabase $db): void {
    // El bloqueo y la existencia de registros impiden duplicar la migración inicial.
    $section=$db->one("SELECT id,version_actual FROM institucional_secciones WHERE clave='autoridades' FOR UPDATE");
    if ((int)$db->one('SELECT COUNT(*) n FROM institucional_autoridades')['n']>0) return;
    $source=$db->one("SELECT valor FROM institucional_valores WHERE version_id=? AND campo='autoridades_texto'",'i',[(int)($section['version_actual']??0)]);
    $order=0;
    foreach(explode("\n",$source['valor']??'') as $line) {
        if(!str_contains($line,'|')) continue;
        [$cargo,$full]=array_map('trim',explode('|',$line,2));
        $profession='';
        if(preg_match('/^(Prof\.|Lic\.|Dr\.|Dra\.|Ing\.)\s+/u',$full,$match)) { $profession=$match[1]; $full=trim(substr($full,strlen($match[0]))); }
        $parts=preg_split('/\s+/u',$full);
        $apellido=count($parts)>1?array_pop($parts):'';
        $nombre=implode(' ',$parts);
        $id=$db->insert('INSERT INTO institucional_autoridades (nombre,apellido,cargo,profesion,descripcion,anio,visible,orden) VALUES (?,?,?,?,?,?,1,?)','sssssii',[$nombre,$apellido,$cargo,$profession,'',(int)date('Y'),$order++]);
        $row=$db->one('SELECT * FROM institucional_autoridades WHERE id=?','i',[$id]);
        $db->insert('INSERT INTO institucional_autoridades_historial (autoridad_id,revision,motivo,datos_json) VALUES (?,1,?,?)','iss',[$id,'Migración del listado institucional. Revisar separación de nombres y apellidos.',json_encode($row,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);
    }
});
echo 'Autoridades migradas: '.$db->one('SELECT COUNT(*) n FROM institucional_autoridades')['n'].PHP_EOL;
