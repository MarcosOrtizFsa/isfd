<?php
/**
 * Controlador de la portada pública de Formosa Educa IA.
 * Prepara datos seguros y delega toda la presentación a PHPLIB.
 */
include __DIR__ . '/../../../../lib/template.inc';
include __DIR__ . '/datos_home.php';
include __DIR__ . '/neuria_cliente.php';

/** Escapa cualquier valor que se imprima como texto o atributo HTML. */
function home_escapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Normaliza una etiqueta para utilizarla como valor de filtro. */
function home_slug($valor)
{
    $valor = strtolower(trim((string) $valor));
    return preg_replace('/[^a-z0-9_-]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $valor));
}

/** Convierte la fecha técnica de NEURIA en una fecha legible. */
function home_fecha($valor)
{
    try {
        $fecha = new DateTime((string) $valor);
        return $fecha->format('d/m/Y');
    } catch (Exception $error) {
        return '';
    }
}

/** Construye el segmento legible de la URL sin alterar el identificador real. */
function home_url_amigable($valor)
{
    $valor = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $valor);
    $valor = strtolower($valor);
    $valor = preg_replace('/[^a-z0-9]+/', '-', $valor);
    return trim($valor, '-') ?: 'noticia';
}

$t = new _template(__DIR__ . '/../templates');
$t->set_file(array(
    'home' => 'home.html',
    'tarjeta_novedad' => 'tarjeta_novedad.html',
    'tarjeta_idea' => 'tarjeta_idea.html',
    'filtro_novedad' => 'filtro_novedad.html'
));

// La Home consume solamente contenidos aprobados por NEURIA. Si el servicio
// todavía no está configurado o falla, el resto del portal continúa disponible.
$clienteNeuria = new NeuriaCliente();
$respuestaNeuria = $clienteNeuria->listar(6);
$novedades = array();
if ($respuestaNeuria['ok']) {
    foreach ($respuestaNeuria['items'] as $item) {
        $novedades[] = array(
            'id' => (int) $item['id'],
            'categoria' => $item['tipo'] !== '' ? $item['tipo'] : 'Contenido',
            'fecha' => home_fecha($item['aprobado'] !== '' ? $item['aprobado'] : $item['actualizado']),
            'titulo' => $item['titulo'],
            'resumen' => $item['bajada'],
            'icono' => 'bi-newspaper'
        );
    }
}

$estadoNovedades = '';
if (count($novedades) === 0) {
    $estadoNovedades = '<p class="fe-empty">Todavía no hay publicaciones disponibles. Volvé a visitarnos pronto.</p>';
}

// Datos editables generales del portal.
$t->set_var(array(
    'PORTAL_NOMBRE' => home_escapar($portal['nombre']),
    'PORTAL_SIGLA' => home_escapar($portal['sigla']),
    'PORTAL_LOGO' => home_escapar($portal['logo_ruta']),
    'PORTAL_DESCRIPCION' => home_escapar($portal['descripcion']),
    'PORTAL_CORREO' => home_escapar($portal['correo']),
    'PORTAL_CONTACTO' => home_escapar($portal['contacto']),
    'PORTAL_REDES' => home_escapar($portal['redes']),
    'ANIO' => date('Y'),
    'NOVEDADES' => '',
    'FILTROS_NOVEDADES' => '',
    'ESTADO_NOVEDADES' => $estadoNovedades,
    'CLASE_CONTENIDO_NOVEDADES' => count($novedades) > 0 ? '' : 'd-none',
    'IDEAS' => ''
));

// Los filtros nacen del campo real `tipo`; la API no entrega categorías.
$tipos = array_values(array_unique(array_column($novedades, 'categoria')));
foreach ($tipos as $tipo) {
    $t->set_var(array(
        'FILTRO_VALOR' => home_escapar(home_slug($tipo)),
        'FILTRO_ETIQUETA' => home_escapar($tipo)
    ));
    $t->parse('FILTROS_NOVEDADES', 'filtro_novedad', true);
}

// Render de novedades con una plantilla repetible.
foreach ($novedades as $novedad) {
    $t->set_var(array(
        'ID_NOVEDAD' => home_escapar($novedad['id']),
        'CATEGORIA' => home_escapar($novedad['categoria']),
        'CATEGORIA_FILTRO' => home_escapar(home_slug($novedad['categoria'])),
        'FECHA' => home_escapar($novedad['fecha']),
        'TITULO_NOVEDAD' => home_escapar($novedad['titulo']),
        'URL_NOVEDAD' => home_escapar(home_url_amigable($novedad['titulo'])),
        'RESUMEN' => home_escapar($novedad['resumen']),
        'ICONO' => home_escapar($novedad['icono'])
    ));
    $t->parse('NOVEDADES', 'tarjeta_novedad', true);
}

// Render de buenas ideas con el mismo patrón reutilizable.
foreach ($ideas as $idea) {
    $t->set_var(array(
        'ID_IDEA' => home_escapar($idea['id']),
        'NIVEL' => home_escapar($idea['nivel']),
        'TITULO_IDEA' => home_escapar($idea['titulo']),
        'OBJETIVO_BREVE' => home_escapar($idea['objetivo_breve'])
    ));
    $t->parse('IDEAS', 'tarjeta_idea', true);
}

// Solo las buenas ideas locales viajan embebidas. El detalle NEURIA se obtiene
// desde el endpoint interno y la credencial nunca llega al navegador.
$opciones_json = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES |
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$t->set_var('JSON_IDEAS', json_encode($ideas, $opciones_json));

$t->pparse('OUT', 'home');
?>
