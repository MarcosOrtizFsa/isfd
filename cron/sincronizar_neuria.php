<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/../sistema/modulos/noticias/php/neuria_coordinador.php';
// Incluye Piloto Automático aunque no existan solicitudes originadas en ISFD.
try {
    $result = (new NeuriaCoordinador(app_db(),new NeuriaV1Cliente()))->sincronizar();
    echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['ok'] ? 0 : 1);
} catch (Throwable $error) {
    fwrite(STDERR,$error->getMessage().PHP_EOL);
    exit(1);
}
