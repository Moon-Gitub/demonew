//EDITAR MEDIO DE PAGO
$(".tablas").on("click", ".btnEditarMedioPago", function(){
	var idMedioPago = $(this).attr("idMedioPago");
	var datos = new FormData();
	datos.append("idMedioPago", idMedioPago);
	$.ajax({
		url: "ajax/medios_pago.ajax.php",
		method: "POST",
      	data: datos,
      	cache: false,
     	contentType: false,
     	processData: false,
     	dataType:"json",
     	success: function(respuesta){
     		$("#editarCodigo").val(respuesta["codigo"]);
     		$("#editarNombre").val(respuesta["nombre"]);
     		$("#editarDescripcion").val(respuesta["descripcion"] || "");
     		$("#editarOrden").val(respuesta["orden"]);
     		$("#idMedioPago").val(respuesta["id"]);
     		
     		// Checkboxes
     		$("#editarRequiereCodigo").prop("checked", respuesta["requiere_codigo"] == 1);
     		$("#editarRequiereBanco").prop("checked", respuesta["requiere_banco"] == 1);
     		$("#editarRequiereNumero").prop("checked", respuesta["requiere_numero"] == 1);
     		$("#editarRequiereFecha").prop("checked", respuesta["requiere_fecha"] == 1);
     		$("#editarActivo").prop("checked", respuesta["activo"] == 1);
     	}
	})
})

//ELIMINAR MEDIO DE PAGO
$(".tablas").on("click", ".btnEliminarMedioPago", function(){
	 var idMedioPago = $(this).attr("idMedioPago");
	 swal({
	 	title: '¿Está seguro de borrar el medio de pago?',
	 	text: "¡Si no lo está puede cancelar la acción!",
	 	type: 'warning',
	 	showCancelButton: true,
	 	confirmButtonColor: '#3085d6',
	 	cancelButtonColor: '#d33',
	 	cancelButtonText: 'Cancelar',
	 	confirmButtonText: 'Si, borrar medio de pago!'
	 }).then(function(result){
	 	if(result.value){
	 		window.location = "index.php?ruta=medios-pago&idMedioPago="+idMedioPago;
	 	}
	 })
});
