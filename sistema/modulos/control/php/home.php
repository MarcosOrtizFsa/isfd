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
	'ver'	=> "home.html",
	'un_control'=> "un_control.html",
	'una_opcion'=> "una_opcion.html"
	));
$t->set_var("titulo_modulo","Usuarios");

$system_07_ver = isset($_POST['system_07_ver']) ? $_POST['system_07_ver'] : NULL;
$system_07_priv = isset($_POST['system_07_priv']) ? $_POST['system_07_priv'] : NULL;
$system_estado = isset($_POST['system_estado']) ? $_POST['system_estado'] : NULL;

if ($reset == "go")
{
$_SESSION['where_control'] = "";
$where_control = isset($_SESSION['where_control']) ? $_SESSION['where_control'] : NULL;
}

$where=" 	WHERE 
			system_04_perfil.rela_system_03=system_03_usuarios.id_system_03 
			and 
			system_07_privilegios.id_system_07=system_03_usuarios.rela_system_07 
			";			

if ( trim($system_07_ver)!="" or trim($system_07_priv)!="" or trim($variable_buscar)!="" or trim($system_estado)!="")
{
		if ( $system_estado!='' )
		{
			$where.=" and system_03_usuarios.system_03_estado='$system_estado' ";	
		}

		if ( $system_07_ver=='1' )
		{
			$where.=" and system_07_privilegios.system_07_admin='1' ";	
		}

		if ( $system_07_ver=='2' )
		{
			$where.=" and system_07_privilegios.system_07_public='1' ";	
		}
		

		if ( $system_07_priv!='' )
		{
			$where.=" and system_03_usuarios.rela_system_07='$system_07_priv' ";	
		}
		
		$variable_buscar = trim($variable_buscar);
		if ( $variable_buscar!='' )
		{
			$variable_buscar=formatear_dni($variable_buscar);
			
			if (ctype_digit($variable_buscar)) 
			{	
				
				if (strlen($variable_buscar)=='8') //  es un dni
				{
				  $where.=" and system_04_perfil.system_04_dni='$variable_buscar'  ";
				}
				else // es un id
				{
				 $where.=" and system_03_usuarios.id_system_03='$variable_buscar' ";
				}		
			} 
			else 
			{
			  $where.=" and system_04_perfil.system_04_apellido like '%$variable_buscar%'  ";
			}
		}
		
		$where.=" and system_04_perfil.rela_system_03=system_03_usuarios.id_system_03 and system_07_privilegios.id_system_07=system_03_usuarios.rela_system_07 ";
	
		$_SESSION['where_control']= $where;
}
else
{	

	if ( (isset($_SESSION['where_control']) ? $_SESSION['where_control'] : NULL) !='' )
	{
	
	}
	else
	{
	$_SESSION['where_control']= $where;
	}
}


$where_control = isset($_SESSION['where_control']) ? $_SESSION['where_control'] : NULL;

