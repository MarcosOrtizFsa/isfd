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
	

	

	
	public function agregar_registro(		$rela_system_07,
											$rela_system_06,											
											$system_04_nombre,
											$system_04_apellido,
											$system_04_profesion,	
											$system_04_cuil,
											$system_04_email,
											$system_04_celular,															
											$system_fecha,
											$system_hora,
											$system_03_cuir,
											$codigo_captcha,
											$system_08_titulo_site,
											$system_08_dominio,	
											$system_08_descripcion_site,
											$system_08_celular,
											$system_08_email_alerta,
											$system_08_email
											)
	{
		
		if ( $system_04_nombre=="" || $system_04_apellido=="" || $system_04_celular=="" )
		{
			return "Error: Complete los campos obligatorios... (*) ";
			exit;
		}		
		
		if ( $system_04_profesion=="" || $system_04_cuil=="")
		{
			return "Error: Razon social y CUIT de la empresa";
			exit;
		}
		
		/*if (!( $codigo_captcha == $_SESSION['captcha_session'] ))
		{
		return "Error: El c&oacute;digo captcha no es igual...";
		exit;
		}*/
	
		if ( $rela_system_07=="5" )
		{
			$system_03_modo="3";// 3  cliente por defecto
		}
			

		$row = $this -> bd -> EnviarQuery("Select system_04_perfil.*, system_03_usuarios.*
		from 
		system_04_perfil, system_03_usuarios 
		where 
		system_04_perfil.rela_system_03=system_03_usuarios.id_system_03 
		and 
		system_04_perfil.system_04_cuil='$system_04_cuil' 
		");
		if ($row == TRUE)
		{
			if ($row[0]['system_03_estado'] == '0')
			{
				return "Error: Tu registro no esta confirmado aun...\nVerifica entre tus correos No Deseados.";
			}
			else
			{
				return "Error: Ya estas registrado! \nIntenta recuperar tu clave.";
			}			
			exit;
		}
		
		
	
		// OPTENGO EL USUARIO SI EXISTE UN CUIL
		$system_03_usuario=$system_04_cuil;
		$clave_fija = "123456";
		$encriptado_sha1 = sha1($clave_fija, false);
		
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
		'$rela_system_06',
		'$system_03_usuario',
		'$encriptado_sha1',
		'$system_03_cuir',
		'$system_03_modo',
		'0',
		'$system_fecha',
		'$system_hora'
		)");
		
		if($respuesta!='Fatal')
		{
	
			$row = $this -> bd -> EnviarQuery("Select * from  system_03_usuarios 
			where 
			system_03_cuir='$system_03_cuir' ");
			if ($row == TRUE)
			{
				$rela_system_03 = $row[0]['id_system_03'];
				
				$respuesta = $this -> bd -> EnviarQuery("INSERT INTO system_04_perfil
				( 	
					rela_system_03,
					system_04_dni,
					system_04_cuil,
					system_04_nombre,
					system_04_apellido,
					system_04_email,
					system_04_celular,
					system_04_profesion
				) 
				VALUES 
				(
					'$rela_system_03',
					'$system_04_dni',
					'$system_04_cuil',
					'$system_04_nombre',
					'$system_04_apellido',
					'$system_04_email',
					'$system_04_celular',
					'$system_04_profesion'
				)");
				if($respuesta!='Fatal')
				{
					$_SESSION['captcha_session']='';

					$asuntomail="Solicitud de Registro en $system_08_titulo_site";
					$bodymail="$system_fecha a las $system_hora hs. \n";
					$bodymail.="Nombre y Apellido: $system_04_nombre $system_04_apellido \n";
					$bodymail.="E-mail: $system_04_email\n";
					$bodymail.="Celular: $system_04_celular\n";
					$bodymail.="Razon social: $system_04_profesion\n";
					$bodymail.="CUIT: $system_04_cuil\n\n";
					
					$headder="MIME-Version: 1.0\nContent-type: text/plain; charset=iso-8859-1 \n";
					$headder.="FROM: $system_04_nombre $system_04_apellido <$system_04_email>\n";
					$headder.="Reply-To: $system_08_email\n";
					
					
					if (mail("$system_08_email_alerta","$asuntomail","$bodymail","$headder"))
					{
						return "Perfecto! Pronto te daremos el alta...";				
					}
					else
					{
						return "Listo! Ponte en contacto con $system_08_titulo_site para tu alta...";
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




public function recuperar_clave(		$system_04_email,
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
										)
	{		
		

		if (!( $codigo_captcha == $_SESSION['captcha_session'] ))
		{
		return '<i class="bi bi-emoji-dizzy"></i> El c&oacute;digo captcha no es igual...';
		exit;
		}			
							
		
		$asuntomail2="Restablecer Clave";
		$bodymail2="";
		$bodymail2.="Pinch&aacute; el siguiente link para restablecer tu clave!";
		$bodymail2.="<br>";
		$bodymail2.="<br>";

		$bodymail2.="<a href=\"$system_08_dominio/tem/2/1/$system_03_cuir\" target=\"new\">RESTABLECER CLAVE</a>";

		$bodymail2.="<br /><br />";
		$bodymail2.="+------------------------------------------------------------------------------+";
		$bodymail2.="<div style=\" background: #cccccc; font-size: 12px; font-family:Tahoma; \" >";
		$bodymail2.="Si no logras pinchar el v&iacute;nculos, puedes copiar el siguiente c&oacute;digo y pegarlo en la URL de tu navegador:";
		$bodymail2.="<br /><br />";
		$bodymail2.="$system_08_dominio/tem/3/1/$system_03_cuir";
		$bodymail2.="<br />";
		$bodymail2.="</div>";
		$bodymail2.="+------------------------------------------------------------------------------+";
		$bodymail2.="<br /><br />";
		$bodymail2.="Si a&uacute;n no logras optener tus datos de acceso, envi&aacute; un WhatsApp al $system_08_celular <br>";
		$bodymail2.="O un e-mail a <a href=\"mailto:$system_08_email\">$system_08_email</a> <br>";
		$bodymail2.="<br />";
		$bodymail2.="<br />";
		$bodymail2.="";
		
		
		$headder2="MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1 \n";
		$headder2.="FROM: $system_08_titulo_site <$system_08_email>\n";
		$headder2.="Reply-To: $system_08_email\n";
		
		$_SESSION['captcha_session']='';
		
		if (mail("$system_04_email","$asuntomail2","$bodymail2","$headder2"))
		{	
		return '<i class="bi bi-emoji-laughing"></i> Enviado! Verif&iacute;ca tu correo para continuar...<br>Recuerda buscar en "Correos no deseados".';	
		}
		else
		{
		return '<i class="bi bi-exclamation-triangle-fill"></i> No se pudo completar en envio...';	
		}
				
	}

			
}
?>
