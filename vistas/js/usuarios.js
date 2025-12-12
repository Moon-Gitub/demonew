/*=============================================
SUBIENDO LA FOTO DEL USUARIO
=============================================*/
$(".nuevaFoto").change(function(){

	var imagen = this.files[0];
	
	/*=============================================
  	VALIDAMOS EL FORMATO DE LA IMAGEN SEA JPG O PNG
  	=============================================*/

  	if(imagen["type"] != "image/jpeg" && imagen["type"] != "image/png"){

  		$(".nuevaFoto").val("");

  		 swal({
		      title: "Error al subir la imagen",
		      text: "¡La imagen debe estar en formato JPG o PNG!",
		      type: "error",
		      confirmButtonText: "¡Cerrar!"
		    });

  	}else if(imagen["size"] > 2000000){

  		$(".nuevaFoto").val("");

  		 swal({
		      title: "Error al subir la imagen",
		      text: "¡La imagen no debe pesar más de 2MB!",
		      type: "error",
		      confirmButtonText: "¡Cerrar!"
		    });

  	}else{

  		var datosImagen = new FileReader;
  		datosImagen.readAsDataURL(imagen);

  		$(datosImagen).on("load", function(event){

  			var rutaImagen = event.target.result;

  			$(".previsualizar").attr("src", rutaImagen);

  		})

  	}
})

/*=============================================
EDITAR USUARIO
=============================================*/
$(".tablas").on("click", ".btnEditarUsuario", function(){

	var idUsuario = $(this).attr("idUsuario");
	
	var datos = new FormData();
	datos.append("idUsuario", idUsuario);

	$.ajax({

		url:"ajax/usuarios.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		headers: {
			'X-Requested-With': 'XMLHttpRequest'
		},
		success: function(respuesta){
		    
		    // Validar que la respuesta no sea null o undefined
		    if(!respuesta || respuesta === null || respuesta.error){
		        console.error("Error: No se recibieron datos del usuario", respuesta);
		        swal({
		            type: "error",
		            title: "Error",
		            text: respuesta && respuesta.error ? respuesta.error : "No se pudieron cargar los datos del usuario",
		            showConfirmButton: true,
		            confirmButtonText: "Cerrar"
		        });
		        return;
		    }
		    
			$("#editarNombre").val(respuesta["nombre"] || "");
			$("#editarUsuario").val(respuesta["usuario"] || "");
			$("#editarPerfil").html(respuesta["perfil"] || "");
			$("#editarPerfil").val(respuesta["perfil"] || "");
		
			$("#editarSucursal").html(respuesta["sucursal"] || "");
			$("#editarSucursal").val(respuesta["sucursal"] || "");
			
			//$("#editarListaPrecio").html(respuesta["listas_precio"]);
			//$("#editarListaPrecio").val(respuesta["listas_precio"]);
	        let listasSistema = $(".preciosVentaUsuario");
	        for(let x=0; x < $(listasSistema).length; x++){
                $(listasSistema[x]).prop('checked', false);
            }
            
            let listasUsuario = respuesta["listas_precio"] || "";
            if(listasUsuario && listasUsuario !== ""){
                listasUsuario = listasUsuario.split(",");
                for(let i=0; i < listasUsuario.length; i++){
                    for(let x=0; x < $(listasSistema).length; x++){
                        if(($(listasSistema[x]).val()) == listasUsuario[i]){
                            $(listasSistema[x]).prop('checked', true);
                        }
                    }
                }
            }
            
			$("#editarPuntoVenta").val(respuesta["puntos_venta"] || "");
			$("#fotoActual").val(respuesta["foto"] || "");
			$("#passwordActual").val(respuesta["password"] || "");
			if(respuesta["foto"] && respuesta["foto"] !== ""){
				$(".previsualizarEditar").attr("src", respuesta["foto"]);
			}else{
				$(".previsualizarEditar").attr("src", "vistas/img/usuarios/default/anonymous.png");
			}
		},
		error: function(xhr, status, error){
		    console.error("Error AJAX:", error);
		    console.error("Status:", status);
		    console.error("Response:", xhr.responseText);
		    
		    // Intentar parsear la respuesta como JSON
		    let mensajeError = "Error al cargar los datos del usuario";
		    try {
		        if(xhr.responseText){
		            const respuestaError = JSON.parse(xhr.responseText);
		            if(respuestaError.error){
		                mensajeError = respuestaError.error;
		            } else if(respuestaError.mensaje){
		                mensajeError = respuestaError.mensaje;
		            }
		        }
		    } catch(e){
		        // Si no es JSON, usar el mensaje por defecto
		        if(xhr.status === 401){
		            mensajeError = "No autorizado. Por favor, inicia sesión nuevamente.";
		        } else if(xhr.status === 403){
		            mensajeError = "Acceso denegado. Verifica tus permisos.";
		        } else if(xhr.status === 404){
		            mensajeError = "No se encontró el recurso solicitado.";
		        } else if(xhr.status === 500){
		            mensajeError = "Error del servidor. Contacta al administrador.";
		        }
		    }
		    
		    swal({
		        type: "error",
		        title: "Error",
		        text: mensajeError,
		        showConfirmButton: true,
		        confirmButtonText: "Cerrar"
		    });
		}
	});
})

/*=============================================
ACTIVAR USUARIO
=============================================*/
$(".tablas").on("click", ".btnActivar", function(){

	var idUsuario = $(this).attr("idUsuario");
	var estadoUsuario = $(this).attr("estadoUsuario");

	var datos = new FormData();
 	datos.append("activarId", idUsuario);
  	datos.append("activarUsuario", estadoUsuario);

  	$.ajax({

	  url:"ajax/usuarios.ajax.php",
	  method: "POST",
	  data: datos,
	  cache: false,
      contentType: false,
      processData: false,
      success: function(respuesta){
        console.log(respuesta)
      }

  	})

  	if(estadoUsuario == 0){

  		$(this).removeClass('btn-success');
  		$(this).addClass('btn-danger');
  		$(this).html('Desactivado');
  		$(this).attr('estadoUsuario',1);

  	}else{

  		$(this).addClass('btn-success');
  		$(this).removeClass('btn-danger');
  		$(this).html('Activado');
  		$(this).attr('estadoUsuario',0);
  	}
})

/*=============================================
REVISAR SI EL USUARIO YA ESTÁ REGISTRADO
=============================================*/
$("#nuevoUsuario").change(function(){
	$(".alert").remove();
	var usuario = $(this).val();
	var datos = new FormData();
	datos.append("validarUsuario", usuario);
	 $.ajax({
	    url:"ajax/usuarios.ajax.php",
	    method:"POST",
	    data: datos,
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: "json",
	    success:function(respuesta){
	    	if(respuesta){
	    		$("#nuevoUsuario").parent().after('<div class="alert alert-warning">Este usuario ya existe en la base de datos</div>');
	    		$("#nuevoUsuario").val("");
	    	}
	    }
	})
})

/*=============================================
ELIMINAR USUARIO
=============================================*/
$(".tablas").on("click", ".btnEliminarUsuario", function(){

  var idUsuario = $(this).attr("idUsuario");
  var fotoUsuario = $(this).attr("fotoUsuario");
  var usuario = $(this).attr("usuario");

  swal({
    title: '¿Está seguro de borrar el usuario?',
    text: "¡Si no lo está puede cancelar la accíón!",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Si, borrar usuario!'
  }).then(function(result){
    if(result.value){
      window.location = "index.php?ruta=usuarios&idUsuario="+idUsuario+"&usuario="+usuario+"&fotoUsuario="+fotoUsuario;
    }
  })
})