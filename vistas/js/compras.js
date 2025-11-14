//////////////////////////////////////////////////CREAR-COMPRA.PHP//////////////////////////////////

//AUTOCOMPLETAR PROVEEDORES 
$( "#autocompletarProveedor" ).autocomplete({
  source: function( request, response ) {
    $.ajax({
      url:"ajax/proveedores.ajax.php",
      dataType: "json",
      data: {
        listadoProveedor: request.term
      },
      success: function( data ) {
    		response( data );
      }, 
      error: function(e){
      	console.log(e.responseText)
      }
    });
  },
  minLength: 3,
  focus: function (event, ui) {
        event.preventDefault();
    },
  select: function( event, ui ) {
  	event.preventDefault();
		var idSeleccionado = ui.item.value.id;
		if(idSeleccionado[0]!=""){
			$("#seleccionarProveedor").val(idSeleccionado);
			$("#autocompletarProveedor").val(ui.item.value.nombre);
		}
	}
});

//CARGAR LA TABLA DINÁMICA DE COMPRAS (usado en crear-compra)
$('#tablaCompras').DataTable({
    "ajax": "ajax/datatable-compras.ajax.php",
    "deferRender": true,
		"retrieve": true,
		"processing": true,
		"language": GL_DATATABLE_LENGUAJE
});

//AGREGANDO PRODUCTOS DESDE LA TABLA CON CLICK - (crear-compra)
$("#tablaCompras tbody").on("click", "button.agregarProductoCompra", function(){
	var idProducto = $(this).attr("idProducto");
	$(this).addClass("btn-default");
	var datos = new FormData();
  datos.append("idProducto", idProducto);
	var total = 0;
  $.ajax({
     	url:"ajax/productos.ajax.php",
    	method: "POST",
    	data: datos,
    	cache: false,
    	contentType: false,
    	processData: false,
    	dataType:"json",
    	success:function(respuesta){
    		console.log(respuesta["tipo_iva"])
				var codigo = respuesta["codigo"];	
	 	    var descripcion = respuesta["descripcion"];
	     	var precio = redondear(respuesta["precio_compra"],2);
				var ganancia = redondear(respuesta["margen_ganancia"],2);
				var precioVenta = redondear(respuesta["precio_venta"],2);	
				var tipoIva = (respuesta["tipo_iva"]) ? respuesta["tipo_iva"] : 0;
	      $(".nuevoProducto").prepend(
	      	'<div class="row" style="padding-left:25px;padding-bottom:5px;">'+
				  	'<!-- Descripción del producto -->'+
			      '<div class="col-xs-4" style="padding-right:0px">'+	          
		          '<div class="input-group">'+
		              '<span class="input-group-btn"><button type="button" class="btn btn-danger btn-sm quitarProductoCompra" idProducto="'+idProducto+'"><i class="fa fa-times"></i></button></span>'+
		              '<input type="text" class="form-control input-sm nuevaDescripcionProducto" idProducto="'+idProducto+'" value="'+descripcion+'" readonly required>'+
					 		'</div>'+
	          '</div>'+
	          '<!-- Cantidad del producto -->'+
	          '<div class="col-xs-2">'+
	             '<input type="text" step="any" class="form-control  input-sm nuevaCantidadProductoCompra" style="text-align:center;" min="1" value="1" required autocomplete="off">'+
	          '</div>' +
	          '<!-- Precio del producto -->'+
	          '<div class="col-xs-2 ingresoPrecio" style="padding-left:0px">'+
	          '<div class="input-group">'+
	            '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
							'<input type="hidden" class="nuevoPrecioProductoOculto" tipoIva="'+tipoIva+'" value="'+precio+'">'+
							'<input type="text" style="text-align:center; border-spacing: 0px; margin: 0px; padding: 0px; space: 0px; font-size:15px;" step="any" class="form-control input-sm nuevoPrecioProductoValidar" tipoIva="'+tipoIva+'" value="'+precio+'" required>'+
		        '</div>'+
		      '</div>'+
				  '<div class="col-xs-2 ingresoGanancia" style="padding-left:0px">'+
	          '<div class="input-group">'+
	            '<span class="input-group-addon"><i class="fa fa-percent"></i></span>'+ 
					  	'<input type="text" style="text-align:center; border-spacing: 0px; margin: 0px; padding: 0px; space: 0px; font-size:15px;" step="any" class="form-control input-sm nuevoPrecioGanancia" value="'+ganancia+'" required>'+
		        '</div>'+
		      '</div>'+
				  '<div class="col-xs-2 ingresoPrecioVenta" style="padding-left:0px">'+
	          '<div class="input-group">'+
	            '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
					   	'<input type="text" style="text-align:center; border-spacing: 0px; margin: 0px; padding: 0px; space: 0px; font-size:15px;" step="any" class="form-control input-sm nuevoPrecioProductoVentaValidar" value="'+precioVenta+'" required>'+
	          '</div>'+
	        '</div>'+
	      '</div>');
	    	// SUMAR TOTAL DE PRECIOS
		    sumarTotalPreciosCompras();
	      // AGRUPAR PRODUCTOS EN FORMATO JSON
	      listarProductosCompras();
    	}
  })
});

