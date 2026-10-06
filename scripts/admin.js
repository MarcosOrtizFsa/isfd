
function cargar_inicial(url_content){
	if (url_content!=''){	
		url_content = url_content.replace("%","&");
		cargar_get(url_content,'content_menu');
		
	}
}

function cargar_modulo(url)
{
	if (url!=''){
		////$.blockUI(); // abro precarga
		cargar_post('modulos/root/php/home.php','content_seccion','');
	}
}
function acceso_denegado()
{	
	alert('No tiene acceso!');
}
function sin_permisos()
{	
	alert('No tiene permisos para esto...');
}
function restringido()
{	
	alert('No puede hacer esto aqu\u00ed...');
}
function solicitud_enviada()
{	
	alert('Aguarda, tu pedido ya fu\u00e9 enviado...');
}
function solicitud_enproceso()
{	
	alert('Ten paciencia, estamos elaborando tu pedido.');
}
function solicite_atencion()
{	
	alert('Solicite atenci\u00f3n para esto...');
}
function cancelar_pedido()
{	
	alert('Si quieres cancelar tu pedido, acercate a la caja...');
}
function pase_por_caja()
{	
	alert('Pase por caja y abone el pedido. Gracias por visitarnos');
}
function validar_login(usu,cla,cuir)
{
	if (usu=="" || cla=="" )
	{
		alert("Ingrese su Usuario y Clave");
	}
	else
	{
		var ajax=nuevo_ajax(); 
		//peticionamos los datos, le damos la url enviada desde el link
		ajax.open("POST", "php/valida.php",true);
		var vars="usu=" + encodeURIComponent(usu) + "&cla=" + encodeURIComponent(cla) + "&cuir=" + encodeURIComponent(cuir);
		ajax.onreadystatechange=function(){
				if(ajax.readyState==1){
				}
				else 
				if(ajax.readyState==4){
						if(ajax.status==200){
							var respuesta_mostrar=ajax.responseText.replace(/(^\s*)|(\s*$)/g,"");
							var tip=respuesta_mostrar.substring(0,1);
							var respu=respuesta_mostrar.substring(2,22);
							//alert(ajax.responseText);
							if (respu=='desconocido')
							{
									alert('Usuario o clave desconocidos');
									document.getElementById("cla").value = '';
									document.getElementById("usu").value = '';
							}
							else if (respu=='errorlog')
							{
									alert('Usuario o clave inv\u00e1lido');
									document.getElementById("cla").value = '';
									document.getElementById("usu").value = '';
							}
							else if (respu=='suspendido')
							{
									alert('Su cuenta fu\u00e9 suspendida');
									document.getElementById("cla").value = '';
							}
							else if (respu=='restringido')
							{
									alert('\u00c1rea restringida!');
									document.getElementById("cla").value = '';
							}
							else if (respu=='rechazado')
							{
									alert('Su cuenta fu\u00e9 rechazada');
									document.getElementById("cla").value = '';
							}
							else if (respu=='espera')
							{
									alert('Su cuenta a\u00fan no fue activada...');
									document.getElementById("cla").value = '';
							}
							else if (respu=='vencido')
							{
									alert('El v\u00ednculo ha expirado...');
									document.getElementById("cla").value = '';
							}
							else if (respu=='incompleto')
							{
									alert('El v\u00ednculo ha expirado...');
									document.getElementById("cla").value = '';
							}
							else if (tip=='1' && respu=='ok')
							{
								window.location = "../";
							}
							else
							{
								alert('No se pudo iniciar sesión. Verificá los datos e intentá nuevamente.');
							}

						}else if(ajax.status==404){
							alert('Error: No se encuent\u00f3 la p\u00e1gina');
						}else{
							alert("Error:" + ajax.status); 
						}
				}
		}
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send(vars);
	}
}


function logout()
{
	var answer = confirm ("Cerrar Sesi\u00f3n?");
	if (answer)
	{
		window.location = "php/logout.php";
	}
}

function crack()
{
	window.location = "php/logout.php";
}

function acceptNum_valida(evt,usu,cla,time)
{
	// NOTE: Backspace = 8, Enter = 13, '0' = 48, '9' = 57
 	if (evt.keyCode == 13)
	{
	validar_login(usu,cla,time); 
	}
}


function solo_enter(vars)
{	
	document.getElementById("variable_buscar").value = ''+vars+'';
	$(variable_buscar).focus();
}

