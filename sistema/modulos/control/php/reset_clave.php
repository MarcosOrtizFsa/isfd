<?php
session_start();
include "../../../../lib/template.inc";
include "../../../../lib/mysql_conect.php";
include "../../../php/constructor_sql.php";
include "../../../php/abm.php";
include "../../../php/funciones.php";
include "_abm.php";
$mysqli = new _Abm($base);

$t = new _template('../templates');
$t->set_file(array(
	'ver'	=> "reset_clave.html"
	));

$id_system_03=isset($_POST['id_system_03']) ? $_POST['id_system_03'] : NULL;
$id_system_01 = isset($_POST['id_system_01']) ? $_POST['id_system_01'] : NULL;


$row = $mysqli -> consulta_SQL("Select * from  system_03_usuarios where id_system_03='$id_system_03' ");
if ($row == TRUE)
{
	$id_system_03 = 	$row[0]['id_system_03'];
	$system_03_clave = 	$row[0]['system_03_clave'];
}
		
	$url="'modulos/control/php/_interfaz.php'";
	$vars="'nombre_funcion=salvar_clave&id_system_03=$id_system_03&";
	$vars.="system_03_clave='+encodeURIComponent(abm2.system_03_clave.value)+'&";
	$vars.="system_03_clave_copy='+encodeURIComponent(abm2.system_03_clave_copy.value)";
	$url_exito="'modulos/control/templates/reset_ok.html'";
	$id="'content_reset'";
	$vars_exito="''";
	$t->set_var("funcion_salvar","guardar_mostrar($url,$vars,$url_exito,$id,$vars_exito)");

	$url2="'templates/nada.html'";
	$id2="'content_reset'";
	$vars2="''";
	$t->set_var("funcion_volver","cargar_post($url2,$id2,$vars2)");
	
					
$t->pparse("OUT", "ver");
?>
