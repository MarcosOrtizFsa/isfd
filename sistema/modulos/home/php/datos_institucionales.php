<?php
/** Adaptador para las vistas existentes: todo el contenido institucional viene de BD. */
require_once __DIR__ . '/../../../../lib/institucional_repositorio.php';
require_once __DIR__ . '/../../../../lib/portal_institucional.php';
$institucion = (new InstitucionalRepositorio(app_db()))->actuales();
$institucion = portal_institucional_configurar($institucion);
$institucion['horarios'] = [];
foreach (['manana'=>'Mañana', 'tarde'=>'Tarde', 'noche'=>'Noche'] as $key=>$label) {
    if (!empty($institucion[$key])) $institucion['horarios'][$label] = $institucion[$key];
}
$historiaInstitucional = array_values(array_filter(explode("\n", $institucion['historia_texto'] ?? ''), 'strlen'));
$autoridades = [];
foreach (explode("\n", $institucion['autoridades_texto'] ?? '') as $line) {
    if (str_contains($line, '|')) {
        [$cargo, $nombre] = array_map('trim', explode('|', $line, 2));
        $autoridades[] = ['cargo'=>$cargo, 'nombre'=>$nombre];
    }
}

