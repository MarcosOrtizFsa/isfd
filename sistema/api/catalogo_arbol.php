<?php
declare(strict_types=1);
require __DIR__ . '/../../lib/app.php';
require __DIR__ . '/../modulos/home/php/ofertas_repositorio.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    app_json(['ok'=>false,'message'=>'Método no permitido.'],405);
}
app_json(['ok'=>true,'data'=>(new OfertasRepositorio(app_db()))->arbol()]);
