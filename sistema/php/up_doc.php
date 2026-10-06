<?php
session_start();
include "../../lib/mysql_conect.php";
include "constructor_sql.php";
include "abm.php";
include "funciones.php";


$extraer_info = 		isset($_GET['extraer_info']) ? $_GET['extraer_info'] : NULL;
$id_system_11 = 		isset($_GET['id_system_11']) ? $_GET['id_system_11'] : NULL;
$id_system_15 = 		isset($_GET['id_system_15']) ? $_GET['id_system_15'] : NULL;
$rela_system_11b = 		isset($_GET['rela_system_11b']) ? $_GET['rela_system_11b'] : NULL;
$id_system_11d = 		isset($_GET['id_system_11d']) ? $_GET['id_system_11d'] : NULL;
$system_09_tipo = 		isset($_GET['system_09_tipo']) ? $_GET['system_09_tipo'] : NULL;
$system_09_album = 		isset($_GET['system_09_album']) ? $_GET['system_09_album'] : NULL;

$mensage = '';
$cuet="1";



function guardar_file(	
					$file_name_tmp, 
					$file,
					$destino,
					$sesion_system_03,
					$sesion_system_06,
					$rela_system_04,
					$id_system_10,
					$id_system_11,
					$id_system_15,
					$id_system_11d,
					$rela_system_11b,
					$system_09_tipo,
					$system_09_album,
					$system_09_epigrafe,
					$archivo_name,
					$ext,
					$archivo_size,
					$system_08_path,
					$ancho_requerido,
					$system_checked,
					$mysqli										
					)
{
	if ( move_uploaded_file($file_name_tmp, $file) )
	{ 
		chmod("$file", 0644);
		
		if ($destino !='' )
		{
			$tam = getimagesize("$file"); 
			$n_ancho = 	$tam[0]; 
			$n_alto = 	$tam[1]; 
			
			if ( $n_ancho > $ancho_requerido )
			{
				$n_ancho = 	$ancho_requerido; 
				$n_alto = 	( $n_ancho * ($tam[1] / $tam[0]) ); 
			}
			
			if ($tam[2] == "1"){
			$img2 = imagecreatefromgif("$file");
			$img1 = imagecreatetruecolor($n_ancho,$n_alto);
			imagecopyresampled($img1,$img2,0,0,0,0,$n_ancho,$n_alto,$tam[0],$tam[1]);
			imagegif($img1,"$destino");
			imagedestroy($img2);
			imagedestroy($img1);
			unlink($file); 
			}
			
			if ($tam[2] == "2"){
			$img2 = imagecreatefromjpeg("$file");
			$img1 = imagecreatetruecolor($n_ancho,$n_alto);
			imagecopyresampled($img1,$img2,0,0,0,0,$n_ancho,$n_alto,$tam[0],$tam[1]);
			imagejpeg($img1,"$destino");
			imagedestroy($img2);
			imagedestroy($img1);
			unlink($file); 
			}
			
			if ($tam[2] == "3"){
			$img2 = imagecreatefrompng("$file");
			$img1 = imagecreatetruecolor($n_ancho,$n_alto);
			imagecopyresampled($img1,$img2,0,0,0,0,$n_ancho,$n_alto,$tam[0],$tam[1]);
			imagepng($img1,"$destino");
			imagedestroy($img2);
			imagedestroy($img1);
			unlink($file);  		
			}
		}

		$respuesta = $mysqli -> consulta_SQL("INSERT INTO system_09_archivero  
		(
			id_system_09,
			rela_system_06, 	
			rela_system_03, 	
			rela_system_04, 	
			rela_system_10, 	
			rela_system_11, 	
			rela_system_15, 	
			rela_system_11b, 	
			rela_system_11d, 	
			system_09_tipo,
			system_09_album, 	
			system_09_epigrafe, 	
			system_09_volanta, 	
			system_09_bajada, 	
			system_09_path, 	
			system_09_archivo, 	
			system_09_type, 	
			system_09_size, 	
			system_09_destacado, 	
			system_09_descargado, 	
			system_09_html, 	
			system_09_posicion, 	
			system_09_estado, 	
			system_09_checked
		)
		VALUES 
		(
			DEFAULT,	
			'$sesion_system_06', 
			'$sesion_system_03', 	
			'$rela_system_04',	
			'$id_system_10', 
			'$id_system_11', 
			'$id_system_15', 
			'0',	
			'$rela_system_11d',
			'$system_09_tipo',	
			'$system_09_album',	
			'$system_09_epigrafe', 
			'',	
			'',
			'$system_08_path', 	
			'$archivo_name', 	
			'$ext', 	
			'$archivo_size', 	
			'0',
			'0',
			'', 
			'$system_09_posicion',	
			'1',	 	
			'$system_checked' 
		)");
		if ($respuesta == TRUE)
		{
			return 'Exito! guardado...';
		}
		else
		{
			return 'Fatal! Hay un error en el query guardar_archivo_down[2]';
		}

	}
	else 
	{
	return 'Fatal! Hay un error en move_uploaded_file [1]';
	}
}




function extraer_info(		
		$file_name_tmp, 
		$file,
		$destino,
		$sesion_system_03,
		$sesion_system_06,
		$system_09_tipo,
		$system_09_album,
		$system_09_epigrafe,
		$archivo_name,
		$ext,
		$archivo_size,
		$system_08_path,
		$system_08_ancho_max,
		$system_checked,
		$mysqli										
		)
{
	if (move_uploaded_file ($file_name_tmp, $file))
	{ 
		chmod("$file", 0644);
		
		$mysqli -> guardar_archivo_down(
		$sesion_system_03,
		$sesion_system_06,
		$system_09_tipo,
		$system_09_album,
		$system_09_epigrafe,
		$archivo_name,
		$ext,
		$archivo_size,
		$file,
		$system_checked										
		);			
		if ( $ext == "csv" )
		{
			$cadena_txt = fopen("$file", "r") or exit('Fatal! Hay un error en FOPEN [1]');
			while(!feof($cadena_txt))
			{	
				$array = explode("\n", fgets($cadena_txt));
				foreach ($array as $value) 
				{
					$row = explode(";", $value);			
					if ( ctype_digit($row[0]) == true and count($row) == '8') // si o si debe tener un id. 0=agregar nuevo producto
					{				
						$id_system_11 = 				trim($row[0]);// id
						$rela_system_11a =				trim($row[1]); // rela rubro
						$rela_system_11b =				trim($row[2]); // rela rubro
						$system_11_producto =			utf8_encode($row[3]);	 //	 prroducto						
						$system_11_detalles =			utf8_encode($row[4]);// caracteristicas
						$system_11_unidades =			trim($row[5]);// unidades  xN° | E=entero(x1); M=medio(/2); C=cuarto(/4); O=octavo(/8) 
						$system_11_precio_minorista =	trim($row[6]); // precio
						$system_11_estado =				trim($row[7]); // esatdo


						if ($system_11_unidades == '0' or $system_11_unidades == '')
						{
						$system_11_unidades = 1;
						}
						
										
						if ( $id_system_11 !='' || $rela_system_11a !='' || $system_11_producto !='' || $system_11_precio_minorista !=''  )
						{
							$mysqli -> guardar_dato_extraido(
							$sesion_system_03,
							$sesion_system_06,
							$id_system_11,
							$rela_system_11a,
							$rela_system_11b,
							$system_11_producto,
							$system_11_detalles,
							$system_11_unidades,
							$system_11_stock,
							format_money($system_11_precio_costo),
							format_money($system_11_precio_mayorista),
							format_money($system_11_precio_minorista),
							$system_11_estado,
							$system_checked
							);
						}
						
					}	
					$system_checked++;
					$id_system_11 = 				'';
					$system_11_detalles	=			'';
					$system_11_producto	=			'';
					$system_11_precio_costo	=		'';
					$system_11_precio_mayorista	=	'';
					$system_11_precio_minorista	=	'';
					$system_11_unidades	=			'';
					$system_11_stock	=			'';	
					$system_11_estado_iva = 		'';
				}				
			}
			fclose($cadena_txt);
			
		}	
	}
	else 
	{
	return 'Fatal! Hay un error en move_uploaded_file [1]';
	}
}




if ($_FILES)
{

	foreach ($_FILES as $key) //Iteramos el arreglo de archivos
	{
			
	$archivo_name = 			$key['name'];
	$archivo_name_original = 	$key['name'];//Obtenemos el nombre original del archivo
	$archivo_type = 			$key['type'];
	$archivo_size = 			$key['size'];			
	$file_name_tmp = 			$key['tmp_name']; //Obtenemos la ruta Original del archivo
	$file_name_error = 			$key['error'];
	
			
		if ($file_name_error > '0')
		{
			if ($file_name_error=='1')
			{	
			$mensage= 'Error: '.$cuet.') El archivo supera el peso permitido del servidor.\n';
			}
			else
			if ($file_name_error=='2')
			{
			$mensage= 'Error: '.$cuet.')  El archivo supera el peso programado.\n';
			}
			else
			if ($file_name_error=='3')
			{
			$mensage= 'Error: '.$cuet.') El archivo no se cargo completamente...\n';
			}
			else
			if ($file_name_error=='4')
			{
			$mensage= 'Error: '.$cuet.') Ningun archivo fue subido.\n';
			}
			else
			if ($file_name_error=='7')
			{
			$mensage.= 'Error: '.$cuet.') Verifique la capasidad de su plan de hosting.\n';
			}
			else
			if ($file_name_error=='8')
			{
			$mensage= 'Error: '.$cuet.') No se pudo determinar el tipo de archivo.\n';	
			}
			else
			{
			$mensage= 'Error: '.$cuet.') '.$archivo_name_original.' no pudo subir...\n';	
			}	
		}
		else
		{
			if (!(strpos($archivo_type, "php") || strpos($archivo_type, "inc") || strpos($archivo_type, "js") || strpos($archivo_type, "html") ))
			{
		
				if ( $archivo_size > $system_08_limit_size_up_archivo ) 
				{ 	
				$mensage= 'Error: '.$cuet.') '.$archivo_size.' supera el peso permitido ('.$system_08_limit_size_up_archivo.')\n';	
				}
				else
				{
					
					$ext = explode(".",$archivo_name);
					$name= $ext[0];
					$ext= end($ext);
					
					
					if ( $ext=='csv' and $extraer_info=='ok') // si se trata de un csv lista de precios
					{
						$system_09_album="Temporal";
						$system_09_epigrafe=$archivo_name;
						$archivo_name=time().'.'.$ext;
						$file = '../../archivos/'.$archivo_name;
						$destino = '';
						$system_09_tipo="3";// 1=imagen 2=archivo, 3=temporal
						$system_08_path= $system_08_path.'/archivos';
						
						$mensage = extraer_info(	
						$file_name_tmp, 
						$file,
						$destino,
						$sesion_system_03,
						$sesion_system_06,
						$system_09_tipo,
						$system_09_album,
						$system_09_epigrafe,
						$archivo_name,
						$ext,
						$archivo_size,
						$system_08_path,
						$system_08_ancho_max,
						$system_checked,
						$mysqli										
						);
					}
					else
					if ( $ext=='docx' or $ext=='doc' or $ext=='txt' or $ext=='csv' or $ext=='xls' or $ext=='xlsx' or $ext=='zip' or $ext=='rar' or $ext=='pdf')
					{
						$system_09_album="Archivos";
						$system_09_epigrafe=$archivo_name;
						$archivo_name=time().'_'.urls_amigables($name).'.'.$ext;
						$file = '../../archivos/'.$archivo_name;
						$destino = '';
						$system_09_tipo="2";// 1=imagen 2=archivo
						$system_08_path= $system_08_path.'/archivos';
						
						$mensage= guardar_file(	$file_name_tmp, 
													$file,
													$destino,
													$sesion_system_03,
													$sesion_system_06,
													$rela_system_04,
													$id_system_10,
													$id_system_11,
													$system_09_tipo,
													$system_09_album,
													$system_09_epigrafe,
													$archivo_name,
													$ext,
													$archivo_size,
													$system_08_path,
													$system_08_ancho_max,
													$system_checked,
													$mysqli										
													);
					}
					else
					if ( $ext=='png' or $ext=='jpg' or $ext=='jpeg' or $ext=='gif')
					{
							
						
							
						if ( $rela_system_04 !='' )
						{
							$system_09_album="Perfil";
							$system_09_epigrafe=$archivo_name;
							$archivo_name=date('Ymd_his').'.'.$ext;
							$file = '../../tmp/'.$archivo_name;
							$destino = '../../imagenes/'.$archivo_name;
							$system_09_tipo="1";// 1=imagen 2=archivo
							$system_08_path= $system_08_path.'/imagenes';
							$ancho_requerido = $system_08_ancho_minimo;
						}
						else
						if ( $id_system_11 !='' )
						{
							$system_09_album="Catalogo";
							$system_09_epigrafe=$archivo_name;
							$archivo_name=time().'_'.urls_amigables($name).'.'.$ext;
							$file = '../../tmp/'.$archivo_name;
							$destino = '../../imagenes/'.$archivo_name;
							$system_09_tipo="1";// 1=imagen 2=archivo
							$system_08_path= $system_08_path.'/imagenes';
							$ancho_requerido = $system_08_ancho_max;
						}
						else
						if ( $id_system_10 !='' )
						{
							$system_09_album="Noticia";
							$system_09_epigrafe=$archivo_name;
							$archivo_name=date('Ymd_his').'.'.$ext;
							$file = '../../tmp/'.$archivo_name;
							$destino = '../../imagenes/'.$archivo_name;
							$system_09_tipo="1";// 1=imagen 2=archivo
							$system_08_path= $system_08_path.'/imagenes';
							$ancho_requerido = $system_08_ancho_medio;
						}
						else
						if ( $id_system_11d !='' )
						{
							$system_09_album="Ofertas";
							$system_09_epigrafe=$archivo_name;
							$archivo_name=date('Ymd_his').'.'.$ext;
							$file = '../../tmp/'.$archivo_name;
							$destino = '../../imagenes/'.$archivo_name;
							$system_09_tipo="1";// 1=imagen 2=archivo
							$system_08_path= $system_08_path.'/imagenes';
							$ancho_requerido = $system_08_ancho_medio;
						}
						else
						{
							
							
							if ( $system_09_album != '')
							{
								// ES UNA IMAGEN DE PORTADA
								$system_09_album="IMA_PORTADA";
								$system_09_epigrafe=$archivo_name;
								$archivo_name=time().'_'.urls_amigables($name).'.'.$ext;
								$file = '../../tmp/'.$archivo_name;
								$destino = '../../imagenes/'.$archivo_name;
								$system_09_tipo="1";// 1=imagen 2=archivo, 3=csv
								$system_08_path= $system_08_path.'/imagenes';
								$ancho_requerido = $system_08_ancho_max;
							}
							else
							{
								// POR DEFECTO CONFIGURA PARA GUARDAR COMO BANNERS
								$system_09_album="Banners";
								$system_09_epigrafe=$archivo_name;
								$archivo_name=time().'_'.urls_amigables($name).'.'.$ext;
								$file = '../../tmp/'.$archivo_name;
								$destino = '../../imagenes/'.$archivo_name;
								$system_09_tipo="1";// 1=imagen 2=archivo, 3=csv
								$system_08_path= $system_08_path.'/imagenes';
								$ancho_requerido = $system_08_ancho_max;
							}
						}
						
											
							
						$mensage= guardar_file(		$file_name_tmp, 
													$file,
													$destino,
													$sesion_system_03,
													$sesion_system_06,
													$rela_system_04,
													$id_system_10,
													$id_system_11,
													$id_system_15,
													$id_system_11d,
													$rela_system_11b,
													$system_09_tipo,
													$system_09_album,
													$system_09_epigrafe,
													$archivo_name,
													$ext,
													$archivo_size,
													$system_08_path,
													$ancho_requerido,
													$system_checked,
													$mysqli										
													);

					}
					else
					{	
						$mensage= 'Error: '.$cuet.') '.$archivo_name_original.' No parece ser un archivo permitido aqui...\n';	
					}

				}
				
			}
			else // NO SE ENCONTRO EL TIPO PERMITIDO
			{	
				$mensage= 'Error: '.$cuet.') '.$archivo_name_original.' No se reconoce como archivo para este sistema!\n';			
			}
			
		}
											
	$cuet++;
	}
}
							
echo $mensage;
?>