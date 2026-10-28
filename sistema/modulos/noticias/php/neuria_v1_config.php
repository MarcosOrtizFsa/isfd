<?php
declare(strict_types=1);

function neuria_v1_cargar_env(): void
{
    static $cargado=false;if($cargado)return;$cargado=true;
    $archivo=dirname(__DIR__,4).'/.env';
    if(!is_file($archivo)||!is_readable($archivo))return;
    foreach(file($archivo,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES)?:[] as $linea){
        $linea=trim($linea);if($linea===''||str_starts_with($linea,'#')||!str_contains($linea,'='))continue;
        [$clave,$valor]=array_map('trim',explode('=',$linea,2));
        if($clave===''||getenv($clave)!==false)continue;
        $valor=trim($valor," \t\n\r\0\x0B\"'");putenv($clave.'='.$valor);$_ENV[$clave]=$valor;
    }
}

function neuria_v1_configuracion(): array
{
    neuria_v1_cargar_env();require_once dirname(__DIR__,4).'/lib/credential_vault.php';$databaseConfig=integracion_neuria_configuracion_bd();if($databaseConfig!==null)return $databaseConfig;$url=rtrim(trim((string)getenv('NEURIA_API_URL')),'/');$token=trim((string)getenv('NEURIA_API_TOKEN'));
    return ['url'=>$url,'token'=>$token,'timeout'=>max(3,min(60,(int)(getenv('NEURIA_API_TIMEOUT')?:30))),'limit'=>max(1,min(100,(int)(getenv('NEURIA_SYNC_LIMIT')?:50))),'max_retries'=>2,'configurada'=>$url!==''&&$token!==''];
}
