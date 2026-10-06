<?php
class _Abm {

	private $bd;	
	function __construct($base)
	{
		$this -> bd = $base;
	}
		
	
	public function consulta_SQL($vars)
	{
		$respuesta = $this -> bd -> EnviarQuery($vars);
		return $respuesta;
	}	
	
	
	public function agregar_modificar_preinscripcion(
					$rela_system_07,
					$id_system_11,
					$id_system_06,																	
					$system_04_nombre,
					$system_04_apellido,
					$system_04_dni,	
					$system_04_cuil,
					$system_04_email,
					$system_04_celular,	
					$system_04_telefono,						
					$system_04_localidad,
					$system_04_provincia,
					$system_04_direccion,
					$system_04_barrio,
					$system_04_maps,
					$system_04_detalles,								
					$system_fecha,
					$system_hora,
					$system_checked,
					$codigo_captcha,
					$codigo_tabla,
					$system_65_checked,
					$system_mensaje,
					$system_horario,
					$mysqli
					)
	{		
		
		if ( $system_04_apellido =="" || $system_04_nombre == "" )
		{
			return "Error: Complete los campos obligatorios... (*) ";
			exit;
		}
		if ( $system_04_celular =="" )
		{
			return "Error: Indica un n&uacute;mero de celular ";
			exit;
		}				
		/*if ( verifico_dni($system_04_dni) =="" )
		{
			return "Error: Verifique DNI: $system_04_dni ";
			exit;
		}	*/
		if ($system_04_email=="")
		{
			return "Error: Debe incluir un e-mail. ";
			exit;
		}
		if (!(preg_match("/^([0-9a-zA-Z]+)([._-]([0-9a-zA-Z]+))*@([0-9a-zA-Z]+)([._-]([0-9a-zA-Z]+))*[.]([0-9a-zA-Z]){2}([0-9a-zA-Z])?$/",$system_04_email)))
		{
			return "Error: No parece ser un e-mail verdadero...";
			exit;
		}
		$nombre_apellido = 		$system_04_nombre.' '.$system_04_apellido;
		$system_04_dni = 		$system_65_checked;
		$system_03_estado = 	'0';
		$system_03_modo=		"3";
			

		$row = $this -> bd -> EnviarQuery("Select * from system_04_perfil where system_04_email = '$system_04_email' ");
		if ($row == TRUE)
		{
			$rela_system_03 = 	$row[0]['rela_system_03'];
			if ( $id_system_11 == '' )// es una solicitud de informacion
			{
				return solicitud_informacion_mailing($rela_system_03,$system_65_checked,$system_mensaje,$mysqli);
			}
			else
			{
				return preinscripcion_mailing($id_system_11,$rela_system_03,$system_65_checked,$system_mensaje,$mysqli);
			}			
				
		}
		else
		{
		
		
				$system_03_usuario = $system_04_dni;
				
				if ( $id_system_11 != '' and $codigo_captcha !='' )
				{
					$encriptado_sha1 = sha1($codigo_captcha, false);
				}
				else
				{
					$encriptado_sha1 = sha1($codigo_tabla, false);
				}	
				
				$respuesta = $this -> bd -> EnviarQuery("INSERT INTO system_03_usuarios 
				( 
				id_system_03,
				rela_system_07,
				rela_system_06,
				system_03_usuario,
				system_03_clave,
				system_03_cuir,
				system_03_modo,
				system_03_estado,
				system_03_fecha,
				system_03_hora														
				) 
				VALUES 
				(
				DEFAULT,
				'$rela_system_07',
				'$id_system_06',
				'$system_03_usuario',
				'$encriptado_sha1',
				'$system_checked',
				'$system_03_modo',
				'$system_03_estado',
				'$system_fecha',
				'$system_hora'
				)");
				
				if($respuesta!='Fatal')
				{
			
					$row = $this -> bd -> EnviarQuery("Select id_system_03 from  system_03_usuarios where system_03_cuir = '$system_checked' ");
					if ($row == TRUE)
					{
						$id_system_03 = $row[0]['id_system_03'];
						
						$respuesta = $this -> bd -> EnviarQuery("INSERT INTO system_04_perfil
						( 	
							rela_system_03,	
							system_04_nombre,
							system_04_apellido,
							system_04_dni,
							system_04_cuil,
							system_04_provincia,
							system_04_localidad,
							system_04_barrio,
							system_04_direccion,
							system_04_email,
							system_04_telefono,
							system_04_celular,
							system_04_detalles
						) 
						VALUES 
						(
							'$id_system_03',
							'$system_04_nombre',
							'$system_04_apellido',
							'$system_04_dni',
							'$system_04_cuil',
							'$system_04_provincia',
							'$system_04_localidad',
							'$system_04_barrio',
							'$system_04_direccion',
							'$system_04_email',
							'$system_04_telefono',
							'$system_04_celular',
							'$system_04_detalles'	
						)");
						if($respuesta!='Fatal')
						{
							if ( $id_system_11 == '' )// es una solicitud de informacion
							{
								solicitud_informacion_mailing($id_system_03,$system_65_checked,$system_mensaje,$mysqli);
							}
							else
							{
								return preinscripcion_mailing($id_system_11,$rela_system_03,$system_65_checked,$system_mensaje,$mysqli);
							}
						}
						else
						{
							return 'Fatal! Hay un error en el query [2]';
						}	
					}	
				}
				else
				{
					return 'Fatal! Hay un error en el query [1]';
				}
				
				
		}

		
	}

	
	

public function agregar_lista_mailing(
					$system_04_nombre,
					$system_04_apellido,
					$system_04_email,
					$system_04_celular,
					$system_mensaje,									
					$system_fecha,
					$system_hora,
					$system_checked,
					$mysqli
					)
	{		
		

		/*if ( $system_04_celular =="" )
		{
			return "1";
			exit;
		}*/	
		$system_04_email = strtolower($system_04_email);			
		if ($system_04_email == "")
		{
			return "2";
			exit;
		}
		if (!(preg_match("/^([0-9a-zA-Z]+)([._-]([0-9a-zA-Z]+))*@([0-9a-zA-Z]+)([._-]([0-9a-zA-Z]+))*[.]([0-9a-zA-Z]){2}([0-9a-zA-Z])?$/",$system_04_email)))
		{
			return "3";
			exit;
		}
		$id_system_06 = 1;
		$system_04_dni = $system_checked;
		$system_03_estado = '0';
		$system_03_modo="3";
		$rela_system_07='7'; // 7 es un seguidor. pidio informes	

		$row = $this -> bd -> EnviarQuery("Select * from system_04_perfil where system_04_email = '$system_04_email' ");
		if ($row == TRUE)
		{
			return '5';	
		}
		else
		{
			$system_03_usuario = $system_04_dni;
			$encriptado_sha1 = sha1($system_checked, false);

			$respuesta = $this -> bd -> EnviarQuery("INSERT INTO system_03_usuarios 
			( 
			id_system_03,
			rela_system_07,
			rela_system_06,
			system_03_usuario,
			system_03_clave,
			system_03_cuir,
			system_03_modo,
			system_03_estado,
			system_03_fecha,
			system_03_hora														
			) 
			VALUES 
			(
			DEFAULT,
			'$rela_system_07',
			'$id_system_06',
			'$system_03_usuario',
			'$encriptado_sha1',
			'$system_checked',
			'$system_03_modo',
			'$system_03_estado',
			'$system_fecha',
			'$system_hora'
			)");
			
			if($respuesta!='Fatal')
			{
				$row = $this -> bd -> EnviarQuery("Select id_system_03 from  system_03_usuarios where system_03_cuir = '$system_checked' ");
				if ($row == TRUE)
				{
					$id_system_03 = $row[0]['id_system_03'];
					
					$respuesta = $this -> bd -> EnviarQuery("INSERT INTO system_04_perfil
					( 	
						rela_system_03,	
						system_04_nombre,
						system_04_apellido,
						system_04_dni,
						system_04_cuil,
						system_04_provincia,
						system_04_localidad,
						system_04_barrio,
						system_04_direccion,
						system_04_email,
						system_04_telefono,
						system_04_celular,
						system_04_detalles
					) 
					VALUES 
					(
						'$id_system_03',
						'$system_04_nombre',
						'$system_04_apellido',
						'$system_04_dni',
						'',
						'',
						'',
						'',
						'',
						'$system_04_email',
						'',
						'$system_04_celular',
						'$system_mensaje'	
					)");
					if($respuesta!='Fatal')
					{
						
						return 4;
					}
					else
					{
						return '0';
					}	
				}	
			}
			else
			{
				return '0';
			}				
		}	
	}
	
	
	
	
	
	
	
	public function agregar_remover_menu($rela_system_11,$rela_system_19,$rela_system_20,$rela_system_21)
	{		


		if ( quito_0($rela_system_19) != '' and quito_0($rela_system_20) != '' and quito_0($rela_system_21) != '' )
		{
			
		
			$row = $this -> bd -> EnviarQuery("Select * from system_18_menu_ofertas   where rela_system_11 = '$rela_system_11' and rela_system_19 = '$rela_system_19'  and rela_system_20 = '$rela_system_20'  and rela_system_21 = '$rela_system_21' ");
			if ($row == TRUE)
			{
				$id_system_18 = $row[0]['id_system_18'];
				$respuesta = $this -> bd -> EnviarQuery("DELETE FROM system_18_menu_ofertas	WHERE id_system_18 = '$id_system_18'");
				if(!$respuesta!='Fatal')
				{
					return 'Fatal! Hay un error en el query [6]';
				}
			}
			else
			{
				$respuesta = $this -> bd -> EnviarQuery("INSERT INTO system_18_menu_ofertas  
				( 
				id_system_18,
				rela_system_19,
				rela_system_20,
				rela_system_21,
				rela_system_11														
				) 
				VALUES 
				(
				DEFAULT,
				'$rela_system_19',
				'$rela_system_20',
				'$rela_system_21',
				'$rela_system_11'
				)");
				if(!$respuesta!='Fatal')
				{
					return 'Fatal! Hay un error en el query [5]';
				}
			}			

		
		}
		else
		if ( quito_0($rela_system_19) != '' and quito_0($rela_system_20) != '' )
		{
		
			$row = $this -> bd -> EnviarQuery("Select * from system_18_menu_ofertas   where rela_system_11 = '$rela_system_11' and rela_system_19 = '$rela_system_19'  and rela_system_20 = '$rela_system_20' ");
			if ($row == TRUE)
			{
				$id_system_18 = $row[0]['id_system_18'];
				$respuesta = $this -> bd -> EnviarQuery("DELETE FROM system_18_menu_ofertas	WHERE id_system_18 = '$id_system_18'");
				if(!$respuesta!='Fatal')
				{
					return 'Fatal! Hay un error en el query [4]';
				}
			}
			else
			{
				$respuesta = $this -> bd -> EnviarQuery("INSERT INTO system_18_menu_ofertas  
				( 
				id_system_18,
				rela_system_19,
				rela_system_20,
				rela_system_21,
				rela_system_11														
				) 
				VALUES 
				(
				DEFAULT,
				'$rela_system_19',
				'$rela_system_20',
				'0',
				'$rela_system_11'
				)");
				if(!$respuesta!='Fatal')
				{
					return 'Fatal! Hay un error en el query [3]';
				}
			}			

		}
		else
		{
			
			$row = $this -> bd -> EnviarQuery("Select * from system_18_menu_ofertas   where rela_system_11 = '$rela_system_11'  ");
			if ($row == TRUE)
			{
				$id_system_18 = $row[0]['id_system_18'];
				$respuesta = $this -> bd -> EnviarQuery("DELETE FROM system_18_menu_ofertas	WHERE id_system_18 = '$id_system_18'");
				if(!$respuesta!='Fatal')
				{
					return 'Fatal! Hay un error en el query [2]';
				}
			}
			else
			{
				$respuesta = $this -> bd -> EnviarQuery("INSERT INTO system_18_menu_ofertas  
				( 
				id_system_18,
				rela_system_19,
				rela_system_20,
				rela_system_21,
				rela_system_11														
				) 
				VALUES 
				(
				DEFAULT,
				'$rela_system_19',
				'0',
				'0',
				'$rela_system_11'
				)");
				if(!$respuesta!='Fatal')
				{
					return 'Fatal! Hay un error en el query [1]';
				}
			}			
			
		}
		

		

	}
	
	
	
	
	public function agregar_editar_area($id_system_19,
										$system_19_area 
										)
	{		

		if ($system_19_area == "")
		{
			return "Error: Complete area ";
			exit;
		}
		
		if ($id_system_19 != '')
		{
			$this -> bd -> EnviarQuery("UPDATE system_19_areas SET 
			system_19_area = '$system_19_area'
			WHERE 
			id_system_19 = '$id_system_19'
			");			
		}
		else
		{
			$this -> bd -> EnviarQuery("INSERT INTO  system_19_areas   
			( 
			id_system_19,
			system_19_area,
			system_19_estado														
			) 
			VALUES 
			(
			DEFAULT,
			'$system_19_area',
			'0'
			)");				
		}	
	}
	
	
	public function on_off_area($id_system_19,
										$system_19_estado 
										)
	{
		
		if ($system_19_estado=='1')
		{
			$system_19_estado='2';
		}
		else
		{
			$system_19_estado='1';
		}
		
		$respuesta = $this -> bd -> EnviarQuery("UPDATE system_19_areas SET 
				system_19_estado='$system_19_estado'				
			WHERE 					
				id_system_19='$id_system_19'
			");
		
		if(!$respuesta!='Fatal')
		{
			return 'Fatal! Hay un error en el query [1]';
		}	
	}
	
	
	public function eliminar_area($id_system_19)
	{
		$respuesta = $this -> bd -> EnviarQuery("DELETE FROM system_19_areas 
		WHERE 
		id_system_19='$id_system_19'
		");
		if(!$respuesta!='Fatal')
		{
			return 'Fatal! Hay un error en el query [1]';
		}
	}			


	public function editar_data($modo,$system_08_contador)
	{
		if ($system_08_contador == "")
		{
			return "Error: Indique un valor numerico";
			exit;
		}		
		if ($modo == '1')
		{		
		$respuesta = $this -> bd -> EnviarQuery("UPDATE system_08_configuracion  SET system_08_contador_1 = '$system_08_contador' WHERE id_system_08 = '1' ");			
		}		
		if ($modo == '2')
		{		
		$respuesta = $this -> bd -> EnviarQuery("UPDATE system_08_configuracion  SET system_08_contador_2 = '$system_08_contador' WHERE id_system_08 = '1' ");			
		}
		if ($modo == '3')
		{		
		$respuesta = $this -> bd -> EnviarQuery("UPDATE system_08_configuracion  SET system_08_contador_3 = '$system_08_contador' WHERE id_system_08 = '1' ");			
		}
		if ($modo == '4')
		{		
		$respuesta = $this -> bd -> EnviarQuery("UPDATE system_08_configuracion  SET system_08_contador_4 = '$system_08_contador' WHERE id_system_08 = '1' ");			
		}		
		if(!$respuesta!='Fatal')
		{
			return 'Fatal! Hay un error en el query [1]';
		}		
		
	}	
	
				
}

















	//-------------------FUNCIONES FUERA DE Abm
	function preinscripcion_mailing ($rela_system_11,
									$rela_system_03,
									$system_65_checked,
									$system_mensaje,
									$mysqli
									)
	{
	
	
			// agregamos preincripcion
			$id_system_64 = 		'';
			$system_65_fecha = 		date('Y-m-d');	
			$system_65_hora = 		date('H:i:s'); 
			$system_65_asunto =		'';		
			$system_65_entidad = 	'';	
			$system_65_mail = 		'';		
			$system_65_html = 		''; 
			$system_65_resultado = 	'0'; // 	0=esta en la gatera		
			$system_65_estado=		'0';  // 	0=pediente
			
			$row1 = $mysqli -> consulta_SQL("Select * from system_11_catalogo where id_system_11 = '$rela_system_11' ");
			if ($row1 == TRUE)
			{
				$rela_system_11b = 			$row1[0]['rela_system_11b'];
				$rela_system_11c = 			$row1[0]['rela_system_11c'];
				$system_65_asunto =			$row1[0]['system_11_producto'];
			}
			
			$row2 = $mysqli -> consulta_SQL("Select * from system_04_perfil where rela_system_03 = '$rela_system_03' ");
			if ($row2 == TRUE)
			{
				$system_65_mail = 				$row2[0]['system_04_email'];
				$system_65_entidad = 			$row2[0]['system_04_nombre'].' '.$row2[0]['system_04_apellido'];
			}
			
			$row3 = $mysqli -> consulta_SQL("Select * from system_08_configuracion where id_system_08 = '1' ");
			if ($row3 == TRUE)
			{
				$system_08_dominio = 				$row3[0]['system_08_dominio'];
				$system_08_titulo_site = 			$row3[0]['system_08_titulo_site'];
				//$system_08_descripcion_site = 		$row3[0]['system_08_descripcion_site'];
				$system_08_titulo_siglas = 			$row3[0]['system_08_titulo_siglas'];
				$system_08_email = 					$row3[0]['system_08_email'];	
				$system_08_telefono = 				$row3[0]['system_08_telefono'];
				$system_08_celular = 				$row3[0]['system_08_celular'];
				$system_65_remitente = 				$row3[0]['system_08_email_alerta'];	
				$system_08_direccion = 				$row3[0]['system_08_direccion'];	
				$system_65_remitente_entidad = 		$system_08_titulo_siglas; 
			}
			
			// ADJUNTO EL ULTIMO MENSAJE TIPO 1. PREINSCRIPCION
			$row4 = $mysqli -> consulta_SQL("SELECT * FROM system_64_mensajes WHERE system_64_tipo = '1' ");
			if ( $row4 == true )
			{				
				$id_system_64 = 		$row4[0]['id_system_64'];
				//$system_65_asunto = 	$row4[0]['system_64_asunto'];
				$system_64_mensaje = 	$row4[0]['system_64_mensaje'];		
			} 
		
			$system_65_html="";
			
			
			
			if (trim($system_mensaje) != '')// si es un contacto via web , cambio el mensaje del mail
			{
				$system_65_asunto = "Contacto con ISDFS";
				$system_65_html.='<h3>'.$system_65_asunto.'</h3>';
				$system_65_html.='<br />';
				$system_65_html.= nl2br($system_mensaje);
			}
			else
			{
				$system_65_html.='<h3>'.$system_08_titulo_site.'</h3>';
				$system_65_html.='Estimado/a: '.$system_65_entidad.'';
				$system_65_html.='<br /><br />';
				$system_65_html.= nl2br($system_64_mensaje);
			}
			
			$system_65_html.='<br /><br /><hr>';
			
			// ESTRUCTURA OFERTAS RELACIONADAS
			$system_65_html.= '<strong>OTRAS OFERTAS QUE PODRIAN INTERESARTE:</strong><br />';
			$system_65_html.= estructura_lista_ofertas_mails($system_08_dominio,$rela_system_11b,$rela_system_11c,$mysqli);
			$system_65_html.= '<hr><br />';						
	
			$system_65_body="";

			$system_65_body.='<i><h5>'.$system_08_titulo_siglas.'</h5>';
			$system_65_body.=''.$system_08_direccion;
			$system_65_body.='<br />';
			$system_65_body.='Mail: '.$system_08_email;
			$system_65_body.='<br />';
			$system_65_body.=''.$system_08_telefono;
			$system_65_body.='<br />';
			$system_65_body.='WhatsApp: '.$system_08_celular;
			$system_65_body.='<br />';
			$system_65_body.='<a href="'.$system_08_dominio.'" target="_blank">'.$system_08_dominio.'</a><br /></i>';
			
					
			$respuesta = $mysqli -> consulta_SQL("INSERT INTO system_65_mailing
			( 	
				id_system_65, 	
				rela_system_03, 	
				rela_system_11, 
				rela_system_64,	
				system_65_fecha,	
				system_65_hora, 		
				system_65_entidad,	
				system_65_mail,	
				system_65_asunto, 	
				system_65_body, 	
				system_65_html, 		
				system_65_remitente, 	
				system_65_remitente_entidad, 	
				system_65_resultado, 
				system_65_fecha_ultima_res,
				system_65_hora_ultima_res,
				rela_system_03_ultima_res,	
				system_65_checked, 	
				system_65_estado

			) 
			VALUES 
			(
				DEFAULT,
				'$rela_system_03', 	
				'$rela_system_11', 
				'$id_system_64',	
				'$system_65_fecha',	
				'$system_65_hora', 		
				'$system_65_entidad',	
				'$system_65_mail',	
				'$system_65_asunto', 	
				'$system_65_body', 	
				'$system_65_html', 		
				'$system_65_remitente', 	
				'$system_65_remitente_entidad', 	
				'$system_65_resultado', 
				'0000-00-00',
				'00:00:00',
				'$rela_system_03',	
				'$system_65_checked', 	
				'$system_65_estado'	
			)");
			if($respuesta!='Fatal')
			{
				$data['data'][] = [
					'rela_system_03' => 		"$rela_system_03",
					'rela_system_11' => 		"$rela_system_11",
					'system_65_checked' =>		"$system_65_checked"
				];
				return json_encode( $data );
			}
			else
			{
				return 'Fatal! Hay un error en el query [3]';
			}
		
		
	
	}




function solicitud_informacion_mailing($rela_system_03,$system_65_checked,$system_mensaje,$mysqli)
	{
	
	
			// agregamos preincripcion
			$id_system_64 = 		'';
			$system_65_fecha = 		date('Y-m-d');	
			$system_65_hora = 		date('H:i:s'); 
			$system_65_asunto =		'';		
			$system_65_entidad = 	'';	
			$system_65_mail = 		'';		
			$system_65_html = 		''; 
			$system_65_resultado = 	'0'; // 	0=esta en la gatera		
			$system_65_estado=		'0';  // 	0=pediente
			

			$row2 = $mysqli -> consulta_SQL("Select * from system_04_perfil where rela_system_03 = '$rela_system_03' ");
			if ($row2 == TRUE)
			{
				$system_65_mail = 				$row2[0]['system_04_email'];
				$system_65_entidad = 			$row2[0]['system_04_nombre'].' '.$row2[0]['system_04_apellido'];
			}
			
			$row3 = $mysqli -> consulta_SQL("Select * from system_08_configuracion where id_system_08 = '1' ");
			if ($row3 == TRUE)
			{
				$system_08_dominio = 				$row3[0]['system_08_dominio'];
				$system_08_titulo_site = 			$row3[0]['system_08_titulo_site'];
				//$system_08_descripcion_site = 		$row3[0]['system_08_descripcion_site'];
				$system_08_titulo_siglas = 			$row3[0]['system_08_titulo_siglas'];
				$system_08_email = 					$row3[0]['system_08_email'];	
				$system_08_telefono = 				$row3[0]['system_08_telefono'];
				$system_08_celular = 				$row3[0]['system_08_celular'];
				$system_65_remitente = 				$row3[0]['system_08_email_alerta'];	
				$system_08_direccion = 				$row3[0]['system_08_direccion'];	
				$system_65_remitente_entidad = 		$system_08_titulo_siglas; 
			}
			
			// ADJUNTO EL ULTIMO MENSAJE TIPO 1. PREINSCRIPCION
			$row4 = $mysqli -> consulta_SQL("SELECT * FROM system_64_mensajes WHERE system_64_tipo = '3' ");
			if ( $row4 == true )
			{				
				$id_system_64 = 		$row4[0]['id_system_64'];
				$system_64_mensaje = 	$row4[0]['system_64_mensaje'];		
			} 
		
			$system_65_html="";
			
			
			
			if (trim($system_mensaje) != '')// si es un contacto via web , cambio el mensaje del mail
			{
				$system_65_asunto = "Solicitud de contacto con el ISDFS";
				$system_65_html.='<h3>'.$system_65_asunto.'</h3>';
				$system_65_html.='<br />';
				$system_65_html.= nl2br($system_mensaje);
			}
			else
			{
				$system_65_html.='<h3>'.$system_08_titulo_site.'</h3>';
				$system_65_html.='Estimado/a: '.$system_65_entidad.'';
				$system_65_html.='<br /><br />';
				$system_65_html.= nl2br($system_mensaje);
			}
			
			$system_65_html.='<br /><br /><hr>';
			
					
	
			$system_65_body="";

			$system_65_body.='<i><h5>'.$system_08_titulo_siglas.'</h5>';
			$system_65_body.=''.$system_08_direccion;
			$system_65_body.='<br />';
			$system_65_body.='Mail: '.$system_08_email;
			$system_65_body.='<br />';
			$system_65_body.=''.$system_08_telefono;
			$system_65_body.='<br />';
			$system_65_body.='WhatsApp: '.$system_08_celular;
			$system_65_body.='<br />';
			$system_65_body.='<a href="'.$system_08_dominio.'" target="_blank">'.$system_08_dominio.'</a><br /></i>';
			
					
			$respuesta = $mysqli -> consulta_SQL("INSERT INTO system_65_mailing
			( 	
				id_system_65, 	
				rela_system_03, 	
				rela_system_11, 
				rela_system_64,	
				system_65_fecha,	
				system_65_hora, 		
				system_65_entidad,	
				system_65_mail,	
				system_65_asunto, 	
				system_65_body, 	
				system_65_html, 		
				system_65_remitente, 	
				system_65_remitente_entidad, 	
				system_65_resultado, 
				system_65_fecha_ultima_res,
				system_65_hora_ultima_res,
				rela_system_03_ultima_res,	
				system_65_checked, 	
				system_65_estado

			) 
			VALUES 
			(
				DEFAULT,
				'$rela_system_03', 	
				'0', 
				'$id_system_64',	
				'$system_65_fecha',	
				'$system_65_hora', 		
				'$system_65_entidad',	
				'$system_65_mail',	
				'$system_65_asunto', 	
				'$system_65_body', 	
				'$system_65_html', 		
				'$system_65_remitente', 	
				'$system_65_remitente_entidad', 	
				'$system_65_resultado', 
				'0000-00-00',
				'00:00:00',
				'$rela_system_03',	
				'$system_65_checked', 	
				'$system_65_estado'	
			)");
			if($respuesta!='Fatal')
			{
				$data['data'][] = [
					'rela_system_03' => 		"$rela_system_03",
					'rela_system_11' => 		"0",
					'system_65_checked' =>		"$system_65_checked"
				];
				return json_encode( $data );
			}
			else
			{
				return 'Fatal! Hay un error en el query [3]';
			}
		
		
	
	}
	
	
	function estructura_lista_ofertas_mails($system_08_dominio,$rela_system_11b,$rela_system_11c,$mysqli)
	{	
		$cadena="";		
		$row = $mysqli -> consulta_SQL("Select * from system_11_catalogo 
		where 
		rela_system_11b = '$rela_system_11b' 
		and 
		rela_system_11c = '$rela_system_11c' 
		
		order by system_11_masvisto 
		desc 
		limit 5
		");
		if ($row == true)
		{	
			for ( $i=0; $i < count($row); $i++)
			{
				$system_11_producto = $row[$i]['system_11_producto'];
				$cadena.= '		<a href="'.$system_08_dominio.'/carrera/'.$row[$i]['id_system_11'].'/'.urls_amigables($system_11_producto).'" target="news">'.$system_11_producto.'</a>';	
				$cadena.= '		<br>';	
			}	
	
		}
		return $cadena;	
	}
	
	
	
	/*function guardar_carrera_preinscripcion(		$rela_system_11,
													$rela_system_03,
													$system_16_checked,
													$mysqli
													)
	{
	
		$row = $mysqli -> consulta_SQL("Select * from system_16_preinscripcion where rela_system_11 = '$rela_system_11' and rela_system_03 = '$rela_system_03' ");
		if ($row == TRUE)
		{
			$id_system_16 = $row[0]['id_system_16'];
			// editamos preincripcion
			$respuesta = $mysqli -> consulta_SQL("UPDATE system_16_preinscripcion SET 			
				rela_system_11 = 		'$rela_system_11',
				rela_system_03 = 		'$rela_system_03'														
				WHERE 					
				id_system_16 = 			'$id_system_16'
			");
			if($respuesta != 'Fatal')
			{	
				$data['data'][] = [
					'rela_system_03' => 		"$rela_system_03",
					'rela_system_11' => 		"$rela_system_11",
					'system_16_checked' =>		"$system_16_checked"
				];
				return json_encode( $data );
			}
			else
			{
				return 'Fatal! Hay un error en el query [4]';
			}
			
		}
		else
		{
			// agregamos preincripcion
			$system_16_fecha = date('Y-m-d');
			$system_16_hora = date('H:i:s');
			
			$respuesta = $mysqli -> consulta_SQL("INSERT INTO system_16_preinscripcion
			( 	
				id_system_16,	
				rela_system_11,
				rela_system_03,
				system_16_fecha,
				system_16_hora,
				system_16_checked
			) 
			VALUES 
			(
				DEFAULT,
				'$rela_system_11',
				'$rela_system_03',
				'$system_16_fecha',
				'$system_16_hora',
				'$system_16_checked'	
			)");
			if($respuesta!='Fatal')
			{
				$data['data'][] = [
					'rela_system_03' => 		"$rela_system_03",
					'rela_system_11' => 		"$rela_system_11",
					'system_16_checked' =>		"$system_16_checked"
				];
				return json_encode( $data );
			}
			else
			{
				return 'Fatal! Hay un error en el query [3]';
			}
		
		}
	}
	
	
	
	function json_encode_email(		$id_systema,
									$cuento,
									$destinatario,
									$destinatario_entidad,
									$asunto,
									$cuerpo,
									$html,
									$remitente,
									$remitente_entidad,
									$mysqli
									)
	{
		$data['data'][] = [
			'id_systema' => 			"$id_systema",
			'cuento' => 				"$cuento",
			'destinatario' =>			"$destinatario",
			'destinatario_entidad' =>	"$destinatario_entidad",
			'asunto' => 				"$asunto",
			'cuerpo' => 				"$cuerpo",
			'html' => 					"$html",
			'remitente' => 				"$remitente",
			'remitente_entidad' => 		"$remitente_entidad"
		];
		return json_encode( $data );
	}*/
	
	
?>
