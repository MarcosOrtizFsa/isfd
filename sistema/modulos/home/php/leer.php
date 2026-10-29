<?php
/**
 * Vista pública de una noticia activa administrada por el módulo Noticias.
 */
include __DIR__ . '/../../../../lib/template.inc';
require_once __DIR__ . '/../../../../lib/app.php';
include __DIR__ . '/neuria_sanitizador.php';
include __DIR__ . '/datos_home.php';
include __DIR__ . '/datos_institucionales.php';

function leer_escapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function leer_fecha($valor)
{
    try {
        $fecha = new DateTime((string) $valor);
        return $fecha->format('d/m/Y');
    } catch (Exception $error) {
        return '';
    }
}

function leer_url_publica($ruta)
{
    $raizProyecto = str_replace('\\', '/', (string) realpath(dirname(__DIR__, 4)));
    $raizPublica = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $base = '';
    if ($raizPublica !== '' && str_starts_with(strtolower($raizProyecto), strtolower($raizPublica))) {
        $base = substr($raizProyecto, strlen($raizPublica));
    }
    return '/' . trim($base . '/' . ltrim((string) $ruta, '/'), '/');
}

function leer_url_amigable($valor)
{
    $valor = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $valor);
    $valor = strtolower((string) $valor);
    return trim((string) preg_replace('/[^a-z0-9]+/', '-', $valor), '-') ?: 'noticia';
}

$id = isset($_POST['id_system_10']) ? $_POST['id_system_10'] : (isset($_GET['id_system_10']) ? $_GET['id_system_10'] : null);
$id = filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));

$noticia = null;
$relacionadas = array();
$adjuntos = array();
if ($id !== false && $id !== null) {
    try {
        $db = app_db();
        $noticia = $db->one(
            "SELECT n.id, n.volanta, n.titulo, n.bajada, n.contenido, n.seccion, n.youtube_codigo,
                    (SELECT a.ruta FROM noticia_archivos a
                     WHERE a.noticia_id = n.id AND a.tipo = 'imagen' AND a.deleted_at IS NULL
                     ORDER BY a.orden, a.id LIMIT 1) AS imagen_ruta,
                    COALESCE(n.publicacion_at, n.created_at) AS fecha_publicacion
             FROM noticias n
             WHERE n.id = ?
               AND n.estado = 'activa'
               AND n.deleted_at IS NULL
               AND (n.publicacion_at IS NULL OR n.publicacion_at <= NOW())
             LIMIT 1",
            'i',
            array((int) $id)
        );
        if ($noticia !== null) {
            $relacionadas = $db->all(
                "SELECT n.id, n.titulo, n.bajada, n.seccion,
                        COALESCE(n.publicacion_at, n.created_at) AS fecha_publicacion,
                        (SELECT a.ruta FROM noticia_archivos a
                         WHERE a.noticia_id = n.id AND a.tipo = 'imagen' AND a.deleted_at IS NULL
                         ORDER BY a.orden, a.id LIMIT 1) AS imagen_ruta
                 FROM noticias n
                 WHERE n.id <> ? AND n.estado = 'activa' AND n.deleted_at IS NULL
                   AND (n.publicacion_at IS NULL OR n.publicacion_at <= NOW())
                 ORDER BY (n.seccion = ?) DESC, COALESCE(n.publicacion_at, n.created_at) DESC, n.id DESC
                 LIMIT 4",
                'is',
                array((int) $id, (string) $noticia['seccion'])
            );
            $adjuntos = $db->all(
                "SELECT nombre_original, ruta, mime_type, tamano_bytes
                 FROM noticia_archivos
                 WHERE noticia_id = ? AND tipo = 'adjunto' AND deleted_at IS NULL
                 ORDER BY orden, id",
                'i',
                array((int) $id)
            );
        }
    } catch (Throwable $error) {
        error_log('No fue posible cargar la noticia publica: ' . $error->getMessage());
        $noticia = null;
    }
}

$t = new _template(__DIR__ . '/../templates');
$t->set_file(array(
    'leer' => 'leer.html',
    'relacionada' => 'leer_relacionada.html',
    'adjunto' => 'leer_adjunto.html'
));
$t->set_var(array('RELACIONADAS' => '', 'ADJUNTOS' => ''));

