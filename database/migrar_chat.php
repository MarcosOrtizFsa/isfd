<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../lib/app.php';
foreach (['018_chat_institucional_up.sql','019_chat_websocket_up.sql'] as $migration) {
    $sql = preg_replace('/^--.*$/m', '', file_get_contents(__DIR__ . '/migrations/' . $migration));
    foreach (explode(';', $sql) as $statement) {
        if (trim($statement) !== '') app_db()->execute($statement);
    }
}
echo "Chat instalado: tablas, cinco salas y 512 avatares disponibles.\n";
