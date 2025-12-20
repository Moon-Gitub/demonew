// Variables globales para productos componentes
var productosComponentes = [];
var productosComponentesEditar = [];

// Inicializar DataTable para combos
// Nota: plantilla.js ya inicializa tablas con clase .tablas, así que verificamos si ya está inicializado
$(document).ready(function(){
	// Verificar si la tabla ya tiene DataTables inicializado
	if($.fn.DataTable.isDataTable('#tablaCombos')){
		// Si ya está inicializado, obtener la instancia existente
		var table = $('#tablaCombos').DataTable();
		// Opcional: destruir y reinicializar si es necesario
		// table.destroy();
	} else {
		// Solo inicializar si no está ya inicializado
		$('#tablaCombos').DataTable({
			"retrieve": true, // Permite recuperar instancia existente
			"language": {
				"sProcessing":     "Procesando...",
				"sLengthMenu":     "Mostrar _MENU_ registros",
				"sZeroRecords":    "No se encontraron resultados",
				"sEmptyTable":     "Ningún dato disponible en esta tabla",
				"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
				"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0",
				"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
				"sInfoPostFix":    "",
				"sSearch":         "Buscar:",
				"sUrl":            "",
				"sInfoThousands":  ",",
				"sLoadingRecords": "Cargando...",
				"oPaginate": {
					"sFirst":    "Primero",
					"sLast":     "Último",
					"sNext":     "Siguiente",
					"sPrevious": "Anterior"
				},
				"oAria": {
					"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
					"sSortDescending": ": Activar para ordenar la columna de manera descendente"
				}
			}
		});
	}

	// Configurar autocomplete para producto base (agregar)
	$("#autocompletarProductoCombo").autocomplete({
		source: function(request, response) {
			$.ajax({
				url: "ajax/productos.ajax.php",
				dataType: "json",
				data: {
					listadoProd: request.term
				},
				success: function(data) {
					response(data);
				}
			});
		},
		minLength: 2,
		focus: function(event, ui) {
			event.preventDefault();
		},
		select: function(event, ui) {
			event.preventDefault();
			$("#autocompletarProductoCombo").val(ui.item.value.codigo + " - " + ui.item.value.descripcion);
			$("#nuevoProductoCombo").val(ui.item.value.id);
		}
	});

	// Configurar autocomplete para productos componentes (agregar)
	$("#autocompletarProductoComponente").autocomplete({
		source: function(request, response) {
			$.ajax({
				url: "ajax/productos.ajax.php",
				dataType: "json",
				data: {
					listadoProd: request.term
				},
				success: function(data) {
					response(data);
				}
			});
		},
		minLength: 2,
		focus: function(event, ui) {
			event.preventDefault();
		},
		select: function(event, ui) {
			event.preventDefault();
			agregarProductoComponente(ui.item.value);
			$("#autocompletarProductoComponente").val("");
		}
	});

	// Configurar autocomplete para productos componentes (editar)
	$("#autocompletarProductoComponenteEditar").autocomplete({
		source: function(request, response) {
			$.ajax({
				url: "ajax/productos.ajax.php",
				dataType: "json",
				data: {
					listadoProd: request.term
				},
				success: function(data) {
					response(data);
				}
			});
		},
		minLength: 2,
		focus: function(event, ui) {
			event.preventDefault();
		},
		select: function(event, ui) {
			event.preventDefault();
			agregarProductoComponenteEditar(ui.item.value);
			$("#autocompletarProductoComponenteEditar").val("");
		}
	});

	// Mostrar/ocultar grupo de descuento global (agregar)
	$("#nuevoTipoDescuentoCombo").change(function(){
		var tipo = $(this).val();
		if(tipo == "global" || tipo == "mixto"){
			$("#grupoDescuentoGlobal").show();
		}else{
			$("#grupoDescuentoGlobal").hide();
		}
		actualizarTipoDescuentoGlobal();
	});

	$("#nuevoAplicarDescuentoGlobalCombo").change(function(){
		actualizarTipoDescuentoGlobal();
	});

	// Mostrar/ocultar grupo de descuento global (editar)
	$("#editarTipoDescuentoCombo").change(function(){
		var tipo = $(this).val();
		if(tipo == "global" || tipo == "mixto"){
			$("#grupoDescuentoGlobalEditar").show();
		}else{
			$("#grupoDescuentoGlobalEditar").hide();
		}
		actualizarTipoDescuentoGlobalEditar();
	});

	$("#editarAplicarDescuentoGlobalCombo").change(function(){
		actualizarTipoDescuentoGlobalEditar();
	});

	// Limpiar formulario al cerrar modal agregar
	$("#modalAgregarCombo").on("hidden.bs.modal", function(){
		limpiarFormularioAgregar();
	});

	// Limpiar formulario al cerrar modal editar
	$("#modalEditarCombo").on("hidden.bs.modal", function(){
		limpiarFormularioEditar();
	});
});

