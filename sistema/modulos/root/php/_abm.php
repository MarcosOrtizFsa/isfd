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
	
	
	public function agregar_modificar($id_system_01,$system_01_modulo,$system_01_tipo,$system_01_path_home,$system_01_onoff,$system_01_orden)
	{		
		if ($system_01_modulo=="" || $system_01_tipo=="" || $system_01_path_home=="" || $system_01_onoff=="" )
		{
			return "Error: Debe completar las cajas obligatorias ";
			exit;
		}
		
		if ($id_system_01!='')
		{
			$respuesta = $this -> bd -> EnviarQuery("UPDATE system_01_modulos SET 
				system_01_modulo='$system_01_modulo',
				system_01_tipo='$system_01_tipo',
				system_01_path_home='$system_01_path_home',
				system_01_onoff='$system_01_onoff',
				system_01_orden='$system_01_orden'							
			WHERE 						
				id_system_01='$id_system_01'
			");
			if(!$respuesta!=Fatal)
			{
				return 'Fatal! Hay un error en el query [2]';
			}
		}
		else
		{		
			$respuesta = $this -> bd -> EnviarQuery("INSERT INTO system_01_modulos  
			VALUES 
			(
			DEFAULT,
			'$system_01_modulo',
			'$system_01_tipo',
			'$system_01_path_home',
			'$system_01_onoff',
			'$system_01_orden',
			'0'
			)
			");
			
			if(!$respuesta!=Fatal)
			{
				return 'Fatal! Hay un error en el query [1]';
			}
		}
	}


	public function eliminar($id_system_01)
	{
		$respuesta = $this -> bd -> EnviarQuery("DELETE FROM system_01_modulos 
		WHERE 
		id_system_01='$id_system_01'
		");
		
		if(!$respuesta!=Fatal)
		{
			return 'Fatal! Hay un error en el query [1]';
		}	
	}
	
	public function on_off($id_system_01,$system_01_estado)
	{
		
		if ($system_01_estado==1)
		{
			$system_01_estado=0;
		}
		else
		{
			$system_01_estado=1;
		}
		
		$respuesta = $this -> bd -> EnviarQuery("UPDATE system_01_modulos SET 
				system_01_estado='$system_01_estado'				
			WHERE 					
				id_system_01='$id_system_01'
			");
		
		if(!$respuesta!=Fatal)
		{
			return 'Fatal! Hay un error en el query [1]';
		}	
	}
			
}
?>