function pinchar_enter(evt,url,id,vars)
{   
 	if (evt.keyCode == 13)
	{
	cargar_post(url,id,vars);
	}
}

function validar_mail(cadena) {
	var a = cadena;
	var filter=/^[A-Za-z0-9_.]*@[A-Za-z0-9_]+.[A-Za-z0-9_.]+[A-za-z]$/;
	id = document.getElementById('msg_mail');	
	if (filter.test(a))
	{
		id.innerHTML='';
	}
	else
	{
		id.innerHTML='* E-mail...';
	}
}

function validar_cel(telcel)
{
	var a = telcel;
	var filter=/^[0-9]{5,12}/;
	id = document.getElementById('msg_cel');	
	if (filter.test(a))
	{
		id.innerHTML='OK';
	}
	else
	{
		id.innerHTML='?';
	}
}




function abrir_popup(popup) 
{
	var opciones="toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=yes, width=370, height=500, top=10, left=20";
	window.open(popup,"",opciones);
}

function abrir_popup_g(popup_g) 
{
	var opciones2="toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1000, height=500, top=250, left=200";
	window.open(popup_g,"",opciones2);
}

function desplegar(solappas)
{
	var menu = document.getElementById(solappas);
    if(menu.className == "desplegado")
	{
      menu.className = "ocultar_desplegado";
    }
    else
	{
      menu.className = "desplegado";
    }
}

function CuentaLetras(leng)
{
	var numleg = leng - 1;
	if ( (document.abm.system_11_mensaje.value.length) < leng )
	{
		document.abm.caracteres.value = numleg - document.abm.system_11_mensaje.value.length;
	}
}

function printcontent()
{
var a = window. open('','','scrollbars=yes,width=1200,height=560');

	a.document.open("text/html");
	a.document.write('<html><head><link rel="stylesheet" href="../css/reset.css" media="screen"/></head><body>');
	a.document.write(document.getElementById('content_print').innerHTML);
	a.document.write('</body></html>');
	a.document.close();
	a.print();
}


function doPrint(){
document.all.item("noprint").style.visibility='hidden' 
window.print()
document.all.item("noprint").style.visibility='visible'
}


