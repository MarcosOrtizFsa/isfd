<?php
include __DIR__ . '/../sistema/modulos/home/php/neuria_cliente.php';
include __DIR__ . '/../sistema/modulos/home/php/neuria_sanitizador.php';

function probar($condicion, $mensaje)
{
    if (!$condicion) {
        fwrite(STDERR, "FALLO: {$mensaje}\n");
        exit(1);
    }
    echo "OK: {$mensaje}\n";
}

function clientePrueba($token)
{
    return new NeuriaCliente(array(
        'url' => 'http://127.0.0.1:18087',
        'client_id' => 'cliente-prueba',
        'token' => $token,
        'timeout' => 2,
        'cache_ttl' => 0,
        'cache_dir' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'isfd_neuria_test_' . $token,
        'configurada' => true
    ));
}

$valido = clientePrueba('valid')->listar(6);
probar($valido['ok'] && count($valido['items']) === 1, 'respuesta válida normalizada');

$vacio = clientePrueba('empty')->listar(6);
probar($vacio['ok'] && count($vacio['items']) === 0, 'respuesta sin contenidos');

$noAutorizado = clientePrueba('unauthorized')->listar(6);
probar(!$noAutorizado['ok'] && $noAutorizado['error'] === 'invalid_credentials', 'error 401');

$limitado = clientePrueba('limited')->listar(6);
probar(!$limitado['ok'] && $limitado['error'] === 'rate_limit_exceeded', 'error 429');

$invalido = clientePrueba('invalid-json')->listar(6);
probar(!$invalido['ok'] && $invalido['error'] === 'invalid_response', 'respuesta no JSON');

$servidor = clientePrueba('server-error')->listar(6);
probar(!$servidor['ok'] && $servidor['error'] === 'temporary_error', 'error temporal 503');

$sinConexion = new NeuriaCliente(array(
    'url' => 'http://127.0.0.1:18088', 'client_id' => 'sin-conexion', 'token' => 'privado',
    'timeout' => 1, 'cache_ttl' => 300,
    'cache_dir' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'isfd_neuria_test_connection', 'configurada' => true
));
$errorConexion = $sinConexion->listar(6);
probar(!$errorConexion['ok'] && $errorConexion['error'] === 'connection_error', 'error de conexión');

$cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'isfd_neuria_test_cache';
$conCache = new NeuriaCliente(array(
    'url' => 'http://127.0.0.1:18087', 'client_id' => 'cliente-prueba', 'token' => 'valid',
    'timeout' => 2, 'cache_ttl' => 300, 'cache_dir' => $cacheDir, 'configurada' => true
));
probar($conCache->listar(6)['ok'], 'caché inicial guardada');
$cacheAnterior = new NeuriaCliente(array(
    'url' => 'http://127.0.0.1:18088', 'client_id' => 'cliente-prueba', 'token' => 'no-expuesto',
    'timeout' => 1, 'cache_ttl' => -1, 'cache_dir' => $cacheDir, 'configurada' => true
));
$resultadoCache = $cacheAnterior->listar(6);
probar($resultadoCache['ok'] && $resultadoCache['origen'] === 'cache_anterior', 'caché anterior ante caída');

$limpio = neuria_sanitizar_html($valido['items'][0]['contenido']);
probar(stripos($limpio, '<script') === false && stripos($limpio, 'onclick') === false && stripos($limpio, 'javascript:') === false, 'HTML peligroso eliminado');

$textoPlano = neuria_sanitizar_html("Primer párrafo.\nSegunda línea.\n\nSegundo párrafo.");
probar(substr_count($textoPlano, '<p>') === 2 && substr_count($textoPlano, '<br>') === 1, 'saltos de línea del texto plano preservados');
?>