if ($sesion_system_07=='1')
{
$and  = " and  system_03_usuarios.system_03_modo IN ('0','1','2','3') ";								
}
else
if ($sesion_system_07=='2')
{
$and  = " and  system_03_usuarios.system_03_modo IN  ('1','2','3') ";	
}
else
{
$and  = " and  system_03_usuarios.system_03_modo IN ('3') ";	
}
//  	0=root, 1=gerente, 2=admin, 3=usuario 
//echo $where_control.' '.$and ;
$tSQL = $mysqli -> consulta_SQL("Select COUNT(*) as total_filas from system_04_perfil, system_03_usuarios, system_07_privilegios 
								$where_control
								");
if ($tSQL == TRUE)
{		
	$total_filas = $tSQL[0]['total_filas'];
}
else
{
	$total_filas = '0';		
}
						
$row = $mysqli -> consulta_SQL("Select system_04_perfil.*, system_03_usuarios.*, system_07_privilegios.* from system_04_perfil, system_03_usuarios, system_07_privilegios 
								
								$where_control
								$and 
								
								ORDER BY system_03_usuarios.id_system_03 DESC 
								$LIMITE
								");
if ($row == TRUE)
{
	for ( $i=0; $i < count($row); $i++)
	{	
				
		$id_system_03 = 		$row[$i]['id_system_03'];
		$id_system_04 = 		$row[$i]['id_system_04'];
		$id_system_07 = 		$row[$i]['id_system_07'];
		$rela_system_07 = 		$row[$i]['rela_system_07'];
		$system_03_usuario = 	$row[$i]['system_03_usuario'];
		$system_03_clave = 		$row[$i]['system_03_clave'];
		$system_03_cuir = 		$row[$i]['system_03_cuir'];
		$system_03_estado = 	$row[$i]['system_03_estado'];	
		$system_04_celular = 	$row[$i]['system_04_celular'];
		$system_07_nombre = 	$row[$i]['system_07_nombre'];
		
		$t->set_var("id_system_03",			"$id_system_03");
		$t->set_var("system_03_usuario",	"$system_03_usuario");
		$t->set_var("system_03_fecha",		$row[$i]['system_03_fecha']);
		$t->set_var("system_04_nombre",		$row[$i]['system_04_nombre']);
		$t->set_var("system_04_apellido",	$row[$i]['system_04_apellido']);
		$t->set_var("system_04_email",		$row[$i]['system_04_email']);
		$t->set_var("system_04_dni",		$row[$i]['system_04_dni']);
		$t->set_var("system_04_celular",	"$system_04_celular");
		
		// on off
		if ( optener_permisos('E',$id_system_01,$sesion_system_03,$mysqli) == '1' )
		{	
			$url="'modulos/control/php/_interfaz.php'";
			$vars="'nombre_funcion=on_off&id_system_03=$id_system_03&system_03_estado=$system_03_estado'";
			$url_exito="'modulos/control/php/home.php'";
			$id="'content_seccion'";
			$vars_exito="'id_system_01=$id_system_01'";
			$t->set_var("on_off","guardar_mostrar($url,$vars,$url_exito,$id,$vars_exito);");	
		}
		else
		{
			$t->set_var("on_off","sin_permisos()");
		}
				
		// modificar 
		if ( optener_permisos('M',$id_system_01,$sesion_system_03,$mysqli) == '1')
		{				
			$url="'modulos/control/php/home_abm.php'";
			$id="'content_seccion'";
			$vars="'id_system_01=$id_system_01&id_system_03=$id_system_03'";			
			$t->set_var("funcion_editar","cargar_post($url,$id,$vars)");	
		}
		else
		{
			$t->set_var("funcion_editar","sin_permisos()");
		}	
		
		// elimiar
		if ( optener_permisos('B',$id_system_01,$sesion_system_03,$mysqli) == '1' and  $candado=='on' )
		{
			$url="'modulos/control/php/_interfaz.php'";
			$vars="'nombre_funcion=eliminar&";
			$vars.="id_system_03=$id_system_03'";
			$url_exito="'modulos/control/php/home.php'";
			$id="'content_seccion'";
			$vars_exito="'id_system_01=$id_system_01'";
			$atx="''";
			$msg="'Eliminar este registro?'";
			$t->set_var("funcion_borrar","eliminar_mostrar($url,$vars,$url_exito,$id,$vars_exito,$msg,$atx);");
		}
		else
		{
			$t->set_var("funcion_borrar","sin_permisos()");
		}		
				
		if ($system_03_estado == '2')
		{
			$t->set_var("system_03_estado","bi bi-eye-slash-fill");	
			$t->set_var("estado","SUSPENDIDO");		
		}
		else
		if ($system_03_estado == '1')
		{
			$t->set_var("system_03_estado","bi bi-eye-fill");
			$t->set_var("estado","ACTIVO");	
		}
		else
		{
			$t->set_var("system_03_estado","bi bi-eye");
			$t->set_var("estado","PENDIENTE");	
		}
		
				// ASIGNO MODULOS	
		if ( optener_permisos('V',$id_system_01,$sesion_system_03,$mysqli) == '1' )
		{				
			$url="'modulos/control/php/popup_canvas.php'";
			$id="'popup_canvas'";
			$vars="'id_system_01=$id_system_01&id_system_03=$id_system_03'";
			$t->set_var("funcion_pop_canvas","cargar_post($url,$id,$vars)");		
		}
		else
		{
			$t->set_var("funcion_pop_canvas","sin_permisos()");	
		}


		// WHATSAPP LINK
		if ( optener_permisos('V',$id_system_01,$sesion_system_03,$mysqli) == '1' and  $rela_system_07=='5' )
		{					
			//$system_03_clave = password_hash('$system_03_clave', PASSWORD_DEFAULT, array('cost'=>4));
			//$system_checked_tempo = $system_checked + 68400;// agrego 24 horas de tiempo para que vensa el link
			$system_checked_tempo = $system_checked + 31104000;// agrego 1 año de tiempo para que vensa el link
			//$system_checked_tempo = "9999999999"; // activo sin limite de tiempo
			$t->set_var("funcion_link_whatsapp",'<a href="whatsapp://send?phone='.prefijo_whatsapp($system_04_celular).'&text='.$system_08_dominio.'/login/'.$system_03_cuir.'/'.$system_03_clave.'/'.$system_checked_tempo.'" data-action="share/whatsapp/share"><i class="bi bi-whatsapp"></i></a>');
			$t->set_var("funcion_link_banca",'<a href="'.$system_08_dominio.'/login/'.$system_03_cuir.'/'.$system_03_clave.'/'.$system_checked_tempo.'" target="news"><i class="bi bi-link"></i></a>');		

		$t->set_var("funcion_pop_canvas","");
		}
		else
		{
			$t->set_var("funcion_link_whatsapp",'');
			$t->set_var("funcion_link_banca",'');
		}
		
		

		
		// ASIGNAR CALIFICACIONES	
		if ( optener_permisos('A',$id_system_01,$sesion_system_03,$mysqli) == '1' )
		{				
			// POPUP SERVCIOS		
			$url="'modulos/calificacion/php/pop_niveles.php'";
			$id="'popup_canvas_left'";
			$vars="'id_system_01=$id_system_01&id_system_03=$id_system_03'";
			$funcion_pop_niveles = "cargar_post($url,$id,$vars)";
			
			$url="'modulos/calificacion/php/pop_pre.php'";
			$id="'popup_canvas_left'";
			$vars="'id_system_01=$id_system_01&id_system_03=$id_system_03'";
			$funcion_pop_preinscripciones = "cargar_post($url,$id,$vars)";
			
			if ($id_system_07 == '5')// si es un alumno muestro preinscripcion
			{
				$t->set_var("ico_preinscripciones",'<span  type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample" onClick="'.$funcion_pop_preinscripciones.'" title="Preinscripciones"><i class="bi bi-chat-right-text"></i></span>');
			}
			else
			{
				$t->set_var("ico_preinscripciones",'');
			}
		}
		else
		{
			$funcion_pop_niveles = "sin_permisos()";
			$funcion_pop_preinscripciones = "sin_permisos()";
			$t->set_var("ico_preinscripciones",'');
		}
		
		if ($id_system_07 == '5')// si es un alumno muestro su calificacion
		{
		$t->set_var("system_07_nombre",'<span  type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample" onClick="'.$funcion_pop_niveles.'" title="Nivel">'.$system_07_nombre.'</span>');
		}
		else
		{
		$t->set_var("system_07_nombre",		$system_07_nombre);
		}
		
		$t->parse("LISTADO","un_control",true);
		$id_system_03 = '';
		$id_system_04 = '';

	}
	
	
} 
else 						
{
	$t->set_var("LISTADO",'No hay mas registros...');
}						



	// Funciuon de agregar
	if (  optener_permisos('A',$id_system_01,$sesion_system_03,$mysqli) == '1' )
	{
		$url="'modulos/control/php/home_abm.php'";
		$id="'content_seccion'";
		$vars="'id_system_01=$id_system_01'";		
		$t->set_var("funcion_agregar","cargar_post($url,$id,$vars)");
		
		$url="'modulos/calificacion/php/home.php'";
		$id="'content_seccion'";
		$vars="'id_system_01=$id_system_01'";	
		$t->set_var("funcion_niveles","cargar_post($url,$id,$vars)");

	
	}
	else
	{
		$t->set_var("funcion_agregar","sin_permisos()");
		$t->set_var("funcion_niveles","sin_permisos()");
	}
	

	
	$sala="";
	$row = $mysqli -> consulta_SQL("SELECT * FROM system_07_privilegios
				where 
				system_07_nombre!='ROOT'
				$sala
				ORDER BY id_system_07 ASC
				
				");
	if ($row == TRUE)
	{
		for ( $i=0; $i < count($row); $i++)
		{				
			$id_system_07 = $row[$i]['id_system_07'];
			$system_07_nombre = $row[$i]['system_07_nombre'];
			$t->set_var("system_07_nombre",$system_07_nombre);		
			$t->set_var("ID","$id_system_07");
			$t->set_var("NOMBRE",$system_07_nombre);
			$t->parse("SECCIONES","una_opcion",true);	
		}	
	} 
	else 	
	{
		$t->SET_VAR("SECCIONES","No Existen privilegios");
	}




	// buscador
	$url2="'modulos/control/php/home.php'";
	$id2="'content_seccion'";
	$vars2="'id_system_01=$id_system_01&system_07_priv='";
	$t->set_var("funcion_selector","cargar_post($url2,$id2,$vars2+this.value); ");	

	// buscador
	$url3="'modulos/control/php/home.php'";
	$id3="'content_seccion'";
	$vars3="'id_system_01=$id_system_01&buscar=si&system_estado='";
	$t->set_var("funcion_selector_estado","cargar_post($url3,$id3,$vars3+this.value); ");	
	
	// buscador
	$url="'modulos/control/php/home.php'";
	$id="'content_seccion'";
	$vars="'id_system_01=$id_system_01&buscar=si&";		
	$vars.="variable_buscar='+busqueda.variable_buscar.value";
	$t->set_var("funcion_busqueda","cargar_post($url,$id,$vars)");



$url="'modulos/control/php/home.php'";
$id="'content_seccion'";
$vars="'id_system_01=$id_system_01&mas=$num_filas'";	
$PAGINAR = funcion_paginar_siguiente($total_filas,$num_filas,$url,$id,$vars);
$t->set_var("PAGINAR","$PAGINAR");


	
$pagExc='<a href="modulos/control/php/informe_csv.php?name_archivo=lista_registrados" target="news" title="Descargar Lista" style="font-size:10px;"><img src="../image/iconos/page_excel.png" border="0"></a>';	
$t->set_var("EXCELES","$pagExc");	

	
	
$t->pparse("OUT", "ver");
?>