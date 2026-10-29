<?php
declare(strict_types=1);

require __DIR__ . '/../lib/template.inc';
require __DIR__ . '/../lib/app.php';

$db = app_db();
$user = app_user();
$requestedModule = trim((string) ($_GET['mod'] ?? ''));
$navigation = '';

if ($user !== null) {
    $modules = $db->all("SELECT id_system_01, system_01_modulo, system_01_path_home FROM system_01_modulos WHERE system_01_estado = 1 AND system_01_onoff = 'on' ORDER BY system_01_orden, system_01_modulo");
    if ($user['mode'] === 0) {
        array_unshift($modules, ['id_system_01' => 1, 'system_01_modulo' => 'Modulos', 'system_01_path_home' => 'root']);
    }
    foreach ($modules as $module) {
        $path = (string) $module['system_01_path_home'];
        if (!in_array($path, ['home', 'control', 'ofertas', 'noticias', 'contactos', 'root'], true) || (in_array($path,['root','contactos'],true) && $user['mode'] !== 0)) {
            continue;
        }
        $navigation .= sprintf('<li class="nav-item"><button class="nav-link btn btn-link" data-module="%s" data-module-id="%d">%s</button></li>', app_escape($path), (int) $module['id_system_01'], app_escape($module['system_01_modulo']));
    }
}

$initialUrl = 'modulos/home/php/home.php';
$initialData = [];
// /mipanel sirve tanto el acceso como el panel autenticado. Una vez iniciada
// la sesión debe cargar el módulo inicial sin cambiar la URL pública del panel.
if ($requestedModule === 'login' && $user === null) {
    $initialUrl = 'modulos/login/php/app_home.php';
} elseif ($requestedModule === 'leer') {
    $initialUrl = 'modulos/home/php/leer.php';
    $initialData['id_system_10'] = filter_input(INPUT_GET, 'id_system_10', FILTER_VALIDATE_INT) ?: 0;
} elseif ($requestedModule === 'oferta') {
    $initialUrl = 'modulos/home/php/oferta_leer.php';
    $initialData['slug'] = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($_GET['slug'] ?? '')));
} elseif ($user !== null && in_array($requestedModule, ['control', 'ofertas', 'noticias', 'contactos', 'root'], true)) {
    if (!in_array($requestedModule,['root','contactos'],true) || $user['mode'] === 0) {
        $initialUrl = 'modulos/' . $requestedModule . '/php/app_home.php';
    }
}

$config = $db->one('SELECT * FROM system_08_configuracion ORDER BY id_system_08 LIMIT 1') ?? [];
$scriptDirectory = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/')));
if (basename($scriptDirectory) !== 'sistema') {
    $scriptDirectory = rtrim($scriptDirectory, '/') . '/sistema';
}
$baseUrl = rtrim($scriptDirectory, '/') . '/';
require_once __DIR__ . '/../lib/social_metadata.php';
$social = portal_social_metadata($db, $requestedModule, $initialData, $config, $baseUrl);
$template = new _template(__DIR__ . '/templates');
$template->set_file(['layout' => 'app_index.html']);
$template->set_var([
    'PAGE_TITLE' => app_escape($social['title']),
    'PAGE_DESCRIPTION' => app_escape($social['description']),
    'SOCIAL_URL' => app_escape($social['url']),
    'SOCIAL_IMAGE' => app_escape($social['image']),
    'SOCIAL_TYPE' => app_escape($social['type']),
    'BASE_URL' => app_escape($baseUrl),
    'CSRF_TOKEN' => app_escape(app_csrf_token()),
    'NAVIGATION' => $navigation,
    'SESSION_CLASS' => $user === null ? 'd-none' : '',
    'USER_NAME' => app_escape($user['name'] ?? ''),
    'INITIAL_URL' => app_escape($initialUrl),
    'INITIAL_DATA' => app_escape(http_build_query($initialData)),
]);
$template->pparse('OUT', 'layout');

