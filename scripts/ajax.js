// JavaScript Document

/*
\u00e1 = á
\u00e9 = é
\u00ed = í
\u00f3 = ó
\u00fa = ú
\u00c1 = Á
\u00c9 = É
\u00cd = Í
\u00d3 = Ó
\u00da = Ú
\u00f1 = ñ
\u00d1 = Ñ 
*/


function nuevo_ajax(){
        var xmlhttp=false;
        try{
                xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");

        }catch(e){
                try{
                        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
						
                }catch(E){
                        xmlhttp = false;
                }
        }

        if(!xmlhttp && typeof XMLHttpRequest!='undefined'){
                xmlhttp = new XMLHttpRequest();
        }
        return xmlhttp;
}

function ejecutar_get(url){	
	var ajax=nuevo_ajax(); 
	ajax.open("GET", url,true);
}



function cargar_post(url,id,vars){

       //$.blockUI(); // abro blockUI para ver la precarga mientras ejecuto el scrips. Y luego lo cierro en el scrips carga_interna con //$.unblockUI

		id = document.getElementById(id);
        var ajax=nuevo_ajax(); 
        ajax.open("POST", url,true);
        ajax.onreadystatechange=function()
		{
			if(ajax.readyState==1)
			{}
			else
			if(ajax.readyState==4)
			{
				//$.unblockUI(); // cierro precraga
				if(ajax.status==200){	
					id.innerHTML=ajax.responseText; 	
				}
				else 
				if(ajax.status==404){
					id.innerHTML = '';
					alert("La p\u00e1gina no existe");
				}
				else
				{
					id.innerHTML = '';
					 alert('Error: ' + ajax.status);
				}
			}
        }
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send(vars)
}

function cargar_post_spinner(url,id,vars)
{
		id = document.getElementById(id);
		id.innerHTML = '<center><img src="../image/ajax-loader-small.gif" /> Buscando...</center>';
        var ajax=nuevo_ajax(); 
        ajax.open("POST", url,true);
        ajax.onreadystatechange=function()
		{
			
			if(ajax.readyState==1)
			{}
			else
			if(ajax.readyState==4)
			{
				if(ajax.status==200){	
					id.innerHTML=ajax.responseText; 	
				}
				else 
				if(ajax.status==404){
					id.innerHTML = '';
					alert("La p\u00e1gina no existe");
				}
				else
				{
					id.innerHTML = '';
					 alert('Error: ' + ajax.status);
				}
			}
        }
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send(vars)
}
function cargar_popup_home()
{
	id = document.getElementById('capa_registro');
	var ajax=nuevo_ajax(); 
	ajax.open("POST", "php/json.php",true);
	var vars= vars;

        ajax.onreadystatechange=function()
		{
			if(ajax.readyState==1)
			{}
			else
			if(ajax.readyState==4)
			{
				if(ajax.status==200)
				{	
					var respuesta = JSON.parse(ajax.responseText);
					const data = respuesta.data;
						
						var listado = '';
						listado += `${data[0].cadena}`
						id.innerHTML = listado;
 	
				}
				else 
				if(ajax.status==404){
					id.innerHTML = '';
					alert("La p\u00e1gina no existe");
				}
				else
				{
					id.innerHTML = '';
					 alert('Error: ' + ajax.status);
				}
			}
        }
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send(vars)
}
function acepto_pop_jnso(url,vars)
{
	id = document.getElementById('capa_dialogo_pop');
	var ajax=nuevo_ajax(); 
	ajax.open("POST", url,true);
	ajax.onreadystatechange=function()
	{
		if(ajax.readyState==1)
		{}
		else 
		if(ajax.readyState==4)
		{
			if(ajax.status==200)
			{			
				var respuesta_mostrar=ajax.responseText.replace(/(^\s*)|(\s*$)/g,"");
				cadena = ``
				if ( respuesta_mostrar == 0)
				{
				cadena = `<div class="section-top wow animated fadeInUp" data-wow-delay="1s"><div class="dee" style="margin:0px 10px 0px 0px;"></div> Ups... algo sali\u00f3 mal... Intenta mas tarde</div>`
				}
				else
				if ( respuesta_mostrar == 2)
				{
				cadena = `<div class="section-top wow animated fadeInUp" data-wow-delay="1s"><div class="ham" style="margin:0px 10px 0px 0px;"></div> Debes indicar tu email para continuar...  </div>`
				}
				else
				if ( respuesta_mostrar == 3)
				{
				cadena = `<div class="section-top wow animated fadeInUp" data-wow-delay="1s"><div class="ham" style="margin:0px 10px 0px 0px;"></div> No parece ser en correo verdadero... Intenta con otro</div>`
				}
				else
				{
				cerrar_pop_jnso();
				}					
				capa_dialogo_pop.innerHTML = cadena;
					
			}
			else
			if(ajax.status==404)
			{
				alert('Error: La p\u00e1gina no existe');
			}
			else
			{
				var ok = confirm('Se encontr\u00f3 un ERROR ' + ajax.status + '!\nQuiere notificar esto ahora?')
				if (ok)
				{						
					reportar_error(ajax.status,1,url);
				}
				else
				{
					reportar_error(ajax.status,0,url);
				}
			}
		}
	}
	ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	ajax.send(vars)
}