if ($noticia !== null) {
    $contenido = neuria_sanitizar_html($noticia['contenido']);
    if ($contenido === '') {
        $contenido = '<p>Esta publicación no tiene contenido adicional.</p>';
    }
    $youtube = preg_match('/^[A-Za-z0-9_-]{11}$/', (string) $noticia['youtube_codigo'])
        ? '<div class="ratio ratio-16x9 isfd-reader-video"><iframe src="https://www.youtube-nocookie.com/embed/' . leer_escapar($noticia['youtube_codigo']) . '" title="Video de la noticia" loading="lazy" allowfullscreen></iframe></div>'
        : '';
    $t->set_var(array(
        'ESTADO_CLASE' => '',
        'ESTADO_MENSAJE' => '',
        'NOTICIA_CLASE' => '',
        'VOLANTA' => leer_escapar(trim((string) $noticia['volanta']) !== '' ? $noticia['volanta'] : 'Actualidad institucional'),
        'TIPO' => leer_escapar(trim((string) $noticia['seccion']) !== '' ? $noticia['seccion'] : 'Novedades'),
        'TITULO' => leer_escapar($noticia['titulo']),
        'BAJADA' => leer_escapar($noticia['bajada']),
        'FECHA' => leer_escapar(leer_fecha($noticia['fecha_publicacion'])),
        'IMAGEN_URL' => leer_escapar($noticia['imagen_ruta'] ? leer_url_publica($noticia['imagen_ruta']) : ''),
        'IMAGEN_ALT' => leer_escapar('Imagen de ' . $noticia['titulo']),
        'IMAGEN_CLASE' => $noticia['imagen_ruta'] ? '' : 'd-none',
        'CONTENIDO' => $contenido,
        'YOUTUBE' => $youtube,
        'ADJUNTOS_CLASE' => count($adjuntos) > 0 ? '' : 'd-none',
        'RELACIONADAS_CLASE' => count($relacionadas) > 0 ? '' : 'd-none'
    ));

    foreach ($adjuntos as $archivo) {
        $t->set_var(array(
            'ADJUNTO_NOMBRE' => leer_escapar($archivo['nombre_original']),
            'ADJUNTO_URL' => leer_escapar(leer_url_publica($archivo['ruta'])),
            'ADJUNTO_TAMANO' => leer_escapar(number_format((int) $archivo['tamano_bytes'] / 1024, 0, ',', '.') . ' KB')
        ));
        $t->parse('ADJUNTOS', 'adjunto', true);
    }

    foreach ($relacionadas as $item) {
        $t->set_var(array(
            'RELACIONADA_URL' => '../nota/' . (int) $item['id'] . '/' . leer_escapar(leer_url_amigable($item['titulo'])),
            'RELACIONADA_TITULO' => leer_escapar($item['titulo']),
            'RELACIONADA_BAJADA' => leer_escapar($item['bajada']),
            'RELACIONADA_TIPO' => leer_escapar(trim((string) $item['seccion']) !== '' ? $item['seccion'] : 'Novedades'),
            'RELACIONADA_FECHA' => leer_escapar(leer_fecha($item['fecha_publicacion'])),
            'RELACIONADA_IMAGEN' => leer_escapar($item['imagen_ruta'] ? leer_url_publica($item['imagen_ruta']) : ''),
            'RELACIONADA_IMAGEN_CLASE' => $item['imagen_ruta'] ? '' : 'd-none',
            'RELACIONADA_ICONO_CLASE' => $item['imagen_ruta'] ? 'd-none' : ''
        ));
        $t->parse('RELACIONADAS', 'relacionada', true);
    }
} else {
    // Se mantiene HTTP 200 porque esta vista se inyecta por AJAX dentro del
    // layout principal; el mensaje accesible reemplaza cualquier error técnico.
    $t->set_var(array(
        'ESTADO_CLASE' => '',
        'ESTADO_MENSAJE' => 'La publicación solicitada no está disponible.',
        'NOTICIA_CLASE' => 'd-none',
        'VOLANTA' => '', 'TIPO' => '', 'TITULO' => '', 'BAJADA' => '', 'FECHA' => '',
        'IMAGEN_URL' => '', 'IMAGEN_ALT' => '', 'IMAGEN_CLASE' => 'd-none', 'CONTENIDO' => '',
        'YOUTUBE' => '', 'ADJUNTOS_CLASE' => 'd-none',
        'RELACIONADAS_CLASE' => 'd-none'
    ));
}

$t->set_var(array(
    'PORTAL_NOMBRE' => leer_escapar($institucion['nombre_corto']),
    'PORTAL_SIGLA' => leer_escapar($institucion['sigla']),
    'TELEFONO' => leer_escapar($institucion['telefono']),
    'EMAIL' => leer_escapar($institucion['email']),
    'DIRECCION' => leer_escapar($institucion['direccion']),
    'CAMPUS_URL' => leer_escapar($institucion['campus_url']),
    'PREINSCRIPCION_URL' => leer_escapar($institucion['preinscripcion_url']),
    'ANIO' => date('Y')
));
$t->set_var(portal_institucional_variables($institucion));
$t->pparse('OUT', 'leer');
?>
