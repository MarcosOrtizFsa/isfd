<?php
declare(strict_types=1);

final class CredentialVault
{
    private string $key;
    public function __construct(?string $secret=null)
    {
        $secret=$secret??(string)getenv('ISFD_CREDENTIAL_KEY');
        if(strlen($secret)<32)throw new RuntimeException('Falta ISFD_CREDENTIAL_KEY o es demasiado corta.');
        $this->key=hash('sha256',$secret,true);
    }
    public function encrypt(string $plain):string
    {
        $iv=random_bytes(12);$tag='';$cipher=openssl_encrypt($plain,'aes-256-gcm',$this->key,OPENSSL_RAW_DATA,$iv,$tag,'isfd-integrations-v1');
        if($cipher===false)throw new RuntimeException('No se pudo cifrar la credencial.');
        return base64_encode($iv.$tag.$cipher);
    }
    public function decrypt(string $encoded):string
    {
        $raw=base64_decode($encoded,true);if($raw===false||strlen($raw)<29)throw new RuntimeException('Credencial cifrada inválida.');$iv=substr($raw,0,12);$tag=substr($raw,12,16);$plain=openssl_decrypt(substr($raw,28),'aes-256-gcm',$this->key,OPENSSL_RAW_DATA,$iv,$tag,'isfd-integrations-v1');
        if($plain===false)throw new RuntimeException('No se pudo descifrar la credencial.');return $plain;
    }
}

function integracion_neuria_configuracion_bd():?array
{
    if(!function_exists('app_db'))return null;
    try{$row=app_db()->one("SELECT * FROM integracion_credenciales WHERE servicio='neuria' AND activa=1 LIMIT 1");if(!$row||empty($row['token_cifrado']))return null;$token=(new CredentialVault())->decrypt((string)$row['token_cifrado']);return ['url'=>rtrim((string)$row['base_url'],'/'),'token'=>$token,'timeout'=>(int)$row['timeout_segundos'],'limit'=>(int)$row['sync_limite'],'max_retries'=>2,'configurada'=>$token!==''&&$row['base_url']!==''];}catch(Throwable){return null;}
}
