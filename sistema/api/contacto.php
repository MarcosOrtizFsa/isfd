<?php
declare(strict_types=1);
require __DIR__ . '/../../lib/app.php';
require __DIR__ . '/consultas_soporte.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') app_json(['ok'=>false,'message'=>'Método no permitido.'],405);
consulta_recibir(null,'contacto');

