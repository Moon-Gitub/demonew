// vistas/js/integraciones.js

$(".tablas").on("click", ".btnEditarIntegracion", function(){

	var idIntegracion = $(this).attr("idIntegracion");
	
	var datos = new FormData();
	datos.append("idIntegracion", idIntegracion);

	$.ajax({
		url:"ajax/integraciones.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType: "json",
		success: function(respuesta){
			
			console.log("Respuesta AJAX:", respuesta);
			
			$("#editarNombre").val(respuesta["nombre"]);
			$("#editarTipo").val(respuesta["tipo"]);
			$("#editarWebhookUrl").val(respuesta["webhook_url"]);
			$("#editarApiKey").val(respuesta["api_key"]);
			$("#editarDescripcion").val(respuesta["descripcion"]);
			$("#editarActivo").prop("checked", respuesta["activo"] == 1);
			$("#idIntegracion").val(respuesta["id"]);

		},
		error: function(xhr, status, error){
			console.error("Error AJAX:", error);
			console.error("Respuesta:", xhr.responseText);
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