function cerrar_pop_jnso()
{
	id = document.getElementById('capa_registro');
	id.innerHTML = '';
}
function cargar_post_mas(url,id,vars,url2,id2,vars2){

       //$.blockUI(); // abro blockUI para ver la precarga mientras ejecuto el scrips. Y luego lo cierro en el scrips carga_interna con //$.unblockUI

		id = document.getElementById(id);
        var ajax=nuevo_ajax(); 
        ajax.open("POST", url,true);
        ajax.onreadystatechange=function()
		{
			if(ajax.readyState==1)
			{}
			else
			if(ajax.readyState==4)
			{
				//$.unblockUI(); // cierro precraga
				if(ajax.status==200){	
					id.innerHTML=ajax.responseText; 
					cargar_post(url2,id2,vars2);
					cargar_post('modulos/home/php/calendario.php','contain_calendario','');
				}
				else 
				if(ajax.status==404){
					id.innerHTML = '';
					alert("La p\u00e1gina no existe");
				}
				else
				{
					id.innerHTML = '';
					 alert('Error: ' + ajax.status);
				}
			}
        }
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send(vars)
}
function simple_post(vars){
        //creamos el objeto XMLHttpRequest
        var ajax=nuevo_ajax(); 
        //peticionamos los datos, le damos la url enviada desde el link
        ajax.open("POST", vars,true);
        ajax.onreadystatechange=function(){
                if(ajax.readyState==1){
                }else if(ajax.readyState==4){
                        //ajax.responseText; 
                	}
        }
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send(vars)
}


function guardar_mostrar(url,vars,url_exito,id,vars_exito)
{
        var ajax=nuevo_ajax(); 
        ajax.open("POST", url,true);
		//$.blockUI(); // abro blockUI para ver la precarga mientras ejecuto el scrips. Y luego lo cierro en el scrips carga_interna con //$.unblockUI

        ajax.onreadystatechange=function(){
                if(ajax.readyState==1)
				{}
				else 
				if(ajax.readyState==4)
				{
					if(ajax.status==200){
						
						var respuesta_mostrar=ajax.responseText.replace(/(^\s*)|(\s*$)/g,"");
						var respuesta=respuesta_mostrar.substring(0,6);
						var respuesta_sinerror=respuesta_mostrar.substring(7,150);
						//alert(respuesta);
						if (respuesta=='Fatal!')
						{								
							//$.unblockUI(); // cierro precraga
							var ok = confirm(respuesta_mostrar+'\nQuiere notificar esto ahora?')
							if (ok)
							{						
								reportar_error('Fatal!',1,url);
							}
							else
							{
								reportar_error('Fatal!',0,url);
							}
						}
						else
						if  (respuesta=='Error:')
						{
							//$.unblockUI(); // cierro precraga
							alert(respuesta_mostrar);
						}
						else
						{
							cargar_post(url_exito,id,vars_exito);
						}
						
					}
					else
					if(ajax.status==404)
					{
						//$.unblockUI(); // cierro precraga
						alert('Error: La p\u00e1gina no existe');
					}
					else
					{
						//$.unblockUI(); // cierro precraga
						var ok = confirm('Se encontr\u00f3 un ERROR ' + ajax.status + '!\nQuiere notificar esto ahora?')
						if (ok)
						{						
							reportar_error(ajax.status,1,url);
						}
						else
						{
							reportar_error(ajax.status,0,url);
						}
					}
                }
        }
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send(vars)
}