//AGREGANDO PRODUCTOS DESDE LA TABLA, BUSCANDO CODIGO Y PRESIONANDO ENTER - (crear-compra)
$("#tablaCompras_filter input").keyup( function (e) {
	if (e.keyCode == 13 ) {
		var idProducto = $(this).val();
		console.log(idProducto);
		var datos = new FormData();
	  datos.append("codigoProducto", idProducto);
		var total = 0;
	  $.ajax({
			url:"ajax/productos.ajax.php",
			method: "POST",
			data: datos,
			cache: false,
			contentType: false,
			processData: false,
			dataType:"json",
			success:function(respuesta){
				var codigo = respuesta["codigo"];	
				var descripcion = respuesta["descripcion"];
				var precio = redondear(respuesta["precio_compra"],2);
				var ganancia = redondear(respuesta["margen_ganancia"],2);
				var precioVenta = redondear(respuesta["precio_venta"],2);	
				var tipoIva = (respuesta["tipo_iva"]) ? respuesta["tipo_iva"] : 0;
	     	$(".nuevoProducto").prepend(
		     	'<div class="row" style="padding-left:25px;padding-bottom:5px;">'+
				  	'<!-- Descripción del producto -->'+
	          '<div class="col-xs-4" style="padding-right:0px">'+
	            '<div class="input-group">'+
	              '<span class="input-group-btn"><button type="button" class="btn btn-danger btn-sm quitarProductoCompra" idProducto="'+idProducto+'"><i class="fa fa-times"></i></button></span>'+
	              '<input type="text" class="form-control input-sm nuevaDescripcionProducto" idProducto="'+idProducto+'" value="'+descripcion+'" readonly required>'+
				 			'</div>'+
	          '</div>'+
	          '<!-- Cantidad del producto -->'+
	          '<div class="col-xs-2">'+
		          '<input type="text" step="any" class="form-control  input-sm nuevaCantidadProductoCompra" style="text-align:center;" min="1" value="1" required autocomplete="off">'+
	          '</div>' +
	          '<!-- Precio del producto -->'+
	          '<div class="col-xs-2 ingresoPrecio" style="padding-left:0px">'+
		          '<div class="input-group">'+
	  	          '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
								'<input type="hidden" class="nuevoPrecioProductoOculto" tipoIva="'+tipoIva+'" value="'+precio+'">'+
						 		'<input type="text" style="text-align:center; border-spacing: 0px; margin: 0px; padding: 0px; space: 0px; font-size:15px;" step="any" class="form-control input-sm nuevoPrecioProductoValidar" tipoIva="'+tipoIva+'" value="'+precio+'" required>'+
		          '</div>'+
		        '</div>'+
				  	'<div class="col-xs-2 ingresoGanancia" style="padding-left:0px">'+
	            '<div class="input-group">'+
		            '<span class="input-group-addon"><i class="fa fa-percent"></i></span>'+ 
							  '<input type="text" style="text-align:center; border-spacing: 0px; margin: 0px; padding: 0px; space: 0px; font-size:15px;" step="any" class="form-control input-sm nuevoPrecioGanancia" value="'+ganancia+'" required>'+
	            '</div>'+
	          '</div>'+
					  '<div class="col-xs-2 ingresoPrecioVenta" style="padding-left:0px">'+
		            '<div class="input-group">'+
			            '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
			 						'<input type="text" style="text-align:center; border-spacing: 0px; margin: 0px; padding: 0px; space: 0px; font-size:15px;" step="any" class="form-control input-sm nuevoPrecioProductoVentaValidar" value="'+precioVenta+'" required>'+
		            '</div>'+
	          '</div>'+
	        '</div>') 

	        // SUMAR TOTAL DE PRECIOS
	        sumarTotalPreciosCompras();
	        // AGRUPAR PRODUCTOS EN FORMATO JSON
	        listarProductosCompras();
					localStorage.removeItem("quitarProductoCompra");
		   	}
	   });
	}
});

//MODIFICAR LA CANTIDAD - (usado en crear-compra)
$(".formularioCompra").on("keyup", "input.nuevaCantidadProductoCompra", function(){
    // SUMAR TOTAL DE PRECIOS
    sumarTotalPreciosCompras();
    // AGRUPAR PRODUCTOS EN FORMATO JSON
    listarProductosCompras();
});

//MODIFICAR PRECIO VENTA - (usado en crear-compra)
$(".formularioCompra").on("keyup", "input.nuevoPrecioProductoVentaValidar", function(){
  // SUMAR TOTAL DE PRECIOS
  sumarTotalPreciosCompras();
  // AGRUPAR PRODUCTOS EN FORMATO JSON
  listarProductosCompras();
});

//MODIFICAR EL PRECIO COMPRA - (usado en crear-compra)
$(".formularioCompra").on("keyup", "input.nuevoPrecioProductoValidar", function(){
	var precioArticulo = Number(this.value);
	var porcentaje = $(this).closest('.row').find('.nuevoPrecioGanancia').val();
	var tipoIva = Number($(this).closest('.row').find('.nuevoPrecioProductoValidar').attr("tipoIva"));
	var precioFinal = precioArticulo + (precioArticulo * porcentaje / 100); //ganancia
	precioFinal = precioFinal + (precioFinal * tipoIva / 100); //agrego iva
	precioFinal = redondear(precioFinal,2);
	$(this).closest('.row').find('.nuevoPrecioProductoVentaValidar').val(precioFinal);
    // SUMAR TOTAL DE PRECIOS
    sumarTotalPreciosCompras();
    // AGRUPAR PRODUCTOS EN FORMATO JSON
    listarProductosCompras();
});

//MODIFICAR GANANCIA - (usado en crear-compras)
$(".formularioCompra").on("keyup", "input.nuevoPrecioGanancia", function(){
	var porcentaje = Number(this.value);
	var precioArticulo = Number($(this).closest('.row').find('.nuevoPrecioProductoValidar').val());
	var tipoIva = Number($(this).closest('.row').find('.nuevoPrecioProductoValidar').attr("tipoIva"));
	var precioFinal = precioArticulo + (precioArticulo * porcentaje / 100); //ganancia
	precioFinal = precioFinal + (precioFinal * tipoIva / 100); //agrego iva
	precioFinal = redondear(precioFinal,2);
	$(this).closest('.row').find('.nuevoPrecioProductoVentaValidar').val(precioFinal);
  // SUMAR TOTAL DE PRECIOS
  sumarTotalPreciosCompras();
	// AGRUPAR PRODUCTOS EN FORMATO JSON
	listarProductosCompras();
})

//QUITAR PRODUCTOS DE LA VENTA Y RECUPERAR BOTÓN - (crear-compra)
var idQuitarProducto = [];
localStorage.removeItem("quitarProductoCompra");
$(".formularioCompra").on("click", "button.quitarProductoCompra", function(){
	$(this).parent().parent().parent().parent().remove();
	var idProducto = $(this).attr("idProducto");
	//ALMACENAR EN EL LOCALSTORAGE EL ID DEL PRODUCTO A QUITAR
	if(localStorage.getItem("quitarProductoCompra") == null){
		idQuitarProducto = [];
	}else{
		idQuitarProducto.concat(localStorage.getItem("quitarProductoCompra"))
	}
	idQuitarProducto.push({"idProducto":idProducto});
	localStorage.setItem("quitarProductoCompra", JSON.stringify(idQuitarProducto));
	$("button.recuperarBoton[idProducto='"+idProducto+"']").removeClass('btn-default');
	$("button.recuperarBoton[idProducto='"+idProducto+"']").addClass('btn-primary agregarProducto');
	if($(".nuevoProducto").children().length == 0){
		$("#nuevoTotalCompra").val(0);
		$("#totalCompra").val(0);
		$("#cantidadArticulos").val(0);
		$("#nuevoTotalCompra").attr("total",0);
		$("#listaProductosCompras").val('');
	}else{
    // SUMAR TOTAL DE PRECIOS
    sumarTotalPreciosCompras();
    // AGRUPAR PRODUCTOS EN FORMATO JSON
    listarProductosCompras();
	}
})

