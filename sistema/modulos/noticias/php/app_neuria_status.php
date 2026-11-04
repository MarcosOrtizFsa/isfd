<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
require_once __DIR__.'/neuria_v1_cliente.php';
app_require_mode([0,1,2]);
app_verify_csrf();
header('Cache-Control: no-store');

/** Solo devuelve información pública de diagnóstico; nunca expone URL, token ni respuesta cruda. */
function noticias_neuria_estado(string $estado, string $mensaje): void
{
    app_json(['estado'=>$estado, 'mensaje'=>$mensaje, 'verificado_at'=>date(DATE_ATOM)]);
}

try {
    $credencial = app_db()->one("SELECT activa FROM integracion_credenciales WHERE servicio='neuria' LIMIT 1");
    if ($credencial && !(int)$credencial['activa']) {
        noticias_neuria_estado('desactivada', 'La integración está desactivada en la configuración de Neuria.');
    }
    $config = neuria_v1_configuracion();
    if (empty($config['configurada'])) {
        noticias_neuria_estado('sin_configurar', 'No hay credenciales disponibles. Revisá la configuración de Neuria.');
    }
    // Comprobación breve e independiente de la sincronización y sus reintentos.
    $config['timeout'] = min(5, max(1, (int)$config['timeout']));
    $config['max_retries'] = 0;
    session_write_close(); // No bloquea otras acciones del administrador mientras espera la API.
    $resultado = (new NeuriaV1Cliente($config))->identidad();
    if ($resultado['ok']) {
        noticias_neuria_estado('conectada', 'NEURIA respondió correctamente y aceptó las credenciales.');
    }
    $mensajes = [
        'invalid_credentials'=>'NEURIA rechazó las credenciales. Revisá el token.',
        'forbidden'=>'NEURIA rechazó el acceso. Revisá los permisos de la credencial.',
        'not_found'=>'No se encontró el endpoint de NEURIA. Revisá la URL configurada.',
        'https_required'=>'La conexión a NEURIA requiere una URL HTTPS.',
        'rate_limit_exceeded'=>'NEURIA alcanzó el límite de consultas. Volvé a intentar más tarde.',
        'invalid_response'=>'NEURIA devolvió una respuesta inesperada.',
    ];
    noticias_neuria_estado('error', $mensajes[$resultado['error']] ?? 'No se pudo comprobar la conexión con NEURIA. Puede estar temporalmente fuera de servicio.');
} catch (Throwable $error) {
    error_log('Estado NEURIA: '.$error->getMessage());
    noticias_neuria_estado('error', 'No se pudo verificar la integración. Revisá la configuración y el registro del servidor.');
}
