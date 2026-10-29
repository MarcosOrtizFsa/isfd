<?php
declare(strict_types=1);

/** Solo IDs reales o URLs de YouTube; nunca se acepta HTML de iframe del cliente. */
function autoridad_youtube(string $value): string
{
    $value = trim($value);
    if ($value === '' || preg_match('/^[A-Za-z0-9_-]{11}$/', $value)) return $value;
    $parts = parse_url($value);
    if (!$parts || !in_array(strtolower($parts['scheme']??''), ['https','http'], true)) throw new InvalidArgumentException('Ingresá un enlace válido de YouTube.');
    $host = strtolower($parts['host']??'');
    $path = $parts['path']??'';
    $code = '';
    if ($host === 'youtu.be') $code = trim($path,'/');
    elseif (in_array($host,['youtube.com','www.youtube.com','m.youtube.com','www.youtube-nocookie.com'],true)) {
        parse_str($parts['query']??'', $query);
        if ($path === '/watch') $code = is_string($query['v']??null)?$query['v']:'';
        elseif (preg_match('~^/(shorts|embed|live)/([A-Za-z0-9_-]{11})/?$~',$path,$match)) $code = $match[2];
    }
    if (!preg_match('/^[A-Za-z0-9_-]{11}$/',$code)) throw new InvalidArgumentException('No se reconoce el video de YouTube.');
    return $code;
}

/** Registro de proveedores admitidos: transforma enlaces públicos en reproductores seguros. */
function autoridad_video(string $value): string
{
    $value=trim($value);
    if ($value==='') return '';
    try { return 'https://www.youtube-nocookie.com/embed/'.autoridad_youtube($value); } catch (InvalidArgumentException $error) {}
    $parts=parse_url($value);
    if (!$parts || ($parts['scheme']??'')!=='https' || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) throw new InvalidArgumentException('Ingresá un enlace HTTPS de YouTube, HeyGen o Vimeo.');
    $host=strtolower($parts['host']??'');
    $path=$parts['path']??'';
    if ($host==='app.heygen.com' && preg_match('~^/embeds/([a-f0-9]{32})/?$~i',$path,$match)) return 'https://app.heygen.com/embeds/'.$match[1];
    if (in_array($host,['vimeo.com','www.vimeo.com','player.vimeo.com'],true) && preg_match('~^/(?:video/)?([0-9]+)/?$~',$path,$match)) {
        parse_str($parts['query']??'', $query);
        $hash=$query['h']??'';
        return 'https://player.vimeo.com/video/'.$match[1].(is_string($hash)&&preg_match('/^[a-zA-Z0-9]+$/',$hash)?'?h='.$hash:'');
    }
    throw new InvalidArgumentException('Admite YouTube, Vimeo y enlaces HeyGen con formato https://app.heygen.com/embeds/ID.');
}

function autoridad_validar(array $input): array
{
    $data = [];
    foreach (['nombre'=>120,'apellido'=>120,'cargo'=>160,'profesion'=>160,'descripcion'=>2000] as $key=>$limit) {
        $value = $input[$key]??'';
        if (!is_string($value) || mb_strlen(trim($value))>$limit) throw new InvalidArgumentException('Revisá el campo '.$key.'.');
        $data[$key] = trim($value);
    }
    if ($data['nombre']==='' || $data['apellido']==='' || $data['cargo']==='') throw new InvalidArgumentException('Completá nombre, apellido y cargo.');
    foreach (['anio'=>[1900,2200],'orden'=>[0,65535]] as $key=>[$min,$max]) {
        $value=filter_var($input[$key]??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>$min,'max_range'=>$max]]);
        if ($value===false) throw new InvalidArgumentException('Revisá '.$key.'.');
        $data[$key]=$value;
    }
    $video=$input['video_url']??$input['youtube']??'';
    if (!is_string($video)) throw new InvalidArgumentException('El enlace de video no es válido.');
    $data['video_url']=autoridad_video($video);
    $data['youtube_codigo']=str_starts_with($data['video_url'],'https://www.youtube-nocookie.com/embed/')?basename($data['video_url']):'';
    $data['visible']=isset($input['visible'])?1:0;
    return $data;
}

/** Cada edición genera una instantánea; las fotos anteriores se conservan para el historial. */
function autoridad_guardar(AppDatabase $db, int $id, int $expected, array $data, ?string $newImage, bool $removeImage, int $user, string $reason): int
{
    if ($reason==='' || mb_strlen($reason)>250) throw new InvalidArgumentException('Indicá un motivo de hasta 250 caracteres.');
    return $db->transaction(function(AppDatabase $db) use ($id,$expected,$data,$newImage,$removeImage,$user,$reason): int {
        $old=$id?$db->one('SELECT * FROM institucional_autoridades WHERE id=? FOR UPDATE','i',[$id]):null;
        if ($id && (!$old || (int)$old['revision']!==$expected)) throw new UnexpectedValueException('La autoridad cambió. Recargá antes de guardar.');
        $data['imagen_url']=$newImage??($removeImage?'':($old['imagen_url']??''));
        $columns=array_keys($data);
        $values=array_values($data);
        if ($id) {
            $assignments=implode(',',array_map(static fn($key)=>$key.'=?',$columns));
            $db->execute('UPDATE institucional_autoridades SET '.$assignments.',revision=revision+1 WHERE id=?',str_repeat('s',count($values)).'i',[...$values,$id]);
        } else {
            $id=$db->insert('INSERT INTO institucional_autoridades ('.implode(',',$columns).') VALUES ('.implode(',',array_fill(0,count($values),'?')).')',str_repeat('s',count($values)),$values);
        }
        $saved=$db->one('SELECT * FROM institucional_autoridades WHERE id=?','i',[$id]);
        $db->insert('INSERT INTO institucional_autoridades_historial (autoridad_id,revision,usuario_id,motivo,datos_json) VALUES (?,?,?,?,?)','iiiss',[$id,(int)$saved['revision'],$user,$reason,json_encode($saved,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);
        return $id;
    });
}
