<?php
session_start();
include "../../../../lib/template.inc";
include "../../../../lib/mysql_conect.php";
include "../../../php/constructor_sql.php";
include "../../../php/abm.php";
include "../../../php/funciones.php";
include "_abm.php";
$mysqli = new _Abm($base);

$rela_system_07 = isset($_POST['rela_system_07']) ? $_POST['rela_system_07'] : NULL;
$rela_system_06 = isset($_POST['rela_system_06']) ? $_POST['rela_system_06'] : NULL;
$system_04_nombre = isset($_POST['system_04_nombre']) ? $_POST['system_04_nombre'] : NULL;
$system_04_apellido = isset($_POST['system_04_apellido']) ? $_POST['system_04_apellido'] : NULL;
$system_04_email = isset($_POST['system_04_email']) ? $_POST['system_04_email'] : NULL;
$system_04_celular = isset($_POST['system_04_celular']) ? $_POST['system_04_celular'] : NULL;
$system_04_cuil = isset($_POST['system_04_cuil']) ? $_POST['system_04_cuil'] : NULL;
$system_04_profesion = isset($_POST['system_04_profesion']) ? $_POST['system_04_profesion'] : NULL ;
$system_03_cuir = isset($_POST['system_03_cuir']) ? $_POST['system_03_cuir'] : NULL;
$codigo_captcha = isset($_POST['codigo_captcha']) ? $_POST['codigo_captcha'] : NULL;
$nombre_funcion = isset($_POST['nombre_funcion']) ? $_POST['nombre_funcion'] : NULL;

$system_04_cuil=formatear_cuit($system_04_cuil);


switch ($nombre_funcion)
{
	
	case "agregar_registro":	
	$mensaje = $mysqli -> agregar_registro(	$rela_system_07,
											$rela_system_06,											
											$system_04_nombre,
											$system_04_apellido,
											$system_04_profesion,	
											formatear_cuit($system_04_cuil),
											$system_04_email,
											$system_04_celular,															
											$system_fecha,
											$system_hora,
											$system_03_cuir,
											$codigo_captcha,
											$system_08_titulo_site,
											$system_08_dominio,	
											$system_08_descripcion_site,
											$system_08_celular,
											$system_08_email_alerta,
											$system_08_email
											);
	break;


		
	default:
	$mensaje="No hay funci&oacute;n...";
	break;	
}
echo "$mensaje";
?>
