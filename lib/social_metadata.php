<?php
declare(strict_types=1);

/** Metadatos disponibles sin JavaScript para los rastreadores de redes sociales. */
function portal_social_metadata(AppDatabase $db, string $module, array $data, array $config, string $baseUrl): array
{
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'isfd.com.ar');
    $local = preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/i', $host) === 1;
    $origin = $local ? 'http://' . $host : 'https://isfd.com.ar';
    $root = rtrim(dirname(rtrim($baseUrl, '/')), '/.');
    $siteUrl = $origin . $root;
    $meta = [
        'title' => $config['system_08_titulo_site'] ?? 'ISFD Félix Atilio Cabrera',
        'description' => $config['system_08_descripcion_site'] ?? 'Formación Docente Continua y Técnica en Formosa.',
        'url' => $siteUrl . '/',
        'image' => $siteUrl . '/image/logo.png',
        'type' => 'website',
    ];
    $item = null;
    if ($module === 'leer') {
        $item = $db->one(
            "SELECT n.titulo, n.bajada AS resumen,
                (SELECT a.ruta FROM noticia_archivos a WHERE a.noticia_id=n.id
                 AND a.tipo='imagen' AND a.deleted_at IS NULL ORDER BY a.orden,a.id LIMIT 1) AS imagen_url
             FROM noticias n WHERE n.id=? AND n.estado='activa' AND n.deleted_at IS NULL
             AND (n.publicacion_at IS NULL OR n.publicacion_at<=NOW()) LIMIT 1",
            'i', [(int) ($data['id_system_10'] ?? 0)]
        );
        if ($item) {
            $slug = strtolower((string) iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $item['titulo']));
            $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', $slug), '-') ?: 'noticia';
            $meta['url'] = $siteUrl . '/nota/' . (int) $data['id_system_10'] . '/' . $slug;
            $meta['type'] = 'article';
        }
    } elseif ($module === 'oferta') {
        require_once __DIR__ . '/../sistema/modulos/home/php/ofertas_repositorio.php';
        $item = (new OfertasRepositorio($db))->buscarPorSlug((string) ($data['slug'] ?? ''));
        if ($item) $meta['url'] = $siteUrl . '/oferta/' . rawurlencode($item['slug']);
    }
    if ($item) {
        $meta['title'] = $item['titulo'];
        $meta['description'] = trim((string) $item['resumen']) ?: $meta['description'];
        $image = trim((string) ($item['imagen_url'] ?? ''));
        if (preg_match('#^https?://#i', $image)) {
            $meta['image'] = $image;
        } elseif ($image !== '') {
            // Las noticias guardan rutas relativas; las ofertas también admiten rutas públicas.
            $meta['image'] = str_starts_with($image, '/')
                ? $origin . $image
                : $siteUrl . '/' . preg_replace('#^(\.\./)+#', '', $image);
        }
    }
    $meta['description'] = mb_substr(trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $meta['description']), ENT_QUOTES, 'UTF-8'))), 0, 240);
    return $meta;
}
