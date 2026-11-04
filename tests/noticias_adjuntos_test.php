<?php
declare(strict_types=1);
// Prueba HTTP local: crea una nota pendiente identificada y la retira en finally.
if (PHP_SAPI!=='cli') exit;
ob_start(); // Permite cerrar la sesión temporal después de informar resultados.
require __DIR__.'/../lib/app.php';
require __DIR__.'/../sistema/modulos/noticias/php/adjuntos_soporte.php';
function adjunto_check(bool $ok,string $mensaje): void {
    if (!$ok) throw new RuntimeException($mensaje);
    echo 'OK: '.$mensaje.PHP_EOL;
}
$db=app_db();
$usuario=$db->one('SELECT id_system_03,rela_system_07,rela_system_06,system_03_modo FROM system_03_usuarios WHERE system_03_modo=0 LIMIT 1');
if (!$usuario) throw new RuntimeException('La prueba necesita un administrador local existente.');
$_SESSION['user_id']=(int)$usuario['id_system_03'];
$_SESSION['user_mode']=0;
$_SESSION['role_id']=(int)$usuario['rela_system_07'];
$_SESSION['site_id']=(int)$usuario['rela_system_06'];
$csrf=app_csrf_token();$sesion=session_id();session_write_close();
$base='http://localhost/isfd.com.ar/sistema/modulos/noticias/php/';
$peticion=function(string $endpoint,array $datos,bool $multipart=false) use ($base,$sesion,$csrf): array {
    $datos['csrf_token']=$csrf;
    $curl=curl_init($base.$endpoint);
    curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$multipart?$datos:http_build_query($datos),CURLOPT_COOKIE=>'ISFDSESSID='.$sesion,CURLOPT_TIMEOUT=>20]);
    $body=curl_exec($curl);$status=(int)curl_getinfo($curl,CURLINFO_HTTP_CODE);curl_close($curl);
    if (in_array($endpoint,['app_form.php','app_adjuntos.php'],true)) return ['html'=>(string)$body];
    $json=json_decode((string)$body,true);
    if (!is_array($json)) throw new RuntimeException('Respuesta HTTP no JSON: '.$status);
    return $json;
};
$marca='Prueba adjuntos '.bin2hex(random_bytes(8));$id=0;$temporales=[];
try {
    adjunto_check(noticia_youtube('https://www.youtube.com/watch?feature=share&v=dQw4w9WgXcQ')==='dQw4w9WgXcQ' && noticia_youtube('https://falso.test/youtube.com/watch?v=dQw4w9WgXcQ')===null,'Valida dominio y código de YouTube');
    $r=$peticion('app_adjuntos_action.php',['action'=>'video_save','noticia_id'=>0,'youtube'=>'dQw4w9WgXcQ']);
    adjunto_check(!$r['ok'],'Adjuntos bloqueados sin nota guardada');
    $nuevo=$peticion('app_form.php',[])['html'];
    adjunto_check(str_contains($nuevo,'Guardá primero el texto') && !str_contains($nuevo,'type="file"'),'Formulario nuevo sin carga prematura de archivos');
    $texto=['action'=>'save','titulo'=>$marca,'contenido'=>"Texto inicial\n\nSegundo párrafo.",'estado'=>'pendiente'];
    $r=$peticion('app_action.php',$texto);$id=(int)($r['id']??0);
    adjunto_check(!empty($r['ok']) && $id>0,'Texto guardado antes de adjuntar');
    $png=tempnam(sys_get_temp_dir(),'isfd-img-');$temporales[]=$png;
    $imagen=imagecreatetruecolor(20,20);imagepng($imagen,$png);imagedestroy($imagen);
    $pdf=tempnam(sys_get_temp_dir(),'isfd-pdf-');$temporales[]=$pdf;file_put_contents($pdf,"%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");
    foreach ([['imagen',$png,'image/png','prueba.png'],['adjunto',$pdf,'application/pdf','prueba.pdf']] as [$tipo,$ruta,$mime,$nombre]) {
        $r=$peticion('app_adjuntos_action.php',['action'=>'upload','noticia_id'=>$id,'tipo'=>$tipo,'archivos[0]'=>new CURLFile($ruta,$mime,$nombre)],true);
        adjunto_check(!empty($r['ok']),'Carga independiente: '.$tipo);
    }
    foreach (['docx'=>'word/document.xml','xlsx'=>'xl/workbook.xml'] as $extension=>$entrada) {
        $ruta=tempnam(sys_get_temp_dir(),'isfd-office-');$temporales[]=$ruta;
        $zip=new ZipArchive();$zip->open($ruta,ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml','<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>');
        $zip->addFromString($entrada,'<?xml version="1.0"?><document/>');$zip->close();
        $r=$peticion('app_adjuntos_action.php',['action'=>'upload','noticia_id'=>$id,'tipo'=>'adjunto','archivos[0]'=>new CURLFile($ruta,'application/zip','prueba.'.$extension)],true);
        adjunto_check(!empty($r['ok']),'Reconoce documento Office '.$extension);
    }
    $r=$peticion('app_adjuntos_action.php',['action'=>'upload','noticia_id'=>$id,'tipo'=>'imagen','archivos[0]'=>new CURLFile($pdf,'application/pdf','prueba.pdf')],true);
    adjunto_check(!$r['ok'],'Documento rechazado en panel de imágenes');
    $r=$peticion('app_adjuntos_action.php',['action'=>'video_save','noticia_id'=>$id,'youtube'=>'https://youtu.be/dQw4w9WgXcQ']);
    adjunto_check(!empty($r['ok']),'Video guardado por separado');
    $html=$peticion('app_form.php',['id'=>$id])['html'];
    $dom=new DOMDocument();@$dom->loadHTML('<?xml encoding="UTF-8">'.$html);$xpath=new DOMXPath($dom);
    adjunto_check($xpath->query('//form[@id="news-form"]//input[@type="file" or @name="youtube"]')->length===0 && $xpath->query('//div[@id="news-attachments-panel"]//img')->length===1 && $xpath->query('//div[@id="news-attachments-panel"]//iframe')->length===1,'Paneles separados con vista previa de imagen y YouTube');
    $r=$peticion('app_action.php',array_replace($texto,['id'=>$id,'contenido'=>'Texto actualizado.']));
    $fila=$db->one('SELECT contenido,youtube_codigo FROM noticias WHERE id=?','i',[$id]);
    adjunto_check(!empty($r['ok']) && $fila['youtube_codigo']==='dQw4w9WgXcQ' && (int)$db->one('SELECT COUNT(*) n FROM noticia_archivos WHERE noticia_id=? AND deleted_at IS NULL','i',[$id])['n']===4,'Guardar texto conserva imagen, documentos y video');
    $archivo=$db->one('SELECT id FROM noticia_archivos WHERE noticia_id=? LIMIT 1','i',[$id]);
    $r=$peticion('app_adjuntos_action.php',['action'=>'file_remove','noticia_id'=>$id,'archivo_id'=>$archivo['id']]);
    adjunto_check(!empty($r['ok']) && $db->one('SELECT deleted_at FROM noticia_archivos WHERE id=?','i',[$archivo['id']])['deleted_at']!==null,'Retiro lógico de un adjunto');
    $r=$peticion('app_adjuntos_action.php',['action'=>'video_remove','noticia_id'=>$id]);
    adjunto_check(!empty($r['ok']) && $db->one('SELECT youtube_codigo FROM noticias WHERE id=?','i',[$id])['youtube_codigo']===null,'Retirar video conserva el texto');
} finally {
    $creadas=$db->all('SELECT id FROM noticias WHERE titulo=?','s',[$marca]);
    $raiz=realpath(__DIR__.'/../uploads/noticias');
    foreach ($creadas as $creada) {
        foreach ($db->all('SELECT ruta FROM noticia_archivos WHERE noticia_id=?','i',[$creada['id']]) as $archivo) {
            $ruta=realpath(__DIR__.'/../'.$archivo['ruta']);
            if ($ruta && $raiz && str_starts_with($ruta,$raiz.DIRECTORY_SEPARATOR)) unlink($ruta);
        }
        $db->execute('DELETE FROM noticia_archivos WHERE noticia_id=?','i',[$creada['id']]);
        $db->execute('DELETE FROM noticias WHERE id=? AND titulo=?','is',[$creada['id'],$marca]);
    }
    foreach ($temporales as $ruta) if (is_file($ruta)) unlink($ruta);
    session_start();session_destroy();
    ob_end_flush();
}
