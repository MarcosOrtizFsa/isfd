<?php
session_start();
header("Content-Type: text/html; charset=utf-8");
include __DIR__ . "/../lib/template.inc";
include __DIR__ . "/../lib/mysql_conect.php";
include __DIR__ . "/php/constructor_sql.php";
include __DIR__ . "/php/abm.php";
include __DIR__ . "/php/funciones.php";

$t = new _template(__DIR__ . '/templates/');
$t->set_file(array(
	'_INDEX'   	=> "index.html"
));


	
$row = $mysqli -> consulta_SQL("SELECT * FROM system_09_archivero 
								where 
								system_09_album = 'Banners'
								and 
								system_09_estado = '1'
								
								ORDER BY system_09_posicion ASC LIMIT 0,10 
								");	
$cadena = "";
$cuento = "1";	
$n_carru = "0";							
$cadena2= '';
$edit_banner='';
if($row == true) 
{					
	for ( $i=0; $i < count($row); $i++)
	{		
		$id_system_09 = 		$row[$i]['id_system_09'];
		$system_path_flayer = 	$row[$i]['system_09_path'].'/'.$row[$i]['system_09_archivo'];
		$rela_system_11 = 		$row[$i]['rela_system_11'];
		$rela_system_11d = 		$row[$i]['rela_system_11d'];
		$rela_system_19 = 		$row[$i]['rela_system_19'];
		$system_09_bajada = 	$row[$i]['system_09_bajada'];
		$system_09_volanta = 	$row[$i]['system_09_volanta'];
		$system_09_epigrafe = 	$row[$i]['system_09_epigrafe'];	
		$system_09_html = 		$row[$i]['system_09_html'];
		
		$rela_system_11a = 		'';
		$row1 = $mysqli -> consulta_SQL("SELECT * FROM system_11d_agrupados  where id_system_11d = '$rela_system_11d'");	
		if($row1 == true) 
		{	
			// LINKERAR AL GRUPO
			$id_system_11d = 		$row1[0]['id_system_11d'];
			$action_click= ' style="cursor:pointer;"  onclick="location.href=\'../agrupados/'.$id_system_11d.'/mas_informacion\'" ';
		}
		else
		{
			if($rela_system_11 != '0')
			{
				// LINKEAR A LA CARRERA
				$action_click= ' style="cursor:pointer;"  onclick="location.href=\'../carrera/'.$rela_system_11.'/'.urls_amigables(consultar_producto($rela_system_11,$mysqli)).'\'" ';
			}
			else
			{
				//LINKEAR AL AREA
				$action_click= ' style="cursor:pointer;"  onclick="location.href=\'../area/'.$rela_system_19.'/mas_informacion\'" ';
			}
		}
		
		if ( $sesion_system_03 !='' || $sesion_system_07 !='' || $sesion_system_06 !='' )
		{
			$url="'modulos/ima/php/edit_am.php'";
			$id="'carouselExampleIndicators'";
			$vars="'id_system_09=$id_system_09'";			
			$funcion_editar = "cargar_post($url,$id,$vars)";
			$edit_banner = '<div style="font-size:12px; color:red; cursor: pointer; float:left; margin:10px;" onclick="'.$funcion_editar.'"><h3 class="bi bi-pencil-square"></h3></div>';
		}
		
		$contenido_html = '<span '.$action_click.' class="badge " id="estilo_boton" >MAS INFORMACI&Oacute;N</span>'.$edit_banner.'';			
		$contenido_flayer= 	"<h4 class='estilo_volanta'>$system_09_volanta</h4><h1 class='estilo_bajada'>$system_09_bajada</h1>";
		$contenido_flayer.= "<div class='caj_bot'>".$contenido_html."</div>";			
		
		if ($cuento == 1)
		{
			$cadena2.= '<button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="'.$n_carru.'" class="active" 	aria-current="true" aria-label="Slide '.$cuento.'"></button>';
			
			$cadena.= '<div class="carousel-item active section-top wow animated fadeInRight" data-wow-delay=".5s">';
			$cadena.= '		<div class="d-block w-100" style="background: url(\''.$system_path_flayer.'\') no-repeat 50% 50%; background-size: cover; width: 100%;">';
			$cadena.= '			<div class="conten_flayer">'.$contenido_flayer.'</div>'; 
			$cadena.= '		</div>';
			$cadena.= '</div>';
		}
		else
		{
			$cadena2.= '<button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="'.$n_carru.'" aria-label="Slide '.$cuento.'"></button>';
			
			$cadena.= '<div class="carousel-item section-top wow animated fadeInRight" data-wow-delay=".5s">';
			$cadena.= '		<div class="d-block w-100" style="background: url(\''.$system_path_flayer.'\') no-repeat 50% 50%; background-size: cover; width: 100%;">'; 
			$cadena.= '			<div class="conten_flayer">'.$contenido_flayer.'</div>'; 
			$cadena.= '		</div>';
			$cadena.= '</div>';
		}
		
		
		$rela_system_11 ='';
		

		$cuento++;	
		$n_carru++;			
	}
}
else
{
	$cadena2= '<button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>';

	$cadena.= '<div class="carousel-item active section-top wow animated fadeInRight" data-wow-delay=".5s">';
	$cadena.= '	<div class="d-block w-100" style="background: url(\'../image/defaul_slider.gif\') no-repeat 50% 50%; background-size: cover; width: 100%; height: 520px;"></div>'; 
	$cadena.= '</div>';
}


$t->set_var("CARRUSEL_INDICATOR",$cadena2);
$t->set_var("LISTA_FOTO_EPIGRAFE",$cadena);

$face='';
$face.='<div style="display:scroll; position: relative; float:left; margin:0px 0px 0px 30px;">';
$face.='<iframe src="https://www.facebook.com/plugins/like.php?href=https://www.facebook.com/isdfs.formosa?skip_nax_wizard=true&amp;send=false&amp;layout=box_count&amp;show_faces=false&amp;width=500&amp;action=like&amp;font=verdana&amp;colorscheme=light&amp;height=65" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:120px; height:65px;"></iframe>';
$face.='</div>';

$print_menu='';
$print_opciones='';
$estilo_dinamico ='';
$menu_pasa_banner='';
$clase_shell='';
$t->set_var("ocult_carousel","alt");

if ( $sesion_system_03 !='' || $sesion_system_07 !='' || $sesion_system_06 !='' )
{		
	$t->set_var("ocult_carousel","hide");
	// CREO LOS BOTONES DEL MENU SISTEMA, PERFIL, SALIR Y PUBLICO)
	$row = $mysqli -> system_01_modulos();
	if ($row == TRUE)
	{
		for ( $i=0; $i < count($row); $i++)
		{	
			if (($mysqli -> permiso_modulo($row[$i]['id_system_01'],$sesion_system_03) == TRUE)  )
			{
			$print_menu.= 	'<li><a class="" href="'.$system_08_dominio.'/'.$modo_login.'/'.$row[$i]['id_system_01'].'/'.urls_amigables($row[$i]['system_01_modulo']).'">'.$row[$i]['system_01_modulo'].'</a></li>';
			}	
		}	
	}
	$print_opciones.= 	'<li><a class=""  href="javascript:;" onclick="location.href=\'perfil\'"><i class="bi bi-person-fill"></i></a></li>';
	$print_opciones.= 	'<li><a class=""  href="javascript:;" onclick="logout();"><i class="bi bi-box-arrow-right"></i></a></li>';
	
	$row = $mysqli -> open_modulo($id_system_01,'0');
	if ($mod == 'perfil')
	{
		$url="modulos/control/php/home_abm.php?id_system_01=2&id_system_03=".$sesion_system_03;
		$cargar_inicial=" cargar_post('".$url."','content_seccion','reset=go'); ";
	}
	else
	if ($row == TRUE)
	{
		$url="modulos/".trim($row[0]['system_01_path_home'])."/php/home.php?id_system_01=$id_system_01";		
		$cargar_inicial=" cargar_post('".$url."','content_seccion','reset=go'); ";	
	}
	else
	if ($mod == 'buscador')
	{	
		$cargar_inicial=" cargar_post('modulos/home/php/buscador_listado.php','content_seccion','reset=go'); ";		
	}
	else
	if ($mod == 'area')
	{
		$t->set_var("ocult_carousel","hide");
		$id_system_19 = isset($_GET['id_system_19']) ? $_GET['id_system_19'] : NULL;	
		$rela_system_20 = isset($_GET['rela_system_20']) ? $_GET['rela_system_20'] : NULL;	
		$rela_system_21 = isset($_GET['rela_system_21']) ? $_GET['rela_system_21'] : NULL;	
		$url="modulos/home/php/area.php";
		$cargar_inicial=" cargar_post('".$url."','content_seccion','reset=go&id_system_19=".$id_system_19."&rela_system_20=".$rela_system_20."&rela_system_21=".$rela_system_21."'); ";		

	}
	else
	if ($mod == 'carrera')
	{
		$t->set_var("ocult_carousel","hide");
		$id_system_11 = isset($_GET['id_system_11']) ? $_GET['id_system_11'] : NULL;	
		$cargar_inicial=" cargar_post('modulos/home/php/page_carrera.php','content_seccion','reset=go&id_system_11=".$id_system_11."'); ";	
	}
	else
	{				
		$cargar_inicial="  cargar_post_mas('modulos/home/php/home.php','content_seccion','','modulos/home/php/recargar.php','relistar_catalogo','');  ";
		

		$vars="id_system_01=$id_system_01&system_09_tipo=1";// tipo 1 es una imagen foto
		$funcion_cargar_archivo = funcion_cargar_banner_portada($vars);

		$t->set_var("ocult_carousel","alt");
		$menu_pasa_banner.='<div class="minitex" style="background: #FFFF66; width:100%; display:table; text-align: center; ">';
		$menu_pasa_banner.=''.$funcion_cargar_archivo;
		$menu_pasa_banner.='</div>';			
	}
	
	
	
	
		
	$estilo_dinamico = ' style="position:relative;" ';					
}
else
if ($mod == 'buscador')
{	
	$print_opciones = '';//OCULTO MENU DE SISTEMA
	$menu_i = ''; // OCULTO MENU I	
	//$pag = "_HOME";	
	$t->set_var("ocult_carousel","hide");
	$cargar_inicial=" cargar_post('modulos/home/php/buscador_listado.php','content_seccion','reset=go'); ";		
}
else
if ($mod == 'agrupados')
{	
	$print_opciones = '';//OCULTO MENU DE SISTEMA
	$menu_i = ''; // OCULTO MENU I	
	//$pag = "_HOME";	
	$rela_system_11d = isset($_GET['rela_system_11d']) ? $_GET['rela_system_11d'] : NULL;	
	$t->set_var("ocult_carousel","hide");
	$cargar_inicial=" cargar_post('modulos/home/php/page_ofertas.php','content_seccion','reset=go&id_system_11d=".$rela_system_11d."'); ";		
}
else
if ($mod == 'area')
{	
	$print_opciones = '';//OCULTO MENU DE SISTEMA
	$menu_i = ''; // OCULTO MENU I	
	//$pag = "_HOME";	
	$t->set_var("ocult_carousel","hide");
	$id_system_19 = isset($_GET['id_system_19']) ? $_GET['id_system_19'] : NULL;	
	$rela_system_20 = isset($_GET['rela_system_20']) ? $_GET['rela_system_20'] : NULL;	
	$rela_system_21 = isset($_GET['rela_system_21']) ? $_GET['rela_system_21'] : NULL;	
	$url="modulos/home/php/area.php";
	$cargar_inicial=" cargar_post('".$url."','content_seccion','reset=go&id_system_19=".$id_system_19."&rela_system_20=".$rela_system_20."&rela_system_21=".$rela_system_21."'); ";		
}
else
if ($mod == 'ofertas')
{	
	$print_opciones = '';//OCULTO MENU DE SISTEMA
	$menu_i = ''; // OCULTO MENU I	
	//$pag = "_HOME";	
	$t->set_var("ocult_carousel","hide");
	$rela_system_11d = isset($_GET['rela_system_11d']) ? $_GET['rela_system_11d'] : NULL;	
	$url="modulos/home/php/page_ofertas.php";
	$cargar_inicial=" cargar_post('".$url."','content_seccion','reset=go&id_system_11d=".$rela_system_11d."'); ";		
}
else
if ($mod == 'carrera')
{	
	$print_opciones = '';//OCULTO MENU DE SISTEMA
	$menu_i = ''; // OCULTO MENU I	
	//$pag = "_HOME";	
	$t->set_var("ocult_carousel","hide");
	$id_system_11 = isset($_GET['id_system_11']) ? $_GET['id_system_11'] : NULL;	
	$cargar_inicial=" cargar_post('modulos/home/php/page_carrera.php','content_seccion','reset=go&id_system_11=".$id_system_11."'); ";		
}
else
if ($mod == 'leer')
{	
	$clase_shell = 'd-none';
	$print_opciones = '';//OCULTO MENU DE SISTEMA
	$menu_i = ''; // OCULTO MENU I	
	//$pag = "_HOME";	
	$t->set_var("ocult_carousel","hide");
	$id_system_10 = isset($_GET['id_system_10']) ? $_GET['id_system_10'] : NULL;	
	$url="modulos/home/php/leer.php";
	$cargar_inicial=" cargar_post('".$url."','content_seccion','reset=go&id_system_10=".$id_system_10."'); ";		
}
else
if ($mod == 'login')
{
	$cargar_inicial=" cargar_post('modulos/login/php/home.php','content_seccion','reset=go'); ";
	$t->set_var("TITULO_LOGIN",''.$system_08_contactos_visibles.'');
	$t->set_var("ocult_carousel","hide");
}
else
if ($mod == 'contactos')
{
	$cargar_inicial=" cargar_post('modulos/home/php/contacto.php','content_seccion','reset=go'); ";
	$t->set_var("TITULO_LOGIN",''.$system_08_contactos_visibles.'');
	$t->set_var("ocult_carousel","hide");
}
else
{
	// La portada pública incorpora su propia cabecera y pie. El shell heredado
	// permanece visible en login y en todos los módulos administrativos.
	$clase_shell = 'd-none';
	$cargar_inicial=" cargar_post('modulos/home/php/home.php','content_seccion','reset=go'); ";	
/*	$print_search.= '<li class="nav-item navbar-collapse"><a class="fs-5 nav-link active font-weight-200" href="#ofertas">Ofertas</a></li>';
	$print_search.= '<li class="nav-item navbar-collapse"><a class="fs-5 nav-link active font-weight-200" href="#novedades">Novedades</a></li>';*/
	//$print_search.= '<li class="nav-item navbar-collapse"><a class="fs-5 nav-link active font-weight-200" href="../contactos">La Instituci&oacute;n</a></li>';
	//$print_search.= ''.$face.' <a title="Compartir ISDFS en Facebook" href="javascript: void(0);" onclick="window.open(\'https://www.facebook.com/sharer.php?u=https://isdfs.edu.ar\',\'ventanacompartir\', \'toolbar=0, status=0, width=550, height=400\');"><div class="compartir_face"></div></a>';
	
	/*VERIFICO ANCHO Y ALTO DE PANTALLA DEL CLIENTE PARA GURDARLO EN BD*/
	 
}

//$t->set_var("ESTRUCTURA_OFERTAS_ACADEMICAS",		estructura_ofertas_academicas($mysqli));



$contacto_por_whatsapp = "whatsapp://send?phone=".prefijo_whatsapp($system_08_whatsapp)."&text=ISDFS:";
$t->set_var("menu_pasa_banner",				"$menu_pasa_banner");		
$t->set_var("CLASE_SHELL",					$clase_shell);
$t->set_var("contacto_por_whatsapp",		$contacto_por_whatsapp);
$t->set_var("estilo_dinamico",				$estilo_dinamico);
$t->set_var("cargar_inicial",				$cargar_inicial);
$t->set_var("system_08_titulo_site",		$system_08_titulo_site);
$t->set_var("print_opciones",				$print_opciones);
$t->set_var("menu_i",						$menu_i);
$t->set_var("print_menu",					$print_menu);	
$https_activo = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$esquema = $https_activo ? 'https' : 'http';
$host_actual = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$directorio_actual = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (basename($directorio_actual) !== $modo_login) {
	$directorio_actual = rtrim($directorio_actual, '/') . '/' . $modo_login;
}
$dir_base = $esquema . '://' . $host_actual . rtrim($directorio_actual, '/') . '/';
$t->set_var("dir_base",						$dir_base);
$t->set_var("print_search",					$print_search);	
$t->set_var("contactos_titular",			$contactos_titular);
$t->set_var("redes_titular",				$redes_titular);	
$t->set_var("system_08_mapsgoogle",			$system_08_mapsgoogle);
$t->set_var("system_08_contactos_visibles",	nl2br($system_08_contactos_visibles));
$red = explode('^',							funcion_ver_redes($rela_system_06,$mysqli));
$t->set_var("funcion_ver_redes",			$red[0]);
$t->set_var("funcion_ver_redes_pie",		$red[1]);

		
$og_image =  	"../image/izo-logo.png"; 
$og_dominio =  	rtrim($system_08_dominio, '/');
$script=	'';	 
$script.=	'<meta property="og:type" 			content="article" />';
$script.=	'<meta property="og:image" 			content="'.$og_image.'" />';
$script.=	'<meta property="og:title" 			content="'.$system_08_titulo_site.'" />';
$script.=	'<meta property="og:description" 	content="'.$system_08_descripcion_site.'" />';
$script.=	'<meta property="og:url" 			content="'.$og_dominio.'/" />';	

$t->set_var("script",$script);
$t->set_var("system_08_keywords",$system_08_keywords);
$t->set_var("system_08_descripcion_site",$system_08_descripcion_site);

	

$t->pparse("OUT", "_INDEX");
?>
