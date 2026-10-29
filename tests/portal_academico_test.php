<?php
declare(strict_types=1);
require __DIR__ . '/../lib/app.php';
require __DIR__ . '/../sistema/modulos/home/php/ofertas_repositorio.php';

function comprobar(bool $condition, string $message): void
{
    if (!$condition) { fwrite(STDERR, "FALLO: {$message}\n"); exit(1); }
    echo "OK: {$message}\n";
}

function rechaza(callable $operation, string $message): void
{
    try { $operation(); }
    catch (mysqli_sql_exception $error) { echo "OK: {$message}\n"; return; }
    fwrite(STDERR, "FALLO: {$message}\n"); exit(1);
}

$db = app_db();
$repository = new OfertasRepositorio($db);
$all = $repository->listar(['page'=>1,'per_page'=>20]);
comprobar($all['meta']['total'] === 7, 'la vista expone las siete ofertas publicadas');
comprobar($all['meta']['per_page'] === 20, 'paginación explícita');
$slugs=array_column($all['items'],'slug');
comprobar(count(array_unique(array_filter($slugs)))===count($all['items']),'cada oferta tiene un slug único');

$superior = $repository->listar(['nivel'=>'superior','page'=>1,'per_page'=>20]);
comprobar(count($superior['items']) === 5, 'ofertas demostrativas de Educación Superior');

$basica = $repository->listar(['nivel'=>'basica','page'=>1,'per_page'=>20]);
comprobar(count($basica['items']) === 2, 'filtro Educación Básica');

$secundaria = $repository->listar(['categoria'=>'nivel-secundario','page'=>1,'per_page'=>20]);
comprobar(count($secundaria['items']) === 1 && $secundaria['items'][0]['tipo'] === 'ciclo-orientado', 'categoría padre incluye descendientes');

$legacy = $repository->listar(['nivel'=>'primario','page'=>1,'per_page'=>20]);
comprobar(count($legacy['items']) === 1, 'compatibilidad con nivel histórico');

$facets = $repository->listar(['modalidad'=>'presencial','page'=>1,'per_page'=>20]);
comprobar(count($facets['items'])>0&&count(array_filter($facets['items'],fn(array $item):bool=>$item['modalidad_codigo']!=='presencial'))===0,'filtro de modalidad vigente');

$search = $repository->listar(['q'=>'Humanidades','page'=>1,'per_page'=>20]);
comprobar(count($search['items']) === 1, 'busqueda de texto');

$detail = $repository->buscarPorSlug((string)$all['items'][0]['slug']);
comprobar($detail !== null && (int)$detail['id']===(int)$all['items'][0]['id'], 'detalle por slug');

$defaultPage = $repository->listar([]);
comprobar($defaultPage['meta']['per_page'] === 6, 'paginación predeterminada');

$profesoradosId = (int)$db->one("SELECT id FROM catalogo_categorias WHERE slug='profesorados'")['id'];
rechaza(fn() => $db->execute('UPDATE catalogo_categorias SET admite_ofertas=0 WHERE id=?','i',[$profesoradosId]), 'una categoría con ofertas no puede transformarse en contenedora');

$tree = $repository->arbol();
comprobar(count($tree) === 2 && array_sum(array_column($tree, 'ofertas_count')) === 7, 'árbol con conteos publicados');

$tables = $db->all("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME IN ('catalogo_niveles','catalogo_categorias','ofertas','oferta_turno','oferta_area','oferta_convocatorias','oferta_documentos','oferta_plan_estudios','portal_consultas','portal_bandeja_salida')", 's', [BD]);
comprobar(count($tables) === 10, 'estructura completa, multimedia y plan de estudios');
$youtubeColumn = $db->one("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME='ofertas' AND COLUMN_NAME='youtube_codigo'", 's', [BD]);
comprobar($youtubeColumn !== null, 'video de YouTube disponible por oferta');

try {
    $db->transaction(function (AppDatabase $database): void {
        $basicaId = (int)$database->one("SELECT id FROM catalogo_niveles WHERE codigo='basica'")['id'];
        $superiorId = (int)$database->one("SELECT id FROM catalogo_niveles WHERE codigo='superior'")['id'];
        $modalidadId = (int)$database->one("SELECT id FROM modalidades WHERE codigo='presencial'")['id'];
        $stamp = (string)random_int(100000, 999999);

        $database->execute('CALL catalogo_crear_categoria(?,NULL,?,?,0,90)', 'iss', [$basicaId,'test-raiz-'.$stamp,'Test raíz']);
        $root = (int)$database->one('SELECT id FROM catalogo_categorias WHERE slug=?', 's', ['test-raiz-'.$stamp])['id'];
        $database->execute('CALL catalogo_crear_categoria(?,?,?,?,0,10)', 'iiss', [$basicaId,$root,'test-hija-'.$stamp,'Test hija']);
        $child = (int)$database->one('SELECT id FROM catalogo_categorias WHERE slug=?', 's', ['test-hija-'.$stamp])['id'];
        $database->execute('CALL catalogo_crear_categoria(?,?,?,?,0,10)', 'iiss', [$basicaId,$child,'test-nieta-'.$stamp,'Test nieta']);
        $grandchild = (int)$database->one('SELECT id FROM catalogo_categorias WHERE slug=?', 's', ['test-nieta-'.$stamp])['id'];

        rechaza(fn() => $database->execute('CALL catalogo_crear_categoria(?,?,?,?,1,10)', 'iiss', [$basicaId,$grandchild,'test-cuarta-'.$stamp,'Test cuarta']), 'se rechaza profundidad mayor a tres');
        rechaza(fn() => $database->execute('CALL catalogo_mover_categoria(?,?)', 'ii', [$root,$grandchild]), 'se rechazan ciclos');
        rechaza(fn() => $database->execute('CALL catalogo_crear_categoria(?,?,?,?,1,10)', 'iiss', [$superiorId,$root,'test-otro-nivel-'.$stamp,'Test otro nivel']), 'padre e hija deben pertenecer al mismo nivel');
        rechaza(fn() => $database->insert("INSERT INTO ofertas (categoria_id,modalidad_id,slug,titulo) VALUES (?,?,?,'Inválida')", 'iis', [$root,$modalidadId,'test-oferta-'.$stamp]), 'solo las hojas admiten ofertas');

        $database->execute('CALL catalogo_crear_categoria(?,NULL,?,?,0,95)', 'iss', [$basicaId,'test-destino-'.$stamp,'Test destino']);
        $target = (int)$database->one('SELECT id FROM catalogo_categorias WHERE slug=?', 's', ['test-destino-'.$stamp])['id'];
        $database->execute('CALL catalogo_mover_categoria(?,?)', 'ii', [$child,$target]);
        $moved = $database->one('SELECT profundidad,ruta FROM catalogo_categorias WHERE id=?', 'i', [$grandchild]);
        comprobar((int)$moved['profundidad'] === 3 && str_contains($moved['ruta'], '/'.$target.'/'.$child.'/'), 'movimiento recalcula todo el subárbol');

        throw new RuntimeException('ROLLBACK_PRUEBA');
    });
} catch (RuntimeException $error) {
    if ($error->getMessage() !== 'ROLLBACK_PRUEBA') throw $error;
}
comprobar(!$db->one("SELECT id FROM catalogo_categorias WHERE slug LIKE 'test-%' LIMIT 1"), 'las pruebas de integridad no dejan datos residuales');

