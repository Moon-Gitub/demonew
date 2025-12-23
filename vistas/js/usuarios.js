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
// Verificar que el documento esté listo
$(document).ready(function(){
	console.log("usuarios.js cargado correctamente");
	console.log("Registrando evento para .btnEditarUsuario");
});

$(".tablas").on("click", ".btnEditarUsuario", function(e){
	
	// Prevenir comportamiento por defecto
	e.preventDefault();
	
	var idUsuario = $(this).attr("idUsuario");
	
	console.log("=== EDITAR USUARIO ===");
	console.log("ID Usuario:", idUsuario);
	
	// Validar que el ID existe
	if(!idUsuario || idUsuario === "" || idUsuario === undefined){
		console.error("Error: No se encontró el ID del usuario");
		swal({
			type: "error",
			title: "Error",
			text: "No se pudo obtener el ID del usuario",
			showConfirmButton: true,
			confirmButtonText: "Cerrar"
		});
		return false;
	}
	
	var datos = new FormData();
	datos.append("idUsuario", idUsuario);
	
	console.log("Enviando petición AJAX...");

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
		beforeSend: function(){
			console.log("Petición enviada, esperando respuesta...");
		},
		success: function(respuesta){
			
			console.log("Respuesta recibida:", respuesta);
		    
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
		    
			console.log("Llenando formulario con datos...");
			
			$("#editarNombre").val(respuesta["nombre"] || "");
			$("#editarUsuario").val(respuesta["usuario"] || "");
			$("#editarPerfil").html(respuesta["perfil"] || "");
			$("#editarPerfil").val(respuesta["perfil"] || "");
		    $("#editarRazonSocial").val(respuesta["empresa"]);
		
			$("#editarSucursal").html(respuesta["sucursal"] || "");
			$("#editarSucursal").val(respuesta["sucursal"] || "");
			
			console.log("Datos cargados:", {
				nombre: respuesta["nombre"],
				usuario: respuesta["usuario"],
				perfil: respuesta["perfil"],
				sucursal: respuesta["sucursal"]
			});
			
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
			
			console.log("Formulario completado exitosamente");
			
			// Abrir el modal después de cargar los datos
			setTimeout(function(){
				$("#modalEditarUsuario").modal("show");
			}, 100);
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