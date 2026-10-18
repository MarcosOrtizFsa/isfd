<?php
declare(strict_types=1);
require __DIR__ . '/../../lib/app.php';
require __DIR__ . '/../modulos/home/php/ofertas_repositorio.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') app_json(['ok'=>false,'message'=>'Método no permitido.'],405);
$allowed=['q','nivel','categoria','tipo','modalidad','turno','area','estado','destacada','page','per_page'];$filters=[];
foreach($allowed as $key)$filters[$key]=trim((string)($_GET[$key]??''));
$result=(new OfertasRepositorio(app_db()))->listar($filters);
app_json(['ok'=>true,'data'=>$result['items'],'meta'=>$result['meta']]);

