<?php
declare(strict_types=1);
require __DIR__.'/../../../../lib/app.php';
$user=app_require_mode([0]);
app_verify_csrf();
require_once __DIR__.'/../../../../lib/autoridades.php';
$stored=null;
try {
    $data=autoridad_validar($_POST);
    $imageUrl=null;
    $file=$_FILES['imagen']??null;
    if ($file && (int)$file['error']!==UPLOAD_ERR_NO_FILE) {
        if ((int)$file['error']!==UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name']) || $file['size']>5242880) throw new InvalidArgumentException('La imagen debe pesar hasta 5 MB.');
        $size=getimagesize($file['tmp_name']);
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!$size || !in_array($mime,['image/jpeg','image/png','image/webp'],true) || $size[0]*$size[1]>12000000) throw new InvalidArgumentException('Usá JPG, PNG o WebP de hasta 12 megapíxeles.');
        if (!function_exists('imagecreatefromstring')) throw new RuntimeException('GD no está disponible.');
        $image=imagecreatefromstring(file_get_contents($file['tmp_name']));
        if (!$image) throw new InvalidArgumentException('No se pudo leer la imagen.');
        $width=min(1000,$size[0]);
        $height=(int)round($size[1]*$width/$size[0]);
        $canvas=imagecreatetruecolor($width,$height);
        imagefill($canvas,0,0,imagecolorallocate($canvas,255,255,255));
        imagecopyresampled($canvas,$image,0,0,0,0,$width,$height,$size[0],$size[1]);
        $directory=dirname(__DIR__,4).'/uploads/autoridades';
        if (!is_dir($directory) && !mkdir($directory,0755,true)) throw new RuntimeException('No se pudo preparar el directorio.');
        $filename=bin2hex(random_bytes(18)).'.jpg';
        $stored=$directory.'/'.$filename;
        if (!imagejpeg($canvas,$stored,88)) throw new RuntimeException('No se pudo guardar la imagen.');
        imagedestroy($image); imagedestroy($canvas);
        $imageUrl='../uploads/autoridades/'.$filename;
    }
    $id=autoridad_guardar(app_db(),app_int('id'),app_int('revision'),$data,$imageUrl,isset($_POST['quitar_imagen']),$user['id'],app_post('motivo'));
    app_json(['ok'=>true,'id'=>$id,'message'=>'Autoridad guardada. La edición quedó registrada en el historial.']);
} catch (Throwable $error) {
    // Solo se retira el archivo nuevo si el guardado falló; las fotos históricas permanecen.
    if ($stored && is_file($stored)) unlink($stored);
    if ($error instanceof InvalidArgumentException || $error instanceof UnexpectedValueException) app_json(['ok'=>false,'message'=>$error->getMessage()],422);
    error_log('Guardar autoridad: '.$error->getMessage());
    app_json(['ok'=>false,'message'=>'No se pudo guardar la autoridad.'],500);
}