//SUMAR TODOS LOS PRECIOS - (crear-compra)
function sumarTotalPreciosCompras(){
	var precioItem = $(".nuevoPrecioProductoValidar");
	var cantidadItem = $(".nuevaCantidadProductoCompra");
	var arraySumaPrecio = [];
	var arraySumaCantidad = [];  
	for(var i = 0; i < precioItem.length; i++){
		arraySumaPrecio.push(Number($(precioItem[i]).val()));
	}
	for(var i = 0; i < cantidadItem.length; i++){
		arraySumaCantidad.push(Number($(cantidadItem[i]).val()));	 
	}
	function sumaArrayPrecios(total, numero){
		return total + numero;
	}
	function sumaArrayCantidades(totalDos, numeroDos){
		return totalDos + numeroDos;
	}
	var sumaTotalPrecio = arraySumaPrecio.reduce(sumaArrayPrecios);
	var sumaTotalCantidades = arraySumaCantidad.reduce(sumaArrayCantidades);
	$("#nuevoTotalCompra").val(redondear(sumaTotalPrecio,2));
	$("#totalCompra").val(sumaTotalPrecio);
	$("#cantidadArticulos").val(sumaTotalCantidades);
	$("#nuevoTotalCompra").attr("total",sumaTotalPrecio);
}

//LISTAR TODOS LOS PRODUCTOS - (crear-compra)
function listarProductosCompras(){
	var listaProductosCompras = [];
	var descripcion = $(".nuevaDescripcionProducto");
	var cantidad = $(".nuevaCantidadProductoCompra");
	var precioOculto = $(".nuevoPrecioProductoOculto");
	var precio = $(".nuevoPrecioProductoValidar");
	var ganancia = $(".nuevoPrecioGanancia");
	var precioVenta = $(".nuevoPrecioProductoVentaValidar");
	var totalAfuera = 0;
	for(var i = 0; i < descripcion.length; i++){
		var ver = $(precio[i]).val()*$(cantidad[i]).val();
		totalAfuera = parseFloat(totalAfuera) + parseFloat(ver);
		listaProductosCompras.push({ "id" : $(descripcion[i]).attr("idProducto"),
			  "descripcion" : $(descripcion[i]).val(),
			  "pedidos" : $(cantidad[i]).val(),
			  "recibidos" : 0,
			  "precioCompraOriginal" : $(precioOculto[i]).val(),
			  "precioCompra" : $(precio[i]).val(),
			  "ganancia" : $(ganancia[i]).val(),
			  "tipo_iva" : $(precioOculto[i]).attr("tipoIva"),
			  "precioVenta" : $(precioVenta[i]).val(),
			  "total" : ver});
	}
	$("#listaProductosCompras").val(JSON.stringify(listaProductosCompras)); 
	$("#nuevoTotalCompra").val(redondear(totalAfuera,2));
	$("#totalCompra").val(totalAfuera);
	$("#nuevoTotalCompra").attr("total",totalAfuera);
}

//datepicker de crear COMPRA
$('.inputFechaCompra').datepicker({
    dateFormat: 'dd/mm/yy',
    todayBtn: "linked",
    autoclose: true,
    todayHighlight: true,
    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
    onSelect: function () {
    	selectedDate = $.datepicker.formatDate("yy-mm-dd", $(this).datepicker('getDate'));
    	if($(this).attr('id') == 'fechaEntrega'){
    		$("#fechaEntregaHidden").val(selectedDate);
    	}
    	if($(this).attr('id') == 'fechaPago'){
    		$("#fechaPagoHidden").val(selectedDate);
    	} 
        
        //alert(selectedDate)
    }
});

//////////////////////////////////////////////////FIN CREAR-COMPRA//////////////////////////////////


///////////////////////////////////////////////   COMPRAS.PHP ///////////////////////////////7

//BORRAR COMPRA
$("#tablaListarCompras").on("click", ".btnEliminarCompra", function(){
  var idCompra = $(this).attr("idCompra");
  swal({
        title: '¿Está seguro de borrar la compra?',
        text: "¡Si no lo está puede cancelar la accíón!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Si, borrar compra!'
      }).then(function(result){
        if (result.value) {
            window.location = "index.php?ruta=compras&idCompra="+idCompra;
        }
  })
});

//LISTAR COMPRAS (compras.php)
//AGREGA UN INPUT TEXT PARA BUSCAR EN CADA COLUMNA
$("#tablaListarCompras tfoot th").each(function (i) {
  var title = $(this).text();
  if(title != ""){
    $(this).html('<input type="text" placeholder="Filtrar por ' + title + '" />');
  }

});

	/*=============================================
	TABLA LISTAR COMPRAS (compras.php)
	=============================================*/
	var tablaListarComp = $("#tablaListarCompras").DataTable({
		"order": [[ 0, "desc" ]],
		"pageLength": 50,
		"language": GL_DATATABLE_LENGUAJE,
		"dom": 'Bfrtip',
		"buttons": GL_DATATABLE_BOTONES,
		"columnDefs": [
	        {
	            "targets": [ 5,6,7,8,9,10,11,12,13,14 ],
	            "visible": false
	        }
	    ]

	});

	tablaListarComp.columns().every(function () {
	      var that = this;
	      $('input', this.footer()).on('keyup change', function () {
	        if (that.search() !== this.value) {  
	            that
	                .column($(this).parent().index() + ':visible')
	                .search(this.value)
	                .draw(); 
	        }
	      });
	});

	/*=============================================
	IMPRIMIR INGRESO
	=============================================*/
	$("#tablaListarCompras").on("click", ".btnImprimirIngresoMercaderia", function(){
		var codigoCompra = $(this).attr("codigoCompra");
		window.open("compra/"+codigoCompra, "_blank");
	});

//////////////////////////////////////////////    FIN COMPRAS.PHP ///////////////////////////////



/////////////////////////////////////////////    INGRESO.PHP ///////////////////////////////
	/*=============================================
	BOTON EDITAR INGRTESO ingreso.php
	=============================================*/
	$(".tablas").on("click", ".btnEditarIngreso", function(){

		var idCompra = $(this).attr("idCompra");

		window.location = "index.php?ruta=editar-ingreso&idCompra="+idCompra;


	});

	/*=============================================
	IMPRIMIR COMPRA PARCIAL ingreso.php
	=============================================*/
	$(".tablas").on("click", ".btnImprimirCompraParcial", function(){

		var codigoCompra = $(this).attr("codigoCompra");

		window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/compraParcial.php?codigo="+codigoCompra, "_blank");

	});

/////////////////////////////////////////////    FIN INGRESO.PHP ///////////////////////////////



