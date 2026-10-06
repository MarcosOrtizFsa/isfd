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
	
	public function agregar_modificar(	$id_system_01,
										$id_system_03,
										$id_system_13,
										$id_system_11,
										$sesion_system_06,	
										$rela_system_07,															
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
										$mysqli
										)
	{		
		if ( $id_system_01 !="" )
		{
			if ( $rela_system_07 =="" )
			{
				return "Error: Elije tipo de registro";
				exit;	
			}
		}		
		if ( $system_04_apellido =="" || $system_04_celular ==""  || $system_04_dni =="")
		{
			return "Error: Complete los campos obligatorios... (*) ";
			exit;
		}				
		if ( verifico_dni($system_04_dni) =="" )
		{
			return "Error: Verifique DNI... $system_04_dni ";
			exit;
		}	
		/*if ( verifico_cuit($system_04_cuil)=="" )
		{
			return "Error: Verifique CUIT... ";
			exit;
		}*/
		
		$system_03_estado = '0';
		//0=root; 1=gerente; 2=administracion; 3=publico
		if ( $rela_system_07=="1" )// 0 root
		{
			$system_03_modo="0";
		}
		else
		if ( $rela_system_07=="2" )// gerente
		{
			$system_03_modo="1";
		}
		else
		if ( $rela_system_07=="3")// administrador
		{
			$system_03_modo="2";
		}
		else
		if ( $rela_system_07=="4" )// vendedor
		{
			$system_03_modo="2";
		}
		else
		if ( $rela_system_07=="5" )// Franquicia
		{
			$system_03_modo="2";
		}
		else						// el resto de registros son de modo 3 cliente
		{
			$system_03_modo="3";
		}
	
		
		if ($id_system_03 != '')
		{										
			if ( $id_system_01 != "" ) // CAMBIO EL MODO 
			{
				$this -> bd -> EnviarQuery("UPDATE system_03_usuarios SET 
				rela_system_07 = '$rela_system_07',
				system_03_modo = '$system_03_modo'
				WHERE 
				id_system_03 = '$id_system_03'
				");
			}
			
			// SIEMPRE EDITO USUARIO (dni)	
			$this -> bd -> EnviarQuery("UPDATE system_03_usuarios SET 
			system_03_usuario = '$system_04_dni'
			WHERE 
			id_system_03 = '$id_system_03'
			");
				
			//$_SESSION['sesion_perfil']="";
			$respuesta = $this -> bd -> EnviarQuery("UPDATE system_04_perfil SET 			
				system_04_nombre = 		'$system_04_nombre',
				system_04_apellido = 	'$system_04_apellido',
				system_04_dni = 		'$system_04_dni',
				system_04_cuil = 		'$system_04_cuil',
				system_04_provincia = 	'$system_04_provincia',
				system_04_localidad =	'$system_04_localidad',
				system_04_barrio =		'$system_04_barrio',
				system_04_direccion =	'$system_04_direccion',
				system_04_email =		'$system_04_email',
				system_04_telefono = 	'$system_04_telefono',
				system_04_celular =		'$system_04_celular',
				system_04_detalles = 	'$system_04_detalles'														
				WHERE 					
				rela_system_03 = '$id_system_03'
			");
			if($respuesta != 'Fatal')
			{	
				if (quito_0($id_system_13) != '') // edito tabla 13
				{
					salvar_rela03_ventas($id_system_13,$id_system_03,$id_system_11,$mysqli);
				}
				else
				{
					return 'Infor: Listo, salvado...';
				}
			}
			else
			{
				return 'Fatal! Hay un error en el query [3]';
			}
		
		}
		else
		{	
		

			$row = $this -> bd -> EnviarQuery("Select * from system_04_perfil where system_04_dni = '$system_04_dni' ");
			if ($row == TRUE)
			{
				return "Error: Ya estas registrado!";
				exit;
			}
	
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
				'$sesion_system_06',
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
			
					$row = $this -> bd -> EnviarQuery("Select id_system_03 from  system_03_usuarios 
					where 
					system_03_cuir='$system_checked' ");
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
							if (quito_0($id_system_13) != '') // edito tabla 13
							{
								salvar_rela03_ventas($id_system_13,$id_system_03,$id_system_11,$mysqli);
							}
							else
							{
								return 'Infor: Listo, guardado...';
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





	public function eliminar($id_system_03)
	{
		$respuesta = $this -> bd -> EnviarQuery("DELETE FROM system_03_usuarios 
		WHERE 
		id_system_03='$id_system_03'
		");
		
		$this -> bd -> EnviarQuery("DELETE FROM system_04_perfil WHERE rela_system_03='$id_system_03' ");
		
		if(!$respuesta!='Fatal')
		{
			return 'Fatal! Hay un error en el query [1]';
		}	
	}
	
	public function on_off($id_system_03,$system_03_estado)
	{
		
		if ($system_03_estado=='1')
		{
			$system_03_estado='2';
		}
		else
		{
			$system_03_estado='1';
		}
		
		$respuesta = $this -> bd -> EnviarQuery("UPDATE system_03_usuarios SET 
				system_03_estado='$system_03_estado'				
			WHERE 					
				id_system_03='$id_system_03'
			");
		
		if(!$respuesta!='Fatal')
		{
			return 'Fatal! Hay un error en el query [1]';
		}	
	}
	
	
	
	
	 public function salvar_clave($id_system_03,$system_03_clave,$system_03_clave_copy)
	 {
	 
		if ($system_03_clave=='' or $system_03_clave_copy=='')
		{
			return "Error: Indique nueva clave y repita...";
			exit;
		}
		
		if ( $system_03_clave != $system_03_clave_copy)
		{
			return "Error: Las claves son diferentes...";
			exit;
		}
		
		if (strlen($system_03_clave)  < '5' )
		{
			return "Error: Clave muy corta...";
			exit;
		}
		
		if (strlen($system_03_clave)  > '8' )
		{
			return "Error: M&aacute;ximo 8 caracteres...";
			exit;
		}
		
		if (strpos($system_03_clave, " "))
		{
			return "Error: Asigna una clave sin espacios...";
			exit;
		}
		
		//  CODIFICO LA CLAVE
		$encriptado_sha1 = sha1($system_03_clave, false);
		
		$respuesta = $this -> bd -> EnviarQuery("UPDATE system_03_usuarios SET 
		system_03_clave='$encriptado_sha1'
		WHERE 
		id_system_03='$id_system_03'
		");
		if(!$respuesta!='Fatal')
		{
			return 'Fatal! Hay un error en el query [1]';
		}
	
	 }



	public function asignar_modulo($id_system_01,$id_system_03)
	{
		
		$row = $this -> bd -> EnviarQuery("Select * from system_02_permisos 
		where 
		rela_system_03='$id_system_03' 
		and 
		rela_system_01='$id_system_01' 
		");
		if ($row == TRUE)
		{
			$id_system_02 = $row[0]['id_system_02'];
			$this -> bd -> EnviarQuery("DELETE FROM system_02_permisos WHERE id_system_02='$id_system_02' ");
			if(!$respuesta!='Fatal')
			{
				return 'Fatal! Hay un error en el query [2]';
			}
		}
		else
		{		
			$respuesta = $this -> bd -> EnviarQuery("INSERT INTO system_02_permisos
			( 	
				rela_system_03,	
				rela_system_01
			) 
			VALUES 
			(
				'$id_system_03',
				'$id_system_01'	
			)");
			if(!$respuesta!='Fatal')
			{
				return 'Fatal! Hay un error en el query [1]';
			}			
		}		
	
	}

	public function remover($id_system_09)
	{
		$respuesta = $this -> bd -> EnviarQuery("DELETE FROM system_09_archivero 
		WHERE 
		id_system_09='$id_system_09'
		");
		if(!$respuesta!='Fatal')
		{
			return 'Fatal! Hay un error en el query [1]';
		}	
	}
	
	
	public function salvar_permiso(	$id_system_02,
									$system_02_A,
									$system_02_B,
									$system_02_M,
									$system_02_E,
									$system_02_V,
									$system_02_S,
									$system_02_D,
									$system_02_I,
									$system_02_C
									)
	{
	
		$row = $this -> bd -> EnviarQuery("Select * from system_02_permisos where id_system_02='$id_system_02' ");			
		if ($system_02_A != '')
		{
			if ($row[0]['system_02_A'] == '1') {$system_02_A=0;} else {$system_02_A=1;}
			$this -> bd -> EnviarQuery("UPDATE system_02_permisos SET system_02_A='$system_02_A' WHERE id_system_02='$id_system_02' ");
		}

		if ($system_02_B != '')
		{
			if ($row[0]['system_02_B'] == '1') {$system_02_B=0;} else {$system_02_B=1;}
			$this -> bd -> EnviarQuery("UPDATE system_02_permisos SET system_02_B='$system_02_B' WHERE id_system_02='$id_system_02' ");
		}

		if ($system_02_M != '')
		{
			if ($row[0]['system_02_M'] == '1') {$system_02_M=0;} else {$system_02_M=1;}
			$this -> bd -> EnviarQuery("UPDATE system_02_permisos SET system_02_M='$system_02_M' WHERE id_system_02='$id_system_02' ");
		}

		if ($system_02_E != '')
		{
			if ($row[0]['system_02_E'] == '1') {$system_02_E=0;} else {$system_02_E=1;}
			$this -> bd -> EnviarQuery("UPDATE system_02_permisos SET system_02_E='$system_02_E' WHERE id_system_02='$id_system_02' ");
		}

		if ($system_02_V != '')
		{
			if ($row[0]['system_02_V'] == '1') {$system_02_V=0;} else {$system_02_V=1;}
			$this -> bd -> EnviarQuery("UPDATE system_02_permisos SET system_02_V='$system_02_V' WHERE id_system_02='$id_system_02' ");
		}
		
		if ($system_02_S != '')
		{
			if ($row[0]['system_02_S'] == '1') {$system_02_S=0;} else {$system_02_S=1;}
			$this -> bd -> EnviarQuery("UPDATE system_02_permisos SET system_02_S='$system_02_S' WHERE id_system_02='$id_system_02' ");
		}
		
		if ($system_02_D != '')
		{
			if ($row[0]['system_02_D'] == '1') {$system_02_D=0;} else {$system_02_D=1;}
			$this -> bd -> EnviarQuery("UPDATE system_02_permisos SET system_02_D='$system_02_D' WHERE id_system_02='$id_system_02' ");
		}
		
		if ($system_02_I != '')
		{
			if ($row[0]['system_02_I'] == '1') {$system_02_I=0;} else {$system_02_I=1;}
			$this -> bd -> EnviarQuery("UPDATE system_02_permisos SET system_02_I='$system_02_I' WHERE id_system_02='$id_system_02' ");
		}
		
		if ($system_02_C != '')
		{
			if ($row[0]['system_02_C'] == '1') {$system_02_C=0;} else {$system_02_C=1;}
			$this -> bd -> EnviarQuery("UPDATE system_02_permisos SET system_02_C='$system_02_C' WHERE id_system_02='$id_system_02' ");
		}
	}				
}

?>