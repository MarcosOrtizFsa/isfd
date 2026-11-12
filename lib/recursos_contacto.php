<?php
declare(strict_types=1);

/** Solo destinos web absolutos; nunca javascript, HTML, credenciales o archivos locales. */
function recurso_contacto_validar(array $input): array
{
    $tema = trim(is_string($input['tema'] ?? null) ? $input['tema'] : '');
    $url = trim(is_string($input['url'] ?? null) ? $input['url'] : '');
    $parts = parse_url($url);
    if ($tema === '' || mb_strlen($tema) > 160) throw new InvalidArgumentException('Indicá un tema de hasta 160 caracteres.');
    if (strlen($url) > 1000 || !filter_var($url,FILTER_VALIDATE_URL) || !$parts ||
        !in_array(strtolower($parts['scheme'] ?? ''),['http','https'],true) ||
        isset($parts['user']) || isset($parts['pass'])) {
        throw new InvalidArgumentException('Ingresá una URL completa y válida, comenzando con https:// o http://.');
    }
    $orden = filter_var($input['orden'] ?? 0,FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>65535]]);
    if ($orden === false) throw new InvalidArgumentException('El orden debe ser un número entre 0 y 65535.');
    return ['tema'=>$tema,'url'=>$url,'orden'=>$orden,'visible'=>isset($input['visible']) ? 1 : 0];
}

/** Reencodifica sin recortar ni reducir dimensiones: preserva la legibilidad del QR. */
function recurso_contacto_imagen(array $file): array
{
    if ((int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
        !is_uploaded_file($file['tmp_name'] ?? '') || (int)$file['size'] > 5242880) {
        throw new InvalidArgumentException('Subí una imagen de hasta 5 MB.');
    }
    $info = getimagesize($file['tmp_name']);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!$info || !in_array($mime,['image/png','image/jpeg','image/webp'],true) || $info[0]*$info[1] > 12000000) {
        throw new InvalidArgumentException('Usá PNG, JPG o WebP de hasta 12 megapíxeles. Para QR recomendamos PNG.');
    }
    if (!function_exists('imagecreatefromstring')) throw new RuntimeException('GD no está disponible.');
    $image = imagecreatefromstring(file_get_contents($file['tmp_name']));
    if (!$image) throw new InvalidArgumentException('No se pudo leer la imagen.');
    $directory = dirname(__DIR__).'/uploads/recursos_contacto';
    $path = null;
    try {
        if (!is_dir($directory) && !mkdir($directory,0755,true)) throw new RuntimeException('No se pudo preparar el directorio de imágenes.');
        imagesavealpha($image,true);
        $name = bin2hex(random_bytes(18)).'.png';
        $path = $directory.'/'.$name;
        if (!imagepng($image,$path,6)) throw new RuntimeException('No se pudo guardar la imagen.');
        return ['path'=>$path,'url'=>'../uploads/recursos_contacto/'.$name];
    } catch (Throwable $error) {
        if ($path && is_file($path)) unlink($path);
        throw $error;
    } finally {
        imagedestroy($image);
    }
}

function recursos_contacto_html(AppDatabase $db): string
{
    try {
        $items = $db->all('SELECT id,tema,url,imagen_url FROM institucional_recursos_contacto WHERE visible=1 AND deleted_at IS NULL ORDER BY orden,id');
    } catch (Throwable $error) {
        error_log('Recursos de contacto: '.$error->getMessage());
        return '';
    }
    if (!$items) return '';
    $total = count($items);
    $html = '<section class="contact-resources mt-4" aria-label="Recursos y asistentes institucionales"><div id="contact-resource-carousel" class="carousel slide" data-bs-interval="false"><div class="carousel-inner">';
    foreach ($items as $index=>$item) {
        $tema = app_escape($item['tema']);
        $url = app_escape($item['url']);
        $image = app_escape($item['imagen_url']);
        $html .= '<div class="carousel-item'.($index === 0 ? ' active' : '').'"><article class="contact-resource-card"><a class="contact-resource-image" href="'.$url.'" target="_blank" rel="noopener noreferrer" data-contact-resource aria-label="Abrir '.$tema.' en otra ventana"><img src="'.$image.'" alt="Imagen o código QR de '.$tema.'" loading="lazy"></a><div class="contact-resource-copy"><p class="isfd-kicker">Conectá con el instituto</p><h3>'.$tema.'</h3><p>Escaneá el QR, si la imagen lo incluye, o abrí el enlace.</p><a class="btn btn-outline-dark btn-sm" href="'.$url.'" target="_blank" rel="noopener noreferrer" data-contact-resource>Abrir enlace <i class="bi bi-box-arrow-up-right ms-1" aria-hidden="true"></i><span class="visually-hidden"> en otra ventana</span></a></div></article><p class="small text-secondary text-center mb-0 mt-3">'.($index+1).' de '.$total.'</p></div>';
    }
    $html .= '</div>';
    if ($total > 1) {
        $html .= '<div class="d-flex justify-content-between gap-2 mt-3"><button type="button" class="btn btn-outline-secondary btn-sm" data-bs-target="#contact-resource-carousel" data-bs-slide="prev" aria-label="Recurso anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i> Anterior</button><button type="button" class="btn btn-outline-secondary btn-sm" data-bs-target="#contact-resource-carousel" data-bs-slide="next" aria-label="Recurso siguiente">Siguiente <i class="bi bi-chevron-right" aria-hidden="true"></i></button></div>';
    }
    return $html.'</div></section>';
}
