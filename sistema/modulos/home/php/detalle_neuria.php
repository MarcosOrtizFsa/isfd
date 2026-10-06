<?php
/**
 * Endpoint interno para el modal de detalle.
 * La credencial de NEURIA permanece en el servidor y nunca forma parte del JSON.
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

include __DIR__ . '/neuria_cliente.php';
include __DIR__ . '/neuria_sanitizador.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
if (!$id) {
    http_response_code(404);
    echo json_encode(array('ok' => false, 'mensaje' => 'El contenido solicitado no está disponible.'));
    exit;
}

$cliente = new NeuriaCliente();
$resultado = $cliente->obtener($id);
if (!$resultado['ok'] || count($resultado['items']) === 0) {
    $estado = $resultado['http_code'] === 404 ? 404 : 503;
    http_response_code($estado);
    echo json_encode(array('ok' => false, 'mensaje' => 'El contenido no está disponible en este momento.'));
    exit;
}

$item = $resultado['items'][0];
echo json_encode(array(
    'ok' => true,
    'item' => array(
        'id' => $item['id'],
        'titulo' => $item['titulo'],
        'tipo' => $item['tipo'],
        'fecha' => $item['aprobado'] !== '' ? $item['aprobado'] : $item['actualizado'],
        'contenido_html' => neuria_sanitizar_html($item['contenido'])
    )
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
