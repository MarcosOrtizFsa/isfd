<?php
class Abm
{

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
	
	
	public function login_validar($usu,$cla)
	{
		$respuesta = $this -> bd -> EnviarQuery("SELECT * FROM system_03_usuarios 
				WHERE 
				system_03_usuario='$usu' 
				AND 
				system_03_clave='$cla'
				");
		return $respuesta;
	}

	public function login_pasaport($rr,$cc)
	{
		$respuesta = $this -> bd -> EnviarQuery("SELECT * FROM system_03_usuarios 
				WHERE 
				system_03_cuir='$rr'
				AND 
				system_03_clave='$cc' 				
				");
		return $respuesta;
	}
		
	public function system_07_privilegios($rela_system_07)
	{
		$respuesta = $this -> bd -> EnviarQuery("SELECT * FROM system_07_privilegios WHERE id_system_07='$rela_system_07'");
		return $respuesta;
	}
				
	public function system_01_modulos()
	{
	
		$id_system_03 = isset($_SESSION['sesion_system_03']) ? $_SESSION['sesion_system_03'] : NULL;
		$id_system_07= isset($_SESSION['sesion_system_07']) ? $_SESSION['sesion_system_07'] : NULL;
		$rela_system_06= isset($_SESSION['sesion_system_06']) ? $_SESSION['sesion_system_06'] : NULL;
		$system_03_modo = isset($_SESSION['sesion_system_03_modo']) ? $_SESSION['sesion_system_03_modo'] : NULL; // 0=root / 1=gerente | 2=socio vendedor | 3 cliente 															
		// 0=root, 1=gerente, 2=administracion, 3=publico
		if ( $system_03_modo=='0' ) // EXCLUSIVO PARA ROOT.
		{	
			$where_select="Select system_01_modulos.* from system_01_modulos   order by system_01_orden asc";	
		}
		else
		if ( $system_03_modo=='1' )   // TECNICO
		{		
			$where_select="Select system_01_modulos.* from system_01_modulos 
			where 
			system_01_modulos.system_01_onoff='on' 
			and 
			system_01_modulos.system_01_estado='1' 
			and 
			system_01_modulos.system_01_tipo IN ('abm','sys','acces','public') 
			
			order by system_01_modulos.system_01_orden asc
			";
		}
		else 
		if ( $system_03_modo=='2' )   // ADMINISTRADORES
		{		
			$where_select="Select system_01_modulos.* from system_01_modulos 
			where 
			system_01_modulos.system_01_onoff='on' 
			and 
			system_01_modulos.system_01_estado='1' 
			and 
			system_01_modulos.system_01_tipo IN ('acces','public') 
			
			order by system_01_modulos.system_01_orden asc
			";
		}
		else
		{	
			$where_select="Select system_01_modulos.* from system_01_modulos 
			where 
			system_01_modulos.system_01_onoff='on'
			and 
			system_01_modulos.system_01_tipo IN ('public') 
			and 
			system_01_modulos.system_01_estado='1' 
			
			order by system_01_modulos.system_01_orden asc
			";
		}
		
		$respuesta = $this -> bd -> EnviarQuery($where_select);
		return $respuesta;
	}
	
	public function system_08_configuracion()
	{
		$respuesta = $this -> bd -> EnviarQuery("SELECT * FROM system_08_configuracion ");
		return $respuesta;
	}

	public function permiso_modulo($rela_system_01,$rela_system_03)
	{
		$respuesta = $this -> bd -> EnviarQuery("Select * from system_02_permisos where rela_system_01='$rela_system_01' and rela_system_03='$rela_system_03' ");
		if ($rela_system_03 == 1)
		{
		return true;
		}
		else
		{
		return $respuesta;
		}
		
	}
	


	public function open_modulo($id_system_01,$public)
	{
		$and="";
		if ($public == '1')
		{
		$and=" and system_01_tipo='public' ";
		}
		$respuesta = $this -> bd -> EnviarQuery("Select * from system_01_modulos where id_system_01='$id_system_01' $and ");
		return $respuesta;
	}
	
	public function system_03_usuarios($sesion_system_03)
	{
		$respuesta = $this -> bd -> EnviarQuery(" Select * from system_03_usuarios where id_system_03='$sesion_system_03' ");
		return $respuesta;
	}
	
	public function system_06_sala($id_system_06)
	{
		$respuesta = $this -> bd -> EnviarQuery(" Select * from system_06_sala where id_system_06='$id_system_06' ");
		return $respuesta;
	}
	
	public function system_04_perfil($id_system_03)
	{
		$respuesta = $this -> bd -> EnviarQuery("Select * from system_04_perfil where rela_system_03='$id_system_03' ");
		return $respuesta;
	}
	
	public function reporte_error($error,$notif,$url_error,$system_08_titulo_site,$system_08_email_alerta)
	{
		$system_00_fecha = date('Y-m-d');
		$system_00_hora = date('H:i:s');
		
		if ($notif=='1')
		{			
			$asuntomail="Reporte ERROR $error ";
			$bodymail="Reporte ERROR $error -<br>";
			$bodymail.="$system_08_titulo_site <br>";
			$bodymail.="$url_error <br>";
			$bodymail.="$system_00_fecha - $system_00_hora ";

			
			$headder="MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1 \n";
			$headder.="FROM: $system_08_titulo_site <NORESPODER@mail.com>\n";
			$headder.="Reply-To: $system_08_email_alerta\n";
			
			if (mail("$system_08_email_alerta","$asuntomail","$bodymail","$headder"))
			{	
				$system_00_notif= "1";	
			}
			else
			{
				$system_00_notif= "0";	
			}

		}
		else
		{
		$system_00_notif= "0";	
		}
			
			
		$respuesta = $this -> bd -> EnviarQuery("INSERT INTO system_00_errores 
		VALUES 
		(
		DEFAULT, 
		'$system_00_fecha',
		'$system_00_hora',
		'$error',
		'$url_error',
		'$system_00_notif'
		) ");

		return $system_00_notif;		
		
	}
	
	public function guardar_archivo_down(	
						$sesion_system_03,
						$sesion_system_06,
						$rela_system_04,
						$id_system_10,
						$id_system_11,
						$id_system_15,
						$rela_system_11d,
						$rela_system_11b,
						$system_09_tipo,
						$system_09_album,
						$system_09_epigrafe,
						$archivo_name,
						$ext,
						$archivo_size,
						$system_08_path,
						$system_checked										
						)
	{
		$system_fecha = date('Y-m-d');
		$system_hora = date('H:i:s');
		$system_09_posicion = time();


		$respuesta = $this -> bd -> EnviarQuery("INSERT INTO system_09_archivero 
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
		return $respuesta;		
		
	}
	
	public function guardar_dato_extraido(	
									$sesion_system_03,
									$sesion_system_06,
									$id_system_11,
									$rela_system_11a,
									$rela_system_11b,
									$system_11_producto,
									$system_11_detalles,
									$system_11_unidades,
									$system_11_stock,
									$system_11_precio_costo,
									$system_11_precio_mayorista,
									$system_11_precio_minorista,
									$system_11_estado,
									$system_checked
									)
	{
		$system_fecha = date('Y-m-d');
		$system_hora = date('H:i:s');
		
		$ver = $this -> bd -> EnviarQuery("Select * from system_11_catalogo WHERE id_system_11 = '$id_system_11'");
		
		if ( $ver == true )
		{							

			$respuesta = $this -> bd -> EnviarQuery("UPDATE system_11_catalogo SET 
				rela_system_11a = '$rela_system_11a',
				rela_system_11b = '$rela_system_11b',
				system_11_fecha = '$system_fecha',
				system_11_hora = '$system_hora',
				system_11_producto = '$system_11_producto',
				system_11_detalles = '$system_11_detalles',
				system_11_precio_minorista = '$system_11_precio_minorista',
				system_11_unidades = '$system_11_unidades',
				system_11_estado = '$system_11_estado'															
				WHERE 					
				id_system_11 = '$id_system_11'
			");
			if(!$respuesta!='Fatal')
			{
				return 'Fatal! Hay un error en el query [2]';
			}
		
		}
		else
		{	
				
				$respuesta = $this -> bd -> EnviarQuery("INSERT INTO system_11_catalogo 
				( 
				id_system_11,
				rela_system_03,
				rela_system_06,
				rela_system_11a,
				rela_system_11b,
				system_11_fecha,
				system_11_hora,
				system_11_producto,
				system_11_detalles,
				system_11_precio_costo,
				system_11_precio_mayorista,
				system_11_precio_minorista,
				system_11_unidades,
				system_11_estado,
				system_11_checked														
				) 
				VALUES 
				(
				DEFAULT,
				'$sesion_system_03',
				'$sesion_system_06',
				'$rela_system_11a',
				'$rela_system_11b',
				'$system_fecha',
				'$system_hora',
				'$system_11_producto',
				'$system_11_detalles',
				'$system_11_precio_costo',
				'$system_11_precio_mayorista',
				'$system_11_precio_minorista',
				'$system_11_unidades',
				'$system_11_estado',
				'$system_checked'
				)");
				
				if(!$respuesta != 'Fatal')
				{
					return 'Fatal! Hay un error en el query [1]';
				}
		}	
	}
	
	public function script_meta_property($id_system_11)
	{
		$respuesta = $this -> bd -> EnviarQuery("Select * from system_11_catalogo WHERE id_system_11='$id_system_11'");
		return $respuesta;
	}
	
	public function system_09_archivero($id_system_09)
	{
		$respuesta = $this -> bd -> EnviarQuery("Select * from system_09_archivero where id_system_09='$id_system_09'");
		return $respuesta;			
	}
	
	public function guardar_descarga_archivo($id_system_09,$system_09_descargado)
	{
		$respuesta = $this -> bd -> EnviarQuery("UPDATE system_09_archivero SET system_09_descargado='$system_09_descargado' WHERE id_system_09='$id_system_09' ");
		return $respuesta;			
	}
	
	public function system_11_catalogo($id_system_11)
	{
		$respuesta = $this -> bd -> EnviarQuery("Select * from system_11_catalogo where id_system_11='$id_system_11'");
		return $respuesta;
	}
	
	
}

//---------------------------------------------


	
// FUNCION LOGISTICA PARA TODOS LOS MODULOES
function guardar_logistica($rela_system_03,$rela_system_07,$rela_system_06,$rela_system_01,$system_05_accion,$system_05_detalles,$system_05_mensaje,$mysqli)
{
	$system_05_fecha = date('Y-m-d');
	$system_05_hora = date('H:i:s');

	$mysqli -> consulta_SQL("INSERT INTO system_05_logistica 
	VALUES 
	(
	DEFAULT, 
	'$rela_system_03',
	'$rela_system_07',
	'$rela_system_06',
	'$rela_system_01',
	'$system_05_fecha',
	'$system_05_hora',
	'$system_05_accion',
	'$system_05_detalles',
	'$system_05_mensaje'
	) ");

}

function optener_permisos($acceso,$rela_system_01,$sesion_system_03,$mysqli)
{
	$retorno ='';
	$system_03_modo='';
	$dat = $mysqli -> consulta_SQL("Select * from system_03_usuarios where id_system_03='$sesion_system_03' ");
	if ($dat == true)
	{
		$system_03_modo = $dat[0]['system_03_modo'];
	}
	
	
	$row = $mysqli -> consulta_SQL("Select * from system_02_permisos where rela_system_01='$rela_system_01' AND rela_system_03 = '$sesion_system_03' ");
	if ($row == true)
	{
		switch (strtoupper($acceso))
		{
			case "A": // AGREGAR
			$retorno = $row[0]['system_02_A'];
			break;
			case "B": // BORRA
			$retorno = $row[0]['system_02_B'];
			break;
			case "M": // MODIFICA
			$retorno = $row[0]['system_02_M'];
			break;
			case "E": // ESTADO
			$retorno = $row[0]['system_02_E'];
			break;
			case "V": // VERIFICA
			$retorno = $row[0]['system_02_V'];
			break;
			case "S": // SUBE
			$retorno = $row[0]['system_02_S'];
			break;
			case "D": // DESCARGA
			$retorno = $row[0]['system_02_D'];
			break;
			case "I": // IMPRIME
			$retorno = $row[0]['system_02_I'];
			break;
			case "C": // CHAT
			$retorno = $row[0]['system_02_C'];
			break;						
			default:// NO TIENE PERMISOS
			$retorno = '0';
			break;		
		}
	}
	
	
	
	if ($system_03_modo == '0' or $system_03_modo == '1')
	{
	$retorno = '1';// accedo si es modo root o tecnico
	}

	return $retorno;	
}


?>