function eliminar_mostrar(url,vars,url_exito,id,vars_exito,msj)
{
	var confirmar = confirm (msj);
	if (confirmar)
	{
		//$.blockUI(); // abro precargador

		var ajax=nuevo_ajax(); 
        ajax.open("POST", url,true);
        ajax.onreadystatechange=function()
		{
			if(ajax.readyState==1)
			{}
			else 
			if(ajax.readyState==4)
			{
				if(ajax.status==200)
				{
					cargar_post(url_exito,id,vars_exito);
				}
				else
				if(ajax.status==404)
				{
					//$.unblockUI(); // cierro precraga
					alert('Error: La p\u00e1gina no existe');
				}
				else
				{
					//$.unblockUI(); // cierro precraga
					var ok = confirm('Se encontr\u00f3 un ERROR ' + ajax.status + '!\nQuiere notificar esto ahora?')
					if (ok)
					{						
						reportar_error(ajax.status,1,url);
					}
					else
					{
						reportar_error(ajax.status,0,url);
					}
				}
			}
        }
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send(vars);
	}	
}



function eliminar_refrescar(url,vars,msj)
{
	var confirmar = confirm (msj);
	if (confirmar)
	{
		//$.blockUI(); // abro precargador

		var ajax=nuevo_ajax(); 
        ajax.open("POST", url,true);
        ajax.onreadystatechange=function()
		{
			if(ajax.readyState==1)
			{}
			else 
			if(ajax.readyState==4)
			{
				if(ajax.status==200)
				{
					window.location = "../";
				}
				else
				if(ajax.status==404)
				{
					//$.unblockUI(); // cierro precraga
					alert('Error: La p\u00e1gina no existe');
				}
				else
				{
					//$.unblockUI(); // cierro precraga
					var ok = confirm('Se encontr\u00f3 un ERROR ' + ajax.status + '!\nQuiere notificar esto ahora?')
					if (ok)
					{						
						reportar_error(ajax.status,1,url);
					}
					else
					{
						reportar_error(ajax.status,0,url);
					}
				}
			}
        }
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send(vars);
	}	
}



