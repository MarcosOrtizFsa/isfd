<?php
session_start();
include "../../../../lib/template.inc";
include "../../../../lib/mysql_conect.php";
include "../../../php/constructor_sql.php";
include "../../../php/abm.php";
include "../../../php/funciones.php";
include "_abm.php";
$mysqli = new _Abm($base);

$system_65_checked = 	isset($_POST['system_65_checked']) 	? $_POST['system_65_checked'] : NULL;
$system_mensaje = 	isset($_POST['system_mensaje']) 	? $_POST['system_mensaje'] : NULL;
$rela_system_11 = 	isset($_POST['rela_system_11']) 	? $_POST['rela_system_11'] : NULL;
$rela_system_19 = isset($_POST['rela_system_19']) ? $_POST['rela_system_19'] : NULL;
$rela_system_20 = isset($_POST['rela_system_20']) ? $_POST['rela_system_20'] : NULL;
$rela_system_21 = isset($_POST['rela_system_21']) ? $_POST['rela_system_21'] : NULL;
$id_system_19 = 	isset($_POST['id_system_19']) 	? $_POST['id_system_19'] : NULL;
$system_19_area = 	isset($_POST['system_19_area']) 	? $_POST['system_19_area'] : NULL;
$system_18a_tipo = 	isset($_POST['system_18a_tipo']) 	? $_POST['system_18a_tipo'] : NULL;
$system_19_estado = 	isset($_POST['system_19_estado']) 	? $_POST['system_19_estado'] : NULL;
$modo = 	isset($_POST['modo']) 	? $_POST['modo'] : NULL;
$system_08_contador = 	isset($_POST['system_08_contador']) 	? $_POST['system_08_contador'] : NULL;
$system_horario = 	isset($_POST['system_horario']) 	? $_POST['system_horario'] : NULL;

switch ($nombre_funcion)
{
	
	case "agregar_modificar_preinscripcion":	
	$mensaje = $mysqli -> agregar_modificar_preinscripcion(
					$rela_system_07,
					$id_system_11,
					$id_system_06,																	
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
					$system_65_checked,
					$system_mensaje,
					$system_horario,
					$mysqli
					);
	break;
	
	case "agregar_lista_mailing":	
	$mensaje = $mysqli -> agregar_lista_mailing(																
					$system_04_nombre,
					$system_04_apellido,
					$system_04_email,
					$system_04_celular,
					$system_mensaje,									
					$system_fecha,
					$system_hora,
					$system_checked,
					$mysqli
					);
	break;
	
	case "salvar_mensaje":
	$mensaje = $mysqli -> salvar_mensaje(	$system_17_checked,
											$system_17_tema,
											$system_17_apellido,
											$system_17_nombre,
											$system_17_email,
											$system_17_celular,
											$system_17_mensaje,
											$system_fecha,
											$system_hora,
											$codigo_captcha
										);
	break;
	

	case "agregar_remover_menu":
	$mensaje = $mysqli -> agregar_remover_menu($rela_system_11,$rela_system_19,$rela_system_20,$rela_system_21);
	break;
	
	case "agregar_editar_area":
	$mensaje = $mysqli -> agregar_editar_area($id_system_19,
										$system_19_area 
										);
	break;
	
	case "on_off_area":
	$mensaje = $mysqli -> on_off_area($id_system_19,
										$system_19_estado 
										);
	break;

	case "eliminar_area":
	$mensaje = $mysqli -> eliminar_area($id_system_19);
	break;

	case "editar_data":
	$mensaje = $mysqli -> editar_data($modo,$system_08_contador);
	break;
					
	default:
	$mensaje="No hay funci&oacute;n...";
	break;	
}
echo "$mensaje";
?>
