<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
require __DIR__.'/../lib/app.php';
$sql=preg_replace('/^--.*$/m','',file_get_contents(__DIR__.'/migrations/019_chat_websocket_up.sql'));
foreach(explode(';',$sql) as $statement) if(trim($statement)!=='') app_db()->execute($statement);
echo "Credenciales temporales y revisión WebSocket instaladas.\n";