function on_off(url,vars,url_exito,id,vars_exito,msj)
{
//creamos el objeto XMLHttpRequest
	var answer = confirm (msj);
	if (answer)
	{
		    var ajax=nuevo_ajax(); 
        //peticionamos los datos, le damos la url enviada desde el link
        ajax.open("POST", url,true);
        ajax.onreadystatechange=function(){
                if(ajax.readyState==1){ 
                }else if(ajax.readyState==4){
                        if(ajax.status==200){
							
							
							var respuesta_mostrar=ajax.responseText.replace(/(^\s*)|(\s*$)/g,"");
							var respuesta=respuesta_mostrar.substring(0,6);
							var respuesta_sinerror=respuesta_mostrar.substring(7,95);
							//alert(respuesta);
							if (respuesta=='Fatal!')
							{								
								//alert(respuesta_mostrar);
								showDialog('<img src="../image/iconos/dee.png"  border="0" align="absmiddle"> Contacte al Soporte t\u00e9cnico.',respuesta_sinerror,'error');
							}
							else
							if  (respuesta=='Error:')
							{
								//alert(respuesta_mostrar);
								showDialog('<img src="../image/iconos/ham.png"  border="0" align="absmiddle"> Cuidado...',respuesta_sinerror,'warning');
							}
							else
							if  (respuesta=='Listo!')
							{
								//alert(respuesta_mostrar);
								showDialog('<img src="../image/iconos/hapy.png"  border="0" align="absmiddle"> Listo!',respuesta_sinerror,'success');
							}
							else
							{
								//alert(respuesta_mostrar);
								//showDialog('<img src="../image/iconos/loader.gif"  border="0" align="absmiddle"> Aguarde...',respuesta_mostrar,'success','1');
								cargar_post(url_exito,id,vars_exito);
							}
							
							
						}else if(ajax.status==404){
							alert('Error: La p\u00e1gina no existe');
    					}else{
                            alert("Error:".ajax.status); 
                        }
                }
        }
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send(vars)
	}
}



function guardar_vars(url,vars){	
	var ajax=nuevo_ajax(); 
	ajax.open("POST", url,true);
	ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	ajax.send(vars)
}

function guardar_vars_refresh(url,vars){	
	var ajax=nuevo_ajax(); 
	ajax.open("POST", url,true);	
	window.location = "../";
	ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	ajax.send(vars)
}

function solo_refresh()
{		
	window.location = "../";
}

function cargar_rebuscador(url,vars)
{
	const formulario = document.querySelector('#variable_buscar_dinamico');
	const boton = document.querySelector('#button-addon2');
	const resultado = document.querySelector('#resultado_dinamico');


	id = document.getElementById('resultado');
	var ajax=nuevo_ajax(); 
	ajax.open("POST", "modulos/home/php/json.php",true);
	var vars= vars;
	

	ajax.onreadystatechange=function(){
		
		
						var respuesta = JSON.parse(ajax.responseText);
						const data = respuesta.data;
						
						const filtrar = ()=>{
							resultado.innerHTML = '';    
							const texto = variable_buscar_dinamico.value.toLowerCase();
						
							for(var i = 0; i < data.length; i++) {
						
									
								let producto = data[i].system_11_producto.toLowerCase();
								
						
									if (producto.indexOf(texto) !== -1)
									{
										
										resultado.innerHTML += `<tr>`				
										resultado.innerHTML += `	<td class=list_menu>`
										resultado.innerHTML += `	<center><h5>${data[i].system_11_producto}</h5></center>`
										resultado.innerHTML += `	<center><button type="button" class="btn btn-success" onclick="${data[i].funcion_cargar_carro}">(${data[i].system_11_codigo}) x${data[i].system_11_unidades} $ ${data[i].total_venta}</button></center>`
										resultado.innerHTML += `	</td>`
										resultado.innerHTML += `</tr>`
									}
						
									

									if(resultado.innerHTML === '')
									{
										resultado.innerHTML += '';				
									}
									
									
							}
						
						}
						
						
						boton.addEventListener('click', filtrar)
						formulario.addEventListener('keyup', filtrar)
						
						
						// LISTAD PREVIO
						var listado = '';
						for(var i = 0; i < data.length; i++) {
							
							
							listado += `<tr>`				
							listado += `	<td class=list_menu>`
							listado += `	<center><h5>${data[i].system_11_producto}</h5></center>`
							listado += `	<center><button type="button" class="btn btn-outline-success" onclick="${data[i].funcion_cargar_carro}">(${data[i].system_11_codigo}) x${data[i].system_11_unidades} $ ${data[i].total_venta}</button></center>`
							listado += `	</td>`
							listado += `</tr>`

										
						}
						document.getElementById('resultado_dinamico')
						.innerHTML = listado;
						
						
		
		
		}
	ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	ajax.send(vars);
}


