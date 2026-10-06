<?php
/**
 * Configuración privada de NEURIA.
 *
 * Los secretos se leen exclusivamente desde variables de entorno del servidor.
 * Este archivo puede versionarse porque no contiene credenciales ni valores reales.
 */
function neuria_configuracion()
{
    $url = trim((string) getenv('NEURIA_API_URL'));
    $clientId = trim((string) getenv('NEURIA_CLIENT_ID'));
    $token = trim((string) getenv('NEURIA_API_TOKEN'));

    // Alternativa temporal para desarrollo local. Las variables de entorno
    // siempre tienen prioridad cuando las tres están completas.
    if ($url === '' || $clientId === '' || $token === '') {
        $rutaLocal = __DIR__ . '/../../../../config_private/neuria.local.php';
        if (is_file($rutaLocal)) {
            $configuracionLocal = include $rutaLocal;
            if (is_array($configuracionLocal)) {
                $url = trim((string) ($configuracionLocal['url'] ?? ''));
                $clientId = trim((string) ($configuracionLocal['client_id'] ?? ''));
                $token = trim((string) ($configuracionLocal['token'] ?? ''));
            }
        }
    }

    // Acepta temporalmente tanto la URL base como el endpoint completo. El
    // cliente normaliza ambos formatos para evitar duplicar la ruta documentada.
    $rutaEndpoint = '/sistema/modulos/api_clientes/php/api_contenido.php';
    $posicionEndpoint = stripos($url, $rutaEndpoint);
    if ($posicionEndpoint !== false) {
        $url = substr($url, 0, $posicionEndpoint);
    }

    return array(
        'url' => rtrim($url, '/'),
        'client_id' => $clientId,
        'token' => $token,
        'timeout' => 8,
        'cache_ttl' => 300,
        'cache_dir' => rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'isfd_neuria_cache',
        'configurada' => $url !== '' && $clientId !== '' && $token !== ''
    );
}
?>
