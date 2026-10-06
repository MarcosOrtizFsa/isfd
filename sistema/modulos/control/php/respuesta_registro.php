<?php
session_start();
include "../../../../lib/template.inc";
include "../../../../lib/mysql_conect.php";
include "../../../php/constructor_sql.php";
include "../../../php/abm.php";
include "../../../php/funciones.php";

$t = new _template('../templates');
$t->set_file(array(
	'ver'	=> "respuesta_registro.html"
	));



	$row = $mysqli -> consulta_SQL("Select system_04_perfil.*, system_03_usuarios.* from system_04_perfil, system_03_usuarios
					where 
					system_04_perfil.rela_system_03 = system_03_usuarios.id_system_03 
					and 	
					system_04_perfil.system_04_dni = '$system_04_dni'
					");
	if ($row == TRUE)
	{			
		$id_system_03 = 		$row[0]['id_system_03'];
		$system_03_cuir = 		$row[0]['system_03_cuir'];
		$rela_system_07 = 		$row[0]['rela_system_07'];
		$id_system_04 = 		$row[0]['id_system_04'];
		$system_04_nombre = 	$row[0]['system_04_nombre'];
		$system_04_apellido = 	$row[0]['system_04_apellido'];
		$system_04_email = 		$row[0]['system_04_email'];
		$system_04_dni = 		$row[0]['system_04_dni'];
		$system_04_celular = 	$row[0]['system_04_celular'];
		$system_04_provincia = 	$row[0]['system_04_provincia'];
		$system_04_localidad = 	$row[0]['system_04_localidad'];
		$system_04_barrio = 	$row[0]['system_04_barrio'];
		$system_04_direccion = 	$row[0]['system_04_direccion'];
		$t->set_var("msj_resultado",'Siguiente si eres t&uacute;');
		$t->set_var("msj_boton",'Siguiente...');
		$t->set_var("btn_boton",'btn-success');
		$t->set_var("ver_captcha",'hide');
		
		$no_soy ="";
		$t->set_var("msj_boton_opciones",'<button type="button" class="btn btn-secondary"  onClick="'.$no_soy.'" style="width:100%; margin-top:10px;">No soy esta persona</button>');
	}
	else
	{
		$id_system_03 = 		'';
		$rela_system_07 = 		'6';// cliente reistrado
		$id_system_04 = 		'';
		$system_04_nombre = 	'';
		$system_04_apellido = 	'';
		$system_04_email = 		'';
		$system_04_celular = 	'';
		$system_04_provincia = 	'';
		$system_04_localidad = 	'';
		$system_04_barrio = 	'';
		$system_04_direccion = 	'';
		$system_03_cuir = $system_checked;
		$t->set_var("msj_boton",'Registrarme Ahora');
		$t->set_var("btn_boton",'btn-primary');
		$t->set_var("msj_resultado",'Complete el registro para continuar...');
		$t->set_var("ver_captcha",'');
		$t->set_var("msj_boton_opciones",'');			
		$_SESSION['captcha_session'] = crear_capcha();
	}


		
	$t->set_var("system_04_nombre",		$system_04_nombre);
	$t->set_var("system_04_apellido",	$system_04_apellido);
	$t->set_var("system_04_email",		$system_04_email);
	$t->set_var("system_04_dni",		$system_04_dni);
	$t->set_var("system_04_celular",	$system_04_celular);
	$t->set_var("system_04_localidad",	$system_04_localidad);
	$t->set_var("system_04_barrio",		$system_04_barrio);
	$t->set_var("system_04_direccion",	$system_04_direccion);

	
	
	$url="'modulos/control/php/_interfaz.php'"; // siempre va a abm_interfaz
	$vars="'nombre_funcion=agregar_modificar&";
	$vars.="rela_system_07=$rela_system_07&id_system_03=$id_system_03&id_system_11=$id_system_11&";
	$vars.="id_system_13=$id_sesion_13&system_03_cuir=$system_03_cuir&";
	//$vars.="system_04_email='+smp.system_04_email.value+'&";
	$vars.="system_04_dni='+smp.system_04_dni.value+'&";
	$vars.="system_04_celular='+smp.system_04_celular.value+'&";
	$vars.="system_04_apellido='+encodeURIComponent(smp.system_04_apellido.value)+'&";
	$vars.="system_04_nombre='+encodeURIComponent(smp.system_04_nombre.value)+'&";
	$vars.="system_04_provincia='+encodeURIComponent(smp.system_04_provincia.value)+'&";
	$vars.="system_04_localidad='+encodeURIComponent(smp.system_04_localidad.value)+'&";
	$vars.="system_04_barrio='+encodeURIComponent(smp.system_04_barrio.value)+'&";
	$vars.="system_04_direccion='+encodeURIComponent(smp.system_04_direccion.value)+'&";
	$vars.="codigo_captcha='+smp.codigo_captcha.value";
	
	$url_exito="'modulos/public_html/php/popup_canvas_left_lista.php'";
	$id="'popup_canvas'";
	$vars_exito="'id_system_11=$id_system_11'";


	$t->set_var("funcion_registrar_cliente","guardar_mostrar($url,$vars,$url_exito,$id,$vars_exito)");		

	$t->set_var("funcion_volver","cargar_post($url_exito,$id,$vars_exito)");		

	
	
						
$t->pparse("OUT", "ver");
?>
