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
	'ver'	=> "registrate.html"
	));

$system_03_cuir = isset($_POST['system_03_cuir']) ? $_POST['system_03_cuir'] : NULL;


$row = $mysqli -> consulta_SQL("Select system_04_perfil.*, system_03_usuarios.*
		from 
		system_04_perfil, system_03_usuarios 
		where 
		system_04_perfil.rela_system_03=system_03_usuarios.id_system_03 
		and 
		system_03_usuarios.system_03_cuir='$system_03_cuir' 
		");
if ($row == TRUE)
{
	$id_system_03 = 		$row[0]['id_system_03'];
	$id_system_04 = 		$row[0]['id_system_04'];
	$system_04_nombre = 	$row[0]['system_04_nombre'];
	$system_04_apellido = 	$row[0]['system_04_apellido'];
	$system_04_profesion = 	$row[0]['system_04_profesion'];// razon soial
	$system_04_celular = 	$row[0]['system_04_celular'];
	$system_04_email = 		$row[0]['system_04_email'];
	$system_04_cuil = 		$row[0]['system_04_cuil'];	

	$titulo_modulo='<div class="col-12"><button type="button" class="btn btn-warning"  onclick="location.href=\'../sistema\'" style=" width:100%;"><i class="bi bi-emoji-smile"></i> Listo '.$system_04_nombre.'! Pronto te daremos el alta...</button></div> ';	

	$rela_system_07="";
	$rela_system_06="";
}
else
{
	$rela_system_07="5"; // asigno privilegios cliente
	$rela_system_06="1"; // asigno rela_6 sala del sitio
	$system_04_nombre = '';
	$system_04_apellido = '';
	$system_04_profesion = 		'';
	$system_04_celular = 	'';
	$system_04_email = 		'';
	$system_03_cuir = 	$system_checked;
	$titulo_modulo=' <div class="col-8"><button type="button"  onClick="{funcion_guardar}" class="btn btn-success" style=" width:100%;">Registrarme ahora</button></div><div class="col-4"><button type="button" class="btn btn-danger"  onclick="location.href=\'../sistema\'" style=" width:100%;">Cancelar</button></div><br>* Obligatorio &nbsp;&nbsp;&nbsp;&nbsp;** CUIT Usuario ';	
	//$_SESSION['captcha_session']=crear_capcha();
}

	$t->set_var("titulo_modulo",$titulo_modulo);
	
	$url="'modulos/login/php/_interfaz.php'"; // siempre va a abm_interfaz
	$vars="'nombre_funcion=agregar_registro&";
	$vars.="rela_system_07=$rela_system_07&";
	$vars.="rela_system_06=$rela_system_06&";
	$vars.="system_03_cuir=$system_03_cuir&";	
	$vars.="system_04_nombre='+encodeURIComponent(abm.system_04_nombre.value)+'&";
	$vars.="system_04_apellido='+encodeURIComponent(abm.system_04_apellido.value)+'&";
	//$vars.="rela_system_49='+abm.rela_system_49.value+'&";
	$vars.="system_04_cuil='+abm.system_04_cuil.value+'&";
	//$vars.="system_04_fecha_nacimiento='+abm.system_04_fecha_nacimiento.value+'&";	
	//$vars.="system_04_lugar_nacimiento='+abm.system_04_lugar_nacimiento.value+'&";		
	$vars.="system_04_email='+abm.system_04_email.value+'&";
	//$vars.="system_04_profesion='+encodeURIComponent(abm.system_04_profesion.value)+'&";
	//$vars.="system_04_ocupacion='+encodeURIComponent(abm.system_04_ocupacion.value)+'&";
	//$vars.="system_04_telefono='+encodeURIComponent(abm.system_04_telefono.value)+'&";
	$vars.="system_04_celular='+encodeURIComponent(abm.system_04_celular.value)+'&";
	//$vars.="system_04_detalles='+encodeURIComponent(abm.system_04_detalles.value)+'&";
	//$vars.="system_04_direccion='+encodeURIComponent(abm.system_04_direccion.value)+'&";
	//$vars.="system_04_localidad='+encodeURIComponent(abm.system_04_localidad.value)+'&";	
	//$vars.="codigo_captcha='+abm.codigo_captcha.value+'&";			
	$vars.="system_04_profesion='+abm.system_04_profesion.value";	
	
	$url_exito="'modulos/login/php/registrate.php'";
	$id="'content_registrate'";
	$vars_exito="'system_03_cuir=$system_checked'";	


	$t->set_var("funcion_guardar","guardar_mostrar($url,$vars,$url_exito,$id,$vars_exito)");
	
	$url="'modulos/login/php/registrate.php'";
	$id="'content_registrate'";
	$vars="'reset=go'";
	$t->set_var("funcion_recargar_capcha","cargar_post($url,$id,$vars)");

$t->pparse("OUT", "ver");
?>