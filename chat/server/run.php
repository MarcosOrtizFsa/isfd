<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
ini_set('memory_limit','128M');
if (!is_file(__DIR__.'/vendor/autoload.php')) {
    fwrite(STDERR,"Faltan dependencias. Ejecutá composer install --no-dev en chat/server.\n"); exit(1);
}
require __DIR__.'/vendor/autoload.php';
require __DIR__.'/../lib/actions.php';
require __DIR__.'/ChatServer.php';
// El proceso no mantiene una sesión PHP abierta: autentica con credenciales temporales de BD.
session_write_close();
try {
    $config=chat_ws_config();
    $loop=React\EventLoop\Loop::get();
    $application=new ChatServer(app_db(),$loop,$config['origins']);
    $websocket=new Ratchet\WebSocket\WsServer($application);
    $websocket->enableKeepAlive($loop,25);
    $socket=new React\Socket\SocketServer($config['listen'],[], $loop);
    $server=new Ratchet\Server\IoServer(new Ratchet\Http\HttpServer($websocket),$socket,$loop);
    echo 'Chat WebSocket escuchando en '.$config['listen'].PHP_EOL;
    echo 'Orígenes permitidos: '.implode(', ',$config['origins']).PHP_EOL;
    echo "Para detener: Ctrl+C. El chat continuará por AJAX.\n";
    $loop->run();
} catch (Throwable $e) {
    fwrite(STDERR,'No se pudo iniciar WebSocket: '.$e->getMessage().PHP_EOL); exit(1);
}
