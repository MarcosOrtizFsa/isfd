<?php
session_start();
include "../../../../lib/template.inc";
include "../../../../lib/mysql_conect.php";
include "../../../php/constructor_sql.php";
include "../../../php/abm.php";
include "../../../php/funciones.php";
include "_abm.php";
$mysqli = new _Abm($base);


$id_system_02 = isset($_POST['id_system_02']) ? $_POST['id_system_02'] : NULL;
$system_02_A  = isset($_POST['system_02_A']) ? $_POST['system_02_A'] : NULL;
$system_02_B  = isset($_POST['system_02_B']) ? $_POST['system_02_B'] : NULL;
$system_02_M  = isset($_POST['system_02_M']) ? $_POST['system_02_M'] : NULL;
$system_02_E  = isset($_POST['system_02_E']) ? $_POST['system_02_E'] : NULL;
$system_02_V  = isset($_POST['system_02_V']) ? $_POST['system_02_V'] : NULL;
$system_02_S = isset($_POST['system_02_S']) ? $_POST['system_02_S'] : NULL;
$system_02_D  = isset($_POST['system_02_D']) ? $_POST['system_02_D'] : NULL;
$system_02_I  = isset($_POST['system_02_I']) ? $_POST['system_02_I'] : NULL;
$system_02_C  = isset($_POST['system_02_C']) ? $_POST['system_02_C'] : NULL;



$system_05_detalles="";	
				
switch ($nombre_funcion)
{
		
	case "agregar_modificar":	
	$system_05_mensaje = $mysqli -> agregar_modificar(
					$id_system_01,
					$id_system_03,
					$id_system_13,
					$id_system_11,
					$id_system_06,	
					$rela_system_07,																
					$system_04_nombre,
					$system_04_apellido,
					$system_04_dni,	
					$system_04_cuil,
					$system_04_email,
					$system_04_celular,	
					$system_04_telefono,						
					$system_04_localidad,
					$system_04_provincia,
					$system_04_direccion,
					$system_04_barrio,
					$system_04_maps,
					$system_04_detalles,								
					$system_fecha,
					$system_hora,
					$system_checked,
					$codigo_captcha,
					$codigo_tabla,
					$mysqli
					);
	$system_05_detalles="Registrador: $sesion_system_03 | $id_system_03, $rela_system_07, $system_04_nombre $system_04_apellido, $system_04_dni, $system_04_cuil, $system_checked, $codigo_tabla";
	break;
	
	case "eliminar":
	$system_05_mensaje = $mysqli -> eliminar($id_system_03);
	$system_05_detalles="$id_system_03";
	break;	
	
	case "on_off":
	$system_05_mensaje = $mysqli -> on_off($id_system_03,$system_03_estado);
	$system_05_detalles="$id_system_03,$system_03_estado";
	break;

	case "salvar_clave":
	$system_05_mensaje = $mysqli -> salvar_clave($id_system_03,$system_03_clave,$system_03_clave_copy);
	$system_05_detalles="$id_system_03,$system_03_clave,$system_03_clave_copy";
	break;

	case "asignar_modulo":
	$system_05_mensaje = $mysqli -> asignar_modulo($id_system_01,$id_system_03);
	$system_05_detalles="$id_system_01,$id_system_03";
	break;
	
	case "saltar_completado":
	$_SESSION['sesion_perfil']="";
	break;

	case "salvar_permiso":
	$system_05_mensaje = $mysqli -> salvar_permiso(
		$id_system_02,
		$system_02_A,
		$system_02_B,
		$system_02_M,
		$system_02_E,
		$system_02_V,
		$system_02_S,
		$system_02_D,
		$system_02_I,
		$system_02_C
		);
	$system_05_detalles="$id_system_02,$sesion_system_03";
	break;

	case "remover":
	$system_05_mensaje = $mysqli -> remover($id_system_09);
	$system_05_detalles="$id_system_09,$sesion_system_03";
	break;
					
	default:
	$system_05_mensaje="No hay funci&oacute;n...";
	break;		
						
}
guardar_logistica($sesion_system_03,$sesion_system_07,$sesion_system_06,$id_system_01,$nombre_funcion,$system_05_detalles,$system_05_mensaje,$mysqli);

echo "$system_05_mensaje";
?>
