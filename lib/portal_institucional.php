<?php
declare(strict_types=1);

/** Solo datos públicos: excluye correo de alertas y parámetros internos. */
function portal_institucional_configurar(array $defaults): array
{
    $config = app_db()->one('SELECT * FROM system_08_configuracion ORDER BY id_system_08 LIMIT 1') ?? [];
    // Identidad, contacto, horarios y textos provienen del repositorio versionado.
    foreach (['facebook', 'twitter', 'instagram', 'youtube', 'tiktok'] as $network) {
        $url = trim((string) ($config['system_08_' . $network] ?? ''));
        $defaults[$network . '_url'] = portal_institucional_url($url) ? $url : '';
    }
    // El campo versionado tiene prioridad sobre la configuración anterior del mapa.
    $map = trim((string) ($defaults['mapa_url'] ?? $config['system_08_mapsgoogle'] ?? ''));
    if (preg_match('/src=["\']([^"\']+)["\']/i', $map, $match)) $map = html_entity_decode($match[1], ENT_QUOTES, 'UTF-8');
    $host = strtolower((string) parse_url($map, PHP_URL_HOST));
    if (portal_institucional_url($map) && in_array($host, ['www.google.com', 'maps.google.com', 'www.google.com.ar'], true) && str_contains((string) parse_url($map, PHP_URL_PATH), '/maps/embed')) {
        $defaults['mapa_url'] = $map;
    }
    return $defaults;
}

function portal_institucional_url(string $url): bool
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
}

/** HTML compartido por portada y vistas de detalle; todos los valores se escapan. */
function portal_institucional_variables(array $data): array
{
    $socials = '';
    foreach (['facebook'=>'Facebook', 'instagram'=>'Instagram', 'youtube'=>'YouTube', 'twitter'=>'Twitter / X', 'tiktok'=>'TikTok'] as $network => $label) {
        $icon = $network === 'twitter' ? 'twitter-x' : $network;
        $url = $data[$network . '_url'] ?? '';
        if ($url !== '') {
            $socials .= '<a href="'.app_escape($url).'" target="_blank" rel="noopener noreferrer" aria-label="'.$label.'" title="'.$label.'"><i class="bi bi-'.$icon.'" aria-hidden="true"></i></a>';
        } else {
            $socials .= '<span aria-label="'.$label.' próximamente" title="'.$label.' próximamente"><i class="bi bi-'.$icon.'" aria-hidden="true"></i></span>';
        }
    }
    $contacts = '';
    $mobile = trim((string) ($data['celular'] ?? ''));
    if ($mobile !== '') $contacts .= '<p class="mb-2">Celular: <a href="tel:'.app_escape(preg_replace('/[^0-9+]/', '', $mobile)).'">'.app_escape($mobile).'</a></p>';
    $number = preg_replace('/\D/', '', (string) ($data['whatsapp'] ?? ''));
    $whatsapp = '';
    if (preg_match('/^[1-9][0-9]{7,14}$/', $number)) {
        $url = 'https://wa.me/' . $number;
        $contacts .= '<p class="mb-2"><a href="'.$url.'" target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp me-1"></i>WhatsApp: '.app_escape($data['whatsapp']).'</a></p>';
        $whatsapp = '<a class="isfd-chat" href="'.$url.'" target="_blank" rel="noopener noreferrer" aria-label="Contactar por WhatsApp" title="Contactar por WhatsApp"><i class="bi bi-whatsapp"></i></a>';
    }
    if (!empty($data['contactos_visibles'])) $contacts .= '<p>'.nl2br(app_escape($data['contactos_visibles'])).'</p>';
    return ['REDES'=>$socials, 'CONTACTOS_ADICIONALES'=>$contacts, 'WHATSAPP_BOTON'=>$whatsapp, 'PIE_TEXTO'=>app_escape($data['pie_texto']??'')];
}
