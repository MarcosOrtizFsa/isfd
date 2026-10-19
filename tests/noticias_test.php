<?php
declare(strict_types=1);
require __DIR__ . '/../lib/app.php';
require_once __DIR__ . '/../sistema/modulos/home/php/neuria_cliente.php';

function noticias_comprobar(bool $condition,string $message):void
{
    if(!$condition){fwrite(STDERR,"FALLO: {$message}\n");exit(1);}
    echo "OK: {$message}\n";
}

$db=app_db();
$tables=$db->all("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME IN ('noticias','noticia_archivos')",'s',[BD]);
noticias_comprobar(count($tables)===2,'tablas editoriales disponibles');
$module=$db->one("SELECT id_system_01 FROM system_01_modulos WHERE system_01_path_home='noticias' AND system_01_onoff='on' AND system_01_estado=1");
noticias_comprobar($module!==null,'módulo Noticias activo');
$duplicates=$db->one('SELECT COUNT(*) total FROM (SELECT neuria_id FROM noticias WHERE neuria_id IS NOT NULL GROUP BY neuria_id HAVING COUNT(*)>1) x');
noticias_comprobar((int)$duplicates['total']===0,'ofertas de Neuria sin duplicados');
$neuria=(new NeuriaCliente())->listar(1);
noticias_comprobar($neuria['ok']&&count($neuria['items'])===1,'Neuria entrega ofertas al módulo');
$temporary=$db->one("SELECT COUNT(*) total FROM noticias WHERE titulo LIKE 'Noticia temporal%' OR titulo='Noticia local temporal'");
noticias_comprobar((int)$temporary['total']===0,'pruebas sin noticias residuales');
