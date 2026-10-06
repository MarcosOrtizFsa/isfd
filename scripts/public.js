/*VERIFICO ANCHO Y ALTO DE PANTALLA DEL CLIENTE PARA GURDARLO EN BD*/

function contar_visita()
{
	var ajax=nuevo_ajax(); 
	ajax.open("POST", "modulos/contador/php/json_conteo.php",true);
	var vars = 'ancho='+screen.width+'&alto='+screen.height;

        ajax.onreadystatechange=function()
		{
			if(ajax.readyState==1)
			{}
			else
			if(ajax.readyState==4)
			{
				if(ajax.status==200)
				{	
					 	
				}
				else 
				if(ajax.status==404){
					alert("La p\u00e1gina no existe");
				}
				else
				{
					 alert('Error: ' + ajax.status);
				}
			}
        }
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send(vars)
}



