<?php
session_start();
include "../../../../lib/template.inc";
include "../../../../lib/mysql_conect.php";
include "../../../php/constructor_sql.php";
include "../../../php/abm.php";
include "../../../php/funciones.php";
include "_abm.php";
$mysqli = new _Abm($base);


$id_system_01= 		isset($_POST['id_system_01']) ? $_POST['id_system_01'] : NULL;
$system_01_modulo= 		isset($_POST['system_01_modulo']) ? $_POST['system_01_modulo'] : NULL;
$system_01_tipo= 		isset($_POST['system_01_tipo']) ? $_POST['system_01_tipo'] : NULL;
$system_01_path_home= 		isset($_POST['system_01_path_home']) ? $_POST['system_01_path_home'] : NULL;
$system_01_onoff= 		isset($_POST['system_01_onoff']) ? $_POST['system_01_onoff'] : NULL;
$system_01_estado= 		isset($_POST['system_01_estado']) ? $_POST['system_01_estado'] : NULL;
$system_01_orden= 		isset($_POST['system_01_orden']) ? $_POST['system_01_orden'] : NULL;
$nombre_funcion= 		isset($_POST['nombre_funcion']) ? $_POST['nombre_funcion'] : NULL;
	
switch ($nombre_funcion)
{
	
	case "agregar_modificar":	
	$mensaje = $mysqli -> agregar_modificar($id_system_01,$system_01_modulo,$system_01_tipo,$system_01_path_home,$system_01_onoff,$system_01_orden);
	break;
	
	
	case "eliminar":
	$mensaje = $mysqli -> eliminar($id_system_01);
	break;
	
	
	case "on_off":
	$mensaje = $mysqli -> on_off($id_system_01,$system_01_estado);
	break;
	
	default:
	$mensaje="No hay funci&oacute;n...";
	break;	
}
echo "$mensaje";
?>
