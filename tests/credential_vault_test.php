<?php
declare(strict_types=1);
require __DIR__.'/../lib/credential_vault.php';
function cv(bool $ok,string $message):void{if(!$ok){fwrite(STDERR,"FALLO: $message\n");exit(1);}echo "OK: $message\n";}
$vault=new CredentialVault(str_repeat('clave-segura-',4));$cipher=$vault->encrypt('credencial-ficticia');
cv($cipher!=='credencial-ficticia','credencial no almacenada en texto plano');
cv($vault->decrypt($cipher)==='credencial-ficticia','credencial cifrada recuperable con clave maestra');
try{(new CredentialVault(str_repeat('otra-clave-',4)))->decrypt($cipher);cv(false,'clave incorrecta rechazada');}catch(RuntimeException){cv(true,'clave incorrecta rechazada');}
