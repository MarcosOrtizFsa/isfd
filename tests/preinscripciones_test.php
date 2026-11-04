<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') exit;
require __DIR__.'/../lib/app.php';
require __DIR__.'/../lib/mysql_conect.php';
require __DIR__.'/../preinscripcion/servicio.php';
function preCheck(bool $ok,string $mensaje): void {
    if (!$ok) throw new RuntimeException($mensaje);
    echo 'OK: '.$mensaje.PHP_EOL;
}
function preReject(callable $operacion): void {
    try {$operacion();} catch (DomainException $e) {return;}
    throw new RuntimeException('Se aceptó una operación inválida.');
}
$nombre='isfd_pre_test_'.bin2hex(random_bytes(6));
$conexion=new mysqli(HOST,USU,CLA);
$conexion->query('CREATE DATABASE `'.$nombre.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
try {
    $db=new AppDatabase(HOST,USU,CLA,$nombre);
    foreach (['ofertas','catalogo_categorias','catalogo_niveles','modalidades'] as $tabla) $db->execute('CREATE TABLE `'.$tabla.'` LIKE `'.str_replace('`','``',BD).'`.`'.$tabla.'`');
    $sql=file_get_contents(__DIR__.'/../database/migrations/020_preinscripciones_up.sql');
    for ($i=0;$i<2;$i++) foreach (explode(';',$sql) as $sentencia) if (trim($sentencia)!=='') $db->execute($sentencia);
    $nivel=$db->insert("INSERT INTO catalogo_niveles (codigo,nombre) VALUES ('superior','Superior')");
    $categoria=$db->insert("INSERT INTO catalogo_categorias (nivel_id,slug,nombre,profundidad,ruta) VALUES (?,'profesorado','Profesorado',1,'/1/')",'i',[$nivel]);
    $modalidad=$db->insert("INSERT INTO modalidades (codigo,nombre) VALUES ('presencial','Presencial')");
    $oferta=$db->insert("INSERT INTO ofertas (categoria_id,modalidad_id,slug,titulo,estado_publicacion,estado_oferta) VALUES (?,?,'prueba','Carrera de prueba','publicada','inscripcion_abierta')",'ii',[$categoria,$modalidad]);
    $servicio=new Preinscripciones($db);
    preCheck(count($servicio->carreras())===1,'Carrera abierta disponible');
    $datos=['oferta_id'=>$oferta,'nombre'=>'Ana','apellido'=>'Pérez','dni'=>'30123456','email'=>'ana@example.test','telefono'=>'3704 506262','acepta_politica'=>'1'];
    $hash=hash('sha256','prueba');
    preReject(fn()=>$servicio->registrar(array_replace($datos,['telefono'=>'123']),$hash));
    preReject(fn()=>$servicio->registrar(array_replace($datos,['dni'=>'abc']),$hash));
    preReject(fn()=>$servicio->registrar(array_replace($datos,['acepta_politica'=>'0']),$hash));
    $servicio->registrar($datos,$hash);
    $servicio->registrar(array_replace($datos,['nombre'=>'No sobrescribir']),$hash);
    $fila=$db->one('SELECT * FROM portal_preinscripciones');
    preCheck((int)$db->one('SELECT COUNT(*) n FROM portal_preinscripciones')['n']===1 && $fila['nombre']==='Ana','Reenvío sin duplicar ni sobrescribir');
    $servicio->gestionar((int)$fila['id'],'en_revision','Contactada',1,'pendiente');
    preReject(fn()=>$servicio->gestionar((int)$fila['id'],'formalizada','',1,'pendiente'));
    $servicio->gestionar((int)$fila['id'],'formalizada','Documentación recibida',1,'en_revision');
    preCheck((int)$db->one('SELECT COUNT(*) n FROM portal_preinscripcion_historial')['n']===3,'Historial y protección de cambios concurrentes');
    $db->execute("UPDATE ofertas SET estado_oferta='cerrada' WHERE id=?",'i',[$oferta]);
    preCheck($servicio->carreras()===[],'Carrera cerrada fuera del selector');
    preReject(fn()=>$servicio->registrar(array_replace($datos,['dni'=>'32123456']),$hash));
    $db->execute("UPDATE ofertas SET estado_oferta='inscripcion_abierta',estado_publicacion='borrador' WHERE id=?",'i',[$oferta]);
    preCheck($servicio->carreras()===[],'Borradores fuera del selector');
    $db->execute("UPDATE ofertas SET estado_publicacion='publicada' WHERE id=?",'i',[$oferta]);
    $db->execute('UPDATE catalogo_categorias SET activo=0 WHERE id=?','i',[$categoria]);
    preCheck($servicio->carreras()===[],'Categoría desactivada fuera del selector');
    $db->execute('UPDATE catalogo_categorias SET activo=1 WHERE id=?','i',[$categoria]);
    for ($i=1;$i<=4;$i++) $servicio->registrar(array_replace($datos,['dni'=>(string)(30123456+$i)]),$hash);
    preReject(fn()=>$servicio->registrar(array_replace($datos,['dni'=>'40123456']),$hash));
    preCheck((int)$db->one('SELECT COUNT(*) n FROM portal_preinscripciones')['n']===5,'Límite de envíos por hora');
} finally {
    $conexion->query('DROP DATABASE `'.$nombre.'`');
}
