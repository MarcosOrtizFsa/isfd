<?php
declare(strict_types=1);

/** Texto plano: se escapa al presentarlo, conservando los saltos de línea. */
function glosario_validar(array $input): array
{
    $palabra = trim(is_string($input['palabra'] ?? null) ? $input['palabra'] : '');
    $descripcion = trim(is_string($input['descripcion'] ?? null) ? $input['descripcion'] : '');
    if ($palabra === '' || mb_strlen($palabra) > 150) throw new InvalidArgumentException('La palabra es obligatoria y admite hasta 150 caracteres.');
    if ($descripcion === '' || mb_strlen($descripcion) > 10000) throw new InvalidArgumentException('La descripción es obligatoria y admite hasta 10.000 caracteres.');
    return ['palabra'=>$palabra,'descripcion'=>$descripcion,'visible'=>isset($input['visible']) ? 1 : 0];
}

/** Una migración pendiente no debe impedir la lectura de la portada. */
function glosario_publico_html(AppDatabase $db): string
{
    try {
        $items = $db->all('SELECT palabra,descripcion FROM institucional_glosario WHERE visible=1 AND deleted_at IS NULL ORDER BY palabra,id');
    } catch (Throwable $error) {
        error_log('Lectura del glosario: '.$error->getMessage());
        return '<p class="text-secondary">El glosario no está disponible en este momento.</p>';
    }
    if (!$items) return '<p class="text-secondary">Próximamente publicaremos los términos del glosario institucional.</p>';
    $html = '<dl class="mb-0">';
    foreach ($items as $item) {
        $html .= '<dt class="h5 text-break">'.app_escape($item['palabra']).'</dt><dd class="border-bottom pb-3 mb-3 text-break">'.nl2br(app_escape($item['descripcion'])).'</dd>';
    }
    return $html.'</dl>';
}
