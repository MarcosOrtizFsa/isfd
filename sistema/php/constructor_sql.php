<?php
class Constructor_SQL{
	
	
	private $conexion;
	public $error;

	function __construct($host, $usua, $clav, $base) 
	{
		if(!$this->_connect($host, $usua, $clav, $base)) 
		{
			$this->error = $this->conexion->connect_error;
		}
	}

	function __destruct() {
		if ($this->conexion instanceof mysqli) {
			$this->conexion->close();
		}
	}



	private function _connect($host, $usua, $clav, $base) 
	{
		try {
			$this->conexion = new mysqli($host, $usua, $clav, $base);
			$this->conexion->set_charset("utf8mb4");
		} catch (mysqli_sql_exception $e) {
			$this->error = $e->getMessage();
			return false;
		}

		return true;
	}

	public function EnviarQuery($query) 
	{
		$tipo = strtoupper(substr(ltrim($query), 0, 6));

		switch($tipo) 
		{
			
			
			case 'INSERT':
			try {
				$resultado = $this->conexion->query($query);
			} catch (mysqli_sql_exception $e) {
				$this->error = $e->getMessage();
				return 'Fatal';
			}
			if(!$resultado) 
			{
				$this->error = $this->conexion->error;
				return 'Fatal';	// envio un msj de Fatal
			} 
			else 
			{
				$this->conexion->insert_id;
				return false; 
			}	
			break;
				
			case 'UPDATE':
			case 'DELETE':
			try {
				$resultado = $this->conexion->query($query);
			} catch (mysqli_sql_exception $e) {
				$this->error = $e->getMessage();
				return 'Fatal';
			}
			if(!$resultado)
			{
				$this->error = $this->conexion->error;
				return 'Fatal';	// envio un msj de Fatal
			} 
			else 
			{
				$this->conexion->affected_rows;
				return false; 
			}
			break;

			case 'SELECT':
			try {
				$resultado = $this->conexion->query($query);
			} catch (mysqli_sql_exception $e) {
				$this->error = $e->getMessage();
				return false;
			}
			if(!$resultado) 
			{
				$this->error = $this->conexion->error;
				return 'Fatal';	// envio un msj de Fatal
			} 
			else 
			{
				if ( $resultado->num_rows > '0' )
				{
					while($fila = $resultado->fetch_assoc()) 
					{
					$listar_datos[]=$fila;
					}
					
					if (isset($listar_datos)!='')
					{
					return $listar_datos;
					}
				}
				else
				{
				return FALSE;
				}	
			}
			break;
			
			
			default:
			return false;
			break;
			
			
		}

	}
}
?>
