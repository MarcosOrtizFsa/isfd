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
$t -> set_file(array(
	'ver'	=> "select_1.html"
	));

$rela_system_07=isset($_POST['rela_system_07']) ? $_POST['rela_system_07'] : NULL;

$row = $mysqli -> consulta_SQL("Select * from system_07_privilegios where id_system_07='$rela_system_07' ");
if ($row == TRUE)
{
	$t->set_var("system_07_descripcion", nl2br($row[0]['system_07_descripcion']));
}
else
{
	$t->set_var("system_07_descripcion","No hay privilegios...");
}


$t->pparse("OUT", "ver");
?>