// Función para actualizar el tipo de descuento global (agregar)
function actualizarTipoDescuentoGlobal(){
	var tipo = $("#nuevoAplicarDescuentoGlobalCombo").val();
	if(tipo == "porcentaje"){
		$("#tipoDescuentoGlobal").text("%");
	}else{
		$("#tipoDescuentoGlobal").text("$");
	}
}

// Función para actualizar el tipo de descuento global (editar)
function actualizarTipoDescuentoGlobalEditar(){
	var tipo = $("#editarAplicarDescuentoGlobalCombo").val();
	if(tipo == "porcentaje"){
		$("#tipoDescuentoGlobalEditar").text("%");
	}else{
		$("#tipoDescuentoGlobalEditar").text("$");
	}
}

// Función para agregar producto componente (agregar)
function agregarProductoComponente(producto){
	// Verificar si ya existe
	var existe = productosComponentes.some(function(p){
		return p.id == producto.id;
	});

	if(existe){
		swal({
			title: "Producto ya agregado",
			text: "Este producto ya está en la lista de componentes",
			type: "warning",
			timer: 2000
		});
		return;
	}

	// Agregar a la lista
	var nuevoProducto = {
		id: producto.id,
		codigo: producto.codigo,
		descripcion: producto.descripcion,
		cantidad: 1,
		precio_unitario: producto.precio_venta || null,
		descuento: 0,
		aplicar_descuento: "porcentaje"
	};

	productosComponentes.push(nuevoProducto);
	actualizarListaProductosComponentes();
	actualizarProductosComboInput();
}

// Función para agregar producto componente (editar)
function agregarProductoComponenteEditar(producto){
	// Verificar si ya existe
	var existe = productosComponentesEditar.some(function(p){
		return p.id == producto.id;
	});

	if(existe){
		swal({
			title: "Producto ya agregado",
			text: "Este producto ya está en la lista de componentes",
			type: "warning",
			timer: 2000
		});
		return;
	}

	// Agregar a la lista
	var nuevoProducto = {
		id: producto.id,
		codigo: producto.codigo,
		descripcion: producto.descripcion,
		cantidad: 1,
		precio_unitario: producto.precio_venta || null,
		descuento: 0,
		aplicar_descuento: "porcentaje"
	};

	productosComponentesEditar.push(nuevoProducto);
	actualizarListaProductosComponentesEditar();
	actualizarProductosComboInputEditar();
}

// Función para actualizar lista de productos componentes (agregar)
function actualizarListaProductosComponentes(){
	var html = "";
	
	if(productosComponentes.length == 0){
		html = '<p class="text-muted">No hay productos agregados. Busque y seleccione productos para agregarlos al combo.</p>';
	}else{
		html = '<table class="table table-bordered table-condensed">';
		html += '<thead><tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Precio Unit.</th><th>Descuento</th><th>Acción</th></tr></thead>';
		html += '<tbody>';
		
		productosComponentes.forEach(function(producto, index){
			html += '<tr>';
			html += '<td>'+producto.codigo+'</td>';
			html += '<td>'+producto.descripcion+'</td>';
			html += '<td><input type="number" step="0.01" class="form-control input-sm cantidad-producto" data-index="'+index+'" value="'+producto.cantidad+'" min="0.01"></td>';
			html += '<td><input type="number" step="0.01" class="form-control input-sm precio-producto" data-index="'+index+'" value="'+(producto.precio_unitario || '')+'" placeholder="Auto"></td>';
			
			// Descuento solo si el tipo de descuento lo permite
			var tipoDescuento = $("#nuevoTipoDescuentoCombo").val();
			if(tipoDescuento == "por_producto" || tipoDescuento == "mixto"){
				html += '<td>';
				html += '<div class="input-group input-group-sm">';
				html += '<input type="number" step="0.01" class="form-control descuento-producto" data-index="'+index+'" value="'+producto.descuento+'" min="0">';
				html += '<span class="input-group-addon">%</span>';
				html += '</div>';
				html += '</td>';
			}else{
				html += '<td>-</td>';
			}
			
			html += '<td><button type="button" class="btn btn-danger btn-xs quitar-producto" data-index="'+index+'"><i class="fa fa-times"></i></button></td>';
			html += '</tr>';
		});
		
		html += '</tbody></table>';
	}
	
	$("#listaProductosComponentes").html(html);

	// Event listeners para cantidad y precio
	$(".cantidad-producto").off("change").on("change", function(){
		var index = $(this).data("index");
		productosComponentes[index].cantidad = parseFloat($(this).val()) || 1;
		actualizarProductosComboInput();
	});

	$(".precio-producto").off("change").on("change", function(){
		var index = $(this).data("index");
		var valor = $(this).val();
		productosComponentes[index].precio_unitario = valor ? parseFloat(valor) : null;
		actualizarProductosComboInput();
	});

	$(".descuento-producto").off("change").on("change", function(){
		var index = $(this).data("index");
		productosComponentes[index].descuento = parseFloat($(this).val()) || 0;
		actualizarProductosComboInput();
	});

	$(".quitar-producto").off("click").on("click", function(){
		var index = $(this).data("index");
		productosComponentes.splice(index, 1);
		actualizarListaProductosComponentes();
		actualizarProductosComboInput();
	});
}

