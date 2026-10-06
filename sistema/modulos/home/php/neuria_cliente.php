<?php
include_once __DIR__ . '/neuria_config.php';

/**
 * Cliente HTTP reutilizable para el único endpoint público documentado por NEURIA.
 * Devuelve resultados normalizados y nunca incluye el token en respuestas o errores.
 */
class NeuriaCliente
{
    private $config;
    private $endpoint = '/sistema/modulos/api_clientes/php/api_contenido.php';

    public function __construct($config = null)
    {
        $this->config = is_array($config) ? $config : neuria_configuracion();
    }

    public function estaConfigurado()
    {
        return !empty($this->config['configurada']);
    }

    public function listar($limite = 6, $desde = null)
    {
        $parametros = array('limite' => max(1, min(100, (int) $limite)));
        if ($desde !== null && $desde !== '') {
            $parametros['desde'] = (string) $desde;
        }
        return $this->consultar($parametros, 'lista_' . sha1(json_encode($parametros)));
    }

    public function obtener($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
        if ($id === false) {
            return $this->resultadoError('content_not_found', 404);
        }
        return $this->consultar(array('id' => $id), 'detalle_' . $id);
    }

    private function consultar(array $parametros, $claveCache)
    {
        if (!$this->estaConfigurado()) {
            return $this->resultadoError('not_configured', 0);
        }

        $cacheVigente = $this->leerCache($claveCache, false);
        if ($cacheVigente !== null) {
            $cacheVigente['origen'] = 'cache';
            return $cacheVigente;
        }

        $url = $this->config['url'] . $this->endpoint . '?' . http_build_query($parametros, '', '&', PHP_QUERY_RFC3986);
        $curl = curl_init($url);
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => (int) $this->config['timeout'],
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->config['token'],
                'Accept: application/json'
            ),
            CURLOPT_USERAGENT => 'Formosa-Educa-IA/1.0'
        ));

        $cuerpo = curl_exec($curl);
        $codigoHttp = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $errorConexion = curl_errno($curl) !== 0;
        curl_close($curl);

        if ($errorConexion || $cuerpo === false) {
            return $this->usarCacheAnteriorOError($claveCache, 'connection_error', 0);
        }
        if ($codigoHttp < 200 || $codigoHttp >= 300) {
            $errores = array(401 => 'invalid_credentials', 404 => 'content_not_found', 429 => 'rate_limit_exceeded');
            $codigo = isset($errores[$codigoHttp]) ? $errores[$codigoHttp] : 'temporary_error';
            return $this->usarCacheAnteriorOError($claveCache, $codigo, $codigoHttp);
        }

        $respuesta = json_decode($cuerpo, true);
        if (!is_array($respuesta) || ($respuesta['ok'] ?? false) !== true || !isset($respuesta['items']) || !is_array($respuesta['items'])) {
            return $this->usarCacheAnteriorOError($claveCache, 'invalid_response', $codigoHttp);
        }
        if (!isset($respuesta['client_id']) || !hash_equals((string) $this->config['client_id'], (string) $respuesta['client_id'])) {
            return $this->usarCacheAnteriorOError($claveCache, 'invalid_client_response', $codigoHttp);
        }

        $items = array();
        foreach ($respuesta['items'] as $item) {
            $normalizado = $this->normalizarItem($item);
            if ($normalizado !== null) {
                $items[] = $normalizado;
            }
        }

        $resultado = array('ok' => true, 'items' => $items, 'http_code' => $codigoHttp, 'error' => null, 'origen' => 'api');
        $this->guardarCache($claveCache, $resultado);
        return $resultado;
    }

    private function normalizarItem($item)
    {
        if (!is_array($item) || !isset($item['id'], $item['titulo'])) {
            return null;
        }
        $id = filter_var($item['id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
        if ($id === false || trim((string) $item['titulo']) === '') {
            return null;
        }
        return array(
            'id' => $id,
            'titulo' => trim((string) $item['titulo']),
            'bajada' => trim((string) ($item['bajada'] ?? '')),
            'contenido' => (string) ($item['contenido'] ?? ''),
            'tipo' => trim((string) ($item['tipo'] ?? 'Contenido')),
            'perfil' => trim((string) ($item['perfil'] ?? '')),
            'actualizado' => trim((string) ($item['actualizado'] ?? '')),
            'aprobado' => trim((string) ($item['aprobado'] ?? ''))
        );
    }

    private function rutaCache($clave)
    {
        return $this->config['cache_dir'] . DIRECTORY_SEPARATOR . hash('sha256', $this->config['client_id'] . '|' . $clave) . '.json';
    }

    private function leerCache($clave, $permitirVencido)
    {
        $ruta = $this->rutaCache($clave);
        if (!is_file($ruta) || (!$permitirVencido && (time() - filemtime($ruta)) > (int) $this->config['cache_ttl'])) {
            return null;
        }
        $datos = json_decode((string) @file_get_contents($ruta), true);
        return is_array($datos) && isset($datos['ok']) ? $datos : null;
    }

    private function guardarCache($clave, array $resultado)
    {
        $directorio = $this->config['cache_dir'];
        if (!is_dir($directorio) && !@mkdir($directorio, 0700, true) && !is_dir($directorio)) {
            return;
        }
        @file_put_contents($this->rutaCache($clave), json_encode($resultado, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    private function usarCacheAnteriorOError($clave, $error, $httpCode)
    {
        $anterior = $this->leerCache($clave, true);
        if ($anterior !== null) {
            $anterior['origen'] = 'cache_anterior';
            return $anterior;
        }
        return $this->resultadoError($error, $httpCode);
    }

    private function resultadoError($error, $httpCode)
    {
        return array('ok' => false, 'items' => array(), 'http_code' => $httpCode, 'error' => $error, 'origen' => null);
    }
}
?>
