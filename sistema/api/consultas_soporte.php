<?php
declare(strict_types=1);

function consulta_recibir(?int $offerId, string $type): never
{
    app_verify_csrf();
    if (app_post('sitio_web') !== '') app_json(['ok'=>true,'message'=>'¡Gracias! Recibimos tu consulta.'],201);
    $name=app_post('nombre');$email=app_post('email');$phone=app_post('telefono');$subject=app_post('asunto');$message=app_post('mensaje');
    $accepted=filter_var($_POST['acepta_politica']??false,FILTER_VALIDATE_BOOLEAN);
    $errors=[];
    if($name===''||mb_strlen($name)>120)$errors['nombre']='Ingresá tu nombre (hasta 120 caracteres).';
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)||mb_strlen($email)>190)$errors['email']='Ingresá un email válido.';
    if($phone!==''&&!preg_match('/^[0-9+() .-]{6,40}$/',$phone))$errors['telefono']='Ingresá un teléfono válido.';
    if($type==='contacto'&&($subject===''||mb_strlen($subject)>150))$errors['asunto']='Ingresá un asunto (hasta 150 caracteres).';
    if(mb_strlen($message)<10||mb_strlen($message)>1500)$errors['mensaje']='El mensaje debe tener entre 10 y 1500 caracteres.';
    if(!$accepted)$errors['acepta_politica']='Debés aceptar el uso de tus datos para responder la consulta.';
    if($errors)app_json(['ok'=>false,'message'=>'Revisá los campos indicados.','errors'=>$errors],422);
    $db=app_db();
    if($offerId!==null&&!$db->one('SELECT id FROM portal_ofertas_academicas WHERE id=? AND publicada=1','i',[$offerId]))app_json(['ok'=>false,'message'=>'La oferta seleccionada no existe.'],404);
    $ip=(string)($_SERVER['REMOTE_ADDR']??'unknown');$ipHash=hash('sha256',$ip.'|'.(getenv('ISFD_APP_SECRET')?:'isfd-local'));
    $recent=(int)($db->one("SELECT COUNT(*) total FROM portal_consultas WHERE ip_hash=? AND created_at>=DATE_SUB(NOW(),INTERVAL 1 HOUR)",'s',[$ipHash])['total']??0);
    if($recent>=5)app_json(['ok'=>false,'message'=>'Alcanzaste el límite de consultas. Intentá nuevamente dentro de una hora.'],429);
    $clean=static fn(string $value):string=>trim(strip_tags($value));
    $recipient=getenv('ISFD_CONTACT_EMAIL')?:'ifd.fac@formosa.gov.ar';
    $id=$db->transaction(function(AppDatabase $database)use($offerId,$type,$name,$email,$phone,$subject,$message,$ipHash,$recipient,$clean){
        $id=$database->insert('INSERT INTO portal_consultas (oferta_id,tipo,nombre,email,telefono,asunto,mensaje,ip_hash) VALUES (?,?,?,?,?,?,?,?)','isssssss',[$offerId,$type,$clean($name),$email,$clean($phone),$clean($subject),$clean($message),$ipHash]);
        $mailSubject=$type==='oferta'?'Nueva consulta sobre oferta académica':'Nuevo contacto desde el portal';
        $body='Nombre: '.$clean($name)."\nEmail: ".$email."\nTeléfono: ".$clean($phone)."\n\n".$clean($message);
        $database->insert('INSERT INTO portal_bandeja_salida (consulta_id,destinatario,asunto,cuerpo) VALUES (?,?,?,?)','isss',[$id,$recipient,$mailSubject,$body]);
        return $id;
    });
    app_json(['ok'=>true,'id'=>$id,'message'=>'¡Gracias! Recibimos tu consulta y te vamos a responder a la brevedad.'],201);
}

