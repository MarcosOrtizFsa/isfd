<?php
/**
 * Controlador de la portada pública de Formosa Educa IA.
 * Prepara datos seguros y delega toda la presentación a PHPLIB.
 */
include __DIR__ . '/../../../../lib/template.inc';
require_once __DIR__ . '/../../../../lib/app.php';
include __DIR__ . '/datos_home.php';
include __DIR__ . '/datos_institucionales.php';
include __DIR__ . '/ofertas_repositorio.php';

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

/** Convierte una fecha de publicación en una fecha legible. */
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

/** Genera una URL pública estable, incluso si el portal vive en un subdirectorio. */
function home_url_publica($ruta)
{
    $raizProyecto = str_replace('\\', '/', (string) realpath(dirname(__DIR__, 4)));
    $raizPublica = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $base = '';
    if ($raizPublica !== '' && str_starts_with(strtolower($raizProyecto), strtolower($raizPublica))) {
        $base = substr($raizProyecto, strlen($raizPublica));
    }
    return '/' . trim($base . '/' . ltrim((string) $ruta, '/'), '/');
}

$t = new _template(__DIR__ . '/../templates');
$t->set_file(array(
    'home' => 'portal_home.html',
    'tarjeta_novedad' => 'tarjeta_novedad.html',
    'tarjeta_idea' => 'tarjeta_idea.html',
    'filtro_novedad' => 'filtro_novedad.html'
));

// La Home consume las noticias que el administrador activó en el módulo Noticias.
$novedades = array();
try {
    // Solo se publican noticias activadas por el equipo editorial y cuya fecha ya llegó.
    $noticiasActivas = app_db()->all(
        "SELECT n.id, n.titulo, n.bajada, n.seccion,
                (SELECT a.ruta FROM noticia_archivos a
                 WHERE a.noticia_id = n.id AND a.tipo = 'imagen' AND a.deleted_at IS NULL
                 ORDER BY a.orden, a.id LIMIT 1) AS imagen_ruta,
                COALESCE(n.publicacion_at, n.created_at) AS fecha_publicacion
         FROM noticias n
         WHERE n.estado = 'activa'
           AND n.deleted_at IS NULL
           AND (n.publicacion_at IS NULL OR n.publicacion_at <= NOW())
         ORDER BY COALESCE(n.publicacion_at, n.created_at) DESC, n.id DESC
         LIMIT 6"
    );

    foreach ($noticiasActivas as $item) {
        $novedades[] = array(
            'id' => (int) $item['id'],
            'categoria' => trim((string) $item['seccion']) !== '' ? $item['seccion'] : 'Novedades',
            'fecha' => home_fecha($item['fecha_publicacion']),
            'titulo' => $item['titulo'],
            'resumen' => $item['bajada'],
            'imagen' => $item['imagen_ruta'] ? home_url_publica($item['imagen_ruta']) : '',
            'icono' => 'bi-newspaper'
        );
    }
} catch (Throwable $error) {
    // Una falla de persistencia no debe impedir que cargue el resto del portal.
    error_log('No fue posible cargar las noticias activas: ' . $error->getMessage());
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

// Los filtros nacen de las secciones editoriales de las noticias publicadas.
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
        'IMAGEN_URL' => home_escapar($novedad['imagen']),
        'IMAGEN_ALT' => home_escapar('Imagen de ' . $novedad['titulo']),
        'IMAGEN_CLASE' => $novedad['imagen'] !== '' ? '' : 'd-none',
        'ICONO_CLASE' => $novedad['imagen'] !== '' ? 'd-none' : '',
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

// Solo las buenas ideas locales viajan embebidas en la portada.
$opciones_json = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES |
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$t->set_var('JSON_IDEAS', json_encode($ideas, $opciones_json));

// Datos institucionales y catálogos de oferta.
$catalogos = (new OfertasRepositorio(app_db()))->catalogos();
$crearOpciones = static function (array $items): string {
    $html = '';
    foreach ($items as $item) {
        $html .= '<option value="'.home_escapar($item['codigo']).'">'.home_escapar($item['nombre']).'</option>';
    }
    return $html;
};
$redes = '';
foreach (['facebook'=>'Facebook','instagram'=>'Instagram','youtube'=>'YouTube'] as $red=>$etiqueta) {
    $url = $institucion[$red.'_url'];
    if ($url !== '') $redes .= '<a href="'.home_escapar($url).'" target="_blank" rel="noopener">'.home_escapar($etiqueta).'</a>';
}
$horarios = '';
foreach ($institucion['horarios'] as $turno=>$horario) $horarios .= '<span><strong>'.home_escapar($turno).':</strong> '.home_escapar($horario).'</span>';
$t->set_var([
    'INSTITUCION_NOMBRE' => home_escapar($institucion['nombre']),
    'INSTITUCION_CORTO' => home_escapar($institucion['nombre_corto']),
    'INSTITUCION_SIGLA' => home_escapar($institucion['sigla']),
    'DIRECCION' => home_escapar($institucion['direccion']),
    'ENTORNO' => home_escapar($institucion['entorno']),
    'TELEFONO' => home_escapar($institucion['telefono']),
    'EMAIL' => home_escapar($institucion['email']),
    'CAMPUS_URL' => home_escapar($institucion['campus_url']),
    'PREINSCRIPCION_URL' => home_escapar($institucion['preinscripcion_url']),
    'HERO_IMAGEN' => home_escapar($institucion['hero_imagen']),
    'INSTITUCIONAL_IMAGEN' => home_escapar($institucion['institucional_imagen']),
    'MAPA_URL' => home_escapar($institucion['mapa_url']),
    'HORARIOS' => $horarios,
    'REDES' => $redes !== '' ? $redes : '<span>Próximamente</span>',
    'OPCIONES_TIPO' => $crearOpciones($catalogos['tipo'] ?? []),
    'OPCIONES_TURNO' => $crearOpciones($catalogos['turno'] ?? []),
    'OPCIONES_ESTADO' => $crearOpciones($catalogos['estado'] ?? []),
]);

$t->pparse('OUT', 'home');
?>
