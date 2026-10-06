<?php
// GENERAMOS UN NUMERO UNICO PARA IDENTIFICAR CUALQUIER DATO DE LA BD
date_default_timezone_set("America/Argentina/Buenos_Aires");
$system_fecha = date('Y-m-d');
$system_ano = date('Y');
$system_hora = date('H:i:s');
$hora_public = date('H:i');
$system_checked = time();		
$system_fecha_qr = date('Ymd');
$modo_login ="sistema";
$modulo ="";
$url_exito ="";
$id ="";
$vars_exito ="";
$system_100_checked="";
$where_control ="";
$id_system_101="";
$where_seccion="";
$system_total = '';
$total_filas='0';
$codigo_tabla='';
$id_system_11='';
$rela_system_11a='';
$rela_system_11b='';
$system_11_producto='';
$system_11_detalles='';
$system_11_unidades='';
$system_11_stock='';
$rela_system_11='';
$menu_i ='';
$print_search ='';
$contactos_titular ='';
$redes_titular ='';
$rela_system_04 ='';
$id_system_10='';
$id_system_15='';
$file = '';
$destino = '';
$system_09_tipo="";
$system_09_album="";
$system_09_epigrafe='';

$where_seccion="";
$id_system_01='0';
$nombre_funcion ='';
$titulo_modulo ='';
$impuesto_iva="21";
$og_image="";
$og_url="";
$cargar_inicial="";
$sesion_vendedor = 0;


$contenido_html = '';
		

						
$id_system_01 = 	isset($_GET['id_system_01']) ? $_GET['id_system_01'] : NULL;
if ( (isset($_POST['id_system_01']) ? $_POST['id_system_01'] : NULL) != '' )
{
$id_system_01 = 	isset($_POST['id_system_01']) ? $_POST['id_system_01'] : NULL;
}
$id_system_03=		isset($_POST['id_system_03']) ? $_POST['id_system_03'] : NULL;
if ( (isset($_GET['id_system_03']) ? $_GET['id_system_03'] : NULL) !='' )
{
$id_system_03=		isset($_GET['id_system_03']) ? $_GET['id_system_03'] : NULL;
}
$mod = 				isset($_GET['mod']) ? $_GET['mod'] : NULL;
$rec = 				isset($_GET['rec']) ? $_GET['rec'] : NULL;
$mas = 				isset($_POST['mas']) ? $_POST['mas'] : NULL;
$system_01_modulo = isset($_GET['system_01_modulo']) ? $_GET['system_01_modulo'] : NULL;
$sesion_system_03 = isset($_SESSION['sesion_system_03']) ? $_SESSION['sesion_system_03'] : NULL;
$sesion_system_07= 	isset($_SESSION['sesion_system_07']) ? $_SESSION['sesion_system_07'] : NULL;
$sesion_system_06= 	isset($_SESSION['sesion_system_06']) ? $_SESSION['sesion_system_06'] : NULL;
$sesion_system_03_modo = isset($_SESSION['sesion_system_03_modo']) ? $_SESSION['sesion_system_03_modo'] : NULL;
$sesion_perfil = 	isset($_SESSION['sesion_perfil']) ? $_SESSION['sesion_perfil'] : NULL;
$sesion_vista = 	isset($_SESSION['sesion_vista']) ? $_SESSION['sesion_vista'] : NULL;
$id_sesion_13 = 	isset($_SESSION['id_sesion_13']) ? $_SESSION['id_sesion_13'] : NULL;
$reset = 			isset($_POST['reset']) ? $_POST['reset'] : NULL;
$variable_buscar= 	isset($_POST['variable_buscar']) ? $_POST['variable_buscar'] : NULL;
$system_estado = 	isset($_POST['system_estado']) ? $_POST['system_estado'] : NULL;
if ($reset=='go')
{
$_SESSION['where_seccion']="";
}
$codigo_captcha = 		isset($_POST['codigo_captcha']) ? $_POST['codigo_captcha'] : NULL;
$id_system_09 = 		isset($_POST['id_system_09']) ? $_POST['id_system_09'] : NULL;
$rela_system_07 = 		isset($_POST['rela_system_07']) ? $_POST['rela_system_07'] : NULL;
$system_03_clave = 		isset($_POST['system_03_clave']) ? $_POST['system_03_clave'] : NULL;
$system_03_clave_copy = isset($_POST['system_03_clave_copy']) ? $_POST['system_03_clave_copy'] : NULL;
$system_03_cuir = 		isset($_POST['system_03_cuir']) ? $_POST['system_03_cuir'] : NULL;
$system_03_estado = 	isset($_POST['system_03_estado']) ? $_POST['system_03_estado'] : NULL;
$system_04_nombre = 	isset($_POST['system_04_nombre']) ? $_POST['system_04_nombre'] : NULL;
$system_04_apellido = 	isset($_POST['system_04_apellido']) ? $_POST['system_04_apellido'] : NULL;
$system_04_email = 		isset($_POST['system_04_email']) ? $_POST['system_04_email'] : NULL;
$system_04_celular = 	isset($_POST['system_04_celular']) ? $_POST['system_04_celular'] : NULL;
$system_04_telefono = 	isset($_POST['system_04_telefono']) ? $_POST['system_04_telefono'] : NULL;
$system_04_detalles = 	isset($_POST['system_04_detalles']) ? $_POST['system_04_detalles'] : NULL;
$system_04_cuil = 		isset($_POST['system_04_cuil']) ? $_POST['system_04_cuil'] : NULL;
$system_04_dni = 		isset($_POST['system_04_dni']) ? $_POST['system_04_dni'] : NULL;	
$system_04_direccion = 	isset($_POST['system_04_direccion']) ? $_POST['system_04_direccion'] : NULL;	
$system_04_provincia = 	isset($_POST['system_04_provincia']) ? $_POST['system_04_provincia'] : NULL;
$system_04_localidad = 	isset($_POST['system_04_localidad']) ? $_POST['system_04_localidad'] : NULL;
$system_04_barrio = 	isset($_POST['system_04_barrio']) ? $_POST['system_04_barrio'] : NULL;
$system_04_maps = 		isset($_POST['system_04_maps']) ? $_POST['system_04_maps'] : NULL;

$id_system_10 = 		isset($_GET['id_system_10']) ? $_GET['id_system_10'] : NULL;
$id_system_11 = 		isset($_POST['id_system_11']) ? $_POST['id_system_11'] : NULL;
$id_system_11a=			isset($_POST['id_system_11a']) ? $_POST['id_system_11a'] : NULL;
$rela_system_11a=		isset($_POST['rela_system_11a']) ? $_POST['rela_system_11a'] : NULL;
$system_11a_tipo=		isset($_POST['system_11a_tipo']) ? $_POST['system_11a_tipo'] : NULL;
$id_system_11b=			isset($_POST['id_system_11b']) ? $_POST['id_system_11b'] : NULL;
$rela_system_11b=		isset($_POST['rela_system_11b']) ? $_POST['rela_system_11b'] : NULL;
$id_system_11c=			isset($_POST['id_system_11c']) ? $_POST['id_system_11c'] : NULL;
$rela_system_11c=		isset($_POST['rela_system_11c']) ? $_POST['rela_system_11c'] : NULL;
$system_11_tipo=		isset($_POST['system_11_tipo']) ? $_POST['system_11_tipo'] : NULL;
$system_11_producto=	isset($_POST['system_11_producto']) ? $_POST['system_11_producto'] : NULL;
$system_11_detalles=	isset($_POST['system_11_detalles']) ? $_POST['system_11_detalles'] : NULL;
$system_11_precio_costo=isset($_POST['system_11_precio_costo']) ? $_POST['system_11_precio_costo'] : NULL;
$system_11_precio_mayorista=	isset($_POST['system_11_precio_mayorista']) ? $_POST['system_11_precio_mayorista'] : NULL;
$system_11_precio_minorista=	isset($_POST['system_11_precio_minorista']) ? $_POST['system_11_precio_minorista'] : NULL;
$system_11_estado=		isset($_POST['system_11_estado']) ? $_POST['system_11_estado'] : NULL;
$system_11a_tipo=		isset($_POST['system_11a_tipo']) ? $_POST['system_11a_tipo'] : NULL;
$system_11b_rubro=		isset($_POST['system_11b_rubro']) ? $_POST['system_11b_rubro'] : NULL;
$system_11c_subrubro=	isset($_POST['system_11c_subrubro']) ? $_POST['system_11c_subrubro'] : NULL;
$system_11_codigo=		isset($_POST['system_11_codigo']) ? $_POST['system_11_codigo'] : NULL;
$system_11_estado_iva=	isset($_POST['system_11_estado_iva']) ? $_POST['system_11_estado_iva'] : NULL;
$system_11_stock=		isset($_POST['system_11_stock']) ? $_POST['system_11_stock'] : NULL;
$system_11_unidades=	isset($_POST['system_11_unidades']) ? $_POST['system_11_unidades'] : NULL;
$system_11_checked=		isset($_POST['system_11_checked']) ? $_POST['system_11_checked'] : NULL;
$system_11b_estado=		isset($_POST['system_11b_estado']) ? $_POST['system_11b_estado'] : NULL;
$system_11a_estado = 	isset($_POST['system_11a_estado']) ? $_POST['system_11a_estado'] : NULL;
$system_11c_estado = 	isset($_POST['system_11c_estado']) ? $_POST['system_11c_estado'] : NULL;
$system_nombre_catalogo = isset($_POST['system_nombre_catalogo']) ? $_POST['system_nombre_catalogo'] : NULL;

$system_13_ingreso= 	isset($_POST['system_13_ingreso']) ? $_POST['system_13_ingreso'] : NULL;
$id_system_13= 			isset($_POST['id_system_13']) ? $_POST['id_system_13'] : NULL;
$rela_system_13 = 		isset($_POST['rela_system_13']) ? $_POST['rela_system_13'] : NULL;
$system_13_checked= 	isset($_POST['system_13_checked']) ? $_POST['system_13_checked'] : NULL;
$id_system_12= 			isset($_POST['id_system_12']) ? $_POST['id_system_12'] : NULL;
$system_12_iva= 		isset($_POST['system_12_iva']) ? $_POST['system_12_iva'] : NULL;
$system_12_descuento= 	isset($_POST['system_12_descuento']) ? $_POST['system_12_descuento'] : NULL;
$system_12_importe= 	isset($_POST['system_12_importe']) ? $_POST['system_12_importe'] : NULL;
$system_12_unidades= 	isset($_POST['system_12_unidades']) ? $_POST['system_12_unidades'] : NULL;
$system_12_incremento= 	isset($_POST['system_12_incremento']) ? $_POST['system_12_incremento'] : NULL;
$system_12_observaciones=isset($_POST['system_12_observaciones']) 	? $_POST['system_12_observaciones'] : NULL;
$system_13_iva= 		isset($_POST['system_13_iva']) ? $_POST['system_13_iva'] : NULL;
$system_13_descuento= 	isset($_POST['system_13_descuento']) ? $_POST['system_13_descuento'] : NULL;
$system_13_incremento= 	isset($_POST['system_13_incremento']) ? $_POST['system_13_incremento'] : NULL;
$system_13_total= 		isset($_POST['system_13_total']) ? $_POST['system_13_total'] : NULL;
$system_13_total_haber= isset($_POST['system_13_total_haber']) ? $_POST['system_13_total_haber'] : NULL;
$system_13_tipo=		isset($_POST['system_13_tipo']) 	? $_POST['system_13_tipo'] : NULL;
$system_13_estado=		isset($_POST['system_13_estado']) 	? $_POST['system_13_estado'] : NULL;
$rela_system_03=		isset($_POST['rela_system_03']) 	? $_POST['rela_system_03'] : NULL;

