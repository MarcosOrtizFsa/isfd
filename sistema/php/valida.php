<?php
ini_set("session.cache_expire",30);
ini_set("session.gc_maxlifetime",9000);
session_start();
include "../../lib/mysql_conect.php";
include "constructor_sql.php";
include "abm.php";
include "funciones.php";

$usu = 	isset($_POST['usu']) ? $_POST['usu'] : NULL;
$cla = 	isset($_POST['cla']) ? $_POST['cla'] : NULL;
$cuir = isset($_POST['cuir']) ? $_POST['cuir'] : NULL;
$rr = 	isset($_GET['rr']) ? $_GET['rr'] : NULL;
$cc = 	isset($_GET['cc']) ? $_GET['cc'] : NULL;
$tt = 	isset($_GET['tt']) ? $_GET['tt'] : NULL;
$pp = 	isset($_GET['pp']) ? $_GET['pp'] : NULL; // pp es publico
$mm = 	isset($_GET['mm']) ? $_GET['mm'] : NULL; // mm es mesa
$usu = 	formatear_dni($usu); /*perfil del usuario*/
$cla = 	sha1($cla, false);

$id_system_03=		'';
$rela_system_07=	'';
$rela_system_06=	'';
$system_03_modo=	''; 
$system_03_estado=	'';
$tipo_login = 'LOGIN';

if( $pp !='' )// LINK MONITOR PUBLICO
{
	$_SESSION['sesion_system_03'] = 		0; // sin usuario
	$_SESSION['sesion_system_07'] = 		7; // monitor publico
	$_SESSION['sesion_system_06'] = 		1;	
	$_SESSION['sesion_system_03_modo'] = 	3;// 0=Root / 1=Gerencia | 2=Administradores | 3 publico 															

	header("Location: ../monitor");
	exit;

}
else
if($rr!='' and $cc!='' and $tt!='')// SE TRATA DE UN INGRESO POR LINK 
{
	if ( $tt < $system_checked ) // tiempo de expiracion del link
	{
		echo "<h1>El v&iacute;nculo ha expirado...</h1>";
		echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2; URL=../../../sistema\">";
		exit;
	}
	$row = $mysqli -> login_pasaport($rr,$cc);
}
else
{
	$row = $mysqli -> login_validar($usu,$cla);
}

if ($row == TRUE)
{
	$id_system_03=		$row[0]['id_system_03'];
	$rela_system_07=	$row[0]['rela_system_07']; // PRIVILEGIOS
	$rela_system_06=	$row[0]['rela_system_06']; // BLOG DE ADMINISTRADOR
	$system_03_modo=	$row[0]['system_03_modo']; //  	0=root, 1=gerente, 2=admin, 3=usuario 
	$system_03_estado=	$row[0]['system_03_estado'];

	if ($system_03_estado=='0')
	{
		$res_login= "0:espera";
	}
	else
	if ($system_03_estado=='2')
	{
		$res_login= "0:suspendido";
	}
	else
	if ($system_03_estado=='3')
	{
		$res_login= "0:rechazado";
	}
	else
	{
		$row = $mysqli -> system_07_privilegios($rela_system_07);
		$id_system_07=							$row[0]['id_system_07'];
		$system_07_admin=						$row[0]['system_07_admin'];
		$_SESSION['sesion_system_03'] = 		$id_system_03;
		$_SESSION['sesion_system_07'] = 		$id_system_07; 
		$_SESSION['sesion_system_06'] = 		$rela_system_06;	
		$_SESSION['sesion_system_03_modo'] = 	$system_03_modo;// 0=Root / 1=Gerencia | 2=Administradores | 3 usuario /clientes 															
		
		// VERIFICAMOS SI COMPETA LOS DATOS DEL USUARIO
		$row = $mysqli -> system_04_perfil($id_system_03);
		if ($row == TRUE)
		{
			if( $row[0]['system_04_apellido']=='' || $row[0]['system_04_dni']=='' || $row[0]['system_04_email']==''  || $row[0]['system_04_celular']=='' )
			{
				$_SESSION['sesion_perfil'] = $id_system_03;// DATOS OBLIGATORIOS DEL PERFIL
			}
			
			$rela_system_01="";
			$system_05_accion="LOGIN";
			$system_05_detalles="";
			$system_05_mensaje="";
			guardar_logistica($id_system_03,$rela_system_07,$rela_system_06,$rela_system_01,$tipo_login,$system_05_detalles,$system_05_mensaje,$mysqli);
	
			$res_login= "1:ok";	
			
		}
		else
		{
			$res_login= "0:incompleto";
		}

	}			
}
else
{
	$res_login= "0:restringido";
}		


if( $tt !='' )// INGRESO POR LINK DIPUTADOS
{
	if ( $id_system_07 == '6' )// es un seguidor 
	{
		header("Location: ../../../sistema");
	}
	else
	if ( $id_system_07 == '5' )// es un alumno 
	{
		header("Location: ../../../sistema");
	}
	else
	{
		unset($_SESSION['sesion_system_03']);
		unset($_SESSION['sesion_system_07']);
		unset($_SESSION['sesion_system_06']);
		unset($_SESSION['sesion_system_03_modo']);
		session_destroy();
		echo "<h1>No se logr&oacute; el vinculo...</h1>";
		echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2; URL=../../../sistema\">";
		exit;
	}	
}
else
{
	echo $res_login;
}	
?>
