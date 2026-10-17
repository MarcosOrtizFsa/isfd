<?php
declare(strict_types=1);
require __DIR__ . '/../../../../lib/app.php';
app_verify_csrf();

$attempts = (int) ($_SESSION['login_attempts'] ?? 0);
$blockedUntil = (int) ($_SESSION['login_blocked_until'] ?? 0);
if ($blockedUntil > time()) {
    app_json(['ok' => false, 'message' => 'Demasiados intentos. Espera unos minutos.'], 429);
}

$username = preg_replace('/[^0-9A-Za-z._@-]/', '', app_post('usuario'));
$password = app_post('clave');
if ($username === '' || $password === '') {
    app_json(['ok' => false, 'message' => 'Completa usuario y clave.'], 422);
}

$db = app_db();
$account = $db->one(
    'SELECT u.*, p.system_04_nombre, p.system_04_apellido FROM system_03_usuarios u LEFT JOIN system_04_perfil p ON p.rela_system_03 = u.id_system_03 WHERE u.system_03_usuario = ? LIMIT 1',
    's',
    [$username]
);
$valid = false;
if ($account !== null && (int) $account['system_03_estado'] === 1) {
    $stored = (string) $account['system_03_clave'];
    $valid = password_verify($password, $stored);
    // Migracion transparente: una clave SHA-1 valida se convierte al formato moderno.
    if (!$valid && preg_match('/^[a-f0-9]{40}$/i', $stored) && hash_equals(strtolower($stored), sha1($password))) {
        $valid = true;
        $db->execute('UPDATE system_03_usuarios SET system_03_clave = ? WHERE id_system_03 = ?', 'si', [password_hash($password, PASSWORD_DEFAULT), (int) $account['id_system_03']]);
    }
}
if (!$valid) {
    $_SESSION['login_attempts'] = ++$attempts;
    if ($attempts >= 5) {
        $_SESSION['login_blocked_until'] = time() + 300;
        $_SESSION['login_attempts'] = 0;
    }
    app_json(['ok' => false, 'message' => 'Usuario o clave incorrectos.'], 401);
}

session_regenerate_id(true);
$_SESSION['login_attempts'] = 0;
$_SESSION['login_blocked_until'] = 0;
$_SESSION['user_id'] = (int) $account['id_system_03'];
$_SESSION['role_id'] = (int) $account['rela_system_07'];
$_SESSION['site_id'] = (int) $account['rela_system_06'];
$_SESSION['user_mode'] = (int) $account['system_03_modo'];
$_SESSION['user_name'] = trim((string) $account['system_04_nombre'] . ' ' . (string) $account['system_04_apellido']) ?: $username;
app_log('LOGIN', 'Inicio de sesion');
app_json(['ok' => true]);

