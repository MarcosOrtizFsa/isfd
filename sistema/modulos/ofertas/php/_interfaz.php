<?php
// Controladores anteriores reemplazados por app_*.php.
http_response_code(410);
exit('Esta pantalla fue reemplazada. Ingrese desde mipanel.');

session_start();
include "../../../../lib/template.inc";
include "../../../../lib/mysql_conect.php";
include "../../../php/constructor_sql.php";
include "../../../php/abm.php";
include "../../../php/funciones.php";
include "_abm.php";
$mysqli = new _Abm($base);

$nombre_funcion= 		isset($_POST['nombre_funcion']) ? $_POST['nombre_funcion'] : NULL;
	
switch ($nombre_funcion)
{
	
	default:
	$mensaje="No hay funci&oacute;n...";
	break;	
}
echo "$mensaje";
?>