function guardar_desplegar_lista(url,vars)
{
        var ajax=nuevo_ajax(); 
        ajax.open("POST", url,true);
		//$.blockUI(); // abro blockUI para ver la precarga mientras ejecuto el scrips. Y luego lo cierro en el scrips carga_interna con //$.unblockUI

        ajax.onreadystatechange=function(){
                if(ajax.readyState==1)
				{}
				else 
				if(ajax.readyState==4)
				{
					if(ajax.status==200){
						
						var respuesta_mostrar=ajax.responseText.replace(/(^\s*)|(\s*$)/g,"");
						var respuesta=respuesta_mostrar.substring(0,6);
						var respuesta_sinerror=respuesta_mostrar.substring(7,150);
						//alert(respuesta);
						if (respuesta=='Fatal!')
						{								
							//$.unblockUI(); // cierro precraga
							var ok = confirm(respuesta_mostrar+'\nQuiere notificar esto ahora?')
							if (ok)
							{						
								reportar_error('Fatal!',1,url);
							}
							else
							{
								reportar_error('Fatal!',0,url);
							}
						}
						else
						if  (respuesta=='Error:')
						{
							//$.unblockUI(); // cierro precraga
							alert(respuesta_mostrar);
						}
						else
						{
							recargar_pedidos(3);
							//$.unblockUI(); // cierro precraga
						}
						
					}
					else
					if(ajax.status==404)
					{
						//$.unblockUI(); // cierro precraga
						alert('Error: La p\u00e1gina no existe');
					}
					else
					{
						//$.unblockUI(); // cierro precraga
						var ok = confirm('Se encontr\u00f3 un ERROR ' + ajax.status + '!\nQuiere notificar esto ahora?')
						if (ok)
						{						
							reportar_error(ajax.status,1,url);
						}
						else
						{
							reportar_error(ajax.status,0,url);
						}
					}
                }
        }
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send(vars)
}


function guardar_mostrar_avisar(url,vars,url_exito,id,vars_exito)
{
        var ajax=nuevo_ajax(); 
        ajax.open("POST", url,true);
		//$.blockUI(); // abro blockUI para ver la precarga mientras ejecuto el scrips. Y luego lo cierro en el scrips carga_interna con //$.unblockUI

        ajax.onreadystatechange=function(){
                if(ajax.readyState==1)
				{}
				else 
				if(ajax.readyState==4)
				{
					if(ajax.status==200){
						
						var respuesta_mostrar=ajax.responseText.replace(/(^\s*)|(\s*$)/g,"");
						var respuesta=respuesta_mostrar.substring(0,6);
						var respuesta_sinerror=respuesta_mostrar.substring(7,150);
						//alert(respuesta);
						if (respuesta=='Fatal!')
						{								
							//$.unblockUI(); // cierro precraga
							var ok = confirm(respuesta_mostrar+'\nQuiere notificar esto ahora?')
							if (ok)
							{						
								reportar_error('Fatal!',1,url);
							}
							else
							{
								reportar_error('Fatal!',0,url);
							}
						}
						else
						if  (respuesta=='Error:')
						{
							//$.unblockUI(); // cierro precraga
							alert(respuesta_mostrar);
						}
						else
						{
							alert(respuesta_mostrar);
							cargar_post(url_exito,id,vars_exito);
						}
						
					}
					else
					if(ajax.status==404)
					{
						//$.unblockUI(); // cierro precraga
						alert('Error: La p\u00e1gina no existe');
					}
					else
					{
						//$.unblockUI(); // cierro precraga
						var ok = confirm('Se encontr\u00f3 un ERROR ' + ajax.status + '!\nQuiere notificar esto ahora?')
						if (ok)
						{						
							reportar_error(ajax.status,1,url);
						}
						else
						{
							reportar_error(ajax.status,0,url);
						}
					}
                }
        }
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send(vars)
}