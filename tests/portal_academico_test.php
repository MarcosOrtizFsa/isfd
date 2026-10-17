<?php
declare(strict_types=1);
require __DIR__ . '/../lib/app.php';
require __DIR__ . '/../sistema/modulos/home/php/ofertas_repositorio.php';

function comprobar(bool $condition, string $message): void
{
    if (!$condition) { fwrite(STDERR, "FALLO: {$message}\n"); exit(1); }
    echo "OK: {$message}\n";
}

$repository = new OfertasRepositorio(app_db());
$all = $repository->listar(['page'=>1,'per_page'=>20]);
comprobar($all['meta']['total'] >= 3, 'lista ofertas publicadas');

$superior = $repository->listar(['nivel'=>'superior','page'=>1,'per_page'=>20]);
comprobar(count($superior['items']) === 1 && $superior['items'][0]['slug'] === 'profesorado-en-ingles', 'filtro por nivel');

$search = $repository->listar(['q'=>'Humanidades','page'=>1,'per_page'=>20]);
comprobar(count($search['items']) === 1, 'busqueda de texto');

$detail = $repository->buscarPorSlug('profesorado-en-ingles');
comprobar($detail !== null && $detail['nivel'] === 'superior', 'detalle por slug');

$tables = app_db()->all("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME IN ('portal_consultas','portal_bandeja_salida')", 's', [BD]);
comprobar(count($tables) === 2, 'persistencia de consultas y bandeja disponible');

