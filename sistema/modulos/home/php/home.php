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
    'OPCIONES_TIPO' => $crearOpciones($catalogos['tipo'] ?? []),
    'OPCIONES_TURNO' => $crearOpciones($catalogos['turno'] ?? []),
    'OPCIONES_ESTADO' => $crearOpciones($catalogos['estado'] ?? []),
]);

$t->set_var(portal_institucional_variables($institucion));
require_once __DIR__.'/../../../../lib/videos_institucionales.php';
$videosInstitucionales=institucional_videos_publicos($institucion['videos_json']??'[]');
$videoGaleria='';
if ($videosInstitucionales) {
    $first=$videosInstitucionales[0];
    $single=count($videosInstitucionales)===1;
    $videoGaleria='<div class="institutional-videos" data-institutional-videos="'.home_escapar(json_encode($videosInstitucionales,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)).'" data-index="0">'
        .'<div class="ratio ratio-16x9" data-institutional-player><iframe src="'.home_escapar($first['url']).'" title="'.home_escapar($first['titulo']).'" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen loading="lazy"></iframe></div>'
        .'<div class="institutional-video-caption"><h3 class="h6 mb-1" data-institutional-title>'.home_escapar($first['titulo']).'</h3><a data-institutional-external href="'.home_escapar($first['url']).'" target="_blank" rel="noopener noreferrer">Abrir video aparte</a></div>'
        .'<div class="institutional-video-controls'.($single?' d-none':'').'"><button type="button" class="btn btn-outline-dark btn-sm" data-institutional-step="-1" aria-label="Video anterior">← Anterior</button><span data-institutional-counter role="status" aria-live="polite">1 / '.count($videosInstitucionales).'</span><button type="button" class="btn btn-outline-dark btn-sm" data-institutional-step="1" aria-label="Video siguiente">Siguiente →</button></div></div>';
} else {
    $videoGaleria='<div class="isfd-media" style="background-image:url(\''.home_escapar($institucion['institucional_imagen']).'\')"><span>Conocé el instituto</span></div>';
}
$t->set_var('VIDEOS_INSTITUCIONALES',$videoGaleria);
$historiaHtml = implode('', array_map(static fn($p)=>'<p>'.home_escapar($p).'</p>', $historiaInstitucional));
$autoridadesHtml = '';
require_once __DIR__.'/../../../../lib/autoridades.php';
foreach (app_db()->all('SELECT * FROM institucional_autoridades WHERE visible=1 ORDER BY orden,id') as $persona) {
    $nombre = trim($persona['nombre'].' '.$persona['apellido']);
    $initials = mb_strtoupper(mb_substr($persona['nombre'],0,1).mb_substr($persona['apellido'],0,1));
    $media = $persona['imagen_url'] !== ''
        ? '<img src="'.home_escapar($persona['imagen_url']).'" alt="'.home_escapar($nombre).'" loading="lazy">'
        : '<span class="authority-initials" aria-hidden="true">'.home_escapar($initials).'</span>';
    try { $videoUrl=autoridad_video($persona['video_url'] ?: $persona['youtube_codigo']); }
    catch (InvalidArgumentException $error) { $videoUrl=''; }
    $video = $videoUrl !== ''
        ? '<button type="button" class="authority-play" data-authority-video="'.home_escapar($videoUrl).'" data-authority-name="'.home_escapar($nombre).'" aria-label="Ver saludo de '.home_escapar($nombre).'"><i class="bi bi-play-fill" aria-hidden="true"></i></button>' : '';
    $autoridadesHtml .= '<article class="authority-card"><div class="authority-media">'.$media.$video.'</div><div class="authority-body"><p class="authority-role">'.home_escapar($persona['cargo']).'</p><h3>'.home_escapar($nombre).'</h3><p class="authority-profession">'.home_escapar($persona['profesion']).'</p><p class="authority-description">'.nl2br(home_escapar($persona['descripcion'])).'</p></div></article>';
}
$t->set_var([
    'HERO_ETIQUETA'=>home_escapar($institucion['hero_etiqueta']??''),
    'HERO_TITULO'=>home_escapar($institucion['hero_titulo']??''),
    'HERO_NOMBRE'=>home_escapar($institucion['hero_nombre']??''),
    'HERO_DESCRIPCION'=>home_escapar($institucion['hero_descripcion']??''),
    'HISTORIA_TITULO'=>home_escapar($institucion['historia_titulo']??''),
    'HISTORIA_RESUMEN'=>nl2br(home_escapar($institucion['historia_resumen']??'')),
    'HISTORIA_COMPLETA'=>$historiaHtml,
    'AUTORIDADES'=>$autoridadesHtml,
    'AUTORIDADES_CLASE'=>$autoridadesHtml===''?'d-none':'',
]);
$t->pparse('OUT', 'home');
?>
