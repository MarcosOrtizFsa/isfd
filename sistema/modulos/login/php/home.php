<?php
session_start();
include __DIR__ . "/../../../../lib/template.inc";
include __DIR__ . "/../../../../lib/mysql_conect.php";
include __DIR__ . "/../../../php/constructor_sql.php";
include __DIR__ . "/../../../php/abm.php";
include __DIR__ . "/../../../php/funciones.php";
include __DIR__ . "/_abm.php";
$mysqli = new _Abm($base);

$t = new _template(__DIR__ . '/../templates');
$t->set_file(array(
	'ver'	=> 		"home.html"
	));



	
	$t->set_var("TITULO_LOGIN",''.$system_08_contactos_visibles.'');	
$t->set_var("time", time());
$t->set_var("usu", '');
$t->set_var("cla", '');


	$url="'modulos/login/php/registrate.php'";
	$id="'content_registrate'";
	$vars="'reset=go'";
	$t->set_var("funcion_registrate","cargar_post($url,$id,$vars)");


	$url="'modulos/login/php/recuperar.php'";
	$id="'content_registrate'";
	$vars="'reset=go'";
	$t->set_var("funcion_recuperar","cargar_post($url,$id,$vars)");
		

	$url="'modulos/home/php/home.php'";
	$id="'content_seccion'";
	$vars="''";
	$t->set_var("funcion_no_acepto","cargar_post($url,$id,$vars)");
		
	
		
$t->pparse("OUT", "ver");
?>