// Función para actualizar lista de productos componentes (editar)
function actualizarListaProductosComponentesEditar(){
	var html = "";
	
	if(productosComponentesEditar.length == 0){
		html = '<p class="text-muted">No hay productos agregados. Busque y seleccione productos para agregarlos al combo.</p>';
	}else{
		html = '<table class="table table-bordered table-condensed">';
		html += '<thead><tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Precio Unit.</th><th>Descuento</th><th>Acción</th></tr></thead>';
		html += '<tbody>';
		
		productosComponentesEditar.forEach(function(producto, index){
			html += '<tr>';
			html += '<td>'+producto.codigo+'</td>';
			html += '<td>'+producto.descripcion+'</td>';
			html += '<td><input type="number" step="0.01" class="form-control input-sm cantidad-producto-editar" data-index="'+index+'" value="'+producto.cantidad+'" min="0.01"></td>';
			html += '<td><input type="number" step="0.01" class="form-control input-sm precio-producto-editar" data-index="'+index+'" value="'+(producto.precio_unitario || '')+'" placeholder="Auto"></td>';
			
			// Descuento solo si el tipo de descuento lo permite
			var tipoDescuento = $("#editarTipoDescuentoCombo").val();
			if(tipoDescuento == "por_producto" || tipoDescuento == "mixto"){
				html += '<td>';
				html += '<div class="input-group input-group-sm">';
				html += '<input type="number" step="0.01" class="form-control descuento-producto-editar" data-index="'+index+'" value="'+producto.descuento+'" min="0">';
				html += '<span class="input-group-addon">%</span>';
				html += '</div>';
				html += '</td>';
			}else{
				html += '<td>-</td>';
			}
			
			html += '<td><button type="button" class="btn btn-danger btn-xs quitar-producto-editar" data-index="'+index+'"><i class="fa fa-times"></i></button></td>';
			html += '</tr>';
		});
		
		html += '</tbody></table>';
	}
	
	$("#listaProductosComponentesEditar").html(html);

	// Event listeners para cantidad y precio
	$(".cantidad-producto-editar").off("change").on("change", function(){
		var index = $(this).data("index");
		productosComponentesEditar[index].cantidad = parseFloat($(this).val()) || 1;
		actualizarProductosComboInputEditar();
	});

	$(".precio-producto-editar").off("change").on("change", function(){
		var index = $(this).data("index");
		var valor = $(this).val();
		productosComponentesEditar[index].precio_unitario = valor ? parseFloat(valor) : null;
		actualizarProductosComboInputEditar();
	});

	$(".descuento-producto-editar").off("change").on("change", function(){
		var index = $(this).data("index");
		productosComponentesEditar[index].descuento = parseFloat($(this).val()) || 0;
		actualizarProductosComboInputEditar();
	});

	$(".quitar-producto-editar").off("click").on("click", function(){
		var index = $(this).data("index");
		productosComponentesEditar.splice(index, 1);
		actualizarListaProductosComponentesEditar();
		actualizarProductosComboInputEditar();
	});
}

// Función para actualizar input hidden con productos (agregar)
function actualizarProductosComboInput(){
	$("#productosCombo").val(JSON.stringify(productosComponentes));
}

// Función para actualizar input hidden con productos (editar)
function actualizarProductosComboInputEditar(){
	$("#productosComboEditar").val(JSON.stringify(productosComponentesEditar));
}

// Función para limpiar formulario agregar
function limpiarFormularioAgregar(){
	productosComponentes = [];
	$("#formAgregarCombo")[0].reset();
	$("#nuevoProductoCombo").val("");
	$("#productosCombo").val("[]");
	$("#listaProductosComponentes").html('<p class="text-muted">No hay productos agregados. Busque y seleccione productos para agregarlos al combo.</p>');
	$("#grupoDescuentoGlobal").hide();
}

// Función para limpiar formulario editar
function limpiarFormularioEditar(){
	productosComponentesEditar = [];
	$("#formEditarCombo")[0].reset();
	$("#productosComboEditar").val("[]");
	$("#listaProductosComponentesEditar").html('<p class="text-muted">Cargando productos...</p>');
	$("#grupoDescuentoGlobalEditar").hide();
	$("#imagenActualCombo").html("");
}

