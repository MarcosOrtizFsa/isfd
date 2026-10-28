<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/../sistema/modulos/noticias/php/neuria_requests.php';
$lock=fopen(sys_get_temp_dir().DIRECTORY_SEPARATOR.'isfd_neuria_requests.lock','c');if(!$lock||!flock($lock,LOCK_EX|LOCK_NB)){fwrite(STDERR,"Sincronización ya iniciada.\n");exit(3);}
try{$client=new NeuriaV1Cliente();if(!$client->estaConfigurado())throw new RuntimeException('Falta configuración de Neuria.');$result=(new NeuriaSolicitudes(app_db(),$client))->sincronizarPendientes();echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($result['ok']?0:1);}catch(Throwable $e){fwrite(STDERR,$e->getMessage().PHP_EOL);exit(1);}finally{if(is_resource($lock)){flock($lock,LOCK_UN);fclose($lock);}}