$id_system_14 = 		isset($_POST['id_system_14']) ? $_POST['id_system_14'] : NULL; 
$system_14_fecha_desde =isset($_POST['system_14_fecha_desde']) ? $_POST['system_14_fecha_desde'] : NULL;
$system_14_fecha_hasta =isset($_POST['system_14_fecha_hasta']) ? $_POST['system_14_fecha_hasta'] : NULL;		
$system_14_oferta = 	isset($_POST['system_14_oferta']) ? $_POST['system_14_oferta'] : NULL;
$system_14_descuento = 	isset($_POST['system_14_descuento']) ? $_POST['system_14_descuento'] : NULL; 	
$system_14_incremento = isset($_POST['system_14_incremento']) ? $_POST['system_14_incremento'] : NULL; 	
$system_14_sumo = 		isset($_POST['system_14_sumo']) ? $_POST['system_14_sumo'] : NULL;	
$system_14_resto = 		isset($_POST['system_14_resto']) ? $_POST['system_14_resto'] : NULL;	
$system_14_monto_fijo = isset($_POST['system_14_monto_fijo']) ? $_POST['system_14_monto_fijo'] : NULL;
$system_14_unidades = 	isset($_POST['system_14_unidades']) ? $_POST['system_14_unidades'] : NULL; 	
$system_14_estado = 	isset($_POST['system_14_estado']) ? $_POST['system_14_estado'] : NULL;
$system_14_descripcion = 	isset($_POST['system_14_descripcion']) ? $_POST['system_14_descripcion'] : NULL;
$system_65_checked = 	isset($_POST['system_65_checked']) 	? $_POST['system_65_checked'] : NULL;

$system_11_forma_cursado = 	isset($_POST['system_11_forma_cursado']) 	? $_POST['system_11_forma_cursado'] : NULL;
$system_11_duracion = 		isset($_POST['system_11_duracion']) 	? $_POST['system_11_duracion'] : NULL;
$system_11_inicio = 	isset($_POST['system_11_inicio']) 	? $_POST['system_11_inicio'] : NULL;
$system_11_destinatarios = 	isset($_POST['system_11_destinatarios']) 	? $_POST['system_11_destinatarios'] : NULL;
$system_11_bajada = 		isset($_POST['system_11_bajada']) 	? $_POST['system_11_bajada'] : NULL;
$system_11_capacitadores = 	isset($_POST['system_11_capacitadores']) 	? $_POST['system_11_capacitadores'] : NULL;

$id_system_17 = 		isset($_POST['id_system_17']) ? $_POST['id_system_17'] : NULL;
$system_17_checked = 		isset($_POST['system_17_checked']) ? $_POST['system_17_checked'] : NULL;
$system_17_mensaje = 		isset($_POST['system_17_mensaje']) ? $_POST['system_17_mensaje'] : NULL;
$system_17_tema =			isset($_POST['system_17_tema']) ? $_POST['system_17_tema'] : NULL;
$system_17_apellido =isset($_POST['system_17_apellido']) ? $_POST['system_17_apellido'] : NULL;
$system_17_nombre =isset($_POST['system_17_nombre']) ? $_POST['system_17_nombre'] : NULL;
$system_17_email =			isset($_POST['system_17_email']) ? $_POST['system_17_email'] : NULL;
$system_17_celular =		isset($_POST['system_17_celular']) ? $_POST['system_17_celular'] : NULL;
		
		
$nombre_funcion=		isset($_POST['nombre_funcion']) 	? $_POST['nombre_funcion'] : NULL;

$system_13_total = 			formatear_moneda($system_13_total);
$system_12_importe = 		formatear_moneda($system_12_importe);
$system_13_ingreso = 		formatear_moneda($system_13_ingreso);
$system_13_total_haber = 	formatear_moneda($system_13_total_haber);
$system_04_dni =			formatear_dni($system_04_dni);
$system_04_cuil = 			formatear_cuit($system_04_cuil);
$system_14_sumo = 			formatear_moneda($system_14_sumo); 	
$system_14_resto = 			formatear_moneda($system_14_resto); 	
$system_14_monto_fijo = 	formatear_moneda($system_14_monto_fijo);
$system_14_fecha_desde =	formatear_fecha($system_14_fecha_desde);
$system_14_fecha_hasta =	formatear_fecha($system_14_fecha_hasta);
$system_11_precio_costo=	formatear_moneda($system_11_precio_costo);
$system_11_precio_mayorista=formatear_moneda($system_11_precio_mayorista);
$system_11_precio_minorista=formatear_moneda($system_11_precio_minorista);
$system_13_descuento = 		formatear_porcentual($system_13_descuento);
$system_13_incremento = 	formatear_porcentual($system_13_incremento);
$system_14_descuento = 		formatear_porcentual($system_14_descuento);
$system_14_incremento = 	formatear_porcentual($system_14_incremento);

$base = new Constructor_SQL(HOST,USU,CLA,BD);
$mysqli = new Abm($base);

$row = $mysqli -> system_08_configuracion();
if ($row == TRUE)
{
	$rela_system_06 = 					$row[0]['rela_system_06'];
	$system_08_dominio = 				$row[0]['system_08_dominio'];
	$system_08_titulo_site = 			$row[0]['system_08_titulo_site'];
	$system_08_descripcion_site = 		$row[0]['system_08_descripcion_site'];
	$system_08_titulo_siglas = 			$row[0]['system_08_titulo_siglas'];
	$system_08_keywords = 				$row[0]['system_08_keywords'];
	$system_08_direccion = 				$row[0]['system_08_direccion'];	
	$system_08_email = 					$row[0]['system_08_email'];	
	$system_08_telefono = 				$row[0]['system_08_telefono'];
	$system_08_celular = 				$row[0]['system_08_celular'];
	$system_08_whatsapp = 				$row[0]['system_08_whatsapp'];
	$system_08_facebook = 				$row[0]['system_08_facebook'];
	$system_08_twitter = 				$row[0]['system_08_twitter'];
	$system_08_youtube = 				$row[0]['system_08_youtube'];
	$system_08_instagram = 				$row[0]['system_08_instagram'];
	$system_08_tiktok = 				$row[0]['system_08_tiktok'];	
	$system_08_mapsgoogle = 			$row[0]['system_08_mapsgoogle'];	
	$system_08_path = 					$row[0]['system_08_path'];
	$system_08_email_alerta = 			$row[0]['system_08_email_alerta'];
	$system_08_alerta = 				$row[0]['system_08_alerta'];
	$system_08_dir_origen = 			$row[0]['system_08_dir_origen']; // NOMBRE DIRECTORIO ORIGEN DEL MODULO ARCHIVADOR
	$system_08_contactos_visibles = 	$row[0]['system_08_contactos_visibles'];
	
	$limite_size = 						$row[0]['system_08_limit_size_up']; // limite de peso imagen
	$limite_size_multimedia = 			$row[0]['system_08_limit_size_up_multimedia']; // limite de peso multimedia
	$system_08_limit_size_up_archivo = 	$row[0]['system_08_limit_size_up_archivo']; // limite de peso documentos
	
	//--ancho de las imagenes por subir
	$system_08_ancho_max = 				$row[0]['system_08_ancho_max']; // GRANDE
	$system_08_ancho_medio = 			$row[0]['system_08_ancho_medio'];	// MEDIANO	
	$system_08_ancho_chico = 			$row[0]['system_08_ancho_chico'];		// CHICO
	$system_08_ancho_minimo = 			$row[0]['system_08_ancho_minimo'];	 // PEQUENO
	
	$system_08_checked_1 = 				$row[0]['system_08_checked_1'];			
	$system_08_checked_2 = 				$row[0]['system_08_checked_2'];
	
	$system_08_contador_1 = 				$row[0]['system_08_contador_1'];			
	$system_08_contador_2 = 				$row[0]['system_08_contador_2'];
	$system_08_contador_3 = 				$row[0]['system_08_contador_3'];			
	$system_08_contador_4 = 				$row[0]['system_08_contador_4'];
	
	if ( $limite_size == 2 )
	{
	$limite_size="3903997"; // es igual a 4 MB
	}	
	else
	{		
	$limite_size="2200000"; // es igual a 2 MB
	}	
	
	if ( $system_08_limit_size_up_archivo == 12 )
	{
	$system_08_limit_size_up_archivo=("12288000"); // es igual a 12 MB
	}	
	else
	if ( $system_08_limit_size_up_archivo == 8 )
	{		
	$system_08_limit_size_up_archivo="8192000"; // es igual a 8 MB
	}
	else
	if ( $system_08_limit_size_up_archivo == 3 )
	{
	$system_08_limit_size_up_archivo="3072000"; // es igual a 3 MB
	}
	else
	{
	$system_08_limit_size_up_archivo="1024000"; // es igual a 1 MB
	}

}


$row = $mysqli -> system_07_privilegios($sesion_system_07);
if ($row == TRUE)
{
	$system_07_admin= 	$row[0]['system_07_admin'];
	$system_07_a= 		$row[0]['system_07_a'];
	$system_07_b= 		$row[0]['system_07_b'];
	$system_07_m= 		$row[0]['system_07_m'];
	$system_07_v= 		$row[0]['system_07_v'];
	$system_07_ad= 		$row[0]['system_07_ad'];
	$system_07_sis= 	$row[0]['system_07_sis'];
	$system_07_r = 		$row[0]['system_07_r'];				

	if ($sesion_system_07=='1')
	{
		$candado="on";
		$root_candado="on";
	}
	else
	if ($sesion_system_07=='2')
	{
		$candado="on";
		$root_candado="off";
	}
	else
	{
		$candado="off";
		$root_candado="off";			
	}		
}


$row = $mysqli -> system_03_usuarios($sesion_system_03);
if ($row == TRUE)
{		
	$system_03_cuir = $row[0]['system_03_cuir'];
}

$row = $mysqli -> system_06_sala($id_system_06);
if ($row == TRUE)
{		
	$system_06_sala=	$row[0]['system_06_sala'];
}

$row = $mysqli -> system_04_perfil($sesion_system_03);
if ($row == TRUE)
{		
	$system_04_name_usuario=substr($row[0]['system_04_nombre'], 0, 1).'. '.$row[0]['system_04_apellido'];	
}

$PAGINAR="";			
$num_filas="10";	
if ( $mas=='' or $mas=='0' )
{
$LIMITE =" limit 0,$num_filas ";
}
else
{
$num_filas = $mas + $num_filas;
$LIMITE=" limit 0,$num_filas ";
}