// EDITAR COMBO
$(".tablas").on("click", ".btnEditarCombo", function(){
	var idCombo = $(this).attr("idCombo");
	var datos = new FormData();
	datos.append("idCombo", idCombo);
	
	$.ajax({
		url: "ajax/combos.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType:"json",
		success: function(respuesta){
			if(respuesta){
				$("#idCombo").val(respuesta["id"]);
				$("#editarCodigoCombo").val(respuesta["codigo"]);
				$("#editarNombreCombo").val(respuesta["nombre"]);
				$("#editarDescripcionCombo").val(respuesta["descripcion"] || "");
				$("#editarPrecioVentaCombo").val(respuesta["precio_venta"]);
				$("#editarPrecioMayoristaCombo").val(respuesta["precio_venta_mayorista"] || "");
				$("#editarIvaCombo").val(respuesta["tipo_iva"]);
				$("#editarTipoDescuentoCombo").val(respuesta["tipo_descuento"]);
				$("#editarDescuentoGlobalCombo").val(respuesta["descuento_global"]);
				$("#editarAplicarDescuentoGlobalCombo").val(respuesta["aplicar_descuento_global"]);
				$("#editarActivoCombo").prop("checked", respuesta["activo"] == 1);
				$("#editarProductoCombo").val(respuesta["id_producto"]);
				$("#editarProductoComboDisplay").val(respuesta["producto_codigo"] + " - " + respuesta["producto_descripcion"]);

				// Mostrar imagen actual si existe
				if(respuesta["imagen"] && respuesta["imagen"] != ""){
					$("#imagenActualCombo").html('<img src="'+respuesta["imagen"]+'" class="img-responsive" style="max-height:150px;">');
				}

				// Cargar productos componentes
				productosComponentesEditar = [];
				if(respuesta["productos"] && respuesta["productos"].length > 0){
					respuesta["productos"].forEach(function(prod){
						productosComponentesEditar.push({
							id: prod.id_producto,
							codigo: prod.codigo,
							descripcion: prod.descripcion,
							cantidad: parseFloat(prod.cantidad) || 1,
							precio_unitario: prod.precio_unitario ? parseFloat(prod.precio_unitario) : null,
							descuento: parseFloat(prod.descuento) || 0,
							aplicar_descuento: prod.aplicar_descuento || "porcentaje"
						});
					});
				}

				actualizarListaProductosComponentesEditar();
				actualizarProductosComboInputEditar();

				// Mostrar/ocultar grupo descuento global
				var tipo = respuesta["tipo_descuento"];
				if(tipo == "global" || tipo == "mixto"){
					$("#grupoDescuentoGlobalEditar").show();
				}else{
					$("#grupoDescuentoGlobalEditar").hide();
				}
				actualizarTipoDescuentoGlobalEditar();
			}
		}
	});
});

// VER PRODUCTOS COMBO
$(".tablas").on("click", ".btnVerProductosCombo", function(){
	var idCombo = $(this).attr("idCombo");
	
	$.ajax({
		url: "ajax/combos.ajax.php",
		method: "GET",
		data: {idCombo: idCombo},
		dataType:"json",
		success: function(respuesta){
			var html = "";
			if(respuesta && respuesta.length > 0){
				respuesta.forEach(function(prod, index){
					html += '<tr>';
					html += '<td>'+(index+1)+'</td>';
					html += '<td>'+prod.codigo+'</td>';
					html += '<td>'+prod.descripcion+'</td>';
					html += '<td>'+prod.cantidad+'</td>';
					html += '<td>$'+parseFloat(prod.precio_unitario || prod.precio_venta || 0).toFixed(2)+'</td>';
					html += '<td>'+(prod.descuento > 0 ? prod.descuento+'%' : '-')+'</td>';
					html += '</tr>';
				});
			}else{
				html = '<tr><td colspan="6" class="text-center">No hay productos componentes</td></tr>';
			}
			$("#tablaProductosCombo").html(html);
		},
		error: function(xhr, status, error){
			console.error("Error al cargar productos del combo:", error);
			console.error("Respuesta:", xhr.responseText);
			swal({
				type: "error",
				title: "Error",
				text: "No se pudieron cargar los productos del combo"
			});
		}
	});
});

// ELIMINAR COMBO
$(".tablas").on("click", ".btnEliminarCombo", function(){
	var idCombo = $(this).attr("idCombo");
	swal({
		title: '¿Está seguro de borrar el combo?',
		text: "¡Si no lo está puede cancelar la acción!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: 'Cancelar',
		confirmButtonText: 'Si, borrar combo!'
	}).then(function(result){
		if(result.value){
			window.location = "index.php?ruta=combos&idCombo="+idCombo;
		}
	});
});
