<?php
declare(strict_types=1);
require __DIR__ . '/../../lib/app.php';
require __DIR__ . '/consultas_soporte.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') app_json(['ok'=>false,'message'=>'Método no permitido.'],405);
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
consulta_recibir($id?:null,'oferta');

