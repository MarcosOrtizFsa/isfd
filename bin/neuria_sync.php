<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/../sistema/modulos/noticias/php/neuria_sync.php';
$accion=$argv[1]??'all';
if(!in_array($accion,['all','contents','changes','me'],true)){fwrite(STDERR,"Uso: php bin/neuria_sync.php [all|contents|changes|me]\n");exit(2);}
$lock=fopen(sys_get_temp_dir().DIRECTORY_SEPARATOR.'isfd_neuria_sync.lock','c');
if(!$lock||!flock($lock,LOCK_EX|LOCK_NB)){fwrite(STDERR,"Ya existe una sincronizacion de Neuria en curso.\n");exit(3);}
try{
    $client=new NeuriaV1Cliente();if(!$client->estaConfigurado())throw new RuntimeException('Faltan NEURIA_API_URL y NEURIA_API_TOKEN.');
    if($accion==='me'){$r=$client->identidad();if(!$r['ok'])throw new RuntimeException('No se pudo verificar Neuria: '.$r['error']);$d=is_array($r['data'])?$r['data']:[];echo json_encode(['medio'=>$d['media']['name']??$d['name']??$d['media_name']??null,'organizacion'=>$d['organization']['name']??$d['organization']??$d['organization_name']??null,'permisos'=>$d['credential']['scopes']??$d['permissions']??[],'vencimiento'=>$d['credential']['expires_at']??$d['expires_at']??null,'canales'=>$d['media']['channels']??[]],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;exit(0);}
    $sync=new NeuriaSincronizador(app_db(),$client);$result=$accion==='contents'?$sync->sincronizarContenidos():($accion==='changes'?$sync->sincronizarCambios():$sync->sincronizarTodo());echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
    $ok=$accion==='all'?(($result['contenidos']['ok']??false)&&($result['cambios']['ok']??false)):($result['ok']??false);exit($ok?0:1);
}catch(Throwable $e){fwrite(STDERR,$e->getMessage().PHP_EOL);exit(1);}finally{if(is_resource($lock)){flock($lock,LOCK_UN);fclose($lock);}}