////////////////////////////////////////////	EDITAR-INGRESO.PHP

	// FUNCION PARA CAMBIAR ENTRE INPUT SEGUN REMITO O FACTURA
	function cambioDatosFacturaCompra(valor){

		if(valor=="1" || valor=="6" || valor=="11"){

			$("#datosImpositivos").css("display", "block");
			$("#datosFactura").css("display", "block");
			$("#datosRemito").css("display", "none");

		} else {

			$("#datosImpositivos").css("display", "none");
			$("#datosFactura").css("display", "none");
			$("#datosRemito").css("display", "");

		}

	}

	/*=============================================
	CARGAR LA TABLA DINÁMICA DE COMPRAS (usado en editar-ingreso)
	=============================================*/
	$('.tablaComprasValidar').DataTable({
	    "ajax": "ajax/datatable-comprasValidar.ajax.php",
	    "deferRender": true,
		"retrieve": true,
		"processing": true,
		"language": GL_DATATABLE_LENGUAJE

	});

	/*=============================================
	MODIFICAR LA CANTIDAD - editar-ingreso.php
	=============================================*/
	$(".formularioCompraValidar").on("keyup", "input.nuevaCantidadProductoCompraValidar", function(){
		
		var canti = Number(this.value);

		var precio = $(this).closest('.row').find('.nuevoPrecioProductoCompraValidar').val();
		precio = Number(precio);

		var precioFinal = redondear(canti * precio,2);

		$(this).closest('.row').find('.nuevoPrecioProductoCompraValidarBorrar').val(precioFinal);

	    // AGRUPAR PRODUCTOS EN FORMATO JSON
	    listarProductosComprasValidar();
	});

	/*=============================================
	MODIFICAR PRECIO COMPRA (editar-ingreso.php)
	=============================================*/
	$(".formularioCompraValidar").on("keyup", "input.nuevoPrecioProductoCompraValidar", function(){

		var precio = Number(this.value);

		var canti =  $(this).closest('.row').find('.nuevaCantidadProductoCompraValidar').val();
		canti = Number(canti);

		var precioFinal = redondear(canti * precio,2);

		$(this).closest('.row').find('.nuevoPrecioProductoCompraValidarBorrar').val(precioFinal);
		
	    // AGRUPAR PRODUCTOS EN FORMATO JSON
	    listarProductosComprasValidar();

	});

	/*=============================================
	AGREGANDO PRODUCTOS LA A LA VENTA DESDE LA TABLA
	=============================================*/
	$(".tablaComprasValidar tbody").on("click", "button.agregarProductoCompraValidar", function(){

		var idProducto = $(this).attr("idProducto");

		$(this).addClass("btn-default");

		var datos = new FormData();
	    datos.append("idProducto", idProducto);

	     $.ajax({

	     	url:"ajax/productos.ajax.php",
	      	method: "POST",
	      	data: datos,
	      	cache: false,
	      	contentType: false,
	      	processData: false,
	      	dataType:"json",
	      	success:function(respuesta){

	      	    var descripcion = respuesta["descripcion"];

	          	var precio = respuesta["precio_compra"];
				
				var precioVenta = respuesta["precio_venta"];

				var ganancia = redondear(respuesta["margen_ganancia"],2);

				var tipoIva = respuesta["tipo_iva"];

	          	/*=============================================
	          	EVITAR AGREGAR PRODUTO CUANDO EL STOCK ESTÁ EN CERO
	          	=============================================*/

	          	$(".nuevoProductoValidar").prepend(

					'<div class="row" style="padding:5px 15px">' +

  						'<div class="col-xs-3" style="padding-right:0px">' +

                            '<div class="input-group">' +

                            	'<span class="input-group-btn"><button type="button" class="btn btn-danger btn-sm quitarProductoCompra" idProducto="'+respuesta["id"]+'"><i class="fa fa-times"></i></button></span>' +

                            	'<input type="text" title="'+respuesta["descripcion"]+'" class="form-control input-sm nuevaDescripcionProductoCompraValidar" idProducto="'+respuesta["id"]+'" value="'+respuesta["descripcion"]+'" readonly>' +

                            '</div>' +

                        '</div>' +
  						            
                        '<div class="col-xs-2">' +

                        	'<input type="text" class="form-control input-sm codigoProducto" readonly style="text-align:center;" value="'+respuesta["codigo"]+'"  >' +
  						  
  					    '</div>' +

                        '<div class="col-xs-1">' +
  						                
  						    '<input type="hidden" class="form-control input-sm nuevaCantidadProductoCompraPedidos" style="text-align:center;" value="1" readonly >' +

                            '<input type="input" class="form-control input-sm nuevaCantidadProductoCompraValidar" style="text-align:center;" value="1"  required>' +
                        
                        '</div>' +
  						
                		'<div class="col-xs-2">' +

                			'<input type="hidden" class="nuevoPrecioProductoCompraPedido" value="'+respuesta["precio_compra"]+'" required>' +
                              
                            '<input type="hidden" class="nuevoPrecioProductoCompraValidarBorrar" value="'+respuesta["precio_compra"] +'"  required>' +

                			'<input type="text" title="Precio De Compra" class="form-control input-sm nuevoPrecioProductoCompraValidar" style="text-align:center;" min="1" value="'+respuesta["precio_compra"]+'"  tipoIva="'+tipoIva+'" required>' +
                							
                		'</div>' +

                        '<div class="col-xs-2">' +
                              
                            '<input type="text" title="Precio De Compra" class="form-control input-sm nuevoPrecioGananciaValidar" style="text-align:center;" min="1" value="'+respuesta["margen_ganancia"]+'"  required>' +
                          
                        '</div>' +

                        '<div class="col-xs-2">' +
                            
                            '<input type="text" title="Precio De Venta"  class="form-control input-sm nuevoPrecioVentaProductoCompraValidar" onchange="listarProductosComprasValidarPrecio();" style="text-align:center;" min="1" value="'+respuesta["precio_venta"]+'"  required>' +
                        
                        '</div>' +
                    '</div>');

		        // AGRUPAR PRODUCTOS EN FORMATO JSON
		        listarProductosComprasValidar();

		        // PONER FORMATO AL PRECIO DE LOS PRODUCTOS
		        //$(".nuevoPrecioProductoValidar").number(true, 2);

				localStorage.removeItem("quitarProductoCompra");

	      	}

	     })

	});

	/*=============================================
	LISTAR TODOS LOS PRODUCTOS
	=============================================*/
	function listarProductosComprasValidar(){

		var listaProductosValidarCompra = [];

		var descripcion = $(".nuevaDescripcionProductoCompraValidar");

		var cantidad = $(".nuevaCantidadProductoCompraValidar");

		var pedidos = $(".nuevaCantidadProductoCompraPedidos");
		
		var precio = $(".nuevoPrecioProductoCompraValidar");

		var precioPedido = $(".nuevoPrecioProductoCompraPedido");
		
		var ganancia = $(".nuevoPrecioGananciaValidar");
		
		var precioVenta = $(".nuevoPrecioVentaProductoCompraValidar");
		
		var recibido = $(".recibidos");

		var totalAfuera = 0;
		var ver = 0;
		var totalCalculado = 0;

		for(var i = 0; i < descripcion.length; i++){

			ver = Number($(precio[i]).val()) * Number($(cantidad[i]).val());
			totalAfuera = totalAfuera + ver;

			listaProductosValidarCompra.push({ "id" : $(descripcion[i]).attr("idProducto"),
								  "descripcion" : $(descripcion[i]).val(),
								  "pedidos" : $(pedidos[i]).val(),
								  "recibidos" : $(cantidad[i]).val(),
								  "precioCompraPedido" : $(precioPedido[i]).val(),
								  "precioCompra" : $(precio[i]).val(),
								  "ganancia" : $(ganancia[i]).val(),
								  "tipo_iva" : $(precio[i]).attr("tipoIva"),
								  "precioVenta" : $(precioVenta[i]).val(),
								  "total" : $(precio[i]).val()*$(cantidad[i]).val()});

		}

		$("#listaProductosValidarCompra").val(JSON.stringify(listaProductosValidarCompra));
		
		totalCalculado = redondear(totalAfuera,2); //sumatoria de precios compra
		$("#totalCompraOrden").val(totalCalculado); //subtotal

		var descuentoFactura = Number($("#descuentoCompraOrden").val());
		totalCalculado = totalCalculado - descuentoFactura;		

		$("#totalCompra").val(totalCalculado);
		$("#totalTotalCompraOrden").val(totalCalculado); //total con descuento aplicado
		$("#nuevoTotalCompra").val(redondear(totalCalculado,2));

		//$("#nuevoTotalCompra").val(totalCalculado);
		//$("#nuevoTotalFactura").val(totalCalculado);

		var iva = Number($("#totalIVA").val());
		var ingresosBrutosCalculo = Number($("#precepcionesIngresosBrutos").val());
		var precepcionesCalculo = Number($("#precepcionesIva").val());
		var precepcionesGanancias = Number($("#precepcionesGanancias").val());
		var impuestoInterno = Number($("#impuestoInterno").val());

		var totalFinal = totalCalculado + iva + ingresosBrutosCalculo + precepcionesCalculo + precepcionesGanancias + impuestoInterno;

		$("#nuevoTotalFactura").val(totalFinal);
	
	}

	/*=============================================
	DESCUENTO
	=============================================*/
	$("#descuentoCompraOrden").keyup(function(){

		listarProductosComprasValidar();
	})

	/*=============================================
	MODIFICAR IVA
	=============================================*/
	$(".formularioCompraValidar").on("keyup", "input.totalIVA", function(){

		var cantidad = Number(this.value);
		var totalFactura = Number($("#nuevoTotalCompra").val());

		var totalVer = totalFactura + cantidad + parseFloat($(".precepcionesIngresosBrutos").val()) + parseFloat($(".precepcionesIva").val()) + parseFloat($(".precepcionesGanancias").val()) + parseFloat($(".impuestoInterno").val());
		totalVer = redondear(totalVer,2);

		$("#nuevoTotalFactura").val(totalVer);

	});

	/*=============================================
	MODIFICAR IIBB
	=============================================*/
	$(".formularioCompraValidar").on("keyup", "input.precepcionesIngresosBrutos", function(){

		var cantidad = Number(this.value);
		var totalFactura = Number($("#nuevoTotalCompra").val());

		var totalVer = totalFactura + cantidad + parseFloat($(".totalIVA").val()) + parseFloat($(".precepcionesIva").val()) + parseFloat($(".precepcionesGanancias").val()) + parseFloat($(".impuestoInterno").val());
		totalVer = redondear(totalVer,2);

		$("#nuevoTotalFactura").val(totalVer);

	});

	/*=============================================
	MODIFICAR PERCEPCIONES IVA
	=============================================*/
	$(".formularioCompraValidar").on("keyup", "input.precepcionesIva", function(){

		var cantidad = Number(this.value);
		var totalFactura = Number($("#nuevoTotalCompra").val());

		var totalVer = totalFactura + cantidad + parseFloat($(".totalIVA").val()) + parseFloat($(".precepcionesIngresosBrutos").val()) + parseFloat($(".precepcionesGanancias").val()) + parseFloat($(".impuestoInterno").val());
		totalVer = redondear(totalVer,2);

		$("#nuevoTotalFactura").val(totalVer);

	});

	/*=============================================
	MODIFICAR PERCEPCIONES GANANCIAS
	=============================================*/
	$(".formularioCompraValidar").on("keyup", "input.precepcionesGanancias", function(){

		var cantidad = Number(this.value);
		var totalFactura = Number($("#nuevoTotalCompra").val());

		var totalVer = totalFactura + cantidad + parseFloat($(".totalIVA").val()) + parseFloat($(".precepcionesIngresosBrutos").val()) + parseFloat($(".precepcionesIva").val()) + parseFloat($(".impuestoInterno").val());
		totalVer = redondear(totalVer,2);

		$("#nuevoTotalFactura").val(totalVer);

	});

	/*=============================================
	MODIFICAR IMPUESTOS INTERNOS
	=============================================*/
	$(".formularioCompraValidar").on("keyup", "input.impuestoInterno", function(){

		var cantidad = Number(this.value);
		var totalFactura = Number($("#nuevoTotalCompra").val());

		var totalVer = totalFactura + cantidad + parseFloat($(".totalIVA").val()) + parseFloat($(".precepcionesIngresosBrutos").val()) + parseFloat($(".precepcionesIva").val()) + parseFloat($(".precepcionesGanancias").val());
		totalVer = redondear(totalVer,2);

		$("#nuevoTotalFactura").val(totalVer);

	});


