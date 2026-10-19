<?php
/**
 * Vista pública de una noticia activa administrada por el módulo Noticias.
 */
include __DIR__ . '/../../../../lib/template.inc';
require_once __DIR__ . '/../../../../lib/app.php';
include __DIR__ . '/neuria_sanitizador.php';
include __DIR__ . '/datos_home.php';

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

$id = isset($_POST['id_system_10']) ? $_POST['id_system_10'] : (isset($_GET['id_system_10']) ? $_GET['id_system_10'] : null);
$id = filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));

$noticia = null;
if ($id !== false && $id !== null) {
    try {
        $noticia = app_db()->one(
            "SELECT n.titulo, n.bajada, n.contenido, n.seccion,
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
    } catch (Throwable $error) {
        error_log('No fue posible cargar la noticia publica: ' . $error->getMessage());
        $noticia = null;
    }
}

$t = new _template(__DIR__ . '/../templates');
$t->set_file(array('leer' => 'leer.html'));

if ($noticia !== null) {
    $contenido = neuria_sanitizar_html($noticia['contenido']);
    if ($contenido === '') {
        $contenido = '<p>Esta publicación no tiene contenido adicional.</p>';
    }
    $t->set_var(array(
        'ESTADO_CLASE' => '',
        'ESTADO_MENSAJE' => '',
        'NOTICIA_CLASE' => '',
        'TIPO' => leer_escapar(trim((string) $noticia['seccion']) !== '' ? $noticia['seccion'] : 'Novedades'),
        'TITULO' => leer_escapar($noticia['titulo']),
        'BAJADA' => leer_escapar($noticia['bajada']),
        'FECHA' => leer_escapar(leer_fecha($noticia['fecha_publicacion'])),
        'IMAGEN_URL' => leer_escapar($noticia['imagen_ruta'] ? leer_url_publica($noticia['imagen_ruta']) : ''),
        'IMAGEN_ALT' => leer_escapar('Imagen de ' . $noticia['titulo']),
        'IMAGEN_CLASE' => $noticia['imagen_ruta'] ? '' : 'd-none',
        'CONTENIDO' => $contenido
    ));
} else {
    // Se mantiene HTTP 200 porque esta vista se inyecta por AJAX dentro del
    // layout principal; el mensaje accesible reemplaza cualquier error técnico.
    $t->set_var(array(
        'ESTADO_CLASE' => '',
        'ESTADO_MENSAJE' => 'La publicación solicitada no está disponible.',
        'NOTICIA_CLASE' => 'd-none',
        'TIPO' => '', 'TITULO' => '', 'BAJADA' => '', 'FECHA' => '',
        'IMAGEN_URL' => '', 'IMAGEN_ALT' => '', 'IMAGEN_CLASE' => 'd-none', 'CONTENIDO' => ''
    ));
}

$t->set_var(array(
    'PORTAL_NOMBRE' => leer_escapar($portal['nombre']),
    'PORTAL_LOGO' => leer_escapar($portal['logo_ruta']),
    'ANIO' => date('Y')
));
$t->pparse('OUT', 'leer');
?>
