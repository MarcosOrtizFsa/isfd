<?php
declare(strict_types=1);
require __DIR__.'/../../lib/app.php';
$context=preg_replace('/[^a-z]/','',(string)($_GET['context']??'contacto'));
if(!in_array($context,['contacto','oferta'],true))$context='contacto';
$alphabet='23456789ABCDEFGHJKLMNPQRSTUVWXYZ';$code='';
for($i=0;$i<5;$i++)$code.=$alphabet[random_int(0,strlen($alphabet)-1)];
$_SESSION['portal_captcha_'.$context]=['hash'=>password_hash($code,PASSWORD_DEFAULT),'expires'=>time()+600];
header('Content-Type: image/png');header('Cache-Control: no-store, no-cache, must-revalidate');
$image=imagecreatetruecolor(150,48);$background=imagecolorallocate($image,255,255,255);$text=imagecolorallocate($image,55,24,24);$noise=imagecolorallocate($image,205,174,174);$border=imagecolorallocate($image,153,51,51);
imagefilledrectangle($image,0,0,149,47,$background);
// El texto se dibuja en una capa pequeña y se amplía al doble para conservar
// compatibilidad sin depender de fuentes instaladas en el servidor.
$layer=imagecreatetruecolor(75,24);$layerBackground=imagecolorallocate($layer,255,255,255);$layerText=imagecolorallocate($layer,55,24,24);imagefilledrectangle($layer,0,0,74,23,$layerBackground);imagestring($layer,5,15,4,$code,$layerText);imagecopyresampled($image,$layer,0,0,0,0,150,48,75,24);imagedestroy($layer);
for($i=0;$i<18;$i++)imagesetpixel($image,random_int(2,147),random_int(2,45),$noise);
imageline($image,0,4,149,1,$noise);imageline($image,0,45,149,42,$noise);imagerectangle($image,0,0,149,47,$border);
imagepng($image);imagedestroy($image);