////////////////////////////////////////////	FIN EDITAR-INGRESO.PHP


///-------------------------------------------------------


/*=============================================
LISTAR TODOS LOS PRODUCTOS
=============================================*/
function listarProductosComprasValidarPrecios(){

	var listaProductosValidarCompraPrecios = [];

	var descripcion = $(".nuevaDescripcionProductoCompraValidarPrecios");

	var cantidad = $(".nuevaCantidadProductoValidarPreciosVer");

	var pedidos = $(".nuevaCantidadProductoCompraPedidos");
	
	var factura = $(".nuevaCantidadProductoFactura");
	
	var vencimiento = $(".vencimiento");
	
	var neto = $(".precioNeto");
	
	var iva = $(".ivaCompra");
	
	var precio = $(".nuevoPrecioProductoCompraValidarPrecios");
	
	var precioPedido = $(".nuevoPrecioProductoCompraValidarUnitarioBorrar");
	
	var ganancia = $(".gananciaIndice");
	
	var precioVenta = $(".nuevoPrecioVentaProductoCompraValidarPrecios");
	
	var recibido = $(".recibidos");

	var totalAfuera = 0;
	var totalNetoAfuera = 0;
	var totalIVAVERAfuera = 0;
	
	for(var i = 0; i < descripcion.length; i++){
		var ver = $(precio[i]).val()*$(factura[i]).val();
		totalAfuera = parseFloat(totalAfuera) + parseFloat(ver);
		
		var netoVer = $(neto[i]).val()*$(factura[i]).val();
		totalNetoAfuera = redondear(parseFloat(netoVer) + parseFloat(totalNetoAfuera),2);
		
		var ivaVer = $(iva[i]).val()*$(factura[i]).val();
		totalIVAVERAfuera = redondear(parseFloat(ivaVer) + parseFloat(totalIVAVERAfuera),2);

		listaProductosValidarCompraPrecios.push({ "id" : $(descripcion[i]).attr("idProducto"),
							  "descripcion" : $(descripcion[i]).val(),
							  "pedidos" : $(pedidos[i]).val(),
							  "recibidos" : $(cantidad[i]).val(),
							  "articulosFactura" : $(factura[i]).val(),
							  "vencimiento" : $(vencimiento[i]).val(),
							  "precioCompraPedido" : $(precioPedido[i]).val(),
							  "neto" : $(neto[i]).val(),
							  "iva" : $(iva[i]).val(),
							  "precioCompra" : $(precio[i]).val(),
							  "ganancia" : $(ganancia[i]).val(),
							  "precioVenta" : $(precioVenta[i]).val(),
							  "total" : $(precio[i]).val()*$(cantidad[i]).val()});


		$("#sumaNetoVer").val(totalNetoAfuera);
		$("#sumaIVAVer").val(totalIVAVERAfuera);					  

	}

	$("#listaProductosValidarCompraPrecios").val(JSON.stringify(listaProductosValidarCompraPrecios)); 

	var iva = redondear((totalAfuera*0.21),2);
	var ingresosBrutosCalculo = redondear((totalAfuera*0.03),2);
	//var internos = redondear((totalAfuera*0.08),2);
	var precepcionesCalculo = redondear((totalAfuera*0.05),2);
	var totalCalculado = redondear(parseFloat(totalAfuera)+parseFloat(iva)+parseFloat(ingresosBrutosCalculo)+parseFloat(precepcionesCalculo),2);
	$("#nuevoTotalCompra").val(redondear(totalAfuera,2));
	$("#totalIVA").val(iva);
	$("#ingresosBrutos").val(ingresosBrutosCalculo);
	$("#impuestoInterno").val(0);
	$("#precepciones").val(precepcionesCalculo);
	$("#totalCompra").val(totalAfuera);
	$("#totalCompraFactura").val(totalCalculado);
	
}

