<?php
// Controladores anteriores reemplazados por app_*.php.
http_response_code(410);
exit('Esta pantalla fue reemplazada. Ingrese desde mipanel.');

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
	
	
	
			
}
?>
