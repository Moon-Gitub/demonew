// vistas/js/integraciones.js

$(".tablas").on("click", ".btnEditarIntegracion", function(){

	var idIntegracion = $(this).attr("idIntegracion");
	
	console.log("ID Integración a editar:", idIntegracion);
	
	if(!idIntegracion){
		console.error("No se encontró el ID de la integración");
		alert("Error: No se pudo obtener el ID de la integración");
		return;
	}
	
	var datos = new FormData();
	datos.append("idIntegracion", idIntegracion);

	console.log("Enviando petición AJAX...");

	$.ajax({
		url:"ajax/integraciones.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta){
			
			console.log("Respuesta AJAX completa:", respuesta);
			
			if(respuesta.error){
				console.error("Error en respuesta:", respuesta.error);
				alert("Error: " + respuesta.error);
				return;
			}
			
			// Llenar campos del formulario
			if(respuesta["nombre"]) $("#editarNombre").val(respuesta["nombre"]);
			if(respuesta["tipo"]) $("#editarTipo").val(respuesta["tipo"]);
			if(respuesta["webhook_url"]) $("#editarWebhookUrl").val(respuesta["webhook_url"]);
			if(respuesta["api_key"]) $("#editarApiKey").val(respuesta["api_key"]);
			if(respuesta["descripcion"]) $("#editarDescripcion").val(respuesta["descripcion"]);
			$("#editarActivo").prop("checked", respuesta["activo"] == 1 || respuesta["activo"] == "1");
			if(respuesta["id"]) $("#idIntegracion").val(respuesta["id"]);
			
			console.log("Campos llenados correctamente");

		},
		error: function(xhr, status, error){
			console.error("Error AJAX completo:");
			console.error("Status:", status);
			console.error("Error:", error);
			console.error("Respuesta completa:", xhr.responseText);
			console.error("Status code:", xhr.status);
			
			alert("Error al cargar los datos. Revisa la consola para más detalles.");
		}

	})

})

//ELIMINAR INTEGRACIÓN
$(document).on("click", ".btnEliminarIntegracion", function(){

	var idIntegracion = $(this).attr("idIntegracion");

	swal({
		title: '¿Está seguro de borrar la integración?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar integración!'
	}).then(function(result){
		if (result.value) {
			window.location = "index.php?ruta=integraciones&idIntegracion="+idIntegracion;
		}
	})

})