function funcion_paginar_siguiente($total_filas,$num_filas,$url,$id,$vars)
{		
	if ($num_filas > 0 or $total_filas !='0' or $tSQL !='')
	{
		if ($total_filas =='0' or $total_filas == '')
		{
		$total_filas = 0;
		}
		if ($num_filas =='0' or $num_filas == '')
		{
		$num_filas = 1;
		}
		$tSQL = ceil($total_filas/$num_filas); 
		
		if ($tSQL)
		{
			if ($num_filas > $total_filas)
			{
			$PAGINAS=''.$total_filas.' #';	
			}
			else
			{
			$PAGINAS='<a href="javascript:cargar_post('.$url.','.$id.','.$vars.');" title="Desplegar '.$num_filas.' +"><i class="bi bi-sort-down-alt"></i></a>&nbsp;&nbsp; '.$num_filas.' de '.$total_filas;	
			}
		}	
	}
	else
	{
	$PAGINAS='';
	}
	
	$PAGINAS = isset($PAGINAS) ? $PAGINAS : NULL;
	return "$PAGINAS";
}		
	
	
function color_fila($bgcolor)
{	
	$color_1="#fff";
	$color_2="#f8f9fa";
	if ( $bgcolor!=$color_1 )
	{
	$bgcolor=$color_1;
	}
	else
	{
	$bgcolor=$color_2;
	}
	return $bgcolor;
}

function optengo_modulo($id_system_01,$mysqli)
{			
	$row = $mysqli -> consulta_SQL("SELECT * FROM system_01_modulos where id_system_01='$id_system_01' ");
	return $row[0]['system_01_modulo'];	 
}	

function optengo_imagen_portada($system_09_album,$mysqli)
{
	$row = $mysqli -> consulta_SQL("Select * from system_09_archivero where system_09_album = '$system_09_album' and system_09_tipo='1' order by id_system_09 DESC LIMIT 1");
	if ($row == TRUE)
	{
		$system_09_path =		$row[0]['system_09_path'];
		$system_09_archivo =	$row[0]['system_09_archivo'];
	}
	else
	{
		$system_09_path="../image";
		$system_09_archivo="no-imagen.jpg";
	}
		
			
	return $system_09_path.'/'.$system_09_archivo;		
		
}


function optengo_imagen_2($id_system_10,$id_system_11,$system_09_tipo,$modo,$width,$height,$mysqli)
{
	
	if ($id_system_10 == '0'){$id_system_10 = "";}
	if ($id_system_11 == '0'){$id_system_11 = "";}
	
	if ($id_system_10 != '')
	{
		$where ="  where rela_system_10='$id_system_10' and system_09_tipo='$system_09_tipo'  ";
	}
	else
	if ($id_system_11 != '')
	{
		$where ="  where rela_system_11='$id_system_11' and system_09_tipo='$system_09_tipo'  ";
	}
	else
	{
		$where =" where id_system_09='0' ";// invento un id 0 para que me de FALSE
	}
	
	$row = $mysqli -> consulta_SQL("Select * from system_09_archivero $where ");
	if ($row == TRUE)
	{
		$system_09_path =	$row[0]['system_09_path'];
		$system_09_archivo = $row[0]['system_09_archivo'];
	}
	else
	{
		$system_09_path="../image";
		$system_09_archivo="no-imagen.jpg";
	}
		
	if ($modo=='1')
	{
	return '<div style="background: url('.$system_09_path.'/'.$system_09_archivo.') no-repeat 50% 50%; background-size:contain; width: '.$width.'; height: '.$height.';"></div>';	
	}
	else
	{				
	return $system_09_path.'/'.$system_09_archivo;		
	}	
}

function optengo_imagen_catalogo($id_system_11,$posicion,$mysqli)
{
	$system_09_path =			"https://isdfs.edu.ar/image";
	$system_09_archivo =		"no-imagen.jpg";
	
	

	if ($posicion == 4)
	{
	$LIMIT = " LIMIT 4,1 ";
	}
	else
	if ($posicion == 3)
	{
	$LIMIT = " LIMIT 3,1 ";
	}
	else
	if ($posicion == 2)
	{
	$LIMIT = " LIMIT 2,1 ";
	}
	else
	if ($posicion == 1)
	{
	$LIMIT = " LIMIT 1,1 ";
	}
	else
	{
	$LIMIT = " LIMIT 0,1 ";
	}
	$row = $mysqli -> consulta_SQL("Select * from system_09_archivero where rela_system_11 = '$id_system_11' and system_09_tipo = '1' order by system_09_posicion DESC $LIMIT ");
	if ($row == TRUE)
	{
		$system_09_path =		$row[0]['system_09_path'];
		$system_09_archivo =	$row[0]['system_09_archivo'];
	}
					
	return $system_09_path.'/'.$system_09_archivo;				
}



