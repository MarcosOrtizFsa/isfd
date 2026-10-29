<?php
declare(strict_types=1);
/** Exporta únicamente las incorporaciones 011–017 y sus dependencias de contenido. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../lib/app.php';
$db=app_db();
function sql_value(mixed $value): string {
    if ($value===null) return 'NULL';
    // Hex UTF-8 evita problemas con comillas, saltos de línea y modos SQL del hosting.
    // COLLATE explícito evita #1267 al comparar con columnas de otros cotejamientos.
    return $value==='' ? "(_utf8mb4'' COLLATE utf8mb4_unicode_ci)" : "(CONVERT(X'".bin2hex((string)$value)."' USING utf8mb4) COLLATE utf8mb4_unicode_ci)";
}
function insert_select(string $table,array $row,string $condition,array $expressions=[]): string {
    $values=[];
    foreach($row as $key=>$value) $values[]=$expressions[$key]??sql_value($value);
    return 'INSERT INTO `'.$table.'` (`'.implode('`,`',array_keys($row)).'`) SELECT '.implode(',',$values).' WHERE '.$condition.";\n";
}
$sql="-- ISFD: actualización acumulada 011 a 017, con contenido local.\n";
$sql.='-- Generado: '.date(DATE_ATOM)."\n";
$sql.="-- Seleccionar la BD de producción en phpMyAdmin antes de importar.\n-- Hacer respaldo previo. No contiene DROP, DEFINER, usuarios ni credenciales.\n-- Requiere las tablas base del portal ya instaladas.\n-- No reemplaza contenidos ya existentes en los grupos institucionales.\n-- Las rutas de imágenes requieren copiar también uploads/autoridades y uploads/ofertas.\nSET NAMES utf8mb4;\n";
$sql=str_replace('SET NAMES utf8mb4;', 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;', $sql);
$changes=[
    ['ofertas','youtube_codigo','VARCHAR(20) NULL'],
    ['portal_consultas','atendida_at','DATETIME NULL'],
    ['portal_consultas','respondida_at','DATETIME NULL'],
    ['portal_consultas','gestion_notas','TEXT NULL'],
    ['portal_consultas','gestion_usuario_id','INT UNSIGNED NULL'],
];
function add_column_sql(string $table,string $column,string $definition): string {
    return "SET @isfd_ddl = IF(EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='".$table."' AND COLUMN_NAME='".$column."'), 'SELECT 1', 'ALTER TABLE `".$table.'` ADD COLUMN `'.$column.'` '.$definition."');\nPREPARE isfd_stmt FROM @isfd_ddl;\nEXECUTE isfd_stmt;\nDEALLOCATE PREPARE isfd_stmt;\n";
}
foreach($changes as [$table,$column,$definition]) $sql.=add_column_sql($table,$column,$definition);
$tables=['oferta_plan_estudios','institucional_secciones','institucional_versiones','institucional_valores','institucional_autoridades','institucional_autoridades_historial'];
foreach($tables as $table) {
    $create=$db->one('SHOW CREATE TABLE `'.$table.'`')['Create Table'];
    $sql.="\n".str_replace('CREATE TABLE','CREATE TABLE IF NOT EXISTS',$create).";\n";
}
$sql.=add_column_sql('institucional_autoridades','video_url',"VARCHAR(500) NOT NULL DEFAULT \'\'");
$sql.="\nSTART TRANSACTION;\n";
$sql.="INSERT INTO system_01_modulos (system_01_modulo,system_01_tipo,system_01_path_home,system_01_onoff,system_01_orden,system_01_estado) SELECT 'Contactos','acces','contactos','on',6,1 WHERE NOT EXISTS (SELECT 1 FROM system_01_modulos WHERE system_01_path_home='contactos');\n";
// Los grupos se copian completos para conservar IDs y relaciones sin mezclar historiales.
$sql.="SET @isfd_institucion = ((SELECT COUNT(*) FROM institucional_versiones)=0 AND (SELECT COUNT(*) FROM institucional_valores)=0 AND NOT EXISTS(SELECT 1 FROM institucional_secciones WHERE version_actual IS NOT NULL));\n";
foreach($db->all('SELECT * FROM institucional_secciones ORDER BY id') as $section) {
    // La sección puede haber sido creada por migraciones previas; se relaciona por clave.
    $sql.=insert_select('institucional_secciones',['clave'=>$section['clave'],'version_actual'=>null],'NOT EXISTS(SELECT 1 FROM institucional_secciones WHERE clave='.sql_value($section['clave']).')');
}
$sections=array_column($db->all('SELECT id,clave FROM institucional_secciones'),'clave','id');
foreach($db->all('SELECT * FROM institucional_versiones ORDER BY id') as $row) {
    $row['usuario_id']=null; // IDs de usuarios locales no se atribuyen a usuarios de producción.
    $sql.=insert_select('institucional_versiones',$row,'@isfd_institucion=1',['seccion_id'=>'(SELECT id FROM institucional_secciones WHERE clave='.sql_value($sections[$row['seccion_id']]).')']);
}
foreach($db->all('SELECT * FROM institucional_valores ORDER BY version_id,campo') as $row) $sql.=insert_select('institucional_valores',$row,'@isfd_institucion=1');
foreach($db->all('SELECT clave,version_actual FROM institucional_secciones') as $row) $sql.='UPDATE institucional_secciones SET version_actual='.sql_value($row['version_actual']).' WHERE clave='.sql_value($row['clave'])." AND @isfd_institucion=1;\n";
$sql.="SET @isfd_autoridades=((SELECT COUNT(*) FROM institucional_autoridades)=0 AND (SELECT COUNT(*) FROM institucional_autoridades_historial)=0);\n";
foreach(['institucional_autoridades','institucional_autoridades_historial'] as $table) {
    foreach($db->all('SELECT * FROM `'.$table.'` ORDER BY id') as $row) {
        if(array_key_exists('usuario_id',$row)) $row['usuario_id']=null;
        $sql.=insert_select($table,$row,'@isfd_autoridades=1');
    }
}
// Resuelve las ofertas por slug o título, no por IDs locales.
$reference=json_decode(file_get_contents(__DIR__.'/seeds/ofertas_referencia_fac.json'),true,512,JSON_THROW_ON_ERROR);
$titles=[];
foreach($reference as $item) { $titles[]=$item['title']; if($item['match']) $titles[]=$item['match']; }
$titles=array_values(array_unique($titles));
$planOffers=$db->all('SELECT o.* FROM ofertas o WHERE o.deleted_at IS NULL AND (EXISTS(SELECT 1 FROM oferta_plan_estudios p WHERE p.oferta_id=o.id) OR o.titulo IN ('.implode(',',array_fill(0,count($titles),'?')).')) ORDER BY o.id',str_repeat('s',count($titles)),$titles);
foreach($planOffers as $offer) {
    $localId=$offer['id'];
    $category=$db->one('SELECT slug FROM catalogo_categorias WHERE id=?','i',[(int)$offer['categoria_id']]);
    $modality=$db->one('SELECT codigo FROM modalidades WHERE id=?','i',[(int)$offer['modalidad_id']]);
    $unit=$offer['unidad_academica_id']?$db->one('SELECT nombre FROM unidades_academicas WHERE id=?','i',[(int)$offer['unidad_academica_id']]):null;
    $match='(slug='.sql_value($offer['slug']).' OR titulo='.sql_value($offer['titulo']).') AND deleted_at IS NULL';
    unset($offer['id']);
    $sql.=insert_select('ofertas',$offer,'NOT EXISTS(SELECT 1 FROM ofertas WHERE '.$match.')',[
        'categoria_id'=>'(SELECT id FROM catalogo_categorias WHERE slug='.sql_value($category['slug']).' LIMIT 1)',
        'modalidad_id'=>'(SELECT id FROM modalidades WHERE codigo='.sql_value($modality['codigo']).' LIMIT 1)',
        'unidad_academica_id'=>$unit?'(SELECT id FROM unidades_academicas WHERE nombre='.sql_value($unit['nombre']).' LIMIT 1)':'NULL',
    ]);
    $sql.='SET @isfd_oferta=(SELECT id FROM ofertas WHERE '.$match.' ORDER BY id LIMIT 1);'."\n";
    $sql.="SET @isfd_plan=(SELECT COUNT(*)=0 FROM oferta_plan_estudios WHERE oferta_id=@isfd_oferta);\n";
    foreach($db->all('SELECT * FROM oferta_plan_estudios WHERE oferta_id=? ORDER BY orden,id','i',[(int)$localId]) as $row) {
        unset($row['id']);
        $sql.=insert_select('oferta_plan_estudios',$row,'@isfd_plan=1 AND @isfd_oferta IS NOT NULL',['oferta_id'=>'@isfd_oferta']);
    }
}
$sql.="COMMIT;\nSELECT 'Actualización finalizada' AS resultado, @isfd_institucion AS contenido_institucional_importado, @isfd_autoridades AS autoridades_importadas;\n";
$output=__DIR__.'/ACTUALIZACION_PRODUCCION_011_017.sql';
if(file_put_contents($output,$sql)===false) throw new RuntimeException('No se pudo escribir el SQL.');
echo $output.PHP_EOL.'Tamaño: '.strlen($sql).' bytes'.PHP_EOL;