/*=============================================
CUANDO CARGUE LA TABLA CADA VEZ QUE NAVEGUE EN ELLA
=============================================*/
$("#tablaCompras").on("draw.dt", function(){

	if(localStorage.getItem("quitarProductoCompra") != null){

		var listaIdProductos = JSON.parse(localStorage.getItem("quitarProductoCompra"));

		for(var i = 0; i < listaIdProductos.length; i++){

			$("button.recuperarBoton[idProducto='"+listaIdProductos[i]["idProducto"]+"']").removeClass('btn-default');
			$("button.recuperarBoton[idProducto='"+listaIdProductos[i]["idProducto"]+"']").addClass('btn-primary agregarProducto');

		}

	}

})

/*=============================================
QUITAR PRODUCTOS DE LA VENTA Y RECUPERAR BOTÓN
=============================================*/
localStorage.removeItem("quitarProductoCompraValidar");

$(".formularioCompraValidar").on("click", "button.quitarProductoCompraValidar", function(){

	alert('REMOVE 2')

	$(this).parent().parent().parent().parent().remove();

	var idProducto = $(this).attr("idProducto");

	/*=============================================
	ALMACENAR EN EL LOCALSTORAGE EL ID DEL PRODUCTO A QUITAR
	=============================================*/

	if(localStorage.getItem("quitarProductoCompraValidar") == null){

		idQuitarProducto = [];
	
	}else{

		idQuitarProducto.concat(localStorage.getItem("quitarProductoCompraValidar"))

	}

	idQuitarProducto.push({"idProducto":idProducto});

	localStorage.setItem("quitarProductoCompraValidar", JSON.stringify(idQuitarProducto));

	$("button.recuperarBoton[idProducto='"+idProducto+"']").removeClass('btn-default');

	$("button.recuperarBoton[idProducto='"+idProducto+"']").addClass('btn-primary agregarProducto');

	if($(".nuevoProductoValidar").children().length == 0){

		$("#nuevoTotalCompra").val(0);
		$("#totalCompra").val(0);
		$("#nuevoTotalCompra").attr("total",0);
	
	}else{

        // SUMAR TOTAL DE PRECIOS
        sumarTotalPreciosComprasValidar();

        // AGRUPAR PRODUCTOS EN FORMATO JSON
        listarProductosComprasValidarPrecios();

	}

})

/*=============================================
MODIFICAR PRECIO NETO
=============================================*/

$(".formularioCompraValidar").on("keyup", "input.precioNeto", function(){

var canti = this.value;

	var ivaCompra = $(this).closest('.row').find('.ivaCompra').val();
	var precioFinal = redondear(parseFloat(canti) + parseFloat(ivaCompra),2);
	var actualizarValor = $(this).closest('.row').find('.nuevoPrecioProductoCompraValidarPrecios').val(precioFinal);
	//precio.val(precioFinal);

        // AGRUPAR PRODUCTOS EN FORMATO JSON
        listarProductosComprasValidarPrecios();
})

/*=============================================
MODIFICAR PRECIO IVA
=============================================*/

$(".formularioCompraValidar").on("change", "input.ivaCompra", function(){

var canti = this.value;

	var precio = $(this).closest('.row').find('.precioNeto').val();
	var porcentaje = redondear((canti*precio)/100,2);
	var precioFinal = redondear(parseFloat(porcentaje) + parseFloat(precio),2);
	var actualizarValor = $(this).closest('.row').find('.nuevoPrecioProductoCompraValidarPrecios').val(precioFinal);
	var actualizarIVA = $(this).closest('.row').find('.ivaCompra').val(porcentaje);
	//precio.val(precioFinal);

        // AGRUPAR PRODUCTOS EN FORMATO JSON
        listarProductosComprasValidarPrecios();
})

/*=============================================
VALIDAR PRECIO COMPRA
=============================================*/

$(".formularioCompraValidar").on("change", "input.nuevoPrecioProductoCompraValidarPrecios", function(){

var precioValidado = this.value;

	var precio = $(this).closest('.row').find('.referencia').val();
	
	var borrarIndice = $(this).closest('.row').find('.gananciaIndice').val('');
	var borrarPrecioVenta = $(this).closest('.row').find('.nuevoPrecioVentaProductoCompraValidarPrecios').val('');
	
	if(precioValidado > precio){
		 var aumentar =  document.getElementById("totalCompraOrden");
		 var aumento =  document.getElementById("totalCompra").value;
		 aumentar.value = aumento;
		 listarProductosComprasNota();
	 }else{
		 var aumentar =  document.getElementById("totalCompraOrden");
		 aumentar.value = 0;
	 }
	
})

