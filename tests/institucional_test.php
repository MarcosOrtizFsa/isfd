<?php
declare(strict_types=1);
require __DIR__.'/../lib/app.php';
require_once __DIR__.'/../lib/institucional_repositorio.php';
require_once __DIR__.'/../lib/portal_institucional.php';
$db = app_db();
$repo = new InstitucionalRepositorio($db);
$values = $repo->actuales();
foreach (institucional_campos() as $key=>[$label,$fields]) {
    foreach ($fields as $field=>$unused) {
        if (!array_key_exists($field,$values)) throw new RuntimeException('Falta el campo '.$field);
    }
}
$before = (int)$db->one('SELECT COUNT(*) total FROM institucional_versiones')['total'];
$identity = array_intersect_key($values,institucional_campos()['identidad'][1]);
try {
    $repo->guardar('identidad',2026,'Prueba de versión desactualizada',$identity,-1,null);
    throw new LogicException('Aceptó una versión desactualizada.');
} catch (RuntimeException $error) {
    if (!str_contains($error->getMessage(),'contenido cambió')) throw $error;
}
try {
    $repo->guardar('identidad',2026,'Prueba de formulario incompleto',[],0,null);
    throw new LogicException('Aceptó datos incompletos.');
} catch (InvalidArgumentException $error) {}
$after = (int)$db->one('SELECT COUNT(*) total FROM institucional_versiones')['total'];
if ($before !== $after) throw new RuntimeException('La prueba modificó el historial.');
$links = portal_institucional_variables(['contactos_visibles'=>'<script>alert(1)</script>']);
if (str_contains($links['CONTACTOS_ADICIONALES'],'<script>')) throw new RuntimeException('Contenido sin escapar.');
echo "OK: campos migrados, conflicto de edición, validación y escape. Historial intacto.\n";