function reportar_error(vars,notif,url_error)
{
        var ajax=nuevo_ajax(); 
        ajax.open("POST", 'php/reportes.php',true);
		var vars="error=" + vars + "&notif=" + notif + "&url_error=" + url_error;
        ajax.onreadystatechange=function(){
                if(ajax.readyState==1)
				{}
				else 
				if(ajax.readyState==4)
				{
					if(ajax.status==200){
						
						var respuesta=ajax.responseText.replace(/(^\s*)|(\s*$)/g,"");
						if  (respuesta=='1')
						{
							alert('Notificaci\u00f3n enviada...');
						}	
					}
					else
					if(ajax.status==404)
					{
						alert('Error: La p\u00e1gina no existe');
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





function guardar_solo(url,vars)
{
        var ajax=nuevo_ajax(); 
        ajax.open("POST", url,true);		
        ajax.onreadystatechange=function()
		{		
			if(ajax.status==200)
			{	
				var respuesta_mostrar=ajax.responseText.replace(/(^\s*)|(\s*$)/g,"");
				var respuesta=respuesta_mostrar.substring(0,6);
				var respuesta_sinerror=respuesta_mostrar.substring(7,150);

				if (respuesta=='Fatal!')
				{								
					alert('Fatal! '+respuesta_mostrar);
				}
				else
				if  (respuesta=='Error:')
				{
		
					alert('Error: '+respuesta_mostrar);
				}
				else
				{
					
				}						
			}
			else
			if(ajax.status==404)
			{
				alert('Error: La p\u00e1gina no existe');
			}
			else
			{
				alert('Status: '+ajax.status);
			}               
        }
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		ajax.send(vars)
}

function guardar_volver(url,vars,sesion_system_07)
{}


function quitar_menu_volver(url,vars,msj,rela_system_06,sesion_system_03)
{}


function funcion_salvar_permiso(vars)
{
        var ajax=nuevo_ajax(); 
        ajax.open("POST", 'modulos/control/php/_interfaz.php',true);
		var vars="nombre_funcion=salvar_permiso&"+vars;
        ajax.onreadystatechange=function(){
                if(ajax.readyState==1)
				{}
				else 
				if(ajax.readyState==4)
				{
					if(ajax.status==200){
						
						var respuesta=ajax.responseText.replace(/(^\s*)|(\s*$)/g,"");
						if  (respuesta=='1')
						{
							alert('Notificaci\u00f3n enviada...');
						}	
					}
					else
					if(ajax.status==404)
					{
						alert('Error: La p\u00e1gina no existe');
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


function cargar_menu_dinamico(id_system_11a,id_system_11b,id_system_11c)
{
        var id = document.getElementById(solapa_menu);
		var ajax=nuevo_ajax(); 
        ajax.open("POST", 'php/jason_menu_dinamico.php',true);
		var vars="id_system_11a="+id_system_11a+"&id_system_11b="+id_system_11b+"&id_system_11c="+id_system_11c;
        ajax.onreadystatechange=function(){
                if(ajax.readyState==1)
				{}
				else 
				if(ajax.readyState==4)
				{
					if(ajax.status==200)
					{
			
						var cadena = '';
						
							var respuesta = JSON.parse(ajax.responseText);
							const data = respuesta.data;		

							cadena += `<div class="padding-selec" style=" width:50%; float:left; text-align: center;">`	
							cadena += `	<select class="form-select form-select-lg mb-3" aria-label="Select" onChange="cargar_menu_dinamico(`+id_system_11a+`,this.value,0); cargar_post('modulos/home/php/recargar.php','relistar_catalogo','rela_system_11a=`+id_system_11a+`&rela_system_11b='+this.value);" >`	
							cadena += `	${data[0].menu_rubro}`				
							cadena += `	</select>`	
							cadena += `</div>`	
							cadena += `<div class="padding-selec" style=" width:50%; float:left; text-align: center;">`	
							cadena += `<select class="form-select form-select-lg mb-3" aria-label="Select" onChange="cargar_menu_dinamico(`+id_system_11a+`,`+id_system_11b+`,this.value);  cargar_post('modulos/home/php/recargar.php','relistar_catalogo','rela_system_11a=`+id_system_11a+`&rela_system_11b=`+id_system_11b+`&rela_system_11c='+this.value);" >`	
							cadena += `	${data[0].menu_subrubro}`			
							cadena += `	</select>`	
							cadena += `</div>`	
				
						solapa_menu.innerHTML = cadena;

				}
					else
					if(ajax.status==404)
					{
						alert('Error: La p\u00e1gina no existe');
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


function quitar_menu_volver(rela_system_11,rela_system_19,rela_system_20,rela_system_21)
{	
	var ajax=nuevo_ajax(); 
	ajax.open("POST", 'modulos/home/php/_interfaz.php',true);
	var vars="nombre_funcion=agregar_remover_menu&rela_system_19="+rela_system_19+"&rela_system_11="+rela_system_11+"&tipo="+tipo;
	ajax.onreadystatechange=function(){
			if(ajax.readyState==1)
			{}
			else 
			if(ajax.readyState==4)
			{
				if(ajax.status==200)
				{
		
					menu_ofertas_dinamico(''+rela_system_19+'',''+rela_system_20+'',''+rela_system_21+'');

				}
				else
				if(ajax.status==404)
				{
					alert('Error: La p\u00e1gina no existe');
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


function menu_ofertas_dinamico(rela_system_19,rela_system_20,rela_system_21)
{
        var id = document.getElementById(relistar_catalogo);
		var ajax=nuevo_ajax(); 
        ajax.open("POST", 'modulos/home/php/json_ofertas_dinamico.php',true);
		var vars="rela_system_19="+rela_system_19+"&rela_system_20="+rela_system_20+"&rela_system_21="+rela_system_21;
        ajax.onreadystatechange=function(){
                if(ajax.readyState==1)
				{}
				else 
				if(ajax.readyState==4)
				{
					if(ajax.status==200)
					{
			
						var cadena = '';
						
							var respuesta = JSON.parse(ajax.responseText);
							const data = respuesta.data;		

							cadena += `<div class="padding-selec" style=" text-align:left; color:#fff; background: #003366; width:33.33%; float:left; display: table; border:1px solid #fff;">`
							cadena += `		<div class="mb-3">&Aacute;reas de estudio.</div>`
							cadena += `		<select class="form-select form-select-lg mb-3" style="" onChange="menu_ofertas_dinamico(this.value,0,0)" >`	
							cadena += `			${data[0].menu_areas}`				
							cadena += `		</select>`	
							cadena += `</div>`	
							cadena += `<div class="padding-selec" style=" text-align:left; color:#fff;  background: #003366; width:33.33%; float:left; display: table; border:1px solid #fff;">`
							cadena += `		<div class="mb-3">Tipo de carrera</div>`
							cadena += `		<select class="form-select form-select-lg mb-3" style="" onChange="menu_ofertas_dinamico(${data[0].rela_system_19},this.value,0)" >`	
							cadena += `			${data[0].menu_tipo}`				
							cadena += `		</select>`
							cadena += `</div>`	
							cadena += `<div class="padding-selec" style=" text-align:left; color:#fff;  background: #003366; width:33.33%; float:left; display: table; border:1px solid #fff;">`
							cadena += `		<div class="mb-3">Modalidad</div>`
							cadena += `		<select class="form-select form-select-lg mb-3" style="" onChange="menu_ofertas_dinamico(${data[0].rela_system_19},${data[0].rela_system_20},this.value)" >`	
							cadena += `			${data[0].menu_modo}`				
							cadena += `		</select>`
							cadena += `</div>`	
							
								
								
							cadena += `</div>`	
							cadena += `<div style="width:100%; float:none; padding:0px;" >`	
							cadena += `		<div class="estilo_oferta"  style=" width:90%; float:left; display: table; text-align:left; padding:10px 20px 10px 0px;">${data[0].system_19_area} &nbsp;&nbsp;&nbsp;&nbsp; ${data[0].system_20_tipo_carrera} &nbsp;&nbsp;&nbsp;&nbsp; ${data[0].system_21_modalidad}</div>`			
							if ( `${data[0].administrador}` == 1)
							{
								cadena += `		<div  style=" width:8%; float:right; display: table; text-align:right;"  type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight"  onClick="menu_administrador(${data[0].rela_system_19},${data[0].rela_system_20},${data[0].rela_system_21})"><h2>+</h2></div>`			
							}
							cadena += `</div>`
							cadena += `<div class="" style=" width:100%;  float:none; display: table;">`	
							cadena += `		${data[0].ofertas}`			
							cadena += `</div>`
						
				
						relistar_catalogo.innerHTML = cadena;

				}
					else
					if(ajax.status==404)
					{
						alert('Error: La p\u00e1gina no existe');
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

function menu_administrador(rela_system_19,rela_system_20,rela_system_21)
{
        var id = document.getElementById(popup_canvas);
		var ajax=nuevo_ajax(); 
        ajax.open("POST", 'modulos/home/php/json_menu_administrador.php',true);
		var vars="rela_system_19="+rela_system_19+"&rela_system_20="+rela_system_20+"&rela_system_21="+rela_system_21;
        ajax.onreadystatechange=function(){
                if(ajax.readyState==1)
				{}
				else 
				if(ajax.readyState==4)
				{
					if(ajax.status==200)
					{
			
						var cadena = '';
						
							var respuesta = JSON.parse(ajax.responseText);
							const data = respuesta.data;		


							cadena += `<div class="offcanvas-header" style="background: red;">`
							cadena += `	<h5 class="offcanvas-title" id="offcanvasLeftLabel" style="color:#fff; line-height:90%;">${data[0].tema_admin}</h5>`	
							cadena += `	<button type="button" class="btn-close " data-bs-dismiss="offcanvas" aria-label="Close"></button> `
							cadena += `  </div>`
							cadena += `  <div >`								
							cadena += `		<div class="conten_panel" style="scrollbar-width: auto;">`
							cadena += `			${data[0].administrador}`	
							cadena += `		</div>	`							
							cadena += `  </div>`

				
						popup_canvas.innerHTML = cadena;

				}
					else
					if(ajax.status==404)
					{
						alert('Error: La p\u00e1gina no existe');
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
function cargar_al_menu(rela_system_11,rela_system_19,rela_system_20,rela_system_21)
{
	
		var ajax=nuevo_ajax(); 
        ajax.open("POST", 'modulos/home/php/_interfaz.php',true);
		var vars="nombre_funcion=agregar_remover_menu&rela_system_19="+rela_system_19+"&rela_system_20="+rela_system_20+"&rela_system_21="+rela_system_21+"&rela_system_11="+rela_system_11;
        ajax.onreadystatechange=function(){
                if(ajax.readyState==1)
				{}
				else 
				if(ajax.readyState==4)
				{
					if(ajax.status==200)
					{
			
						menu_ofertas_dinamico(''+rela_system_19+'',''+rela_system_20+'',''+rela_system_21+'');

					}
					else
					if(ajax.status==404)
					{
						alert('Error: La p\u00e1gina no existe');
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

function cargar_al_menu_pup(rela_system_11,rela_system_19,rela_system_20,rela_system_21)
{
	
		var ajax=nuevo_ajax(); 
        ajax.open("POST", 'modulos/home/php/_interfaz.php',true);
		var vars="nombre_funcion=agregar_remover_menu&rela_system_19="+rela_system_19+"&rela_system_20="+rela_system_20+"&rela_system_21="+rela_system_21+"&rela_system_11="+rela_system_11;
        ajax.onreadystatechange=function(){
                if(ajax.readyState==1)
				{}
				else 
				if(ajax.readyState==4)
				{
					if(ajax.status==200)
					{
			
						var respuesta_mostrar=ajax.responseText.replace(/(^\s*)|(\s*$)/g,"");
						var respuesta=respuesta_mostrar.substring(0,6);
						var respuesta_sinerror=respuesta_mostrar.substring(7,150);
						//alert(respuesta);
						if (respuesta=='Fatal!')
						{								
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
							alert(respuesta_mostrar);
						}
						else
						{
							alert('Agregado! F5 para ver...');
						}

					}
					else
					if(ajax.status==404)
					{
						alert('Error: La p\u00e1gina no existe');
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

function admin_menu_areas()
{
        var id = document.getElementById(popup_canvas);
		var ajax=nuevo_ajax(); 
        ajax.open("POST", 'modulos/home/php/json_admin_menu_areas.php',true);
		var vars="";
        ajax.onreadystatechange=function(){
                if(ajax.readyState==1)
				{}
				else 
				if(ajax.readyState==4)
				{
					if(ajax.status==200)
					{
			
						var cadena = '';
						
							var respuesta = JSON.parse(ajax.responseText);
							const data = respuesta.data;		


								cadena += `<div class="offcanvas-header" style="background: red;">`
								cadena += `	<h5 class="offcanvas-title" id="offcanvasLeftLabel" style="color:#fff; line-height:90%;">${data[0].tema_admin}</h5>`	
								cadena += `	<button type="button" class="btn-close " data-bs-dismiss="offcanvas" aria-label="Close"></button> `
								cadena += `  </div>`
								cadena += `  <div class="offcanvas-body">`								
								cadena += `		<div class="conten_panel" style="scrollbar-width: auto;">`
								cadena += `			${data[0].administrador}`	
								cadena += `		</div>	`							
								cadena += `  </div>`

				
						popup_canvas.innerHTML = cadena;

				}
					else
					if(ajax.status==404)
					{
						alert('Error: La p\u00e1gina no existe');
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



function script_agregar_editar_area(id_system_19,system_19_area)
{
		
		var system_19_area = prompt('Area:', ''+system_19_area+'')
		if (system_19_area)
		{
			var ajax=nuevo_ajax(); 	
			ajax.open("POST", "modulos/home/php/_interfaz.php",true);
			var vars = "nombre_funcion=agregar_editar_area&id_system_19="+id_system_19+"&system_19_area="+system_19_area;	
			ajax.onreadystatechange = function()
			{	
				if(ajax.readyState==1){}
				else 
				if(ajax.readyState==4)
				{
					if(ajax.status==200)
					{				
						
						
						var respuesta_mostrar=ajax.responseText.replace(/(^\s*)|(\s*$)/g,"");
						var respuesta=respuesta_mostrar.substring(0,6);
						var respuesta_sinerror=respuesta_mostrar.substring(7,150);
						//alert(respuesta);
						if (respuesta=='Fatal!')
						{								
							alert('Falat! Se encontro un error de sistema!');
						}
						else
						if  (respuesta=='Error:')
						{
							alert(respuesta_sinerror);
						}
						else
						{
							admin_menu_areas();
						}
					}
					else
					if(ajax.status==404)
					{
						alert('Error: La p\u00e1gina no existe');
					}
					else
					{
						alert('Error: ' + ajax.status);
					}
				}					
		
			}
			ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			ajax.send(vars);	
		}
		
	
}

function script_eliminar_area(id_system_19)
{
	var confirmar = confirm ('Eliminar esto?');
	if (confirmar)
	{
			var ajax=nuevo_ajax(); 	
			ajax.open("POST", "modulos/home/php/_interfaz.php",true);
			var vars = "nombre_funcion=eliminar_area&id_system_19="+id_system_19;	
			ajax.onreadystatechange = function()
			{	
				if(ajax.readyState==1){}
				else 
				if(ajax.readyState==4)
				{
					if(ajax.status==200)
					{				
						
						
						var respuesta_mostrar=ajax.responseText.replace(/(^\s*)|(\s*$)/g,"");
						var respuesta=respuesta_mostrar.substring(0,6);
						var respuesta_sinerror=respuesta_mostrar.substring(7,150);
						//alert(respuesta);
						if (respuesta=='Fatal!')
						{								
							alert('Falat! Se encontro un error de sistema!');
						}
						else
						if  (respuesta=='Error:')
						{
							alert(respuesta_sinerror);
						}
						else
						{
							admin_menu_areas();
						}
					}
					else
					if(ajax.status==404)
					{
						alert('Error: La p\u00e1gina no existe');
					}
					else
					{
						alert('Error: ' + ajax.status);
					}
				}					
		
			}
			ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			ajax.send(vars);	
		}	

}



function script_activar_area(id_system_19,system_19_estado)
{
	var ajax=nuevo_ajax(); 	
	ajax.open("POST", "modulos/home/php/_interfaz.php",true);
	var vars = "nombre_funcion=on_off_area&id_system_19="+id_system_19+"&system_19_estado="+system_19_estado;	
	ajax.onreadystatechange = function()
	{	
		if(ajax.readyState==1){}
		else 
		if(ajax.readyState==4)
		{
			if(ajax.status==200)
			{				
				
				
				var respuesta_mostrar=ajax.responseText.replace(/(^\s*)|(\s*$)/g,"");
				var respuesta=respuesta_mostrar.substring(0,6);
				var respuesta_sinerror=respuesta_mostrar.substring(7,150);
				//alert(respuesta);
				if (respuesta=='Fatal!')
				{								
					alert('Falat! Se encontro un error de sistema!');
				}
				else
				if  (respuesta=='Error:')
				{
					alert(respuesta_sinerror);
				}
				else
				{
					admin_menu_areas();
				}
			}
			else
			if(ajax.status==404)
			{
				alert('Error: La p\u00e1gina no existe');
			}
			else
			{
				alert('Error: ' + ajax.status);
			}
		}					

	}
	ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	ajax.send(vars);	
}



function script_editar_datas(modo,info,system_08_contador)
{
		
		var system_08_contador = prompt(info+':', ''+system_08_contador+'')
		if (system_08_contador)
		{
			var ajax=nuevo_ajax(); 	
			ajax.open("POST", "modulos/home/php/_interfaz.php",true);
			var vars = "nombre_funcion=editar_data&modo="+modo+"&system_08_contador="+system_08_contador;	
			ajax.onreadystatechange = function()
			{	
				if(ajax.readyState==1){}
				else 
				if(ajax.readyState==4)
				{
					if(ajax.status==200)
					{				
						
						
						var respuesta_mostrar=ajax.responseText.replace(/(^\s*)|(\s*$)/g,"");
						var respuesta=respuesta_mostrar.substring(0,6);
						var respuesta_sinerror=respuesta_mostrar.substring(7,150);
						//alert(respuesta);
						if (respuesta=='Fatal!')
						{								
							alert('Falat! Se encontro un error de sistema!');
						}
						else
						if  (respuesta=='Error:')
						{
							alert(respuesta_sinerror);
						}
						else
						{
							alert('Listo! Refrescar para ver...');
						}
					}
					else
					if(ajax.status==404)
					{
						alert('Error: La p\u00e1gina no existe');
					}
					else
					{
						alert('Error: ' + ajax.status);
					}
				}					
		
			}
			ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			ajax.send(vars);	
		}
		
	
}


function agregar_al_carrito(id_system_06,id_sesion_13,id_system_11,system_12_unidades)
{}


function quitar_mostrar(url,vars,msj,sesion_system_07)
{}


function agregar_menu_mostrar(url,vars,sesion_system_07)
{}


function salvar_nuevo_precio(id_system_11,id_system_12,id_system_13,por_unidades)
{}


function agregar_sub_rubro(id_system_11,rela_system_11c)
{}


function opciones_menui(id_system_12,axion)
{}