/*=============================================
MODIFICAR PRECIO
=============================================*/

$(".formularioCompraValidar").on("change", "input.gananciaIndice", function(){

var canti = this.value;

	var precio = $(this).closest('.row').find('.referencia').val();
	var precioFinal2 = redondear((precio * canti)/100,2); 
	var precioFinal = redondear(parseFloat(precio) + parseFloat(precioFinal2),2);
	
	var cambiarValorIndice = $(this).closest('.row').find('.gananciaIndice').val(precioFinal2).focus();
	var cambiarValor = $(this).closest('.row').find('.nuevoPrecioVentaProductoCompraValidarPrecios').val(precioFinal).focus();
	

})

$(".formularioPromocion").on("keyup", "input.variacionPrecio", function(){

var canti = this.value/100;

	var precio = $(this).closest('.row').find('.precioProducto').val();
	var precioFinal = precio - (canti * precio);
	//var cambiarPorcentaje = $(this).closest('.row').find('.variacionPrecio').val(canti * precio);
	var cambiarValor = $(this).closest('.row').find('.nuevoPrecioProductoValidar').val(precioFinal);
	listarProductosPromocion();
})

/*=============================================
MODIFICAR ARTICULOS FACTUTA
=============================================*/
$(".formularioCompraValidar").on("keyup", "input.nuevaCantidadProductoFactura", function(){

	listarProductosComprasValidarPrecios();
})

/*=============================================
MODIFICAR PRECIO FACTURA
=============================================*/
$(".formularioFactura").on("keyup", "input.nuevoPrecioArticuloFactura", function(){

	var precio = this.value;

	var cantidad = $(this).closest('.row').find('.nuevaCantidadProductoFactura').val();
	var precioFinal = redondear((cantidad * precio),2); 
	var cambiarValor = $(this).closest('.row').find('.nuevoPrecioFinalFactura').val(precioFinal);
	
	listarProductosFactura();
});


/*=============================================
SUMAR TODOS LOS PRECIOS
=============================================*/

function sumarTotalPreciosComprasValidar(){

	var precioItem = $(".nuevoPrecioProductoCompraValidarBorrar");

	var arraySumaPrecio = [];  

	for(var i = 0; i < precioItem.length; i++){

		 arraySumaPrecio.push(Number($(precioItem[i]).val()));
		
		 
	}

	function sumaArrayPrecios(total, numero){

		return total + numero;

	}

	var sumaTotalPrecio = arraySumaPrecio.reduce(sumaArrayPrecios);

	$("#nuevoTotalCompra").val(sumaTotalPrecio);
	$("#totalCompra").val(sumaTotalPrecio);
	$("#nuevoTotalCompra").attr("total",sumaTotalPrecio);


}



/*=============================================
FUNCIÓN PARA DESACTIVAR LOS BOTONES AGREGAR CUANDO EL PRODUCTO YA HABÍA SIDO SELECCIONADO EN LA CARPETA
=============================================*/

function quitarAgregarProductoCompra(){

	//Capturamos todos los id de productos que fueron elegidos en la venta
	var idProductos = $(".quitarProductoCompra");

	//Capturamos todos los botones de agregar que aparecen en la tabla
	var botonesTabla = $("#tablaCompras tbody button.agregarProducto");

	//Recorremos en un ciclo para obtener los diferentes idProductos que fueron agregados a la venta
	for(var i = 0; i < idProductos.length; i++){

		//Capturamos los Id de los productos agregados a la venta
		var boton = $(idProductos[i]).attr("idProducto");
		
		//Hacemos un recorrido por la tabla que aparece para desactivar los botones de agregar
		for(var j = 0; j < botonesTabla.length; j ++){

			if($(botonesTabla[j]).attr("idProducto") == boton){

				$(botonesTabla[j]).removeClass("btn-primary agregarProducto");
				$(botonesTabla[j]).addClass("btn-default");

			}
		}

	}
	
}

/*=============================================
CARACTERISTICAS PRODUCTO
=============================================*/
$("#tablaCompras tbody").on("click", "div.detalleProductoCompras", function(){

	var idProducto = $(this).attr("idProducto");

	var datos = new FormData();
	datos.append("idProducto", idProducto);
	var total = 0;
	var sucursal1 = 0;
	var sucursal2 = 0;
	var sucursal3 = 0;
	$.ajax({
		url:"ajax/productos.ajax.php",
		method: "POST",
		data: datos,
		cache: false,
		contentType: false,
		processData: false,
		dataType:"json",
		success:function(respuesta){
			
			var codigo = respuesta["codigo"];
			var datosDos = new FormData();
			datosDos.append("producto", codigo);

			$.ajax({
				url:"ajax/sumaProductos.ajax.php",
				method: "POST",
				data: datosDos,
				cache: false,
				contentType: false,
				processData: false,
				dataType:"json",
				success:function(respuestaDos) {

					for (var i = 0; i < respuestaDos.length; i++) {
						var jsonCombo  = respuestaDos[i][6];
						var obj = JSON.parse(jsonCombo);

						for (var j=0; j < obj.length; j++) {

							if(obj[j]['id']==codigo){
								total = parseFloat(total) + parseFloat(obj[j]['cantidad']);
							}else{

							}
							if(obj[j]['sucursal']=="sucursal1" && obj[j]['id']==codigo){
								sucursal1 = parseFloat(sucursal1) + parseFloat(obj[j]['cantidad']);
							}else{

							}
							if(obj[j]['sucursal']=="sucursal2" && obj[j]['id']==codigo){
								sucursal2 = parseFloat(sucursal2) + parseFloat(obj[j]['cantidad']);

							}else{

							}
							if(obj[j]['sucursal']=="sucursal3" && obj[j]['id']==codigo){
								sucursal3 = parseFloat(sucursal3) + parseFloat(obj[j]['cantidad']);

							}else{

							}
						}
					}		

					var descripcion = respuesta["descripcion"];

					var precio = respuesta["precio_compra"];
					var ganancia = respuesta["ganancia"];
					var precioVenta = respuesta["precio_venta"];
					var stotkTotal = parseFloat(respuesta["sucursal1"]) + parseFloat(respuesta["sucursal2"]) + parseFloat(respuesta["sucursal3"]) + parseFloat(respuesta["sucursal4"]) + parseFloat(respuesta["sucursal5"]);
					
					document.getElementById("codigo1").innerHTML = respuesta["codigo"];
					document.getElementById("codigo2").innerHTML = respuesta["codigo"];
					document.getElementById("codigo3").innerHTML = respuesta["codigo"];
					document.getElementById("codigo").innerHTML = respuesta["codigo"];
					//aca va el precio anterior
					var compraAnterior = respuesta["precioCompraAnterior"];
					var partido = compraAnterior.split(' ');
					document.getElementById("precioAnterior").innerHTML = partido[0];
					document.getElementById("fechaUltimaCompra").innerHTML = partido[1];
					
					document.getElementById("totalVendidos").innerHTML = total;
					document.getElementById("totalVendidosSuc1").innerHTML = sucursal1;
					document.getElementById("totalVendidosSuc2").innerHTML = sucursal2;
					document.getElementById("totalVendidosSuc3").innerHTML = sucursal3;
					
					document.getElementById("nombreArticulo").innerHTML = descripcion;
					document.getElementById("nombreArticuloSuc1").innerHTML = descripcion;
					document.getElementById("nombreArticuloSuc2").innerHTML = descripcion;
					document.getElementById("nombreArticuloSuc3").innerHTML = descripcion;
					
					document.getElementById("stockTotal").innerHTML = stotkTotal;
					document.getElementById("sugerenciaCompra").innerHTML = sugerencia;
					
					document.getElementById("stockTotalSuc1").innerHTML = respuesta["sucursal1"];
					document.getElementById("stockTotalSuc2").innerHTML = respuesta["sucursal2"];
					document.getElementById("stockTotalSuc3").innerHTML = respuesta["sucursal3"];
					
					document.getElementById("diasStock").innerHTML = diasStock;
					document.getElementById("diasStockSuc1").innerHTML = diasStockSucurusa1;
					document.getElementById("diasStockSuc2").innerHTML = diasStockSucurusa2;
					document.getElementById("diasStockSuc3").innerHTML = diasStockSucurusa3;								
				}
			})
		}

	})
})

