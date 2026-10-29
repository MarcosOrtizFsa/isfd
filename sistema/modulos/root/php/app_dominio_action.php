<?php
declare(strict_types=1);
require __DIR__ . '/../../../../lib/app.php';
app_require_mode([0]);
app_verify_csrf();
require_once __DIR__ . '/dominio_campos.php';

$db = app_db();
$record = $db->one('SELECT id_system_08 FROM system_08_configuracion ORDER BY id_system_08 LIMIT 1');
if (!$record || app_int('id') !== (int) $record['id_system_08']) {
    app_json(['ok' => false, 'message' => 'La configuración solicitada no existe.'], 404);
}

$assignments = [];
$values = [];
$types = '';
foreach (dominio_campos() as $fields) {
    foreach ($fields as $key => [$label, $type, $limit]) {
        // Exigir el formulario completo evita borrar valores por un envío parcial.
        if (!isset($_POST[$key]) || !is_string($_POST[$key])) {
            app_json(['ok' => false, 'message' => 'Falta el campo: ' . $label], 422);
        }
        $value = trim($_POST[$key]);
        if (mb_strlen($value) > $limit || ($key === 'titulo_site' && $value === '')) {
            app_json(['ok' => false, 'message' => 'Revisá el valor y la longitud de ' . $label . '.'], 422);
        }
        if ($value !== '' && $type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            app_json(['ok' => false, 'message' => $label . ': ingresá un correo válido.'], 422);
        }
        if ($value !== '' && $type === 'url' && (!filter_var($value, FILTER_VALIDATE_URL) || !in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true))) {
            app_json(['ok' => false, 'message' => $label . ': usá una URL completa http:// o https://.'], 422);
        }
        if ($type === 'number') {
            $value = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 2147483647]]);
            if ($value === false) app_json(['ok' => false, 'message' => $label . ': ingresá un entero entre 0 y 2147483647.'], 422);
        }
        // Los nombres de columnas provienen exclusivamente de nuestra lista interna.
        $assignments[] = 'system_08_' . $key . '=?';
        $values[] = $value;
        $types .= $type === 'number' ? 'i' : 's';
    }
}
$values[] = (int) $record['id_system_08'];
try {
    $db->execute('UPDATE system_08_configuracion SET ' . implode(',', $assignments) . ' WHERE id_system_08=?', $types . 'i', $values);
    app_json(['ok' => true, 'message' => 'Configuración de dominio guardada.']);
} catch (Throwable $error) {
    error_log('Error al guardar configuración de dominio: ' . $error->getMessage());
    app_json(['ok' => false, 'message' => 'No se pudieron guardar los cambios. Intentá nuevamente.'], 500);
}
