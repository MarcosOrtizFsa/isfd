<?php
session_start();
include "../../../../lib/mysql_conect.php";
include "../../../php/constructor_sql.php";
include "../../../php/abm.php";
include "../../../php/funciones.php";
include "_abm.php";
$mysqli = new _Abm($base);
$name_archivo = isset($_GET['name_archivo']) ? $_GET['name_archivo'] : NULL;

header("Content-type: application/vnd.ms-csv");
header("Content-Disposition: attachment; filename=\"$name_archivo.csv\";");

$where_control=$_SESSION['where_control'];


$total=0;			
$cadena='';
$cadena.='1ra columna;';
$cadena.='2da columna;';
$cadena.=("\n");		

$row = $mysqli -> consulta_SQL("$where_control");
if ($row == TRUE)
{
	for ( $i=0; $i < count($row); $i++)
	{	
		
		$cadena.=	$row[$i]['system_04_nombre'].';';
		$cadena.= 	$row[$i]['system_04_apellido'].';';
		$cadena.= 	$row[$i]['system_04_celular'].';';
		$cadena.= 	$row[$i]['system_04_email'].';';
		$cadena.= 	$row[$i]['system_04_cuil'].';';


	}
	
	
} 

	
	
echo $cadena;			
?>
