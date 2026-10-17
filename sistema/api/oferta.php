<?php
declare(strict_types=1);
require __DIR__ . '/../../lib/app.php';
require __DIR__ . '/../modulos/home/php/ofertas_repositorio.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') app_json(['ok'=>false,'message'=>'Método no permitido.'],405);
$slug=preg_replace('/[^a-z0-9-]/','',strtolower((string)($_GET['slug']??'')));
$offer=(new OfertasRepositorio(app_db()))->buscarPorSlug($slug);
if(!$offer)app_json(['ok'=>false,'message'=>'La oferta solicitada no existe.'],404);
app_json(['ok'=>true,'data'=>$offer]);

