<?php
/**
 * Vista pública de una noticia completa obtenida desde NEURIA.
 * La consulta y la sanitización ocurren en el servidor; el token nunca se envía.
 */
include __DIR__ . '/../../../../lib/template.inc';
include __DIR__ . '/neuria_cliente.php';
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

$id = isset($_POST['id_system_10']) ? $_POST['id_system_10'] : (isset($_GET['id_system_10']) ? $_GET['id_system_10'] : null);
$id = filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));

$noticia = null;
if ($id !== false && $id !== null) {
    $cliente = new NeuriaCliente();
    $resultado = $cliente->obtener($id);
    if ($resultado['ok'] && count($resultado['items']) > 0) {
        $noticia = $resultado['items'][0];
    }
}

$t = new _template(__DIR__ . '/../templates');
$t->set_file(array('leer' => 'leer.html'));

if ($noticia !== null) {
    $fechaPublicacion = $noticia['aprobado'] !== '' ? $noticia['aprobado'] : $noticia['actualizado'];
    $contenido = neuria_sanitizar_html($noticia['contenido']);
    if ($contenido === '') {
        $contenido = '<p>Esta publicación no tiene contenido adicional.</p>';
    }
    $t->set_var(array(
        'ESTADO_CLASE' => '',
        'ESTADO_MENSAJE' => '',
        'NOTICIA_CLASE' => '',
        'TIPO' => leer_escapar($noticia['tipo'] !== '' ? $noticia['tipo'] : 'Contenido'),
        'TITULO' => leer_escapar($noticia['titulo']),
        'BAJADA' => leer_escapar($noticia['bajada']),
        'FECHA' => leer_escapar(leer_fecha($fechaPublicacion)),
        'CONTENIDO' => $contenido
    ));
} else {
    // Se mantiene HTTP 200 porque esta vista se inyecta por AJAX dentro del
    // layout principal; el mensaje accesible reemplaza cualquier error técnico.
    $t->set_var(array(
        'ESTADO_CLASE' => '',
        'ESTADO_MENSAJE' => 'La publicación solicitada no está disponible.',
        'NOTICIA_CLASE' => 'd-none',
        'TIPO' => '', 'TITULO' => '', 'BAJADA' => '', 'FECHA' => '', 'CONTENIDO' => ''
    ));
}

$t->set_var(array(
    'PORTAL_NOMBRE' => leer_escapar($portal['nombre']),
    'PORTAL_LOGO' => leer_escapar($portal['logo_ruta']),
    'ANIO' => date('Y')
));
$t->pparse('OUT', 'leer');
?>
