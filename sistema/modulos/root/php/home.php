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
	'ver'	=> "home.html",
	'un_root'	=> "un_root.html"
	));
	

$row = $mysqli -> consulta_SQL("Select * from system_01_modulos order by system_01_orden asc");
if ($row == TRUE)
{
	for ( $i=0; $i < count($row); $i++)
	{				
		$id_system_01 = 		$row[$i]['id_system_01'];
		$system_01_modulo = 	$row[$i]['system_01_modulo'];
		$system_01_tipo = 		$row[$i]['system_01_tipo'];
		$system_01_path_home = 	$row[$i]['system_01_path_home'];
		$system_01_onoff = 		$row[$i]['system_01_onoff'];
		$system_01_orden = 		$row[$i]['system_01_orden'];
		$system_01_estado = 	$row[$i]['system_01_estado'];	
				
		if ($system_01_estado == '0')
		{
			$t->set_var("system_01_estado","bi bi-eye-slash-fill");			
		}
		else
		{
			$t->set_var("system_01_estado","bi bi-eye-fill");
		}
		
		// modificar 
		if ($system_07_m == "1")
		{				
			$url="'modulos/root/php/home_abm.php'";
			$id="'content_$id_system_01'";
			$vars="'id_system_01=$id_system_01'";	
			$t->set_var("funcion_editar","cargar_post($url,$id,$vars)");	
		}
		else
		{
			$t->set_var("funcion_editar","sin_permisos()");
		}
		
		// borrar
		if ($system_07_b == "1")
		{
			$url="'modulos/root/php/_interfaz.php'";
			$vars="'nombre_funcion=eliminar&";
			$vars.="id_system_01=$id_system_01'";
			$url_exito="'modulos/root/php/home.php'";
			$id="'content_seccion'";
			$vars_exito="'id_system_01=1'";
			$msg="'Quiere borrar esto?'";
			$t->set_var("funcion_borrar","eliminar_mostrar($url,$vars,$url_exito,$id,$vars_exito,$msg)");	
		}
		else
		{
			$t->set_var("funcion_borrar","sin_permisos()");
		}		
		
		// on off
		if ($system_07_ad == "1")
		{	
			$url="'modulos/root/php/_interfaz.php'";
			$vars="'nombre_funcion=on_off&id_system_01=$id_system_01&system_01_estado=$system_01_estado'";
			$url_exito="'modulos/root/php/home.php'";
			$id="'content_seccion'";
			$vars_exito="'id_system_01=1'";
			$t->set_var("on_off","guardar_mostrar($url,$vars,$url_exito,$id,$vars_exito);");
			
		}
		else
		{
			$t->set_var("on_off","sin_permisos()");
		}			
		
		$bgcolor="";
		$t->set_var("id_system_01","$id_system_01");
		$t->set_var("system_01_modulo","$system_01_modulo");
		$t->set_var("system_01_tipo","$system_01_tipo");
		$t->set_var("system_01_path_home","$system_01_path_home");
		$t->set_var("system_01_onoff","$system_01_onoff");	
		$t->set_var("system_01_orden","$system_01_orden");	
		$t->set_var("bgcolor","$bgcolor");	
		$t->parse("LISTADO","un_root",true);
	}
}
else
{
	$t->set_var("LISTADO","No hay datos...");
}
					


if ($system_07_a == "1")
{
	$url="'modulos/root/php/home_abm.php'";
	$id="'content_0'";
	$vars="''";		
	$t->set_var("funcion_agregar","cargar_post($url,$id,$vars)");
}
else
{
	$t->set_var("funcion_agregar","acceso_denegado()");
}


$t->pparse("OUT", "ver");
?>