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
//Archivos comunes
$t->set_file(array(
	"ver"		=> "home_abm.html",
	"un"		=> "un_root.html",
	"una_opcion" => "una_opcion.html"
	));

$id_system_01 = isset($_POST['id_system_01']) ? $_POST['id_system_01'] : NULL;
$t->set_var("titulo_modulo","");


$row = $mysqli -> consulta_SQL("Select * from system_01_modulos where id_system_01='$id_system_01' ");
if ($row == TRUE)
{			
	$id_system_01 = 		$row[0]['id_system_01'];
	$system_01_modulo = 	$row[0]['system_01_modulo'];
	$system_01_tipo = 		$row[0]['system_01_tipo'];
	$system_01_path_home = 	$row[0]['system_01_path_home'];
	$system_01_onoff = 		$row[0]['system_01_onoff'];
	$system_01_orden = 	$row[0]['system_01_orden'];
	$system_01_estado = 	$row[0]['system_01_estado'];
	
}
else
{
	$id_system_01 = 		'';
	$system_01_modulo = 	'';
	$system_01_tipo = 		'';
	$system_01_path_home = 	'';
	$system_01_onoff = 		'';
	$system_01_estado = 	'';
	$system_01_orden = 	'';
}

	$t->set_var("id_system_01",			"$id_system_01");
	$t->set_var("system_01_modulo",		"$system_01_modulo");
	$t->set_var("system_01_tipo",		"$system_01_tipo");
	$t->set_var("system_01_path_home",	"$system_01_path_home");
	$t->set_var("system_01_onoff",		"$system_01_onoff");
	$t->set_var("system_01_orden",		"$system_01_orden");	

	$url="'modulos/root/php/_interfaz.php'";
	$vars="'nombre_funcion=agregar_modificar&";
	$vars.="id_system_01=$id_system_01&";
	$vars.="system_01_modulo='+abm.system_01_modulo.value+'&";
	$vars.="system_01_tipo='+abm.system_01_tipo.value+'&";
	$vars.="system_01_orden='+abm.system_01_orden.value+'&";
	$vars.="system_01_path_home='+abm.system_01_path_home.value+'&";
	$vars.="system_01_onoff='+abm.system_01_onoff.value";
	$url_exito="'modulos/root/php/home.php'";
	$id="'content_seccion'";
	$vars_exito="'id_system_01=$id_system_01'";	
	$t->set_var("titulo","Editar Modulo");	
	$t->set_var("funcion_guardar","guardar_mostrar($url,$vars,$url_exito,$id,$vars_exito)");		
	
	
	
	$ajustado = array('abm'=>"abm", 'public'=>"public", 'sys'=>"sys", 'acces'=>"acces", 'root'=>"root"); 
	while ($select_sdb6 = current($ajustado)) 
	{
		if (key($ajustado)==$system_01_tipo) 
		{
			$t->set_var("ID",key($ajustado)." SELECTED ");	
		}
		else
		{
			$t->set_var("ID",key($ajustado));	
		}
		$t->set_var("NOMBRE",$select_sdb6);	
		$t->parse("TIPO","una_opcion",true);
		next($ajustado);
	}
					
			
	$onoff = array('on'=>"on", 'off'=>"off"); 
	while ($select_sdb7 = current($onoff)) 
	{
		if (key($onoff)==$system_01_onoff) 
		{
			$t->set_var("ID",key($onoff)." SELECTED ");	
		}
		else
		{
			$t->set_var("ID",key($onoff));	
		}
		$t->set_var("NOMBRE",$select_sdb7);	
		$t->parse("ESTADO","una_opcion",true);
		next($onoff);
	}


$url="'modulos/root/php/home.php'";
$id="'content_seccion'";
$vars="'id_system_01=$id_system_01'";
$t->set_var("funcion_volver","cargar_post($url,$id,$vars)");

$t->pparse("OUT", "ver");
?>
