<?php
header('Content-Type: application/json; charset=utf-8');
$authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = preg_replace('/^Bearer\s+/i', '', $authorization);

if ($token === 'unauthorized') {
    http_response_code(401);
    echo json_encode(array('ok' => false, 'error' => 'invalid_credentials'));
    return;
}
if ($token === 'limited') {
    http_response_code(429);
    echo json_encode(array('ok' => false, 'error' => 'rate_limit_exceeded'));
    return;
}
if ($token === 'invalid-json') {
    echo 'respuesta-invalida';
    return;
}
if ($token === 'server-error') {
    http_response_code(503);
    echo json_encode(array('ok' => false, 'error' => 'temporary_error'));
    return;
}

$items = $token === 'empty' ? array() : array(array(
    'id' => 123,
    'titulo' => 'Contenido de prueba',
    'bajada' => 'Resumen de prueba',
    'contenido' => '<p>Texto <strong>seguro</strong>.</p><script>alert(1)</script><a href="javascript:alert(2)" onclick="alert(3)">enlace</a>',
    'tipo' => 'NOTICIA',
    'perfil' => 'INFORMATIVO',
    'actualizado' => '2026-10-05 13:45:00',
    'aprobado' => '2026-10-05 13:44:00'
));

echo json_encode(array('ok' => true, 'client_id' => 'cliente-prueba', 'count' => count($items), 'items' => $items));
?>
