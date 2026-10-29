<?php
declare(strict_types=1);
// Ejecutar por CLI: php database/migrar_institucional.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../lib/app.php';
require_once __DIR__.'/../lib/institucional_repositorio.php';
$db = app_db();
$sql = file_get_contents(__DIR__.'/migrations/014_contenido_institucional_up.sql');
foreach (explode(';', preg_replace('/^--.*$/m', '', $sql)) as $statement) {
    if (trim($statement) !== '') $db->execute($statement);
}
$values = require __DIR__.'/seeds/institucional_inicial.php';
$config = $db->one('SELECT * FROM system_08_configuracion ORDER BY id_system_08 LIMIT 1') ?? [];
foreach (['nombre'=>'titulo_site','sigla'=>'titulo_siglas','direccion'=>'direccion','telefono'=>'telefono','email'=>'email','celular'=>'celular','whatsapp'=>'whatsapp','contactos_visibles'=>'contactos_visibles'] as $key=>$column) {
    $value = trim((string)($config['system_08_'.$column]??''));
    if ($value !== '') $values[$key] = $value;
}
// Conserva también el mapa configurado si contiene una URL de inserción válida.
$map = trim((string)($config['system_08_mapsgoogle']??''));
if (preg_match('/src=["\']([^"\']+)["\']/i', $map, $match)) $map = html_entity_decode($match[1],ENT_QUOTES,'UTF-8');
if (filter_var($map,FILTER_VALIDATE_URL) && in_array(strtolower((string)parse_url($map,PHP_URL_HOST)),['www.google.com','maps.google.com','www.google.com.ar'],true) && str_contains((string)parse_url($map,PHP_URL_PATH),'/maps/embed')) $values['mapa_url'] = $map;
// Se conserva la información adicional, omitiendo solo líneas idénticas a los horarios.
$lines = preg_split('/\R/', (string)($values['contactos_visibles']??''));
$schedule = array_map(static fn($v)=>rtrim(trim($v),'.'), ['Mañana: '.$values['manana'],'Tarde: '.$values['tarde'],'Noche: '.$values['noche']]);
$values['contactos_visibles'] = implode("\n", array_filter($lines, static fn($line)=>!in_array(rtrim(trim($line),'.'), $schedule, true)));
$repo = new InstitucionalRepositorio($db);
foreach (institucional_campos() as $key=>[$title,$fields]) {
    $row = $db->one('SELECT version_actual FROM institucional_secciones WHERE clave=?','s',[$key]);
    if (!empty($row['version_actual'])) continue; // Reejecutable sin sobrescribir ediciones.
    $data = [];
    foreach ($fields as $field=>$label) $data[$field] = (string)($values[$field]??'');
    $repo->guardar($key, (int)date('Y'), 'Migración del contenido institucional existente', $data, 0, null);
    echo 'Migrado: '.$title.PHP_EOL;
}