function redondear(cantidad,decimales){
	var cantidad=parseFloat(cantidad);
	var decimales=parseFloat(decimales);
	decimales=(!decimales?2:decimales);
	return Math.round(cantidad*Math.pow(10,decimales))/Math.pow(10,decimales);
}

function limpiarModal(){
		document.getElementById("codigo1").value = '';
		document.getElementById("codigo2").innerHTML = '';
		document.getElementById("codigo3").innerHTML = '';
		document.getElementById("codigo").innerHTML = '';
								
		document.getElementById("totalVendidos").innerHTML= '';
		document.getElementById("totalVendidosSuc1").innerHTML  = '';
		document.getElementById("totalVendidosSuc2").innerHTML = '';
		document.getElementById("totalVendidosSuc3").innerHTML = '';
							
		document.getElementById("nombreArticulo").innerHTML = '';
		document.getElementById("nombreArticuloSuc1").innerHTML = '';
		document.getElementById("nombreArticuloSuc2").innerHTML = '';
		document.getElementById("nombreArticuloSuc3").innerHTML = '';
								
		document.getElementById("stockTotal").innerHTML = '';
		document.getElementById("sugerenciaCompra").innerHTML = '';
								
		document.getElementById("stockTotalSuc1").innerHTML = '';
		document.getElementById("stockTotalSuc2").innerHTML = '';
		document.getElementById("stockTotalSuc3").innerHTML= '';
								
		document.getElementById("diasStock").innerHTML = '';
		document.getElementById("diasStockSuc1").innerHTML = '';
		document.getElementById("diasStockSuc2").innerHTML = '';
		document.getElementById("diasStockSuc3").innerHTML = '';
}


/*=============================================
RANGO DE FECHAS
=============================================*/
$('#daterange-btnCompras').daterangepicker(
  {
    ranges   : {
      'Hoy'       : [moment(), moment()],
      'Ayer'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Últimos 7 días' : [moment().subtract(6, 'days'), moment()],
      'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
      'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
      'Último mes'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    },
    startDate: moment(),
    endDate  : moment()
  },
  function (start, end) {
    $('#daterange-btnCompras span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

    var fechaInicial = start.format('YYYY-MM-DD');

    var fechaFinal = end.format('YYYY-MM-DD');

    var capturarRango = $("#daterange-btnCompras span").html();
   
   	localStorage.setItem("capturarRango", capturarRango);

   	window.location = "index.php?ruta=compras&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;

  }

)

/*=============================================
CANCELAR RANGO DE FECHAS
=============================================*/
$(".daterangepicker.opensleft .range_inputs .cancelBtn").on("click", function(){

	localStorage.removeItem("capturarRango");
	localStorage.clear();
	window.location = "compras";
})

/*=============================================
CAPTURAR HOY
=============================================*/
$(".daterangepicker.opensleft .ranges li").on("click", function(){

	var textoHoy = $(this).attr("data-range-key");

	if(textoHoy == "Hoy"){

		var d = new Date();
		
		var dia = d.getDate();
		var mes = d.getMonth()+1;
		var anio = d.getFullYear();

		dia = ("0"+dia).slice(-2);
		mes = ("0"+mes).slice(-2);

		var fechaInicial = anio+"-"+mes+"-"+dia;
		var fechaFinal = anio+"-"+mes+"-"+dia;	

    	localStorage.setItem("capturarRango", "Hoy");

    	window.location = "index.php?ruta=compras&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;

	}

})

function mostrarSaldos(){
	
	window.location = "index.php?ruta=proveedores-saldo&fechaInicial="+document.getElementById("fechaInicial").value;
}

function mostrarInformeProveedores(){
	window.location = "index.php?ruta=informeProveedores&fechaInicial="+document.getElementById("fechaDesde").value+"&fechaFinal="+document.getElementById("fechaHasta").value+"&informe=1";
}





/////////////////////////////////-----------------------------------////////////////////777
/*=============================================
IMPRIMIR COMPRA 
=============================================*/
$(".tablas").on("click", ".btnImprimirCompra", function(){

	var codigoCompra = $(this).attr("codigoCompra");

	window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/ingresoFactura.php?codigo="+codigoCompra, "_blank");

})

/*=============================================
IMPRIMIR COMPRA
=============================================*/
$(".tablas").on("click", ".btnImprimirCompraCtaCte", function(){

	var codigoCompra = $(this).attr("idCompra");

	window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/compra.php?codigo="+codigoCompra, "_blank");

})

/*=============================================
IMPRIMIR COMPRA
=============================================*/
$(".tablas").on("click", ".btnImprimirCompraFinal", function(){

	var codigoCompra = $(this).attr("codigoCompra");

	window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/compra.php?codigo="+codigoCompra, "_blank");

});

/*=============================================
CADA VEZ QUE CARGUE LA TABLA CUANDO NAVEGAMOS EN ELLA EJECUTAR LA FUNCIÓN:
=============================================*/
$('.tablas').on( 'draw.dt', function(){
	quitarAgregarProducto();
});