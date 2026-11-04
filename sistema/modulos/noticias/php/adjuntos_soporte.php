<?php
declare(strict_types=1);

/** Acepta únicamente identificadores y URLs de YouTube, nunca HTML embebido. */
function noticia_youtube(string $valor): ?string
{
    $valor=trim($valor);
    if (preg_match('/^[A-Za-z0-9_-]{11}$/D',$valor)) return $valor;
    $url=parse_url($valor);
    if (!$url || !in_array(strtolower($url['scheme']??''),['https','http'],true)) return null;
    $host=strtolower($url['host']??'');
    $ruta=trim($url['path']??'','/');
    $codigo='';
    if ($host==='youtu.be') $codigo=explode('/',$ruta)[0];
    elseif (in_array($host,['youtube.com','www.youtube.com','m.youtube.com','youtube-nocookie.com','www.youtube-nocookie.com'],true)) {
        parse_str($url['query']??'',$query);
        if ($ruta==='watch') $codigo=is_string($query['v']??null)?$query['v']:'';
        elseif (preg_match('~^(?:embed|shorts|live)/([^/]+)$~',$ruta,$match)) $codigo=$match[1];
    }
    return preg_match('/^[A-Za-z0-9_-]{11}$/D',$codigo)?$codigo:null;
}

/** Valida MIME real y tamaño antes de mover cualquier archivo. */
function noticia_archivos_validar(array $archivos,string $tipo): array
{
    if (!in_array($tipo,['imagen','adjunto'],true)) throw new DomainException('Tipo de adjunto inválido.');
    if (!isset($archivos['name']) || !is_array($archivos['name']) || count($archivos['name'])>10) throw new DomainException('Seleccioná entre uno y diez archivos por carga.');
    $formatos=[
        'image/jpeg'=>['jpg','imagen',8388608], 'image/png'=>['png','imagen',8388608],
        'image/webp'=>['webp','imagen',8388608], 'image/gif'=>['gif','imagen',8388608],
        'application/pdf'=>['pdf','adjunto',15728640], 'application/msword'=>['doc','adjunto',15728640],
        'application/vnd.ms-excel'=>['xls','adjunto',15728640],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>['docx','adjunto',15728640],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>['xlsx','adjunto',15728640],
    ];
    $resultado=[];$finfo=new finfo(FILEINFO_MIME_TYPE);
    foreach ($archivos['name'] as $indice=>$nombre) {
        $error=(int)($archivos['error'][$indice]??UPLOAD_ERR_NO_FILE);
        if ($error===UPLOAD_ERR_NO_FILE) continue;
        if ($error!==UPLOAD_ERR_OK) throw new DomainException('La carga no se completó. Revisá el tamaño y el límite permitido por el servidor.');
        $tmp=(string)($archivos['tmp_name'][$indice]??'');
        if (!is_uploaded_file($tmp)) throw new DomainException('El archivo recibido no es válido.');
        $tamano=filesize($tmp);
        if (!$tamano || $tamano>15728640) throw new DomainException('El archivo supera el tamaño permitido.');
        $mime=(string)$finfo->file($tmp);
        // Algunos servidores identifican DOCX/XLSX como ZIP. Verifica su estructura Office.
        if (in_array($mime,['application/zip','application/octet-stream'],true) && class_exists('ZipArchive')) {
            $zip=new ZipArchive();
            if ($zip->open($tmp)===true) {
                if ($zip->locateName('[Content_Types].xml')!==false) {
                    if ($zip->locateName('word/document.xml')!==false && $zip->locateName('word/vbaProject.bin')===false) $mime='application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                    elseif ($zip->locateName('xl/workbook.xml')!==false && $zip->locateName('xl/vbaProject.bin')===false) $mime='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                }
                $zip->close();
            }
        }
        if (!isset($formatos[$mime])) throw new DomainException('Formato no permitido. Usá imágenes JPG, PNG, WebP o GIF; documentos PDF, Word o Excel.');
        [$extension,$grupo,$limite]=$formatos[$mime];
        if ($grupo!==$tipo) throw new DomainException('Usá el panel correspondiente: Imágenes o Archivos descargables.');
        if ($tamano>$limite) throw new DomainException('Las imágenes admiten hasta 8 MB y los documentos hasta 15 MB.');
        if ($grupo==='imagen' && @getimagesize($tmp)===false) throw new DomainException('No se pudo reconocer la imagen.');
        $resultado[]=['tmp'=>$tmp,'nombre'=>mb_substr(basename(str_replace('\\','/',(string)$nombre)),0,255),'mime'=>$mime,'tamano'=>$tamano,'extension'=>$extension,'tipo'=>$grupo];
    }
    if (!$resultado) throw new DomainException('Seleccioná al menos un archivo.');
    return $resultado;
}

/** Los archivos y sus relaciones se guardan juntos; una falla revierte los archivos movidos. */
function noticia_adjuntos_guardar(AppDatabase $db,int $noticia,array $archivos,int $usuario): void
{
    $movidos=[];
    try {
        $db->transaction(function () use ($db,$noticia,$archivos,$usuario,&$movidos): void {
            if (!$db->one('SELECT id FROM noticias WHERE id=? AND deleted_at IS NULL FOR UPDATE','i',[$noticia])) throw new DomainException('Primero guardá el texto de una noticia disponible.');
            $orden=(int)($db->one('SELECT MAX(orden) maximo FROM noticia_archivos WHERE noticia_id=?','i',[$noticia])['maximo']??-1)+1;
            $relativa='uploads/noticias/'.date('Y/m');
            $directorio=dirname(__DIR__,4).'/'.$relativa;
            if (!is_dir($directorio) && !mkdir($directorio,0755,true) && !is_dir($directorio)) throw new RuntimeException('No se pudo preparar la carpeta de adjuntos.');
            foreach ($archivos as $archivo) {
                $nombre=bin2hex(random_bytes(18)).'.'.$archivo['extension'];
                $destino=$directorio.'/'.$nombre;
                if (!move_uploaded_file($archivo['tmp'],$destino)) throw new RuntimeException('No se pudo guardar el archivo.');
                $movidos[]=$destino;
                $db->insert('INSERT INTO noticia_archivos (noticia_id,tipo,nombre_original,nombre_guardado,mime_type,tamano_bytes,ruta,orden) VALUES (?,?,?,?,?,?,?,?)','issssisi',[$noticia,$archivo['tipo'],$archivo['nombre'],$nombre,$archivo['mime'],$archivo['tamano'],$relativa.'/'.$nombre,$orden++]);
            }
            $db->execute('UPDATE noticias SET actualizado_por=? WHERE id=?','ii',[$usuario,$noticia]);
        });
    } catch (Throwable $error) {
        foreach ($movidos as $ruta) if (is_file($ruta)) unlink($ruta);
        throw $error;
    }
}
