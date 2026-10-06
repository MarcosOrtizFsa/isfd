
function down_multiple_archivos(url_exito,id_exito,vars_dow){	
	var browserName=navigator.appName;
	id = document.getElementById('estado_de_carga');
	id.innerHTML='Cargando...';
	session_archivo_name=1;
	
		var archivos = document.getElementById("archivos");//Creamos un objeto con el elemento que contiene los archivos: el campo input file, que tiene el id = 'archivos'
		var archivo = archivos.files; //Obtenemos los archivos seleccionados en el imput
		var archivos = new FormData();
		for(i=0; i<archivo.length; i++){
		archivos.append('archivo'+i,archivo[i]); //Añadimos cada archivo a el arreglo con un indice direfente
		}

		$.ajax({
			url:'php/up_doc.php?'+ vars_dow, //Url a donde la enviaremos
			type:'POST', //Metodo que usaremos
			contentType:false, //Debe estar en false para que pase el objeto sin procesar
			data:archivos, //Le pasamos el objeto que creamos con los archivos
			processData:false, //Debe estar en false para que JQuery no procese los datos a enviar
			cache:false //Para que el formulario no guarde cache
			}).done(function(msg){//Escuchamos la respuesta y capturamos el mensaje msg
			
			var exito=msg.substring(0,6);
			var msghtml=msg.substring(7,150);
			parent.respuesta_down_multiple_archivos(exito,msghtml,url_exito,id_exito,vars_dow)
		});
	}

function respuesta_down_multiple_archivos(exito,msghtml,url_exito,id_exito,vars_dow){
	var browserName=navigator.appName;
	id = document.getElementById('estado_de_carga');

	if (exito=='Fatal!')
	{								
	alert('Fatal! '+msghtml);
	}
	else
	if  (exito=='Error:')
	{
	alert('Error: '+msghtml);
	}
	else
	{
		cargar_post(''+url_exito+'',''+id_exito+'',''+vars_dow+'');	
	}	
}


function down_ima_lista_urls(url_exito,id_exito,vars_dow){	
	var browserName=navigator.appName;
	id = document.getElementById('estado_de_carga');
	id.innerHTML='Cargando...';
	session_archivo_name=1;
	
		var archivos = document.getElementById("archivos");//Creamos un objeto con el elemento que contiene los archivos: el campo input file, que tiene el id = 'archivos'
		var archivo = archivos.files; //Obtenemos los archivos seleccionados en el imput
		var archivos = new FormData();
		for(i=0; i<archivo.length; i++){
		archivos.append('archivo'+i,archivo[i]); //Añadimos cada archivo a el arreglo con un indice direfente
		}

		$.ajax({
			url:'../php/up_ima_lista.php?'+ vars_dow, //Url a donde la enviaremos
			type:'POST', //Metodo que usaremos
			contentType:false, //Debe estar en false para que pase el objeto sin procesar
			data:archivos, //Le pasamos el objeto que creamos con los archivos
			processData:false, //Debe estar en false para que JQuery no procese los datos a enviar
			cache:false //Para que el formulario no guarde cache
			}).done(function(msg){//Escuchamos la respuesta y capturamos el mensaje msg
			
			var exito=msg.substring(0,6);
			var msghtml=msg.substring(7,150);
			parent.respuesta_down_ima_lista_urls(exito,msghtml,url_exito,id_exito,vars_dow)
		});
	}

function respuesta_down_ima_lista_urls(exito,msghtml,url_exito,id_exito,vars_dow){
	var browserName=navigator.appName;
	id = document.getElementById('estado_de_carga');

	if (exito=='Fatal!')
	{								
	alert('Fatal! '+msghtml);
	}
	else
	if  (exito=='Error:')
	{
	alert('Error: '+msghtml);
	}
	else
	{
		id.innerHTML='Ok!';
		cargar_post(''+url_exito+'',''+id_exito+'',''+vars_dow+'');	
		
	}

	
}


// --------------------CARGA BANMNER EN 'PORDADA
function down_banner_portada(vars_dow){	
	var browserName=navigator.appName;
	id = document.getElementById('estado_de_carga');
	id.innerHTML='Cargando banner...';
	session_archivo_name=1;
	
		var archivos = document.getElementById("archivos");//Creamos un objeto con el elemento que contiene los archivos: el campo input file, que tiene el id = 'archivos'
		var archivo = archivos.files; //Obtenemos los archivos seleccionados en el imput
		var archivos = new FormData();
		for(i=0; i<archivo.length; i++){
		archivos.append('archivo'+i,archivo[i]); //Añadimos cada archivo a el arreglo con un indice direfente
		}

		$.ajax({
			url:'php/up_doc.php?'+ vars_dow, //Url a donde la enviaremos
			type:'POST', //Metodo que usaremos
			contentType:false, //Debe estar en false para que pase el objeto sin procesar
			data:archivos, //Le pasamos el objeto que creamos con los archivos
			processData:false, //Debe estar en false para que JQuery no procese los datos a enviar
			cache:false //Para que el formulario no guarde cache
			}).done(function(msg){//Escuchamos la respuesta y capturamos el mensaje msg
			
			var exito=msg.substring(0,6);
			var msghtml=msg.substring(7,150);
			parent.respuesta_down_banner_portada(exito,msghtml)
		});
	}

function respuesta_down_banner_portada(exito,msghtml){
	var browserName=navigator.appName;
	id = document.getElementById('estado_de_carga');

	if (exito=='Fatal!')
	{								
	alert('Fatal! '+msghtml);
	}
	else
	if  (exito=='Error:')
	{
	alert('Error: '+msghtml);
	}
	else
	{
		window.location = "../";
	}	
}






// -------------------CAMBIAR IMAGEN DE PORTADA - FORMULARIO 1 Y 2
function down_ima_portada(vars_dow,modo){	
	var browserName=navigator.appName;
	id = document.getElementById('estado_de_carga_ima_'+modo);
	id.innerHTML='Cargando imagen '+modo;
	session_archivo_name=1;
	
		var archivos = document.getElementById("archivos"+modo);//Creamos un objeto con el elemento que contiene los archivos: el campo input file, que tiene el id = 'archivos'
		var archivo = archivos.files; //Obtenemos los archivos seleccionados en el imput
		var archivos = new FormData();
		for(i=0; i<archivo.length; i++){
		archivos.append('archivo'+i,archivo[i]); //Añadimos cada archivo a el arreglo con un indice direfente
		}

		$.ajax({
			url:'php/up_ima.php?'+vars_dow, //Url a donde la enviaremos
			type:'POST', //Metodo que usaremos
			contentType:false, //Debe estar en false para que pase el objeto sin procesar
			data:archivos, //Le pasamos el objeto que creamos con los archivos
			processData:false, //Debe estar en false para que JQuery no procese los datos a enviar
			cache:false //Para que el formulario no guarde cache
			}).done(function(msg){//Escuchamos la respuesta y capturamos el mensaje msg
			
			var exito=msg.substring(0,6);
			var msghtml=msg.substring(7,150);
			parent.respuesta_down_ima_portada(modo,exito,msghtml)
		});
	}

function respuesta_down_ima_portada(modo,exito,msghtml){
	var browserName=navigator.appName;
	id = document.getElementById('estado_de_carga_ima_'+modo);

	if (exito=='Fatal!')
	{								
	alert('Fatal! '+msghtml);
	}
	else
	if  (exito=='Error:')
	{
	alert('Error: '+msghtml);
	}
	else
	{
		//id.innerHTML=''+exito+' '+msghtml;
		window.location = "../";
	}	
}

