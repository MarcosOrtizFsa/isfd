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
	'ver'	=> "recuperar.html"
	));
	
if ($reset =='go' )
{
$_SESSION['captcha_session']='';
}

$reset = isset($_POST['reset']) ? $_POST['reset'] : NULL;	
$codigo_captcha = isset($_POST['codigo_captcha']) ? $_POST['codigo_captcha'] : NULL;	
$system_04_email = isset($_POST['system_04_email']) ? $_POST['system_04_email'] : NULL;	

if ($codigo_captcha !='' and $system_04_email !='' )
{
	
	$row = $mysqli -> consulta_SQL("Select system_04_perfil.*, system_03_usuarios.*
		from 
		system_04_perfil, system_03_usuarios 
		where 
		system_04_perfil.rela_system_03=system_03_usuarios.id_system_03 
		and 
		system_04_perfil.system_04_email='$system_04_email' 
		");
	if ($row == TRUE)
	{
			$system_03_estado = $row[0]['system_03_estado'];
			$system_03_cuir = $row[0]['system_03_cuir'];
			
			if ($system_03_estado == '2')
			{
				$msj ='<i class="bi bi-exclamation-diamond"></i> Tu registro fu&eacute; suspendido...';
			}
			else
			if ($system_03_estado == '0')
			{
				$msj ='<i class="bi bi-emoji-neutral"></i> Tu registro no esta confirmado a&uacute;n...\nVerif&iacute;ca entre tus correos No Deseados.';
			}
			else
			{
				$msj = $mysqli -> recuperar_clave(	
											$system_04_email,
											$system_03_cuir,														
											$system_fecha,
											$system_hora,
											$codigo_captcha,
											$system_08_titulo_site,
											$system_08_dominio,	
											$system_08_descripcion_site,
											$system_08_celular,
											$system_08_email_alerta,
											$system_08_email
											);
			}
	}
	else
	{
	$msj ='<i class="bi bi-emoji-frown"></i> Este e-mail no pertenece a este sistema...';
	}

		
	$titulo_modulo=$msj;

}
else
{
	$system_04_email = '';
	$titulo_modulo='';
	$_SESSION['captcha_session']=crear_capcha();
}

		
	
	$t->set_var("titulo_modulo",$titulo_modulo);
	$t->set_var("system_04_email",$system_04_email);
	
	$url="'modulos/login/php/recuperar.php'";
	$id="'content_registrate'";
	$vars="'system_04_email='+abm.system_04_email.value+'&";			
	$vars.="codigo_captcha='+abm.codigo_captcha.value";	


	$t->set_var("funcion_verificar","cargar_post($url,$id,$vars)");
	
	$url="'modulos/login/php/recuperar.php'";
	$id="'content_registrate'";
	$vars="'reset=go'";
	$t->set_var("funcion_recargar_capcha","cargar_post($url,$id,$vars)");

$t->pparse("OUT", "ver");
?>