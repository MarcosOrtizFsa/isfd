<?php
session_start();
include "../../../../lib/template.inc";
include "../../../../lib/mysql_conect.php";
include "../../../php/constructor_sql.php";
include "../../../php/abm.php";
include "../../../php/funciones.php";
include "_abm.php";
$mysqli = new _Abm($base);

$t = new _template('../templates');
//Archivos comunes
$t->set_file(array(
	'ver'			=> "popup_canvas.html"
	));

$id_system_03 = isset($_POST['id_system_03']) ? $_POST['id_system_03'] : NULL;

function funcion_cheched($id_system_01,$id_system_03,$mysqli)
{
	$row = $mysqli -> consulta_SQL("Select * from system_02_permisos 
	where 
	rela_system_03='$id_system_03' 
	and 
	rela_system_01='$id_system_01' 
	");
	if ($row == TRUE)
	{
		return "checked";
	}
	else
	{
		return "";
	}
}

function funcion_ver_permisos($id_system_01,$id_system_03,$mysqli)
{
	$row = $mysqli -> consulta_SQL("Select * from system_02_permisos 
	where 
	rela_system_03='$id_system_03' 
	and 
	rela_system_01='$id_system_01' 
	");
	if ($row == TRUE)
	{
		$cadena= "";
		$id_system_02 = $row[0]['id_system_02'];
		$system_02_A = $row[0]['system_02_A']; 	// AGREGA
		$system_02_B = $row[0]['system_02_B']; 	// BORRA
		$system_02_M = $row[0]['system_02_M'];	//MODIFICA
		$system_02_E = $row[0]['system_02_E'];	//ESTADOS
		$system_02_V = $row[0]['system_02_V'];	//VERIFICA
		$system_02_S = $row[0]['system_02_S'];	//SUBE
		$system_02_D = $row[0]['system_02_D'];	//DESCRAGA
		$system_02_I = $row[0]['system_02_I'];	//IMPRIME
		$system_02_C = $row[0]['system_02_C'];	//CHAT
		
		if ($system_02_A == '1'){$check_A="checked";}else{$check_A="";}
		if ($system_02_B == '1'){$check_B="checked";}else{$check_B="";}
		if ($system_02_M == '1'){$check_M="checked";}else{$check_M="";}
		if ($system_02_E == '1'){$check_E="checked";}else{$check_E="";}
		if ($system_02_V == '1'){$check_V="checked";}else{$check_V="";}
		if ($system_02_S == '1'){$check_S="checked";}else{$check_S="";}
		if ($system_02_D == '1'){$check_D="checked";}else{$check_D="";}
		if ($system_02_I == '1'){$check_I="checked";}else{$check_I="";}
		if ($system_02_C == '1'){$check_C="checked";}else{$check_C="";}
		
		$cadena.= '<input name="" type="checkbox" value="1" onclick="funcion_salvar_permiso(\'id_system_02='.$id_system_02.'&system_02_A=\'+this.value)" '.$check_A.' title="Agrega"/> ';
		$cadena.= '<input name="" type="checkbox" value="1" onclick="funcion_salvar_permiso(\'id_system_02='.$id_system_02.'&system_02_B=\'+this.value)" '.$check_B.' title="Borra"/> ';
		$cadena.= '<input name="" type="checkbox" value="1" onclick="funcion_salvar_permiso(\'id_system_02='.$id_system_02.'&system_02_M=\'+this.value)" '.$check_M.' title="Modifica"/> ';
		$cadena.= '<input name="" type="checkbox" value="1" onclick="funcion_salvar_permiso(\'id_system_02='.$id_system_02.'&system_02_E=\'+this.value)" '.$check_E.' title="Estados"/> ';
		$cadena.= '<input name="" type="checkbox" value="1" onclick="funcion_salvar_permiso(\'id_system_02='.$id_system_02.'&system_02_V=\'+this.value)" '.$check_V.' title="Verifica"/> ';
		$cadena.= '<input name="" type="checkbox" value="1" onclick="funcion_salvar_permiso(\'id_system_02='.$id_system_02.'&system_02_S=\'+this.value)" '.$check_S.' title="Sube"/> ';
		$cadena.= '<input name="" type="checkbox" value="1" onclick="funcion_salvar_permiso(\'id_system_02='.$id_system_02.'&system_02_D=\'+this.value)" '.$check_D.' title="Descarga"/> ';
		$cadena.= '<input name="" type="checkbox" value="1" onclick="funcion_salvar_permiso(\'id_system_02='.$id_system_02.'&system_02_I=\'+this.value)" '.$check_I.' title="Imprime"/> ';
		$cadena.= '<input name="" type="checkbox" value="1" onclick="funcion_salvar_permiso(\'id_system_02='.$id_system_02.'&system_02_C=\'+this.value)" '.$check_C.' title="Chat"/> ';
		return $cadena;
	}

}


 
	$row = $mysqli -> consulta_SQL("Select system_04_perfil.*, system_03_usuarios.*
				from 
				system_04_perfil, system_03_usuarios 
				where 
				system_04_perfil.rela_system_03=system_03_usuarios.id_system_03 
				and
				system_03_usuarios.id_system_03='$id_system_03' 
				
				ORDER BY system_04_perfil.id_system_04 DESC
				");
	if ($row == TRUE)
	{				
		$id_system_03 = 			$row[0]['id_system_03'];
		$t->set_var("title_canvas", $row[0]['system_04_nombre']." ".$row[0]['system_04_apellido']);
	} 		


	$row = $mysqli -> consulta_SQL("Select * from system_01_modulos
									where 
									system_01_onoff='on'
									and
									system_01_tipo!='sys'
									and
									system_01_tipo!='root'
							
									ORDER BY id_system_01 ASC
									");
	$cadena= "";
	if ($row == TRUE)
	{
		for ( $i=0; $i < count($row); $i++)
		{
			$id_system_01 = 		$row[$i]['id_system_01'];
			$system_01_modulo = 	$row[$i]['system_01_modulo'];			
			
			$url="'modulos/control/php/_interfaz.php'";
			$vars="'nombre_funcion=asignar_modulo&id_system_01=$id_system_01&id_system_03=$id_system_03'";			
						
			$url_exito="'modulos/control/php/popup_canvas.php'";
			$id="'popup_canvas'";
			$vars_exito="'id_system_01=$id_system_01&id_system_03=$id_system_03'";
			
			$funcion_asignar = " guardar_mostrar($url,$vars,$url_exito,$id,$vars_exito) ";	
			
			$cadena.= '<li class="list-group-item">';
				$cadena.= '<div class="form-check">';
				
				$cadena.= '<div style="width:45%; float:left;">';		
				
				$cadena.= '  <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" onclick="'.$funcion_asignar.'" '.funcion_cheched($id_system_01,$id_system_03,$mysqli).'>';
				$cadena.= '  <label class="form-check-label" for="flexCheckDefault">';
				$cadena.= '		'.$system_01_modulo.'';
				$cadena.= '  </label>';
				
				$cadena.= '</div>';	
				$cadena.= '<div style="width:55%; float:right;">';
				
				$cadena.= '		'.funcion_ver_permisos($id_system_01,$id_system_03,$mysqli).'';
				
				$cadena.= '</div>';
			$cadena.= '</div>';
			
			$cadena.= '</li>';

		}
		
	}
	$t->set_var("LISTA_MODULOS",$cadena);

$t->pparse("OUT", "ver");
?>
