<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit;
require __DIR__.'/../lib/app.php';
require __DIR__.'/../lib/mysql_conect.php';
require __DIR__.'/../sistema/modulos/ofertas/php/categorias_servicio.php';
require __DIR__.'/../sistema/modulos/home/php/ofertas_repositorio.php';

function comprobarCategoria(bool $ok, string $mensaje): void
{
    if (!$ok) throw new RuntimeException($mensaje);
    echo 'OK: '.$mensaje.PHP_EOL;
}
function rechazarCategoria(callable $operacion): void
{
    try { $operacion(); } catch (DomainException $error) { return; }
    throw new RuntimeException('Se permitió una operación inválida.');
}

// Copia únicamente estructuras en una base efímera; no modifica datos institucionales.
$nombre = 'isfd_categorias_test_'.bin2hex(random_bytes(6));
$conexion = new mysqli(HOST, USU, CLA);
$conexion->query('CREATE DATABASE `'.$nombre.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
try {
    $db = new AppDatabase(HOST, USU, CLA, $nombre);
    $origen = new AppDatabase(HOST, USU, CLA, BD);
    foreach (['catalogo_niveles','catalogo_categorias','ofertas','modalidades','unidades_academicas','oferta_convocatorias','oferta_turno','turnos','oferta_area','areas_conocimiento'] as $tabla) {
        $db->execute('CREATE TABLE `'.$tabla.'` LIKE `'.str_replace('`','``',BD).'`.`'.$tabla.'`');
    }
    foreach (['v_oferta_convocatorias_priorizadas','v_catalogo_ofertas'] as $vistaNombre) {
        $vista = $origen->one('SHOW CREATE VIEW '.$vistaNombre)['Create View'];
        $vista = preg_replace('/DEFINER=`[^`]+`@`[^`]+`\s*/', '', $vista);
        $db->execute(str_replace('`'.BD.'`.', '`'.$nombre.'`.', $vista));
    }
    $integridad = file_get_contents(__DIR__.'/../database/migrations/007_catalogo_integridad_manual_up.sql');
    $conexion->select_db($nombre);
    $cuerpos = explode('$$', explode('DELIMITER $$', $integridad, 2)[1]);
    foreach ($cuerpos as $cuerpo) {
        if (preg_match('/^\s*CREATE (TRIGGER|PROCEDURE)/', $cuerpo)) $conexion->query($cuerpo);
    }
    $nivel = $db->insert("INSERT INTO catalogo_niveles (codigo,nombre) VALUES ('superior','Superior')");
    $modalidad = $db->insert("INSERT INTO modalidades (codigo,nombre) VALUES ('presencial','Presencial')");
    $servicio = new CategoriasServicio($db);
    $repo = new OfertasRepositorio($db);
    $base = ['nombre'=>'Profesorados','nivel_id'=>$nivel,'orden'=>'0','activo'=>'1','admite_ofertas'=>'1'];
    $grupo = $servicio->ejecutar('save', array_replace($base,['nombre'=>'Grupo','admite_ofertas'=>'0']));
    $id = $servicio->ejecutar('save', $base+['parent_id'=>$grupo]);
    comprobarCategoria($db->one('SELECT ruta FROM catalogo_categorias WHERE id=?','i',[$id])['ruta']==='/'.$grupo.'/'.$id.'/', 'Alta con ruta jerárquica y triggers');
    comprobarCategoria($repo->catalogos()['tipo']===[], 'Categorías vacías fuera del filtro');
    $oferta = $db->insert("INSERT INTO ofertas (categoria_id,modalidad_id,slug,titulo) VALUES (?,?,'prueba','Prueba')",'ii',[$id,$modalidad]);
    comprobarCategoria($repo->catalogos()['tipo']===[], 'Borradores fuera del filtro');
    $db->execute("UPDATE ofertas SET estado_publicacion='publicada' WHERE id=?",'i',[$oferta]);
    comprobarCategoria(count($repo->catalogos()['tipo'])===1 && $repo->listar([])['meta']['total']===1, 'Publicada visible en filtro y listado');
    comprobarCategoria($repo->buscarPorSlug('prueba')!==null && $repo->arbol()[0]['ofertas_count']===1, 'Detalle y árbol coherentes');
    $servicio->ejecutar('toggle',['id'=>$grupo]);
    comprobarCategoria($repo->catalogos()['tipo']===[] && $repo->listar([])['meta']['total']===0 && $repo->buscarPorSlug('prueba')===null, 'Rama inactiva oculta en filtro, listado y detalle');
    $servicio->ejecutar('toggle',['id'=>$grupo]);
    $servicio->ejecutar('toggle',['id'=>$id]);
    comprobarCategoria($repo->catalogos()['tipo']===[], 'Hoja inactiva fuera del filtro');
    $servicio->ejecutar('toggle',['id'=>$id]);
    $servicio->ejecutar('save',array_replace($base,['id'=>$id,'nombre'=>'Nuevo nombre']));
    comprobarCategoria($repo->catalogos()['tipo'][0]['codigo']==='profesorados', 'Editar conserva el enlace existente: '.json_encode($repo->catalogos()['tipo']));
    rechazarCategoria(fn()=>$servicio->ejecutar('delete',['id'=>$id]));
    rechazarCategoria(fn()=>$servicio->ejecutar('delete',['id'=>$grupo]));
    rechazarCategoria(fn()=>$servicio->ejecutar('save',array_replace($base,['id'=>$id,'admite_ofertas'=>'0'])));
    rechazarCategoria(fn()=>$servicio->ejecutar('save',$base+['parent_id'=>$id]));
    comprobarCategoria(true,'Protegidas relaciones de ofertas y subcategorías');
    $db->execute('UPDATE ofertas SET deleted_at=NOW() WHERE id=?','i',[$oferta]);
    comprobarCategoria($repo->catalogos()['tipo']===[], 'Ofertas eliminadas fuera del filtro');
    $vacia = $servicio->ejecutar('save',$base);
    $servicio->ejecutar('delete',['id'=>$vacia]);
    comprobarCategoria(!$db->one('SELECT id FROM catalogo_categorias WHERE id=?','i',[$vacia]), 'Baja de categoría vacía');
} finally {
    $conexion->query('DROP DATABASE `'.$nombre.'`');
}