//MODELO MENU COMPLETO PARA EDITAR
function traigo_galeria_editable($id_system_01,$rela_system_06,$rela_system_11a,$rela_system_11b,$url_exito,$id,$vars_exito,$mysqli)
{			
	$cadena="";
	$row = $mysqli -> consulta_SQL("SELECT * FROM system_11_catalogo 
									WHERE 
									rela_system_11b = '$rela_system_11b'
									AND 
									rela_system_11a = '$rela_system_11a' 
					
									ORDER BY system_11_checked DESC  
									");
	if ($row == TRUE)
	{
		for ( $i=0; $i < count($row); $i++)
		{				
			$id_system_11 = 				$row[$i]['id_system_11'];
			$rela_system_11a = 				$row[$i]['rela_system_11a'];
			$rela_system_11b = 				$row[$i]['rela_system_11b'];
			$rela_system_11c = 				$row[$i]['rela_system_11c'];
			$system_11_producto = 			$row[$i]['system_11_producto'];
			$system_11_detalles = 			$row[$i]['system_11_detalles'];
			//$system_11_unidades =			$row[$i]['system_11_unidades'];
			$system_11_tipo =				$row[$i]['system_11_tipo'];
			$system_11_estado =				$row[$i]['system_11_estado'];
			$system_11_checked =			$row[$i]['system_11_checked'];
			//$system_11_precio_minorista = 	$row[$i]['system_11_precio_minorista'];
			//$color_bg = 					optengo_color($rela_system_11b,$mysqli);
			$color_bg =						"#fff";	
	
			/*$url="'modulos/clasificados/php/home_am.php'";
			$id="'cajita_$id_system_11'";
			$vars="'id_system_01=$id_system_01&id_system_11=$id_system_11&rela_system_11b=$rela_system_11b'";
			$funcion_editar_rapido = " cargar_post($url,$id,$vars) ";	*/
			
			$url="'modulos/clasificados/php/home_abm.php'";
			$id="'content_seccion'";
			$vars="'id_system_01=$id_system_01&id_system_11=$id_system_11&rela_system_11a=$rela_system_11a&rela_system_11b=$rela_system_11b'";
			$funcion_editar_full = " cargar_post($url,$id,$vars) ";	
			
			$url="'modulos/clasificados/php/_interfaz.php'";
			$vars="'nombre_funcion=eliminar&id_system_11=$id_system_11'";
			$url_exito="'modulos/clasificados/php/home.php'";
			$id="'content_seccion'";
			$vars_exito="'id_system_01=$id_system_01'";
			$msg="'Eliminar esto?'";
			$atx="''";
			$funcion_borrar = "eliminar_mostrar($url,$vars,$url_exito,$id,$vars_exito,$msg,$atx);";	
		
			$url="'modulos/clasificados/php/_interfaz.php'";
			$vars="'nombre_funcion=on_off&id_system_11=$id_system_11&system_11_estado=$system_11_estado'";
			$url_exito="'modulos/clasificados/php/home.php'";
			$id="'content_seccion'";
			$vars_exito="'id_system_01=$id_system_01'";
			$on_off = "guardar_mostrar($url,$vars,$url_exito,$id,$vars_exito);";
				
				
			$url="'modulos/clasificados/php/_interfaz.php'";
			$vars="'nombre_funcion=rapida_produccion&id_system_11=$id_system_11&system_11_tipo=$system_11_tipo'";
			$url_exito="'modulos/clasificados/php/home.php'";
			$id="'content_seccion'";
			$vars_exito="'id_system_01=$id_system_01'";
			$cambio_rapida_produccion = "guardar_mostrar($url,$vars,$url_exito,$id,$vars_exito);";	
		
			//$funcion_ajustar_precio = "'modulos/clasificados/php/home.php','content_seccion','id_system_01=".$id_system_01."','".$id_system_11."',this.value";
	
			$url="'modulos/clasificados/php/_interfaz.php'";
			$vars="'nombre_funcion=ups&system_11_checked=$system_11_checked&rela_system_11a=$rela_system_11a&rela_system_11b=$rela_system_11b'";
			$url_exito="'modulos/clasificados/php/home.php'";
			$id="'content_seccion'";
			$vars_exito="'id_system_01=$id_system_01'";
			$ups = "guardar_mostrar($url,$vars,$url_exito,$id,$vars_exito);";
			
			
			if ($system_11_tipo == '1')
			{
				$system_11_tipo='<input title="Nuevo!" onclick="'.$cambio_rapida_produccion.'" type="checkbox" value="" checked>';		
			}
			else
			{
				$system_11_tipo='<input title="No-nuevo" onclick="'.$cambio_rapida_produccion.'" type="checkbox" value="" >';
			}
			
			if ($system_11_estado == '2')
			{
				$system_11_estado="bi bi-eye-slash-fill";		
			}
			else
			if ($system_11_estado == '1')
			{
				$system_11_estado="bi bi-eye-fill";	
			}
			else
			{
				$system_11_estado="bi bi-eye";	
			}
	
			$cadena.='<div class="" id="cajita_'.$id_system_11.'">';
				
				
				$cadena.='	<div class="tabl" style=" background:'.$color_bg.';">';
				$cadena.='		<li class="fil-10" style=" background: url(\''.optengo_imagen_catalogo($id_system_11,1,$mysqli).'\') no-repeat 50% 50%;  background-size: cover; height: 70px; cursor: pointer;"  onclick="'.$funcion_editar_full.'">';			
				$cadena.='		&nbsp;';			
				$cadena.='		</li>';
				$cadena.='		<li class="fil-5">';	
				$cadena.='		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:;" onclick="'.$ups.'" title="Subir un nivel"><i class="bi bi-chevron-bar-up"></i></a>';	
				$cadena.='		</li>';
				$cadena.='		<li class="fil-70" style="cursor: pointer;" onclick="'.$funcion_editar_full.'">';	
				$cadena.='		<strong>'.$system_11_producto.'</strong>';	
				$cadena.='		<div class="minitex">'.cadena_secciones($rela_system_11a,$rela_system_11b,$rela_system_11c,$rela_system_06,$mysqli).'</div>';	
				$cadena.='		</li>';				
				$cadena.='		<li class="fil-5">';	
				$cadena.='		'.$system_11_tipo;	
				$cadena.='		</li>';
				$cadena.='		<li class="fil-10" style="text-align: right;">';	
				$cadena.='		 <a href="javascript:;" onclick="'.$on_off.'"><i class="'.$system_11_estado.'"></i></a>&nbsp;&nbsp;<a href="javascript:;" onclick="'.$funcion_editar_full.'" title="Editar"><i class="bi bi-pencil-square"></i></a>&nbsp;&nbsp;<a href="javascript:;" onclick="'.$funcion_borrar.'" title="Borrar"><i class="bi bi-trash-fill"></i></a>';		
				$cadena.='		</li>';	
				$cadena.='	</div>';
				
			$cadena.='</div>';

			$id_system_11 = 	'';
			$system_11_producto='';
			$total_venta = 	'';
			$rela_system_11b = 	'';
			$system_11_unidades = 	'';
			$funcion_optengo_imagen='';
			$funcion_carrito='';
			$funcion_cargar_archivo="";
		}
	} 
	else 		
	{
		$cadena.='<div class="accordion-item">';	
		$cadena.='	<div class="tabl" style=" background:#eee; padding:4px; border:0px;">';
		$cadena.='		<li class="fil-50 minitex">';	
		$cadena.='		 &nbsp;&nbsp;&nbsp;No hay opciones cargadas en esta secci&oacute;n';
		$cadena.='		</li>';	
		$cadena.='		<li class="fil-50">';	
		$cadena.='		 &nbsp;';		
		$cadena.='		</li>';	
		$cadena.='	</div>';				
		$cadena.='</div>';
	}
	
	return $cadena;
}



function traigo_carrito($rela_system_13,$mysqli)
{}

function estructura_selector_lista($id_system_12,$system_12_producto,$system_12_observacione,$system_12_comen)
{}

function numero_carrito($rela_system_13,$mysqli)
{}

	

function calculo_total_listado($id_system_13,$mysqli) // optengo totales del carrito
{}

function salvar_total_listado($id_system_13,$mysqli) // optengo totales del carrito
{}



function consulto_perfil_emisor_mail($rela_system_03,$rela_system_11,$mysqli)
{			
	$cadena='';
	$row = $mysqli -> consulta_SQL("SELECT system_03_usuarios.*, system_04_perfil.* FROM system_03_usuarios, system_04_perfil 
	where 
	system_04_perfil.rela_system_03=system_03_usuarios.id_system_03 
	and 
	system_03_usuarios.id_system_03='$rela_system_03'
	");
	if ($row == TRUE)
	{
		$cadena.= 	'AVISO DE ACTIVIDAD EN EL SITIO WEB.';
		$cadena.=	'<br>';
		$cadena.= 	$row[0]['system_04_nombre'].' '.$row[0]['system_04_apellido'];
		$cadena.=	'<br>';
		$cadena.=	$row[0]['system_04_email']; 
		$cadena.=	'<br>';
		$cadena.=	$row[0]['system_04_celular']; 
		$cadena.=	'<br>';
		$cadena.=	consultar_producto($rela_system_11,$mysqli);
		$cadena.=	'<hr>';
	}
	return $cadena;
}
	
function consulto_perfil($rela_system_03,$mysqli)
{			
	$row = $mysqli -> consulta_SQL("SELECT system_03_usuarios.*, system_04_perfil.* FROM system_03_usuarios, system_04_perfil 
	where 
	system_04_perfil.rela_system_03=system_03_usuarios.id_system_03 
	and 
	system_03_usuarios.id_system_03='$rela_system_03'
	");
	if ($row == TRUE)
	{
		return substr($row[0]['system_04_nombre'], 0, 1).'. '.$row[0]['system_04_apellido'];	 
	}
}	

function consulto_stock($id_system_11,$mysqli)
{}

function limite_stock($stock)
{}

function cadena_secciones($rela_system_11a,$rela_system_11b,$rela_system_11c,$sesion_system_06,$mysqli)
{
	$cadena="";
	if (quito_0($rela_system_11a) != '')
	{
		$row1 = $mysqli -> consulta_SQL("Select * from system_11a_tipo where id_system_11a='$rela_system_11a'  ");
		if ($row1 == TRUE)
		{
			$cadena.= $row1[0]['system_11a_tipo'].'/';
		}	
	}
	if (quito_0($rela_system_11b) != '')
	{
		$row2 = $mysqli -> consulta_SQL("Select * from system_11b_rubro where id_system_11b='$rela_system_11b'   ");
		if ($row2 == TRUE)
		{
			$cadena.= $row2[0]['system_11b_rubro'].'/';
		}
	}
	if (quito_0($rela_system_11c) != '')
	{
		$row3 = $mysqli -> consulta_SQL("Select * from system_11c_subrubro where id_system_11c='$rela_system_11c'  ");
		if ($row3 == TRUE)
		{
			$cadena.= $row3[0]['system_11c_subrubro'].'';
		}
	}
	return $cadena;
}

function consultar_producto($id_system_11,$mysqli)
{
	$row = $mysqli -> consulta_SQL("SELECT * FROM system_11_catalogo WHERE id_system_11='$id_system_11' ");
	if ($row == TRUE)
	{
		return $row[0]['system_11_producto'];
	}		
}

function optengo_color($rela_system_11b,$mysqli)
{
	$row = $mysqli -> consulta_SQL("Select * from system_11b_rubro WHERE id_system_11b='$rela_system_11b'");
	if ($row == TRUE)
	{
		return $row[0]['system_11b_color'];	
	}
}

function optengo_oferta($id_system_11,$mysqli)
{
	$row = $mysqli -> consulta_SQL("Select * from system_11_catalogo WHERE id_system_11 = '$id_system_11'");
	if ($row == TRUE)
	{
		return $row[0]['system_11_producto'];	
	}
}

function optengo_imagen($id_system_04,$modo,$mysqli)
{
	$system_09_path =		"../image";
	$system_09_archivo =	"no-imagen.jpg";
	
	$row = $mysqli -> consulta_SQL("Select * from system_09_archivos where rela_system_04 = '$id_system_04' order by id_system_09 asc ");
	if ( $row == TRUE )
	{
		$system_09_path =		$row[0]['system_09_path'];
		$system_09_archivo =	$row[0]['system_09_archivo'];
	}

		
	if ($modo=='1')
	{
		return '<div style="background: url('.$system_09_path.'/'.$system_09_archivo.') no-repeat 50% 50%; background-size: auto 40px; width: 40px; height: 40px;"></div>';	
	}
	else
	{				
		return $system_09_path.'/'.$system_09_archivo;		
	}	
}

function crear_capcha()
{			
	// Generamos codigos para los campos que lo requieran
	$codigo_tabla='';
	$max_chars = round(rand(5,5));  // tendrá entre 7 y 9 caracteres
	$chars = array();
	for ($i='a'; $i<'z'; $i++) $chars[] = $i;  // creamos vector de letras
	$chars[] = 'z';
	for ($i=0; $i<$max_chars; $i++)
	{
	  $letra = round(rand(0, 1));  // primero escogemos entre letra y número
	  if ($letra) // es letra
		$codigo_tabla .= $chars[round(rand(0, count($chars)-1))];
	  else 
		$codigo_tabla .= round(rand(0, 9));
	}
	return $codigo_tabla;
}	




function optengo_imagen_perfil($id_system_04,$modo,$mysqli)
{
	$row=$mysqli -> consulta_SQL("Select * from system_09_archivos where rela_system_04 = '$id_system_04' order by id_system_09 asc");
	if ($row == TRUE)
	{
		$system_09_path=$row[0]['system_09_path'];
		$system_09_archivo=$row[0]['system_09_archivo'];
	}
	else
	{
		$system_09_path="../image";
		$system_09_archivo="no-imagen.jpg";
	}
		
	if ($modo=='1')
	{
	return '<div style="background: url('.$system_09_path.'/'.$system_09_archivo.') no-repeat 50% 50%; background-size: auto 40px ; width: 40px; height: 40px;"></div>';	
	}
	else
	{				
	return $system_09_path.'/'.$system_09_archivo;		
	}	
}
	
function optengo_nivel_cliente($rela_system_03,$mysqli)
{
	$system_1005_descripcion = 	'';
	$system_1005_nivel = 		'';	
	$system_1005_descuento = 	'';
	$rela_system_1005 = 		'';
	
	$row1 = $mysqli -> consulta_SQL("SELECT * FROM system_1004_calificacion WHERE  rela_system_03 = '$rela_system_03' AND system_1004_estado = '1' order by id_system_1004 desc limit 1");
	if ($row1 == TRUE)
	{
		$rela_system_1005 = $row1[0]['rela_system_1005'];
	}
	
	$row = $mysqli -> consulta_SQL("Select * from system_1005_servicios 
						where 
						system_1005_estado = '1' 
						and
						id_system_1005 = '$rela_system_1005'
						");					
	if ($row == TRUE)
	{	
		$id_system_1005 = 			$row[0]['id_system_1005'];
		$system_1005_descripcion = 	$row[0]['system_1005_descripcion'];
		$system_1005_nivel = 		$row[0]['system_1005_nivel'];	
		$system_1005_descuento = 	quito_0($row[0]['system_1005_descuento']);
	} 
	
	return $system_1005_nivel.': '.$system_1005_descripcion.' Desc: '.$system_1005_descuento.'% ';

}	


function estadistica_compartida($modo,$rela_system_03,$rela_system_09,$rela_system_10,$rela_system_11,$rela_system_12,$mysqli)
{
	$system_15_clickes="";
	
	if ($modo == '1')
	{
		if ($rela_system_11 != '0')
		{
			$row = $mysqli -> consulta_SQL("SELECT * FROM system_15_estadisticas where rela_system_11 = '$rela_system_11' ");
			if ($row == true)
			{			
				$system_15_clickes = 	$row[0]['system_15_clickes'];								
			}	
		}
		else
		if ($rela_system_10 != '0')
		{
			$row = $mysqli -> consulta_SQL("SELECT * FROM system_15_estadisticas where rela_system_10 = '$rela_system_10' ");
			if ($row == true)
			{			
				$system_15_clickes = 	$row[0]['system_15_clickes'];							
			}
		}
		else
		{
			$system_15_clickes="";	
		}
	}
	else
	{
		$system_fecha = date('Y-m-d');
		$hora_public = date('H:i');
		
		if ($rela_system_11 != '0')
		{
			$row = $mysqli -> consulta_SQL("SELECT * FROM system_15_estadisticas where rela_system_11 = '$rela_system_11' ");
			if ($row == true)
			{			
				$id_system_15 = 		$row[0]['id_system_15'];
				$system_15_clickes = 	$row[0]['system_15_clickes'] + 1;			
				
				$mysqli -> consulta_SQL("UPDATE system_15_estadisticas SET 
				system_15_clickes = '$system_15_clickes',
				system_15_fecha = '$system_fecha',
				system_15_hora = '$hora_public'										
				WHERE 											
				id_system_15 = '$id_system_15'
				");					
			}
			else
			{
				$mysqli -> consulta_SQL("INSERT INTO system_15_estadisticas 
				(
				rela_system_11,
				system_15_clickes,
				system_15_fecha,
				system_15_hora
				) 		
				VALUES 
				(
				'$rela_system_11',
				'1',
				'$system_fecha',
				'$hora_public'
				)
				");	
					
				$system_15_clickes="1";	
			}	
	
		}
		else
		if ($rela_system_10 != '0')
		{
			$row = $mysqli -> consulta_SQL("SELECT * FROM system_15_estadisticas where rela_system_10 = '$rela_system_10' ");
			if ($row == true)
			{			
				$id_system_15 = 		$row[0]['id_system_15'];
				$system_15_clickes = 	$row[0]['system_15_clickes'] + 1;			
				
				$mysqli -> consulta_SQL("UPDATE system_15_estadisticas SET 
				system_15_clickes = '$system_15_clickes',
				system_15_fecha = '$system_fecha',
				system_15_hora = '$hora_public'									
				WHERE 											
				id_system_15 = '$id_system_15'
				");					
			}
			else
			{
				$mysqli -> consulta_SQL("INSERT INTO system_15_estadisticas 
				(
				rela_system_10,
				system_15_clickes,
				system_15_fecha,
				system_15_hora
				) 		
				VALUES 
				(
				'$rela_system_10',
				'1',
				'$system_fecha',
				'$hora_public'
				)
				");	
					
				$system_15_clickes="1";	
			}	
			
		
		}
		else
		{
			$system_15_clickes="";	
		}
	}
	

	return $system_15_clickes;			
}


function formatear_fecha($fecha)
{	
	if ($fecha!='')
	{
		$v_fecha = explode('-',$fecha);
		$dia=$v_fecha[0];
		if (strlen($dia)==1)
			$dia="0".$dia;
	
		$mes=$v_fecha[1];
		if (strlen($mes)==1)
			$mes="0".$mes;
	
		$ano=trim($v_fecha[2]);
		
		$ret_fecha=$ano."-".$mes."-".$dia;
	}
	else
	{
	$ret_fecha="";
	}
	return $ret_fecha;
}
function formatear_hora($hora)
{
	$h = explode(':',$hora);
	$_hora=$h[0];

	$_min=$h[1];
	
	$ret_hora=$_hora .":". $_min;
	return $ret_hora;
}
function quito_0($vars)
{
	if ($vars=='0000-00-00'){$vars="";}
	if ($vars=='00-00-0000'){$vars="";}
	if ($vars=='--')		{$vars="";}
	if ($vars=='00:00:00')	{$vars="";}
	if ($vars=='00:00')		{$vars="";}
	if ($vars==':')			{$vars="";}
	if ($vars=='0.0')		{$vars="";}
	if ($vars=='0.00')		{$vars="";}
	if ($vars=='00')		{$vars="";}
	if ($vars=="0")			{$vars="";}
	return $vars;
}

function porcentual($monto,$porciento)
{
	$monto=round(($monto/100) * $porciento, 2);
	return $monto;	
}
function formatear_porcentual($vars)
{
	$vars=str_replace("%","",$vars);
	$vars=str_replace(",",".",$vars);
	return $vars;	
}
function formatear_moneda($mony)
{
	$value = explode(',',$mony);
	$entero = isset($value[0]) ? $value[0] : NULL;
	$decimal = isset($value[1]) ? $value[1] : NULL;
	

	if (strlen($decimal )=='2')
	{
	$entero=str_replace(".","",$entero); //quito puntos al entero
	$mony=$entero.'.'.$decimal ; // entero punto decimal
	}
	else
	{
		$value2 = explode('.',$entero);
		$entero2 = isset($value2[0]) ? $value2[0] : NULL;
		$decimal2 = isset($value2[1]) ? $value2[1] : NULL;

		if (strlen($decimal2)=='2')
		{
		$entero==str_replace(".","",$entero2); //quito puntos al entero
		$mony=$entero.'.'.$decimal2; // entero punto decimal
		}
		else
		{
		$mony=str_replace(".","",$entero); //quito puntos al entero
		}
	}
	return $mony;
}

function format_money($n) // agrego .00 a los enteros que no tengan centavos
{       
	$ajuste = explode('.',$n);
	$unidad = isset($ajuste[0]) ? $ajuste[0] : NULL;
	$centavo = isset($ajuste[1]) ? $ajuste[1] : NULL;
	
	if (strlen($centavo) == '1')
	{
		$centavo = $centavo."0";
	}
	
	if ($centavo == '')
	{
		$centavo = "00";
	}
	
	$mony= $unidad.'.'.$centavo;
	
	if ($mony == '.00')
	{
		$mony = "";
	}
	
	return $mony;	
}

function nuevo_formato_moneda($mony)
{
	$value = explode(',',$mony);
	$entero = isset($value[0]) ? $value[0] : NULL;
	$decimal = isset($value[1]) ? $value[1] : NULL;
	
	if (strlen($decimal) > '0') // es un numero con coma
	{
		$entero = str_replace(".","",$entero); //quito puntos al entero
		if (strlen($decimal) == '1')
		{
		$decimal = $decimal."0";
		}
		
		$retorno = $entero.'.'.$decimal; // entero punto decimal
	}
	else // es un numero entero sin coma
	{
	$retorno = $entero; //puede ue tenga un punto	
	}
	return $retorno;
}


function hora_min($hora)
{
	$h = explode(':',$hora);
	$_hora=$h[0];

	$_min=$h[1];
	
	$ret_hora=$_hora .":". $_min;
	return $ret_hora;
}

function ano_fecha($fecha)
{	
	$v_fecha = explode('-',$fecha);
	$ano=$v_fecha[0];
	$mes=$v_fecha[1];
	$dia=$v_fecha[2];
	
	return $ano;
}

function formatear_dni($dni)
{
	$dni=str_replace(".","",$dni); 
	return $dni;
}

function formatear_cuit($cuit)
{
	$cuit=str_replace(".","",$cuit); 
	$cuit=str_replace("-","",$cuit); 
	return $cuit;
}
function verifico_cuit($cuit)
{
	$cuit=str_replace(".","",$cuit); 
	$cuit=str_replace("-","",$cuit);
	if (strlen($cuit)=='11') {
	$cuit=preg_replace('/([0-9]{2})+([0-9]{8})+([0-9]{1})/', "\\1-\\2-\\3", $cuit);
	$row = explode('-',$cuit);
	if ($row[0] < 10){$cuit="";}
	}else{
	$cuit="";
	}
	return $cuit;
}
function verifico_dni($dni)
{
	$dni=str_replace(".","",$dni); 
	if (strlen($dni)!='8'){
	$dni="";
	}
	return $dni;
}
function convert_dni($dni)
{
	$dni = preg_replace('/([0-9]{1,2})+([0-9]{3})+([0-9]{3})/', "\\1.\\2.\\3", $dni); 
	return trim($dni);
}
function convert_cuit($cuit)
{
	$cuit=str_replace(".","",$cuit); 
	$cuit=str_replace("-","",$cuit);
	$cuit = preg_replace('/([0-9]{2})+([0-9]{8})+([0-9]{1})/', "\\1-\\2-\\3", $cuit); 
	$cuit = trim($cuit);	 
	return $cuit;
}
function convert_cuil_a_dni($cuit)
{
	$cuit=str_replace(".","",$cuit); 
	$cuit=str_replace("-","",$cuit);
	$cuit = preg_replace('/([0-9]{2})+([0-9]{8})+([0-9]{1})/', "\\2", $cuit); 
	$cuit = trim($cuit);	 
	return $cuit;
}


function urls_amigables($url_amigo) {
	// Tranformamos todo a minusculas
	$url_amigo = strtolower($url_amigo);
	//Rememplazamos caracteres especiales latinos
	$finx = array('&','ñ','Ñ','Á','É','Í','Ó','Ú','á','é','í','ó','ú');
	$repx = array('-','n','N','A','E','I','O','U','a','e','i','o','u');
	$url_amigo = str_replace($finx, $repx, utf8_decode($url_amigo));	
	
	// Añaadimos los guiones
	$findv = array(' ', '&', '\r\n', '\n', '+');
	$url_amigo = str_replace ($findv, '-', $url_amigo);
	// Eliminamos y Reemplazamos demás caracteres especiales
	$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
	$repl = array('', '-', '');
	$url_amigo = preg_replace ($find, $repl, $url_amigo);
	return $url_amigo;
}

function prefijo_whatsapp($phone) 
{
	// AGREGO EL PREFIJO 9 AL NUMERO = +54 9 0000 000000
	// quito espacios si tubiera 
	$phone = str_replace(' ','',$phone);
	// Indico el 9 de la caracteristica arg manualmente si no lo ponen
	$phone = preg_replace('/^(\+54)+([0-9]{4})+([0-9]{6})/','\\1 9 \\2 \\3',$phone);
	// quito los espacios en blanco
	$phone = str_replace(' ','',$phone);
	return $phone;
}

function formatear_tel_cel($num)
{
	$num=str_replace("+","",$num); 
	$num=str_replace("-","",$num); 
	$num=str_replace("(","",$num);
	$num=str_replace(")","",$num);
	$num=str_replace(" ","",$num);
	return $num;
}

function fechaleible($FechaLeible) 
{
	$system_fechale=str_replace("-",".",$FechaLeible);
	$my_fecha=strtotime($system_fechale);
	$system_fecha_dia=date('l', $my_fecha);
	$system_fecha_nro=date('j', $my_fecha);
	$system_fecha_mes=date('F', $my_fecha);
	$system_fecha_ano=date('Y', $my_fecha);
	$system_fecha_dia=str_replace("Monday","Lunes",$system_fecha_dia); 
	$system_fecha_dia=str_replace("Tuesday","Martes",$system_fecha_dia); 
	$system_fecha_dia=str_replace("Wednesday","Mi&eacute;rcoles",$system_fecha_dia); 
	$system_fecha_dia=str_replace("Thursday","Jueves",$system_fecha_dia); 
	$system_fecha_dia=str_replace("Friday","Viernes",$system_fecha_dia); 
	$system_fecha_dia=str_replace("Saturday","S&aacute;bado",$system_fecha_dia); 
	$system_fecha_dia=str_replace("Sunday","Domingo",$system_fecha_dia); 
	$system_fecha_mes=str_replace("January","Enero",$system_fecha_mes); 
	$system_fecha_mes=str_replace("February","Febrero",$system_fecha_mes); 
	$system_fecha_mes=str_replace("March","Marzo",$system_fecha_mes); 
	$system_fecha_mes=str_replace("April","Abril",$system_fecha_mes); 
	$system_fecha_mes=str_replace("May","Mayo",$system_fecha_mes); 
	$system_fecha_mes=str_replace("June","Junio",$system_fecha_mes); 
	$system_fecha_mes=str_replace("July","Julio",$system_fecha_mes); 
	$system_fecha_mes=str_replace("August","Agosto",$system_fecha_mes); 
	$system_fecha_mes=str_replace("September","Setiembre",$system_fecha_mes); 
	$system_fecha_mes=str_replace("October","Octubre",$system_fecha_mes); 
	$system_fecha_mes=str_replace("November","Noviembre",$system_fecha_mes); 
	$system_fecha_mes=str_replace("December","Diciembre",$system_fecha_mes); 
	
	
	if ( $FechaLeible == date('Y-m-d') )
	{
	$FechaLeible="Hoy";	
	}
	else
	{
	$FechaLeible=$system_fecha_dia." ".$system_fecha_nro." de ".$system_fecha_mes." del ".$system_fecha_ano;	
	}
	
	return $FechaLeible;
}	

function inscripciones_fechas($FechaLeible) 
{
	$system_fechale=str_replace("-",".",$FechaLeible);
	$my_fecha=strtotime($system_fechale);
	$system_fecha_dia=date('l', $my_fecha);
	$system_fecha_nro=date('j', $my_fecha);
	$system_fecha_mes=date('F', $my_fecha);
	$system_fecha_ano=date('Y', $my_fecha); 
	$system_fecha_mes=str_replace("January","Enero",$system_fecha_mes); 
	$system_fecha_mes=str_replace("February","Febrero",$system_fecha_mes); 
	$system_fecha_mes=str_replace("March","Marzo",$system_fecha_mes); 
	$system_fecha_mes=str_replace("April","Abril",$system_fecha_mes); 
	$system_fecha_mes=str_replace("May","Mayo",$system_fecha_mes); 
	$system_fecha_mes=str_replace("June","Junio",$system_fecha_mes); 
	$system_fecha_mes=str_replace("July","Julio",$system_fecha_mes); 
	$system_fecha_mes=str_replace("August","Agosto",$system_fecha_mes); 
	$system_fecha_mes=str_replace("September","Setiembre",$system_fecha_mes); 
	$system_fecha_mes=str_replace("October","Octubre",$system_fecha_mes); 
	$system_fecha_mes=str_replace("November","Noviembre",$system_fecha_mes); 
	$system_fecha_mes=str_replace("December","Diciembre",$system_fecha_mes); 
	
	

	$FechaLeible=$system_fecha_mes." ".$system_fecha_ano;	
	
	
	return $FechaLeible;
}

function funcion_cargar_elemento($url_exito,$id,$vars,$msj,$style)
{	
	if ($msj=='')
	{
	$msj="Examinar...";
	}
	
	$tipo_multiple='  ';		
	return '<input type="file" '.$tipo_multiple.' id="archivos" class="examinar" onChange="down_multiple_archivos(\''.$url_exito.'\',\''.$id.'\',\''.$vars.'\');" '.$style.'><div id="estado_de_carga" style="font-size:10px;">'.$msj.'</div>';
	
}

function funcion_cargar_banner_portada($vars)
{			
	return '<input type="file" id="archivos" onChange="down_banner_portada(\''.$vars.'\');" style="width: 70px; font-size:10px; float: left;"><div style="width: 100px; font-size:10px; margin:0px; padding:0px; float: left;" id="estado_de_carga">Nuevo banner</div>';
}

function funcion_cargar_imagen_portada($vars,$modo)
{			
	return '<input type="file" id="archivos'.$modo.'" onChange="down_ima_portada(\''.$vars.'\',\''.$modo.'\');" style="width: 70px; font-size:10px; float: left;"><div style="width: 100px; font-size:10px; margin:0px; padding:0px; float: left;" id="estado_de_carga_ima_'.$modo.'">Cambiar imagen</div>';
}

function resumir_descripcion($descrip) 
{
	if (strlen($descrip) > 40)
	{
	$descrip = substr($descrip, 0, 37);
	$descrip = $descrip.'...';
	}
	return $descrip;
}

function importe_mas_iva($importe,$iva)
{
	$ivares= ($importe / 100) * $iva;
	return round($importe - $ivares, 2);	
}

function recortar_texto($texto,$limite)
{			
	if ( strlen($texto) > $limite )
	{
		$texto = sprintf("%.".$limite."s\n",$texto)."... ";
	}			
	return $texto;
}

function dangerous_texts($vars)
{	
	$vars = preg_replace('/(\$)+([0-9\.\,]+)/','\\1 \\2 ',$vars);
	/*$vars = preg_replace('/>/','&#8250;',$vars);
	$vars = preg_replace('/</','&#8249;',$vars);*/
	$vars = str_replace("'","\"",$vars);
	/*$vars = str_replace("’","\"",$vars);
	$vars = str_replace("´","\"",$vars); 
	$vars = str_replace('“',"\"",$vars);
	$vars = str_replace("”","\"",$vars);*/
	
	return $vars;
}

class DocxConversion
{}


//--------------------------------------------------



function calificacion_cliente($modo,$rela_system_03,$rela_system_100,$mysqli)
{
	$TotalesPagado = 0;
	
	$row3 = $mysqli -> consulta_SQL("SELECT * FROM system_13_ventas WHERE  rela_system_100 = '$rela_system_100' AND rela_system_03 = '0' ");
	if ($row3 == TRUE)
	{
		$mysqli -> consulta_SQL("UPDATE system_13_ventas SET 
		rela_system_03 = '$rela_system_03'
		WHERE 
		rela_system_100 = '$rela_system_100'
		");
	}

	// POR TOTAL MONTO PAGADO
	$barrita='';
	$TotalesPagado = '';
	$row2 = $mysqli -> consulta_SQL("SELECT SUM(system_13_total_haber) AS TotalesPagado FROM system_13_ventas WHERE  rela_system_03 = '$rela_system_03' and system_13_estado = '1' ");
	if ($row2 == TRUE)
	{
		$TotalesPagado = $row2[0]['TotalesPagado'];
	}
	
	// POR CANTIDAD VENIDO
	$cantidad_asistencia = 0;
	$row = $mysqli -> consulta_SQL("Select * FROM system_13_ventas WHERE rela_system_03 = '$rela_system_03' and system_13_estado = '1' ");					
	if ($row == TRUE)
	{
		for ( $i=0; $i < count($row); $i++)
		{	
			$cantidad_asistencia++;
		}		
	}
		$barrita = $cantidad_asistencia;
		
		if ($rela_system_03 == '0')
		{
			$barrita='';
		}
		else
		{
		
			if ($barrita > 22)
			{
			$barrita='<i class="bi bi-star-fill amarillo"></i><i class="bi bi-star-fill amarillo"></i><i class="bi bi-star-fill amarillo"></i><i class="bi bi-star-fill amarillo"></i><i class="bi bi-star-fill amarillo"></i>';
			}
			else
			if ($barrita >= 18)
			{
			$barrita='<i class="bi bi-star-fill amarillo"></i><i class="bi bi-star-fill amarillo"></i><i class="bi bi-star-fill amarillo"></i><i class="bi bi-star-fill amarillo"></i><i class="bi bi-star gris"></i>';
			}
			else
			if ($barrita >= 12)
			{
			$barrita='<i class="bi bi-star-fill amarillo"></i><i class="bi bi-star-fill amarillo"></i><i class="bi bi-star-fill amarillo"></i><i class="bi bi-star gris"></i><i class="bi bi-star gris"></i>';
			}
			else
			if ($barrita >= 6)
			{
			$barrita='<i class="bi bi-star-fill amarillo"></i><i class="bi bi-star-fill amarillo"></i><i class="bi bi-star gris"></i><i class="bi bi-star gris"></i><i class="bi bi-star gris"></i>';
			}
			else
			if ($barrita >= 3)
			{
			$barrita='<i class="bi bi-star-fill amarillo"></i><i class="bi bi-star gris"></i><i class="bi bi-star gris"></i><i class="bi bi-star gris"></i><i class="bi bi-star gris"></i>';
			}
			else
			{
			$barrita='<i class="bi bi-star gris"></i><i class="bi bi-star gris"></i><i class="bi bi-star gris"></i><i class="bi bi-star gris"></i><i class="bi bi-star gris"></i>';
			}
		}


	if ($modo == '2')
	{
	return $TotalesPagado;
	}
	else
	if ($modo == '1')
	{
	return $cantidad_asistencia;
	}
	else
	{
	return $barrita;
	}


}





function funcion_redes_compartir($id_system_11,$detalles,$system_08_whatsapp,$path_dominio)
{
	$consulta_x_whatsapp = "whatsapp://send?phone=".prefijo_whatsapp($system_08_whatsapp)."&text=Sobre: ".$detalles;
	$compartir_x_whatsapp = "whatsapp://send?text=".$path_dominio."/fb/whatsapp.php?id=".$id_system_11." ";
	
	$redes='<div class="minitex">';
	$redes.='<a href="javascript: void(0);" onclick="window.open(\'https://www.facebook.com/sharer.php?u='.$path_dominio.'/fb/index.php?id='.$id_system_11.'\',\'ventanacompartir\', \'toolbar=0, status=0, width=550, height=400\');" class="lin"><i class="bi bi-facebook" style=" font-size: 22px;"></i> Compartir</a>&nbsp;&nbsp;&nbsp;&nbsp;';
	$redes.='<a href="'.$compartir_x_whatsapp.'"  data-action="share/whatsapp/share"  class="lin"><i class="bi bi-whatsapp" style=" font-size: 22px;"></i> Compartir</a>';
	$redes.='</div>';
	return $redes;
}

function funcion_redes_multimedia($id_system_16,$title_twitter,$system_16_tema,$path_dominio,$funcion_leer)
{
	$redes='<div style="padding:0px 25px 0px 0px;">';
	$redes.='<a href="javascript: void(0);" onclick="window.open(\'https://www.facebook.com/sharer.php?u='.$path_dominio.'/fb/index.php?id='.$id_system_16.'.multimedia\',\'ventanacompartir\', \'toolbar=0, status=0, width=550, height=400\');"><div class="compartir_facebook"></div></a>';
	$redes.='<a href="javascript: void(0);" onclick="window.open(\'https://twitter.com/home/?status='.$title_twitter.' '.$system_16_tema.'\',\'ventanacompartir\', \'toolbar=0, status=0, width=550, height=400\');" rel="nofollow"><div class="compartir_twitter"></div></a>';
	$redes.='<a href="whatsapp://send?text='.$path_dominio.'/fb/index.php?id='.$id_system_16.'.multimedia_whatsapp" data-action="share/whatsapp/share"><div class="compartir_whatsapp" id="modo_pc"></div></a>';
	$redes.='</div>';
	return $redes;
}

function funcion_ver_redes($rela_system_06,$mysqli)
{	
	
	$cadena="";
	$cadena2="";
	$cadena3="";
	//$cadena.='<li class="tema_menu">REDES</li>';
	$row = $mysqli -> consulta_SQL("SELECT * FROM system_08_configuracion where rela_system_06 = '$rela_system_06' ");
	if ($row == true)
	{	
		$system_08_titular = 		$row[0]['system_08_titular'];
		$system_08_suplente = 		$row[0]['system_08_suplente'];
		$system_08_email = 			$row[0]['system_08_email'];
		$system_08_telefono = 		$row[0]['system_08_telefono'];
		$system_08_celular = 		$row[0]['system_08_celular'];
		$system_08_whatsapp = 		$row[0]['system_08_whatsapp'];
		$system_08_facebook = 		$row[0]['system_08_facebook'];
		$system_08_twitter = 		$row[0]['system_08_twitter'];
		$system_08_youtube = 		$row[0]['system_08_youtube'];
		$system_08_instagram = 		$row[0]['system_08_instagram'];
		$system_08_mapsgoogle = 	$row[0]['system_08_mapsgoogle'];
		$system_08_direccion = 		$row[0]['system_08_direccion'];

		if ($system_08_titular !='' )
		{
		// solo al pie de pagina
		$cadena2.='<div class="tipo_ico2">'.$system_08_titular.'</div>';
		}
		if ($system_08_suplente !='' )
		{
		// solo al pie de pagina
		$cadena2.='<div class="tipo_ico2">'.$system_08_suplente.'</div>';
		}
		if ($system_08_direccion !='')
		{
		// solo al pie de pagina
		$cadena2.='<div class="tipo_ico2">'.$system_08_direccion.'</div>';
		}
		if ($system_08_email!='')
		{
		$cadena2.='<a href="mailto:'.$system_08_email.'" class="lin"><div class="tipo_ico2">'.$system_08_email.'</a></div>';
		}
		if ($system_08_telefono!='')
		{
		// solo al pie
		$cadena2.='<div class="tipo_ico2">'.$system_08_telefono.'</div>';
		}
		if ($system_08_whatsapp!='')
		{
			
		$lin_whass = "whatsapp://send?phone=".prefijo_whatsapp($system_08_whatsapp)."&text=Tu mensaje aqu&iacute;...";
		
		$cadena.='<a href="'.$lin_whass.'" data-action="share/whatsapp/share" class=""><i style="font-size:28px;" class="bi bi-whatsapp"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		//$cadena2.='<a href="'.$lin_whass.'" data-action="share/whatsapp/share" class="lin">Celular<div class="tipo_ico2">'.$system_08_celular.'</a></div>';
		//$cadena3.='<a href="'.$system_08_whatsapp.'"><div class="caja_whatsapp-i"></div></a>';
		}
		if ($system_08_instagram !='')
		{
		$cadena.='<a href="'.$system_08_instagram.'" class=""><i style="font-size:28px;" class="bi bi-instagram"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		//$cadena2.='<a href="'.$system_08_instagram.'" class="lin"><div class="tipo_ico2">'.$system_08_instagram.'</a></div>';
		//$cadena3.='<a href="'.$system_08_instagram.'"><div class="caja_instagram-i"></div></a>';
		}
		if ($system_08_facebook != '')
		{
		$cadena.='<a href="'.$system_08_facebook.'" class=""><i style="font-size:28px;" class="bi bi-facebook"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		//$cadena2.='<a href="'.$system_08_facebook.'" class="lin"><div class="tipo_ico2">'.$system_08_facebook.'</a></div>';
		//$cadena3.='<a href="'.$system_08_facebook.'"><div class="caja_facebook-i"></div></a>';
		}
		if ($system_08_twitter !='')
		{
		$cadena.='<a href="'.$system_08_twitter.'" class=""><i style="font-size:28px;" class="bi bi-twitter"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		//$cadena2.='<a href="'.$system_08_twitter.'" class="lin"><div class="tipo_ico2">'.$system_08_twitter.'</a></div>';
		//$cadena3.='<a href="'.$system_08_twitter.'"><div class="caja_twitter-i"></div></a>';
		}
		if ($system_08_youtube !='')
		{
		$cadena.='<a href="'.$system_08_youtube.'" class=""><i style="font-size:28px;" class="bi bi-youtube"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		//$cadena2.='<a href="'.$system_08_youtube.'" class="lin"><div class="tipo_ico2">'.$system_08_youtube.'</a></div>';
		//$cadena3.='<a href="'.$system_08_youtube.'"><div class="caja_youtube-i"></div></a>';
		}		
	}
	return $cadena.'^'.$cadena2.'^'.$cadena3;	
}

function estructura_ofertas_academicas($mysqli)
{	
	
	$cadena="";		
	$row = $mysqli -> consulta_SQL("SELECT * FROM system_11a_tipo
				where 
				system_11a_estado = '1'
				
				ORDER BY system_11a_orden ASC limit 2 
				");
	if ($row == true)
	{	
		for ( $i=0; $i < count($row); $i++)
		{
			$cadena.= '<div class="col-md-4 col-xl-4">';
			$cadena.= '		<div class="marginado">';
			$cadena.= '		<h5>'.$row[$i]['system_11a_tipo'].'</h5>';
			$cadena.= 		estructura_rubros($row[$i]['id_system_11a'],$mysqli);					
			$cadena.= '		</div>';	
			$cadena.= '</div>';
		}	

	}
	return $cadena;	
}
function estructura_rubros($id_system_11a,$mysqli)
{
	$cadena="";			
	$row = $mysqli -> consulta_SQL("SELECT * FROM system_11b_rubro
				where 
				rela_system_11a = '$id_system_11a'
				and
				system_11b_estado = '1'
				
				ORDER BY system_11b_orden ASC 
				");
	if ($row == true)
	{	
		for ( $i=0; $i < count($row); $i++)
		{
			$cadena.= '<strong>'.$row[$i]['system_11b_rubro'].'</strong><br>';
			$cadena.= 			estructura_sub_rubros($id_system_11a,$row[$i]['id_system_11b'],$mysqli);					

		}	

	}
	return $cadena;	
}
function estructura_sub_rubros($id_system_11a,$id_system_11b,$mysqli)
{
	$cadena="";			
	$row = $mysqli -> consulta_SQL("SELECT * FROM system_11c_subrubro
				where 
				rela_system_11a = '$id_system_11a'
				and
				rela_system_11b = '$id_system_11b'
				and
				system_11c_estado = '1'
				
				ORDER BY system_11c_orden ASC 
				");
	if ($row == true)
	{	
		for ( $i=0; $i < count($row); $i++)
		{
			$cadena.= '- '.$row[$i]['system_11c_subrubro'].'<br>';
		}	

	}
	return $cadena;	
}


function respuesta_mailing($rela_system_03,$rela_system_65,$mysqli)
{		
	$cadena="";		
	$row = $mysqli -> consulta_SQL("SELECT * FROM system_66_respuesta
				where 
				rela_system_65 = '$rela_system_65' 
				and 
				rela_system_03 = '$rela_system_03'
				
				ORDER BY id_system_66 ASC LIMIT 30 
				");
	if ($row == true)
	{	
		for ( $i=0; $i < count($row); $i++)
		{
			
			/*id_system_66,
			rela_system_65,
			,
			sesion_system_03,
			system_66_fecha,
			system_66_hora,
			system_66_respuesta,*/
			
			if ( $row[$i]['rela_system_03'] == $row[$i]['sesion_system_03'] )
			{
				$icono_globo = '<h3 class="bi bi-chat-right-dots"></h3>';
			}
			else
			{
				$icono_globo = '<h3 class="bi ico-operador"></h3>';
			}
			
			$cadena.= '<div style=" width: 100%; float:none; display: table; font-size:12px; padding:10px 0px 10px 0px; border-top:1px solid #ccc;">';
			$cadena.= '	<div style=" width: 20%; float:left; ">';
			$cadena.= 		$icono_globo;
			$cadena.= '		'.$row[$i]['system_66_fecha'].'<br>'.formatear_hora($row[$i]['system_66_hora']).'';
			$cadena.= '	</div>';
			$cadena.= '	<div style=" width: 80%; float:left; ">';
			$cadena.= 		$row[$i]['system_66_respuesta'];
			$cadena.= '	</div>';
			$cadena.= '</div>';
		}	

	}
	return $cadena;	
}


function ultima_respuesta_mailing($rela_system_03,$rela_system_65,$mysqli)
{				
	$row = $mysqli -> consulta_SQL("SELECT * FROM system_66_respuesta
				where 
				rela_system_65 = '$rela_system_65' 
				and 
				rela_system_03 = '$rela_system_03' 
				and 
				sesion_system_03 != rela_system_03
				
				ORDER BY id_system_66 DESC LIMIT 1 
				");
	if ($row == true)
	{	
		return	$row[0]['system_66_respuesta'];
	}
}



function CombertirTextoHTML($filtrartext)
{
	$filtrartext = str_replace("'","&#8217;",$filtrartext);
	$filtrartext = str_replace("‘","&#8216;",$filtrartext);
	$filtrartext = str_replace("’","&#8217;",$filtrartext);
	$filtrartext = str_replace("\“","&#8220;",$filtrartext);
	$filtrartext = str_replace("\”","&#8221;",$filtrartext);
	$filtrartext = str_replace("$","$ ",$filtrartext);
	$filtrartext = str_replace("á","&aacute;",$filtrartext);
	$filtrartext = str_replace("é","&eacute;",$filtrartext);
	$filtrartext = str_replace("í","&iacute;",$filtrartext);
	$filtrartext = str_replace("ó","&oacute;",$filtrartext);
	$filtrartext = str_replace("ú","&uacute;",$filtrartext);
	$filtrartext = str_replace("Á","&Aacute;",$filtrartext);
	$filtrartext = str_replace("É","&Eacute;",$filtrartext);
	$filtrartext = str_replace("Í","&Iacute;",$filtrartext);
	$filtrartext = str_replace("Ó","&Oacute;",$filtrartext);
	$filtrartext = str_replace("Ú","&Uacute;",$filtrartext);	
	$filtrartext = str_replace("ñ","&ntilde;",$filtrartext);
	$filtrartext = str_replace("Ñ","&Ntilde;",$filtrartext);
	$filtrartext = str_replace("°","&deg;",$filtrartext);
	$filtrartext = str_replace("ü","&uuml;",$filtrartext);
	$filtrartext = str_replace("Ü","&Uuml;",$filtrartext);
	$filtrartext = str_replace("õ","&otilde;",$filtrartext);
	$filtrartext = str_replace("Ç","&Ccedil;",$filtrartext);
	$filtrartext = str_replace("ç","&ccedil;",$filtrartext);
	$filtrartext = str_replace("¿","&iquest;",$filtrartext);
	$filtrartext = str_replace("¡","&iexcl;",$filtrartext);
	$filtrartext = str_replace("˜˜ ","&#732;",$filtrartext);
	$filtrartext = str_replace("<p>&nbsp;</p>","<br>",$filtrartext);
	$filtrartext = str_replace("<p> </p>","<br>",$filtrartext);
	
	return $filtrartext;
}


	
function pro_calen($ano,$mes,$c,$mysqli)
{		
	
	$retorno = '';
	$row = $mysqli -> consulta_SQL("Select * from system_23_agenda where  system_23_dia='$c' and system_23_mes='$mes' and system_23_ano='$ano' ");
	if ($row == true)
	{
	 $retorno = '<i class="bi bi-pin-angle-fill"></i>';			
	}
	
	return $retorno;
}

$MESCOMPLETO[1] = 'Enero'; 
$MESCOMPLETO[2] = 'Febrero'; 
$MESCOMPLETO[3] = 'Marzo'; 
$MESCOMPLETO[4] = 'Abril'; 
$MESCOMPLETO[5] = 'Mayo'; 
$MESCOMPLETO[6] = 'Junio'; 
$MESCOMPLETO[7] = 'Julio'; 
$MESCOMPLETO[8] = 'Agosto'; 
$MESCOMPLETO[9] = 'Septiembre'; 
$MESCOMPLETO[10] = 'Octubre'; 
$MESCOMPLETO[11] = 'Noviembre'; 
$MESCOMPLETO[12] = 'Diciembre'; 

$SEMANACOMPLETA[0] = 'Domingo'; 
$SEMANACOMPLETA[1] = 'Lunes'; 
$SEMANACOMPLETA[2] = 'Martes'; 
$SEMANACOMPLETA[3] = 'Mi&eacute;rcoles'; 
$SEMANACOMPLETA[4] = 'Jueves'; 
$SEMANACOMPLETA[5] = 'Viernes'; 
$SEMANACOMPLETA[6] = 'S&aacute;bado'; 

//////////////////////////////////// 
 
$ARRDIASSEMANA = $SEMANACOMPLETA; 
$ARRMES = $MESCOMPLETO; 
$b = '';
$c = '';
if( (isset($_POST['dia']) ? $_POST['dia'] : NULL) =='' ){$dia = date('d');}else{$dia = isset($_POST['dia']) ? $_POST['dia'] : NULL;}
if( (isset($_POST['mes']) ? $_POST['mes'] : NULL) ==''){$mes = date('n');}else{$mes= isset($_POST['mes']) ? $_POST['mes'] : NULL;} 
if( (isset($_POST['ano']) ? $_POST['ano'] : NULL) ==''){$ano = date('Y');}else{$ano= isset($_POST['ano']) ? $_POST['ano'] : NULL;}


$TotalDiasMes = date('t',mktime(0,0,0,$mes,$dia,$ano)); 
$DiaSemanaEmpiezaMes = date('w',mktime(0,0,0,$mes,1,$ano)); 
$DiaSemanaTerminaMes = date('w',mktime(0,0,0,$mes,$TotalDiasMes,$ano)); 
$EmpiezaMesCalOffset = $DiaSemanaEmpiezaMes; 
$TerminaMesCalOffset = 6 - $DiaSemanaTerminaMes; 
$TotalDeCeldas = $TotalDiasMes + $DiaSemanaEmpiezaMes + $TerminaMesCalOffset; 

if($mes == 1)
{ 
	$MesHoy = $mes; 
	$MesAnterior = 12; 
	$MesSiguiente = $mes + 1; 
	$AnoAnterior = $ano - 1; 
	$AnoSiguiente = $ano; 
}
else
if($mes == 12)
{ 
	$MesHoy = $mes; 
	$MesAnterior = $mes - 1; 
	$MesSiguiente = 1; 
	$AnoAnterior = $ano; 
	$AnoSiguiente = $ano + 1; 
}
else
{ 
	$MesHoy = $mes; 
	$MesAnterior = $mes - 1; 
	$MesSiguiente = $mes + 1; 
	$AnoAnterior = $ano - 1; 
	$AnoSiguiente = $ano + 1; 
} 





$ContadorTotal = '0';
$ContadorDia = '0';
$Conectados_Online = '0';
	// CONTADORES 
$ano1=date('Y');
$mes1=date('m');
$dia1=date('d');	
$row = $mysqli -> consulta_SQL("select SUM(system_300_total) AS ContadorTotal from system_300_contador");
if ($row == true)
{
$ContadorTotal = $row[0]['ContadorTotal'];
}

$row1 = $mysqli -> consulta_SQL("select SUM(system_300_total) AS ContadorDia from system_300_contador where system_300_ano='$ano1' and system_300_mes='$mes1' and system_300_dia='$dia1' ");
if ($row1 == true)
{
$ContadorDia = $row1[0]['ContadorDia'];
}

$row2 = $mysqli -> consulta_SQL("SELECT count(system_300b_ip) AS total_en_linea FROM system_300b_contador_en_linea ");
if ($row2 == true)
{
$Conectados_Online = $row2[0]['total_en_linea'];
}

function get_client_ip() 
{
	$ipaddress = '';
	if (getenv('HTTP_CLIENT_IP'))
		$ipaddress = getenv('HTTP_CLIENT_IP');
	else if(getenv('HTTP_X_FORWARDED_FOR'))
		$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	else if(getenv('HTTP_X_FORWARDED'))
		$ipaddress = getenv('HTTP_X_FORWARDED');
	else if(getenv('HTTP_FORWARDED_FOR'))
		$ipaddress = getenv('HTTP_FORWARDED_FOR');
	else if(getenv('HTTP_FORWARDED'))
	   $ipaddress = getenv('HTTP_FORWARDED');
	else if(getenv('REMOTE_ADDR'))
		$ipaddress = getenv('REMOTE_ADDR');
	else
		$ipaddress = 'UNKNOWN';
	return $ipaddress;
}


function traigo_meses($modo,$TotalMes2,$system_300_mes,$system_300_ano,$mysqli)
{
	$row = $mysqli -> consulta_SQL("select SUM(system_300_total) AS TotalMes from system_300_contador 
									where 
									system_300_ano='$system_300_ano'
									and
									system_300_mes='$system_300_mes'
									");
	if ($row == true)
	{
		$TotalMes = $row[0]['TotalMes'];
		$barra_total = round(($TotalMes * 100) / $TotalMes2, 0);	
	}
	else
	{
		$TotalMes = 0;
		$barra_total = "0";
	}
	if ($modo == 1)
	{
	return $barra_total;
	}
	else
	{
	return $TotalMes;
	}
	
}


function traigo_meses_dia($modo,$TotalMes2,$system_300_mes,$system_300_ano,$system_300_dia,$mysqli)
{
	$row = $mysqli -> consulta_SQL("select SUM(system_300_total) AS TotalDia from system_300_contador 
	where 
	system_300_ano='$system_300_ano'
	and
	system_300_mes='$system_300_mes'
	and
	system_300_dia='$system_300_dia'
	
	");
	if ($row == true)
	{
		$TotalDia = $row[0]['TotalDia'];
		$barra_total= round(($TotalDia * 100) / $TotalMes2, 0);	
	}
	else
	{
		$TotalDia = 0;
		$barra_total = "0";
	}
	if ($modo == 1)
	{
	return $barra_total;
	}
	else
	{
	return $TotalDia;
	}
}



function funcion_ver_agrupados($rela_system_11d,$mysqli)
{
	$cadena='';
	$row = $mysqli -> consulta_SQL("SELECT * FROM system_11e_relas_agrupados where rela_system_11d = '$rela_system_11d' ");
	if ($row == TRUE)
	{
		for ( $i=0; $i < count($row); $i++)
		{			
			$id_system_11e  = 			$row[$i]['id_system_11e'];		
			$rela_system_11  = 		$row[$i]['rela_system_11'];
			
			$cadena.= ' - '.optengo_oferta($rela_system_11,$mysqli).'<br>';
		}
	}
	return $cadena;

}

function extraigo_imagen_examinar($modo,$id_system_01,$rela_system_11d,$mysqli)
{
	$cadena='';
	$row = $mysqli -> consulta_SQL("SELECT * FROM system_09_archivero where rela_system_11d = '$rela_system_11d' ");
	if ($row == TRUE)
	{			
		$id_system_09  = 		$row[0]['id_system_09'];		
		$system_09_album  = 	$row[0]['system_09_album'];	
		
		// eliminar imagen
		$url="'modulos/clasificados/php/_interfaz.php'";
		$vars="'nombre_funcion=remover&id_system_09=$id_system_09'";
		$url_exito="'modulos/clasificados/php/agrupados.php'";
		$id="'content_seccion'";
		$vars_exito="'id_system_01=$id_system_01'";
		$atx="''";
		$msg="'Remover esta imagen?'";
		$funcion_remover = "eliminar_mostrar($url,$vars,$url_exito,$id,$vars_exito,$msg,$atx)";
	
		// HERRAMIENTA DE CORTE
		//$funcion_cortar = "javascript:abrir_popup_g('crop/index.php?i=$id_system_09')";
		
		//$cadena.= '<img src="'.$row[0]['system_09_path'].'/'.$row[0]['system_09_archivo'].'" class="card-img-top" alt="'.$system_09_album.'">';
		$cadena.= '<div style="background: url(\''.$row[0]['system_09_path'].'/'.$row[0]['system_09_archivo'].'\') no-repeat 50% 50%; background-size: cover; width: 100%; height: 150px;" class="card-img-top" alt="'.$system_09_album.'" ></div>';	
		//$cadena.= '<i onClick="'.$funcion_cortar.'" class="bi bi-bounding-box"></i>';
		if ($modo == 1)
		{
		$cadena.= '<i onClick="'.$funcion_remover.'" class="bi bi-trash"></i>';
		}
		
	
	}
	else
	{
		if ($modo == 1)
		{
		$url_exito="modulos/clasificados/php/agrupados.php";
		$id="content_seccion";
		$vars="id_system_01=$id_system_01&id_system_11d=$rela_system_11d";// tipo 1 es una imagen foto
		$msj='Subir imagen de banner';	
		$style =' style="width: 100px;" ';	
		$cadena.= funcion_cargar_elemento($url_exito,$id,$vars,$msj,$style);
		}
		else
		{
		$cadena.= '<div style="background: url(\'../image/no-imagen.jpg\') no-repeat 50% 50%; background-size: cover; width: 100%; height: 150px;" class="card-img-top" alt="..." ></div>';	
		}	
	}
	return $cadena;
}



function estructura_oferta($id_system_18,$id_system_11,$mysqli)
{
	$system_09_tipo="1";// se trata de una imagen
	$cadena="";
	$row = $mysqli -> consulta_SQL("SELECT * FROM system_11_catalogo  where id_system_11 = '$id_system_11' ");
	if ($row == TRUE)
	{
						
		$id_system_11 = 				$row[0]['id_system_11'];
		$rela_system_11a = 				$row[0]['rela_system_11a'];
		$rela_system_11b = 				$row[0]['rela_system_11b'];
		$rela_system_11c = 				$row[0]['rela_system_11c'];
		$system_11_estado = 			$row[0]['system_11_estado'];
		$system_11_producto = 			$row[0]['system_11_producto'];
		$system_11_forma_cursado = 		$row[0]['system_11_forma_cursado'];
		$system_11_duracion = 			$row[0]['system_11_duracion'];
		$system_11_inicio = 			$row[0]['system_11_inicio'];
		$system_11_destinatarios = 		$row[0]['system_11_destinatarios'];
		$system_11_bajada = 			$row[0]['system_11_bajada'];
		$system_11_capacitadores = 		$row[0]['system_11_capacitadores'];
		$system_11_detalles = 			$row[0]['system_11_detalles'];
		$system_11_tipo = 				$row[0]['system_11_tipo'];
		$system_11_estado_iva = 		$row[0]['system_11_estado_iva'];//uso como señalador en promo
		

		$imagen_catalogo_1 = 	optengo_imagen_catalogo($id_system_11,1,$mysqli);
		$url_amigable = 		urls_amigables($system_11_producto);
		$funcion_leer = 		"../carrera/$id_system_11/$url_amigable";	
		if ($system_11_estado_iva == 1)
		{
		$system_11_estado_iva='<i class="bi bi-pin-angle-fill" style=" font-size:55px; color: red;" title="INSCRIPCI&Oacute;N ABIERTA!"></i>';
		}
		else
		{
		$system_11_estado_iva='';
		}
		
			$cadena.='<div class="card_oferta">';	
			$cadena.='	<div style="width:80%; display: table; cursor: pointer; margin:10%; border:0px solid #ccc;" class="curve sombra" onclick="location.href=\''.$funcion_leer.'\'" title="'.$id_system_18.'">';	
			$cadena.='	  	<div style="background: #ccc url(\''.$imagen_catalogo_1.'\') no-repeat 50% 50%;  background-size: cover; width:100%; height:180px; margin-bottom:10px;" class="curve_50">'.$system_11_estado_iva.'</div>';	
			$cadena.='	  	<div style=" width:100%; height:60px; border:0px solid #000;">';	
			$cadena.='		<h5>'.$system_11_producto.'</h5>';
			$cadena.='	  	</div>';	
			$cadena.='	</div>';
			$cadena.='</div>';
			
			
	
	} 

	
	return $cadena;
}




?>