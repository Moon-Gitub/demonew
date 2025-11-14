/*=============================================
CARGAR LA TABLA DINÁMICA DE PRODUCTOS EN VENTAS (crear-venta.php editar-venta.php)
=============================================*/
$('#tablaVentas').DataTable( {
    "ajax": "ajax/datatable-ventas.ajax.php",
    "deferRender": true,
	"retrieve": true,
	"processing": true,
	"language": GL_DATATABLE_LENGUAJE, 
	'columnDefs': [
		  {
		      "targets": [3,5],
		      "className": "text-center"
		 }]

});

/*=============================================
AGREGANDO PRODUCTOS A LA VENTA DESDE LA TABLA
=============================================*/
$("#tablaVentas tbody").on("click", "button.agregarProducto", function(){

	var idProducto = $(this).attr("idProducto");
	var cantidad = 1;
	var stockSucursal = $("#sucursalVendedor").val();
	var tipoPrecio = $('#radioPrecio').val(); 

    if(stockSucursal === "" || tipoPrecio === ""){
        swal({
    	   title: "Error",
    	   text: "El usuario debe definir sucursal y lista de precio",
    	   toast: true,
    	   timer: 3000,
    	   position: 'top',
    	   type: "error",
    	   confirmButtonText: "¡Cerrar!"
    	 });
    	return;
    }

	//$(this).removeClass("btn-primary agregarProducto");

	//$(this).addClass("btn-default");

		var datos = new FormData();
	    datos.append("codigoProducto", idProducto);

 		$.ajax({

	     	url:"ajax/productos.ajax.php",
	      	method: "POST",
	      	data: datos,
	      	cache: false,
	      	contentType: false,
	      	processData: false,
	      	dataType:"json",
	      	success:function(respuesta) {
			    //console.log(respuesta);
	      		if(respuesta) {

	      			var precioVta = 0;
					var precioNeto = 0;
	          		var iva = Number(respuesta["tipo_iva"]);
	          		var ivaValor = 0;
	      			var stock = respuesta[stockSucursal]; //TOMO EL STOCK DE LA SUCURSAL ASIGNADA AL USUARIO LOGUEADO

	      			if(respuesta[tipoPrecio] == 0) {

						(async()=> {
							const { value: formValues } = await swal({
							  title: 'Ingrese importe',
							  html:'<p>'+respuesta["codigo"]+'-'+respuesta["descripcion"]+'</p> <div class="input-group"><span class="input-group-addon"><i class="ion ion-social-usd"></i></span><input type="number" id="swal-input1" class="form-control" ></div>',
							  focusConfirm: false,
							  preConfirm: () => {
							    const promesa =  new Promise((resolve, reject) => { 
							    	resolve (document.getElementById('swal-input1').value); 
							    });

							    promesa.then(values => {

							    values = Number(values);
							    precioNeto = (values / ((iva / 100) + 1)) * cantidad; //obtengo el neto
							    ivaValor = (values * cantidad) - precioNeto;          //obtengo el iva

							 	//ACTUALIZO EN LA BD EL NUEVO PRECIO INTRODUCIDO 
		    					//RESERVADOS LOS CODIGOS DEL 1 AL 10 PARA (VARIOS, CARNE, PAN, ETC)
		    					/*
		    					if(idProducto != 1 && idProducto != "1" && 
		    						idProducto != 2 && idProducto != "2" && 
		    						idProducto != 3 && idProducto != "3" && 
		    						idProducto != 4 && idProducto != "4" && 
		    						idProducto != 5 && idProducto != "5" && 
		    						idProducto != 6 && idProducto != "6" && 
		    						idProducto != 7 && idProducto != "7" && 
		    						idProducto != 8 && idProducto != "8" && 
		    						idProducto != 9 && idProducto != "9" && 
		    						idProducto != 10 && idProducto != "10") {

		    							//var tipoPrecio = $('input[name=radioPrecio]:checked').val();
                                        var tipoPrecio = $("#radioPrecio").val();
                                        
				    					var datosActualizarPrecio = new FormData();
									    datosActualizarPrecio.append("actualizarPrecio", 1);
									    datosActualizarPrecio.append("codigoProducto", idProducto);
									    datosActualizarPrecio.append(tipoPrecio, values);

								 		$.ajax({

									     	url:"ajax/productos.ajax.php",
									      	method: "POST",
									      	data: datosActualizarPrecio,
									      	cache: false,
									      	contentType: false,
									      	processData: false,
									      	dataType:"json",
									      	success:function(respuestaActualizar) {
									      		console.log(respuestaActualizar);
									      	},
											error: function(xhr, status, error) {
											  
											 	console.log(status);
												console.log( xhr.responseText);
												console.log( error);
											}

									      })
								}*/
								
		    					$(".nuevoProductoCaja").prepend(

								'<div class="row" style="padding-left:25px;padding-bottom:5px;">'+

						         '<!-- Cantidad del producto -->'+
						          '<div class="col-xs-2 nuevaCantidad">'+						            
						             '<input type="text" autocomplete="off" style="text-align:center;" class="form-control input-sm nuevaCantidadProductoCaja" stock="0" nuevoStock="0" min="0" value="'+cantidad+'" required>'+
						          '</div>'+

								'<!-- descripcion producto -->'+
								 '<div class="col-xs-6" style="padding-right:0px">'+
						            '<div class="input-group">'+						              
						              '<span class="input-group-btn"><button type="button" class="btn btn-danger btn-sm quitarProductoCaja" idProducto="'+respuesta['id']+'"><i class="fa fa-times"></i></button></span>'+
						              '<input type="text" autocomplete="off" class="form-control input-sm nuevaDescripcionProductoCaja" idProducto="'+respuesta['id']+'"  value="'+respuesta['descripcion']+'" required>'+
									  '<input type="hidden" class="nuevaCategoria" value="'+respuesta["id_categoria"]+'">'+
						            '</div>'+
						          '</div>'+

								'<!-- precio unitario -->'+
								'<div class="col-xs-2 nuevoPrecio">'+
								   '<div class="input-group">'+
						             '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
									 '<input type="text" style="text-align:center;" class="form-control input-sm nuevaPrecioUnitario" name="nuevaPrecioUnitario" value="'+values+'"  required>'+
								  '</div>'+
								'</div>'+

						          '<!-- Precio total -->'+
						          '<div class="col-xs-2 ingresoPrecio" style="padding-left:0px">'+
						            '<div class="input-group">'+
						              '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
					  				  '<input type="hidden" class="nuevoTipoIvaValorProducto" value="'+ivaValor+'" netoUnitario="'+precioNeto+'" tipoIva="'+iva+'" cantxIva="1">'+
						              '<input type="hidden" class="nuevoValorTipoIva" value="'+iva+'">'+
									  '<input type="text" class="form-control input-sm nuevoPrecioProductoCaja" precioReal="'+values+'" precioCompra="'+respuesta["precio_compra"]+'" style="text-align:center;" name="nuevoPrecioProductoCaja" value="'+redondear(values*cantidad)+'" required>'+
						            '</div>'+
						          '</div>'+

						        '</div>');

							    })

							  }
							})

						sumarTotalPreciosCaja();

						//CALCULAR SI HAY DESCUENTO
				        calcularDescuentoCaja("nuevoDescuentoPorcentajeCaja");

				        //CALCULAR SI HAY INTERES
				        calcularInteresCaja("nuevoInteresPorcentajeCaja");

				        // PONER FORMATO AL PRECIO DE LOS PRODUCTOS
						listarProductosCaja();

						//RESETEO METODO DE PAGO
		        		$("#nuevoMetodoPagoCaja").prop("selectedIndex", 0).change();

				        $(".nuevoPrecioProductoCaja").number(true, 2);

						localStorage.removeItem("quitarProductoCaja");

						$("#ventaCajaDetalleHidden").val("");
						$("#ventaCajaDetalle").val("");
						$("#autocompletarProducto").val("");
						$("#ventaCajaCantidad").val("1");
						$("#ventaCajaDetalle").focus();						

						})()

	      			} else {

                        //SI NO HAY STOCK NO DEJO AGREGAR PRODUCTO
                        //if(respuesta["stock"] <= 0){
        
        	      			//swal({
        				    //   title: "No hay stock disponible",
        				    //   toast: true,
        				    //   timer: 3000,
        				    //   position: 'top',
        				    //   type: "error",
        				    //   confirmButtonText: "¡Cerrar!"
        				    // });
        				    //return;
        
        	          	//}
        	          	
        	          	/*
        	          	var estaElProducto = JSON.parse($("#listaProductosCaja").val());
        	          	console.log(estaElProducto)
        	          	estaElProducto.filter(function (estaElProducto) { 
        	          	    if(estaElProducto.id == respuesta["id"]) { 
        	          	        console.log(true)
        	          	        
        	          	    } else { 
        	          	        console.log(false)
        	          	        
        	          	    } 
        	          	    
        	          	});
        	          	*/

	      				//precioVta = respuesta["precio_venta"];
	      				precioVta = respuesta[tipoPrecio];

	      				var precioXCantidad = precioVta * cantidad;
	      				
	      				precioNeto = precioVta / ((iva / 100) + 1); //precio neto para cantidad 1
	      				//precioNeto = precioXCantidad / ((iva / 100) + 1); //precio neto para cantidad mayor a 1
	      				console.log(precioNeto);
	      				// ivaValor = precioXCantidad - precioXCantidad / (1 + (iva / 100));
	      				// console.log(ivaValor);
	      				ivaValor = (precioNeto * iva / 100); // * cantidad;
	      				//ivaValor = redondear(ivaValor,2);
	      				console.log(ivaValor);
						console.log(precioXCantidad);

	      				$(".nuevoProductoCaja").prepend(

						'<div class="row" style="padding-left:25px;padding-bottom:5px;">'+

						 '<!-- Cantidad del producto -->'+
				          '<div class="col-xs-2 nuevaCantidad">'+
				             '<input type="text" autocomplete="off" style="text-align:center;" class="form-control input-sm nuevaCantidadProductoCaja" stock="'+stock+'" nuevoStock="'+Number(stock-1)+'" min="0" value="'+cantidad+'"  required>'+
				          '</div>'+
						  
						  '<!-- Descripción del producto -->'+				          
				          '<div class="col-xs-6" style="padding-right:0px">'+
				            '<div class="input-group">'+
				              '<span class="input-group-btn"><button type="button" class="btn btn-danger btn-sm quitarProductoCaja" idProducto="'+respuesta['id']+'"><i class="fa fa-times"></i></button></span>'+
				              '<input type="text" class="form-control input-sm nuevaDescripcionProductoCaja" idProducto="'+respuesta['id']+'" value="'+respuesta['descripcion']+'" readonly>'+
							  '<input type="hidden" class="nuevaCategoria" value="'+respuesta["id_categoria"]+'">'+
				            '</div>'+
				          '</div>'+

						  '<!-- precio unitario del producto -->'+
				          '<div class="col-xs-2 nuevoPrecio">'+
					            '<div class="input-group">'+
								 '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
					             '<input type="text" style="text-align:center;" class="form-control input-sm nuevaPrecioUnitario" name="nuevaPrecioUnitario" value="'+precioVta+'" readonly>'+
				            	'</div>'+
							'</div>'+
							 
				          '<!-- Precio total del producto -->'+
				          '<div class="col-xs-2 ingresoPrecio" style="padding-left:0px">'+
				            '<div class="input-group">'+
				              '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
			  				  '<input type="hidden" class="nuevoTipoIvaValorProducto" value="'+ivaValor+'" netoUnitario="'+precioNeto+'" tipoIva="'+iva+'" cantxIva="'+cantidad+'" readonly required>'+
							  '<input type="hidden" class="nuevoValorTipoIva" value="'+iva+'" readonly required>'+
				              '<input type="text" class="form-control input-sm nuevoPrecioProductoCaja" precioReal="'+precioVta+'" precioCompra="'+respuesta["precio_compra"]+'" style="text-align:center;" name="nuevoPrecioProductoCaja" value="'+precioXCantidad+'" required readonly>'+
				            '</div>'+
				          '</div>'+

				        '</div>');

				        sumarTotalPreciosCaja();

						//CALCULAR SI HAY DESCUENTO
				        calcularDescuentoCaja("nuevoDescuentoPorcentajeCaja");

				        //CALCULAR SI HAY INTERES
				        calcularInteresCaja("nuevoInteresPorcentajeCaja");

				        // PONER FORMATO AL PRECIO DE LOS PRODUCTOS
						listarProductosCaja();

						//RESETEO METODO DE PAGO
		        		$("#nuevoMetodoPagoCaja").prop("selectedIndex", 0).change();

				        $(".nuevoPrecioProductoCaja").number(true, 2);

						localStorage.removeItem("quitarProductoCaja");

						$("#ventaCajaDetalleHidden").val("");
						$("#ventaCajaDetalle").val("");
						$("#autocompletarProducto").val("");
						$("#ventaCajaCantidad").val("1");
						$("#ventaCajaDetalle").focus();

	      			}

				} else {

					swal({
				      title: "Ventas",
				      text: "No se encontró el producto",
					  type: "warning",
					  toast: true,
					  position: 'top',
					  showConfirmButton: false,
					  timer: 3000
					});;

					$("#nuevoCodigoCaja").val(idProducto);

					$("#modalAgregarProductoCaja").modal('show');
					
					$('#modalAgregarProductoCaja').on('shown.bs.modal', function () {
					    $("#nuevaDescripcionCaja").focus();
					})  

				}
			} 

 		});

});

//AL CAMBIAR LOS INPUT DEL ARTICULO LIBRE
/*
$(".nuevoProducto").on("change", "input.nuevoProductoLibre", function(){ 

    // SUMAR TOTAL DE PRECIOS
    sumarTotalPrecios();

    // AGREGAR IMPUESTO
    agregarImpuesto();

    //CALCULAR SI HAY DESCUENTO
    calcularDescuento("nuevoDescuentoPorcentaje");

    //CALCULAR SI HAY INTERES
    calcularInteres("nuevoInteresPorcentaje");

    // AGRUPAR PRODUCTOS EN FORMATO JSON
    listarProductos();

})*/

/*=============================================
CUANDO CARGUE LA TABLA CADA VEZ QUE NAVEGUE EN ELLA
=============================================
$("#tablaVentas").on("draw.dt", function(){

	if(localStorage.getItem("quitarProducto") != null){

		var listaIdProductos = JSON.parse(localStorage.getItem("quitarProducto"));

		for(var i = 0; i < listaIdProductos.length; i++){

			$("button.recuperarBoton[idProducto='"+listaIdProductos[i]["idProducto"]+"']").removeClass('btn-default');
			$("button.recuperarBoton[idProducto='"+listaIdProductos[i]["idProducto"]+"']").addClass('btn-primary agregarProducto');

		}

	}

});*/

/*=============================================
QUITAR PRODUCTOS DE LA VENTA Y RECUPERAR BOTÓN
=============================================*/
var idQuitarProducto = [];

localStorage.removeItem("quitarProductoCaja");

$(".formularioVenta").on("click", "button.quitarProductoCaja", function(){

	$(this).parent().parent().parent().parent().remove();

	var idProducto = $(this).attr("idProducto");

	/*=============================================
	ALMACENAR EN EL LOCALSTORAGE EL ID DEL PRODUCTO A QUITAR
	=============================================*/

	if(localStorage.getItem("quitarProductoCaja") == null){

		idQuitarProducto = [];
	
	}else{

		idQuitarProducto.concat(localStorage.getItem("quitarProductoCaja"))

	}

	idQuitarProducto.push({"idProducto":idProducto});

	localStorage.setItem("quitarProductoCaja", JSON.stringify(idQuitarProducto));

	$("button.recuperarBoton[idProducto='"+idProducto+"']").removeClass('btn-default');

	$("button.recuperarBoton[idProducto='"+idProducto+"']").addClass('btn-primary agregarProducto');

	if($(".nuevoProductoCaja").children().length == 0){

		$("#nuevoImpuestoVenta").val(0);
		$("#nuevoPrecioNeto").val(0);
		$("#nuevoTotalVenta").val(0);
		$("#totalVenta").val(0);
		$("#nuevoTotalVenta").attr("total",0);
		$("#totalVentaMetodoPago").val(0);
		$("#nuevoInteresPrecio").val(0);
		$("#nuevoDescuentoPrecio").val(0);

	}else{

		// SUMAR TOTAL DE PRECIOS
    	sumarTotalPrecios();

    	// AGREGAR IMPUESTO
        agregarImpuesto();

        //CALCULAR SI HAY DESCUENTO
        calcularDescuento("nuevoDescuentoPorcentaje");

        //CALCULAR SI HAY INTERES
        calcularInteres("nuevoInteresPorcentaje");

	}

    //AGRUPAR PRODUCTOS EN FORMATO JSON
    listarProductos();

    //RESETEO METODO DE PAGO
    $("#nuevoMetodoPago").prop("selectedIndex", 0).change();

})

/*=============================================
AGREGANDO PRODUCTOS DESDE EL BOTÓN PARA DISPOSITIVOS
=============================================
var numProducto = 0;

$(".btnAgregarProducto").click(function(){

	numProducto ++;

	var datos = new FormData();
	datos.append("traerProductos", "ok");

	$.ajax({

		url:"ajax/productos.ajax.php",
      	method: "POST",
      	data: datos,
      	cache: false,
      	contentType: false,
      	processData: false,
      	dataType:"json",
      	success:function(respuesta){
      	    
  	    	$(".nuevoProducto").append(

          	'<div class="row" style="padding:5px 15px">'+

			  '<!-- Descripción del producto -->'+
	          
	          '<div class="col-xs-6" style="padding-right:0px">'+
	          
	            '<div class="input-group">'+
	              
	              '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs quitarProducto" idProducto><i class="fa fa-times"></i></button></span>'+

	              '<select class="form-control nuevaDescripcionProducto" id="producto'+numProducto+'" idProducto name="nuevaDescripcionProducto" required>'+

	              '<option>Seleccione el producto</option>'+

	              '</select>'+  
	              '<input type="hidden" class="form-control input-sm nuevaCategoria" name="nuevaCategoria" value="'+respuesta["id_categoria"]+'" readonly required>'+


	            '</div>'+

	          '</div>'+

	          '<!-- Cantidad del producto -->'+

	          '<div class="col-xs-3 ingresoCantidad">'+
	            
	             '<input type="number" step="any" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="0" stock nuevoStock required>'+

	          '</div>' +

	          '<!-- Precio del producto -->'+

	          '<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">'+

	            '<div class="input-group">'+

	              '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
	                 
	              '<input type="number" step="any" class="form-control nuevoPrecioProducto" precioReal="" precioCompra="" name="nuevoPrecioProducto" readonly required>'+
	 
	            '</div>'+
	             
	          '</div>'+

	        '</div>');

	        // AGREGAR LOS PRODUCTOS AL SELECT 

	         respuesta.forEach(funcionForEach);

	         function funcionForEach(item, index){

	         	if(item.stock != 0){

		         	$("#producto"+numProducto).append(

						'<option idProducto="'+item.id+'" value="'+item.descripcion+'">'+item.descripcion+'</option>'
		         	)

		         
		         }	         

	         }

        	 // SUMAR TOTAL DE PRECIOS

    		sumarTotalPrecios()

    		// AGREGAR IMPUESTO
	        
	        agregarImpuesto()

	        // PONER FORMATO AL PRECIO DE LOS PRODUCTOS

	        $(".nuevoPrecioProducto").number(true, 2);


      	}

	})

})
*/
/*=============================================
SELECCIONAR PRODUCTO PARA DISPOSITIVOS MOVILES
=============================================*/
$(".formularioVenta").on("change", "select.nuevaDescripcionProducto", function(){

	var nombreProducto = $(this).val();

	var nuevaDescripcionProducto = $(this).parent().parent().parent().children().children().children(".nuevaDescripcionProducto");

	var nuevoPrecioProducto = $(this).parent().parent().parent().children(".ingresoPrecio").children().children(".nuevoPrecioProducto");

	var nuevaCantidadProducto = $(this).parent().parent().parent().children(".ingresoCantidad").children(".nuevaCantidadProducto");

	var datos = new FormData();
    datos.append("nombreProducto", nombreProducto);

	  $.ajax({

     	url:"ajax/productos.ajax.php",
      	method: "POST",
      	data: datos,
      	cache: false,
      	contentType: false,
      	processData: false,
      	dataType:"json",
      	success:function(respuesta){
      	    
			$(nuevaDescripcionProducto).attr("idProducto", respuesta["id"]);
      	    $(nuevaCantidadProducto).attr("stock", respuesta["stock"]);
      	    $(nuevaCantidadProducto).attr("nuevoStock", Number(respuesta["stock"])-1);
      	    $(nuevoPrecioProducto).val(respuesta["precio_venta"]);
      	    $(nuevoPrecioProducto).attr("precioReal", respuesta["precio_venta"]);

  	      // AGRUPAR PRODUCTOS EN FORMATO JSON
	        listarProductos()

      	}

      })
});

/*=============================================
MODIFICAR LA CANTIDAD
=============================================*/
$(".formularioVenta").on("change", "input.nuevaCantidadProducto", function(){

	var precio = $(this).parent().parent().children(".ingresoPrecio").children().children(".nuevoPrecioProducto");

	var precioFinal = $(this).val() * precio.attr("precioReal");
	
	precio.val(precioFinal);

	var nuevoStock = Number($(this).attr("stock")) - $(this).val();

	$(this).attr("nuevoStock", nuevoStock);

	/*=============================================
	SI LA CANTIDAD ES SUPERIOR AL STOCK REGRESAR VALORES INICIALES
	Modificado, se avisa, pero deja continuar
	=============================================*/
	if(Number($(this).val()) > Number($(this).attr("stock"))){

		//$(this).val(1);

		//$(this).attr("nuevoStock", $(this).attr("stock"));

		//var precioFinal = $(this).val() * precio.attr("precioReal");

		//precio.val(precioFinal);

		//sumarTotalPrecios();

		swal({
	      title: "Sólo hay "+$(this).attr("stock")+" unidades",
	      toast: true,
	      timer: 3000,
	      position: 'top',
	      type: "error",
	      confirmButtonText: "¡Cerrar!"
	    });

	}

	// SUMAR TOTAL DE PRECIOS
	sumarTotalPrecios();

	// AGREGAR IMPUESTO
    agregarImpuesto();

	//CALCULAR SI HAY DESCUENTO
	calcularDescuento("nuevoDescuentoPorcentaje");

	//CALCULAR SI HAY INTERES
	calcularInteres("nuevoInteresPorcentaje");

    // AGRUPAR PRODUCTOS EN FORMATO JSON
    listarProductos();

    //RESETEO METODO DE PAGO
    $("#nuevoMetodoPago").prop("selectedIndex", 0).change();    

});

/*=============================================
CAMBIO DESCUENTO
=============================================*/
$(".nuevoDescuento").bind("keyup", function(e){

	var desdeElemento = e.currentTarget.id;

	calcularDescuento(desdeElemento);

});

/*=============================================
CAMBIO INTERES
=============================================*/
$(".nuevoInteres").bind("keyup", function(e){

	var desdeElemento = e.currentTarget.id;

	calcularInteres(desdeElemento);

});

/*=============================================
SUMAR TODOS LOS PRECIOS
=============================================*/
function sumarTotalPrecios(){

	var precioItem = $(".nuevoPrecioProducto");
	
	var arraySumaPrecio = [];  

	for(var i = 0; i < precioItem.length; i++){

		 arraySumaPrecio.push(Number($(precioItem[i]).val()));

	}

	//en el caso que no haya ningun producto seleccionado, agrego cero (sino se rompe reduce)
	if(arraySumaPrecio.length == 0){ 
		arraySumaPrecio.push(0);
	}

	function sumaArrayPrecios(total, numero){

		return total + numero;

	}

	var sumaTotalPrecio = arraySumaPrecio.reduce(sumaArrayPrecios);
	
	$("#nuevoTotalVenta").val(sumaTotalPrecio);
	$("#totalVenta").val(sumaTotalPrecio);
	$("#nuevoTotalVenta").attr("total",sumaTotalPrecio);
	$("#totalVentaMetodoPago").val(sumaTotalPrecio);

}

/*=============================================
FUNCIÓN AGREGAR IMPUESTO
=============================================*/
function agregarImpuesto(){

	var impuesto = $("#nuevoImpuestoVenta").val();

	var precioTotal = $("#nuevoTotalVenta").attr("total");

	var precioImpuesto = Number(precioTotal * impuesto/100);

	var totalConImpuesto = Number(precioImpuesto) + Number(precioTotal);
	
	$("#nuevoTotalVenta").val(totalConImpuesto);

	$("#totalVenta").val(totalConImpuesto);

	$("#nuevoPrecioImpuesto").val(precioImpuesto);

	$("#nuevoPrecioNeto").val(precioTotal);

}

/*=============================================
FUNCION CALCULAR DESCUENTOS
=============================================*/
function calcularDescuento(elem){

	if($("#nuevoMetodoPago").val() == "Efectivo" || $("#nuevoMetodoPago").val() == "TD") {

		var precioNeto = $("#nuevoPrecioNeto").val();

		var descuentoPorcentaje = $("#nuevoDescuentoPorcentaje").val();

		var descuentoPrecio = $("#nuevoDescuentoPrecio").val();

		var totalConDescuento = 0;

		if(elem == "nuevoDescuentoPorcentaje"){
			//llamado desde importe de descuento

			var nuevoDescPrec = descuentoPorcentaje * precioNeto / 100;

			$("#nuevoDescuentoPrecio").val(nuevoDescPrec);

			totalConDescuento = precioNeto - nuevoDescPrec;

		} else {
			//llamado desde importe de precio

			var nuevoDescPorc = descuentoPrecio * 100 / precioNeto;

			$("#nuevoDescuentoPorcentaje").val(nuevoDescPorc);

			totalConDescuento = precioNeto - descuentoPrecio;

		}

		$("#nuevoTotalVenta").val(Number(totalConDescuento).toFixed(2));
		$("#totalVenta").val(Number(totalConDescuento).toFixed(2));
		$("#nuevoTotalVenta").attr("total",totalConDescuento);
		$("#totalVentaMetodoPago").val(Number(totalConDescuento).toFixed(2));

	}

}

/*=============================================
FUNCION CALCULAR INTERES
=============================================*/
function calcularInteres(elem) {

	if($("#nuevoMetodoPago").val() == "TC") {

		var precioNeto = $("#nuevoPrecioNeto").val();

		var interesPorcentaje = $("#nuevoInteresPorcentaje").val();

		var interesPrecio = $("#nuevoInteresPrecio").val();

		var totalConInteres = 0;

		if(elem == "nuevoInteresPorcentaje"){
			//llamado desde importe de descuento

			var nuevoIntPrec = Number(interesPorcentaje * precioNeto / 100);

			$("#nuevoInteresPrecio").val(nuevoIntPrec);

			totalConInteres = Number(precioNeto) + Number(nuevoIntPrec);
		
		} else {
			//llamado desde importe de precio

			var nuevoIntPorc = Number(interesPrecio * 100 / precioNeto);

			$("#nuevoInteresPorcentaje").val(nuevoIntPorc);

			totalConInteres = Number(precioNeto) + Number(interesPrecio);

		}

		$("#nuevoTotalVenta").val(Number(totalConInteres).toFixed(2));
		$("#totalVenta").val(Number(totalConInteres).toFixed(2));
		$("#nuevoTotalVenta").attr("total",totalConInteres);
		$("#totalVentaMetodoPago").val(Number(totalConInteres).toFixed(2));
	}

}

/*=============================================
CUANDO CAMBIA EL IMPUESTO
=============================================*/
$("#nuevoImpuestoVenta").change(function(){

	agregarImpuesto();

});

/*=============================================
FORMATO AL PRECIO FINAL
=============================================*/
//$("#nuevoTotalVenta").number(true, 2);

/*=============================================
SELECCIONAR MEDIO DE PAGO
=============================================*/
$("#nuevoMetodoPago").change(function(){

	cambiarMetodoPago($(this));

});

/*=============================================
FUNCION CAMBIO METODO DE PAGO
=============================================*/
function cambiarMetodoPago(valorMetodo) {

	var metodo = valorMetodo.val();

	var precioTotal = $("#nuevoPrecioNeto").val();

	$("#listaMetodoPago").val('');

	if($("#estoyEditando").val() == 0) {

		limpiarCajasMedioPago();

	} else {

		$("#estoyEditando").val(0);
	}

	$("#nuevoTotalVenta").val(precioTotal);

	valorMetodo.parent().parent().parent().children('.cajasMetodoPago').html(
	
	 	'<div class="col-xs-6" style="padding-left:0px">'+
		           
           
        '</div>');

	if(metodo == "Efectivo"){

		$("#filaDescuento").css("display", ""); //Muestro Fila con inputs de descuento

		var totalConImpuesto = Number(precioTotal);

		$("#nuevoTotalVenta").val(totalConImpuesto.toFixed(2));

		$("#totalVenta").val(totalConImpuesto.toFixed(2));

		$("#nuevoPrecioImpuesto").val(0); //PARA QUE SE USA????

		$(this).parent().parent().parent().children(".cajasMetodoPago").html(

		 	'<div class="col-xs-6" style="padding-left:0px">'+


            '</div>');

	}

	if(metodo == "TD"){

		if($("#codigoTransaccion").length > 0){

			codTransaccion = $("#codigoTransaccion").val();

		} else {

			codTransaccion = "";
		}

		$("#filaDescuento").css("display", "");
		
		$("#nuevoTotalVenta").val(precioTotal);

		valorMetodo.parent().parent().parent().children('.cajasMetodoPago').html(

		 	'<div class="col-xs-6" style="padding-left:0px">'+
                        
                '<div class="input-group">'+
                     
                  '<input type="number" min="0" class="form-control" id="nuevoCodigoTransaccion" placeholder="Código transacción" value="'+codTransaccion+'">'+
                       
                  '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+
                  
                '</div>'+

              '</div>');

	}

	if(metodo == "TC"){

		if($("#codigoTransaccion").length > 0){

			codTransaccion = $("#codigoTransaccion").val();

		} else {

			codTransaccion = "";
		}

		$("#filaInteres").css("display", "");
		
		$("#nuevoTotalVenta").val(precioTotal);

		valorMetodo.parent().parent().parent().children('.cajasMetodoPago').html(

		 	'<div class="col-xs-6" style="padding-left:0px">'+
                        
                '<div class="input-group">'+
                     
                  '<input type="number" min="0" class="form-control" id="nuevoCodigoTransaccion" placeholder="Código transacción" value="'+codTransaccion+'">'+
                       
                  '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+
                  
                '</div>'+

              '</div>');

	} 

	if(metodo == "CC"){

		if($("#seleccionarCliente").val()==1){

			swal({
			      title: "Ventas",
			      text: "Debe seleccionar cliente",
			      type: "error",
			      confirmButtonText: "¡Cerrar!"
			    });

			$("#nuevoMetodoPagoCaja").prop("selectedIndex", 0);

			return false;
		}

		$("#nuevoTotalVentaCaja").val(precioTotal);

	}

	$("#nuevoDescuentoPorcentaje").keyup();

	listarMetodos();	

}


/*=============================================
FUNCION LIMPIAR MEDIOS DE PAGO
=============================================*/
function limpiarCajasMedioPago(){

	var cero = 0;

	//Oculto filas descuento e interes
	$("#filaInteres").css("display", "none");
	$("#filaDescuento").css("display", "none");

	//Reseteo cajas descuento
	$("#nuevoDescuentoPorcentaje").val(cero.toFixed(2));
	$("#nuevoDescuentoPrecio").val(cero.toFixed(2));
	
	//Reseteo cajas interes
	$("#nuevoInteresPorcentaje").val(cero.toFixed(2));
	$("#nuevoInteresPrecio").val(cero.toFixed(2));
	
	//Reseteo cajas total
	$("#totalVentaMetodoPago").val($('#nuevoPrecioNeto').val());
	$("#totalVenta").val($('#nuevoPrecioNeto').val());
	$("#nuevoTotalVenta").val($('#nuevoPrecioNeto').val());

}

/*=============================================
CAMBIO TRANSACCIÓN
=============================================*/
$(".formularioVenta").on("change", "input#nuevoCodigoTransaccion", function(){

	// Listar método en la entrada
     listarMetodos()

});

/*=============================================
LISTAR TODOS LOS PRODUCTOS
=============================================*/
function listarProductos(){

	var listaProductos = [];

	var descripcion = $(".nuevaDescripcionProducto");

	var cantidad = $(".nuevaCantidadProducto");

	var precio = $(".nuevoPrecioProducto");

	var categoria = $(".nuevaCategoria");

	for(var i = 0; i < descripcion.length; i++){

		listaProductos.push({ "id" : $(descripcion[i]).attr("idProducto"), 
							  "descripcion" : $(descripcion[i]).val(),
							  "cantidad" : $(cantidad[i]).val(),
							  "categoria" : $(categoria[i]).val(),
							  "stock" : $(cantidad[i]).attr("nuevoStock"),
							  "precio_compra" : $(precio[i]).attr("precioCompra"),
							  "precio" : $(precio[i]).attr("precioReal"),
							  "total" : $(precio[i]).val()})

	}

	$("#listaProductos").val(JSON.stringify(listaProductos)); 

}

/*=============================================
LISTAR MÉTODO DE PAGO
=============================================*/
function listarMetodos(){

	var listaMetodos = "";

	switch($("#nuevoMetodoPago").val()) {

		case "Efectivo":

			$("#listaMetodoPago").val("Efectivo");
    			
    	break;

	  	case "TD":
	    	
	    	$("#listaMetodoPago").val($("#nuevoMetodoPago").val()+"-"+$("#nuevoCodigoTransaccion").val());

	    break;

	    case "TC":

	    	$("#listaMetodoPago").val($("#nuevoMetodoPago").val()+"-"+$("#nuevoCodigoTransaccion").val());

	    break;

	}

}

/*=============================================
FUNCIÓN PARA DESACTIVAR LOS BOTONES AGREGAR CUANDO EL PRODUCTO YA HABÍA SIDO SELECCIONADO EN LA CARPETA
=============================================*/
function quitarAgregarProducto(){

	//Capturamos todos los id de productos que fueron elegidos en la venta
	var idProductos = $(".quitarProductoCaja");

	//Capturamos todos los botones de agregar que aparecen en la tabla
	var botonesTabla = $("#tablaVentas tbody button.agregarProducto");

	//Recorremos en un ciclo para obtener los diferentes idProductos que fueron agregados a la venta
	for(var i = 0; i < idProductos.length; i++){

		//Capturamos los Id de los productos agregados a la venta
		var boton = $(idProductos[i]).attr("idProducto");
		
		//Hacemos un recorrido por la tabla que aparece para desactivar los botones de agregar
		for(var j = 0; j < botonesTabla.length; j ++){

			//compruebo que el boton a deahabilitar este seleccionado y que no sea el producto 1 (libre)
			if($(botonesTabla[j]).attr("idProducto") == boton && boton != 1){

				$(botonesTabla[j]).removeClass("btn-primary agregarProducto");
				$(botonesTabla[j]).addClass("btn-default");

			}
		}

	}
	
}

/*=============================================
CADA VEZ QUE CARGUE LA TABLA CUANDO NAVEGAMOS EN ELLA EJECUTAR LA FUNCIÓN:
=============================================*/
$('#tablaVentas').on( 'draw.dt', function(){

	quitarAgregarProducto();

});

/*=============================================
CON TECLA ENTER AGREGA EL PRODUCTO ENCONTRADO A LA LISTAPRODUCTOS
=============================================*/
//$("div.dataTables_filter input").keyup( function (e) {
$("#tablaVentas_filter input").keyup( function (e) {

    if (e.keyCode == 13 || e.which == 13) {

    	var idProducto = $(this).val();
    	var cantidad = 1;
    	var stockSucursal = $("#sucursalVendedor").val();
    	var tipoPrecio = $('#radioPrecio').val(); 

        if(stockSucursal === "" || tipoPrecio === ""){
            swal({
        	   title: "Error",
        	   text: "El usuario debe definir sucursal y lista de precio",
        	   toast: true,
        	   timer: 3000,
        	   position: 'top',
        	   type: "error",
        	   confirmButtonText: "¡Cerrar!"
        	 });
        	return;
        }

    	//$(this).removeClass("btn-primary agregarProducto");
    
    	//$(this).addClass("btn-default");

		var datos = new FormData();
	    datos.append("codigoProducto", idProducto);

 		$.ajax({

	     	url:"ajax/productos.ajax.php",
	      	method: "POST",
	      	data: datos,
	      	cache: false,
	      	contentType: false,
	      	processData: false,
	      	dataType:"json",
	      	success:function(respuesta) {
			    //console.log(respuesta);
	      		if(respuesta) {

	      			var precioVta = 0;
					var precioNeto = 0;
	          		var iva = Number(respuesta["tipo_iva"]);
	          		var ivaValor = 0;
	      			var stock = respuesta[stockSucursal]; //TOMO EL STOCK DE LA SUCURSAL ASIGNADA AL USUARIO LOGUEADO

	      			if(respuesta[tipoPrecio] == 0) {

						(async()=> {
							const { value: formValues } = await swal({
							  title: 'Ingrese importe',
							  html:'<p>'+respuesta["codigo"]+'-'+respuesta["descripcion"]+'</p> <div class="input-group"><span class="input-group-addon"><i class="ion ion-social-usd"></i></span><input type="number" id="swal-input1" class="form-control" ></div>',
							  focusConfirm: false,
							  preConfirm: () => {
							    const promesa =  new Promise((resolve, reject) => { 
							    	resolve (document.getElementById('swal-input1').value); 
							    });

							    promesa.then(values => {

							    values = Number(values);
							    precioNeto = (values / ((iva / 100) + 1)) * cantidad; //obtengo el neto
							    ivaValor = (values * cantidad) - precioNeto;          //obtengo el iva

		    					$(".nuevoProductoCaja").prepend(

								'<div class="row" style="padding-left:25px;padding-bottom:5px;">'+

						         '<!-- Cantidad del producto -->'+
						          '<div class="col-xs-2 nuevaCantidad">'+						            
						             '<input type="text" autocomplete="off" style="text-align:center;" class="form-control input-sm nuevaCantidadProductoCaja" stock="0" nuevoStock="0" min="0" value="'+cantidad+'" required>'+
						          '</div>'+

								'<!-- descripcion producto -->'+
								 '<div class="col-xs-6" style="padding-right:0px">'+
						            '<div class="input-group">'+						              
						              '<span class="input-group-btn"><button type="button" class="btn btn-danger btn-sm quitarProductoCaja" idProducto="'+respuesta['id']+'"><i class="fa fa-times"></i></button></span>'+
						              '<input type="text" autocomplete="off" class="form-control input-sm nuevaDescripcionProductoCaja" idProducto="'+respuesta['id']+'"  value="'+respuesta['descripcion']+'" required>'+
									  '<input type="hidden" class="nuevaCategoria" value="'+respuesta["id_categoria"]+'">'+
						            '</div>'+
						          '</div>'+

								'<!-- precio unitario -->'+
								'<div class="col-xs-2 nuevoPrecio">'+
								   '<div class="input-group">'+
						             '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
									 '<input type="text" style="text-align:center;" class="form-control input-sm nuevaPrecioUnitario" name="nuevaPrecioUnitario" value="'+values+'"  required>'+
								  '</div>'+
								'</div>'+

						          '<!-- Precio total -->'+
						          '<div class="col-xs-2 ingresoPrecio" style="padding-left:0px">'+
						            '<div class="input-group">'+
						              '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
					  				  '<input type="hidden" class="nuevoTipoIvaValorProducto" value="'+ivaValor+'" netoUnitario="'+precioNeto+'" tipoIva="'+iva+'" cantxIva="1">'+
						              '<input type="hidden" class="nuevoValorTipoIva" value="'+iva+'">'+
									  '<input type="text" class="form-control input-sm nuevoPrecioProductoCaja" precioReal="'+values+'" precioCompra="'+respuesta["precio_compra"]+'" style="text-align:center;" name="nuevoPrecioProductoCaja" value="'+redondear(values*cantidad)+'" required>'+
						            '</div>'+
						          '</div>'+

						        '</div>');

							    })

							  }
							})

						sumarTotalPreciosCaja();

						//CALCULAR SI HAY DESCUENTO
				        calcularDescuentoCaja("nuevoDescuentoPorcentajeCaja");

				        //CALCULAR SI HAY INTERES
				        calcularInteresCaja("nuevoInteresPorcentajeCaja");

				        // PONER FORMATO AL PRECIO DE LOS PRODUCTOS
						listarProductosCaja();

						//RESETEO METODO DE PAGO
		        		$("#nuevoMetodoPagoCaja").prop("selectedIndex", 0).change();

				        $(".nuevoPrecioProductoCaja").number(true, 2);

						localStorage.removeItem("quitarProductoCaja");

						$("#ventaCajaDetalleHidden").val("");
						$("#ventaCajaDetalle").val("");
						$("#autocompletarProducto").val("");
						$("#ventaCajaCantidad").val("1");
						$("#ventaCajaDetalle").focus();						

						})()

	      			} else {

	      				precioVta = respuesta[tipoPrecio];

	      				var precioXCantidad = precioVta * cantidad;
	      				
	      				precioNeto = precioVta / ((iva / 100) + 1); //precio neto para cantidad 1
	      				//precioNeto = precioXCantidad / ((iva / 100) + 1); //precio neto para cantidad mayor a 1
	      				console.log(precioNeto);
	      				// ivaValor = precioXCantidad - precioXCantidad / (1 + (iva / 100));
	      				// console.log(ivaValor);
	      				ivaValor = (precioNeto * iva / 100); // * cantidad;
	      				//ivaValor = redondear(ivaValor,2);
	      				console.log(ivaValor);
						console.log(precioXCantidad);

	      				$(".nuevoProductoCaja").prepend(

						'<div class="row" style="padding-left:25px;padding-bottom:5px;">'+

						 '<!-- Cantidad del producto -->'+
				          '<div class="col-xs-2 nuevaCantidad">'+
				             '<input type="text" autocomplete="off" style="text-align:center;" class="form-control input-sm nuevaCantidadProductoCaja" stock="'+stock+'" nuevoStock="'+Number(stock-1)+'" min="0" value="'+cantidad+'"  required>'+
				          '</div>'+
						  
						  '<!-- Descripción del producto -->'+				          
				          '<div class="col-xs-6" style="padding-right:0px">'+
				            '<div class="input-group">'+
				              '<span class="input-group-btn"><button type="button" class="btn btn-danger btn-sm quitarProductoCaja" idProducto="'+respuesta['id']+'"><i class="fa fa-times"></i></button></span>'+
				              '<input type="text" class="form-control input-sm nuevaDescripcionProductoCaja" idProducto="'+respuesta['id']+'"  value="'+respuesta['descripcion']+'" readonly>'+
							  '<input type="hidden" class="nuevaCategoria" value="'+respuesta["id_categoria"]+'">'+
				            '</div>'+
				          '</div>'+

						  '<!-- precio unitario del producto -->'+
				          '<div class="col-xs-2 nuevoPrecio">'+
					            '<div class="input-group">'+
								 '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
					             '<input type="text" style="text-align:center;" class="form-control input-sm nuevaPrecioUnitario" name="nuevaPrecioUnitario" value="'+precioVta+'" readonly>'+
				            	'</div>'+
							'</div>'+
							 
				          '<!-- Precio total del producto -->'+
				          '<div class="col-xs-2 ingresoPrecio" style="padding-left:0px">'+
				            '<div class="input-group">'+
				              '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
			  				  '<input type="hidden" class="nuevoTipoIvaValorProducto" value="'+ivaValor+'" netoUnitario="'+precioNeto+'" tipoIva="'+iva+'" cantxIva="'+cantidad+'" readonly required>'+
							  '<input type="hidden" class="nuevoValorTipoIva" value="'+iva+'" readonly required>'+
				              '<input type="text" class="form-control input-sm nuevoPrecioProductoCaja" precioReal="'+precioVta+'" precioCompra="'+respuesta["precio_compra"]+'" style="text-align:center;" name="nuevoPrecioProductoCaja" value="'+precioXCantidad+'" required readonly>'+
				            '</div>'+
				          '</div>'+

				        '</div>');

				        sumarTotalPreciosCaja();

						//CALCULAR SI HAY DESCUENTO
				        calcularDescuentoCaja("nuevoDescuentoPorcentajeCaja");

				        //CALCULAR SI HAY INTERES
				        calcularInteresCaja("nuevoInteresPorcentajeCaja");

				        // PONER FORMATO AL PRECIO DE LOS PRODUCTOS
						listarProductosCaja();

						//RESETEO METODO DE PAGO
		        		$("#nuevoMetodoPagoCaja").prop("selectedIndex", 0).change();

				        $(".nuevoPrecioProductoCaja").number(true, 2);

						localStorage.removeItem("quitarProductoCaja");

						$("#ventaCajaDetalleHidden").val("");
						$("#ventaCajaDetalle").val("");
						$("#autocompletarProducto").val("");
						$("#ventaCajaCantidad").val("1");
						$("#ventaCajaDetalle").focus();

	      			}

				} else {

					swal({
				      title: "Ventas",
				      text: "No se encontró el producto",
					  type: "warning",
					  toast: true,
					  position: 'top',
					  showConfirmButton: false,
					  timer: 3000
					});

					/*$("#nuevoCodigoCaja").val(idProducto);

					$("#modalAgregarProductoCaja").modal('show');
					
					$('#modalAgregarProductoCaja').on('shown.bs.modal', function () {
					    $("#nuevaDescripcionCaja").focus();
					})*/  

				}
			} 

 		});


		$("#tablaVentas_filter input").val("");

		$("#tablaVentas_filter input").focus();

		$("#tablaVentas_filter input").keyup();

	}
	
});

/*=============================================
DETALLES PRODUCTO
=============================================*/
// $("#tablaVentas tbody").on("click", "div.detalleProductoVentas", function(){

// 	var idProducto = $(this).attr("idProducto");

// 	var datos = new FormData();
//     datos.append("idProducto", idProducto);

//      $.ajax({

//       url:"ajax/productos.ajax.php",
//       method: "POST",
//       data: datos,
//       cache: false,
//       contentType: false,
//       processData: false,
//       dataType:"json",
//       success:function(respuesta){

//       	var cate = respuesta["id_categoria"];
//       	var datosCate = new FormData();
//     	datosCate.append("idCategoria", cate);

//     	$.ajax({

// 	      url:"ajax/categorias.ajax.php",
// 	      method: "POST",
// 	      data: datosCate,
// 	      cache: false,
// 	      contentType: false,
// 	      processData: false,
// 	      dataType:"json",
// 	      success:function(respuestaCate){
// 	      	$("#modDetProdCategoria").val(respuestaCate["categoria"]);
// 	      }
// 	  	});

// 	  	var marc = respuesta["id_marca"];
//       	var datosMarc = new FormData();
//     	datosMarc.append("idMarca", marc);

//     	$.ajax({

// 	      url:"ajax/marcas.ajax.php",
// 	      method: "POST",
// 	      data: datosMarc,
// 	      cache: false,
// 	      contentType: false,
// 	      processData: false,
// 	      dataType:"json",
// 	      success:function(respuestaMarc){
// 	      	$("#modDetProdMarca").val(respuestaMarc["marca"]);
// 	      }
// 	  	});

//     	switch (respuesta["id_proveedor"]) {
//           case "1":$("#modDetProdProveedor").val("Autonáutico Sur (01)"); break;
//           case "2":$("#modDetProdProveedor").val("Saneco (02)"); break;
//           case "3":$("#modDetProdProveedor").val("Nucleo (03)"); break;
//           case "4":$("#modDetProdProveedor").val("Audioinsumos (04)"); break;
//           case "5":$("#modDetProdProveedor").val("Rojo (05)"); break;
//           case "6":$("#modDetProdProveedor").val("Campagna (06)"); break;
//           case "7":$("#modDetProdProveedor").val("Joel (07)"); break;
//           case "8":$("#modDetProdProveedor").val("Alonso (08)"); break;
//           case "9":$("#modDetProdProveedor").val("Bagini S.A. (09)"); break;
//           case "10":$("#modDetProdProveedor").val("Nipro (10)"); break;
//           case "11":$("#modDetProdProveedor").val("Infoandina (11)"); break;
//           case "12":$("#modDetProdProveedor").val("CCH Comax (12)"); break;
//           case "13":$("#modDetProdProveedor").val("Dialer (13)"); break;
//           case "14":$("#modDetProdProveedor").val("Jahro (14)"); break;
//           case "15":$("#modDetProdProveedor").val("ART (15)"); break;
//           case "16":$("#modDetProdProveedor").val("Kompusur (16)"); break;
//           case "99":$("#modDetProdProveedor").val("Varios (99)"); break;
//         }
        
// 		   $("#modDetProdCodigo").val(respuesta["codigo"]);
// 		   $("#modDetProdCodigoProv").val(respuesta["codigo_proveedor"]);
// 		   //$("#modDetProdProveedor").val(respuesta["id_proveedor"]);
// 		   // $("#modDetProdCategoria").val(respuesta["id_categoria"]);
// 	       //$("#modDetProdMarca").val(respuesta["id_marca"]);
// 	       $("#modDetProdDescripcion").val(respuesta["descripcion"]);
// 	       $("#modDetProdInfoAdicional").val(respuesta["informacion_extra"]);
// 	       $("#modDetProdTotal").val(respuesta["stock"]);
// 	       $("#modDetProdPrecioVenta").val('$' + respuesta["precio_venta"]);
//            if(respuesta["imagen"] != ""){
// 	           	$("#modDetProdImagen").attr("src", respuesta["imagen"]);
//            } else {
//            		$("#modDetProdImagen").attr("src", "vistas/img/productos/default/anonymous.png");
//            }

//       }

//   })

// });


$('#nuevaFecEmision').datepicker().datepicker("setDate", new Date());
$('#nuevaFecEmision').datepicker( "option", "dateFormat", "dd/mm/yy" );

//$('#editarFecEmision').datepicker().datepicker({ dateFormat: 'dd-mm-yy' }).val($('#editarFecEmision').val());
//$('#editarFecEmision').datepicker( "option", "dateFormat", "dd/mm/yy" );

$('.editaFecServicios').datepicker();
$('.nuevaFecServicios').datepicker();
$('.nuevaFecServicios').datepicker( "option", "dateFormat", "dd/mm/yy" );

cambiaConcepto();

$('.selectConcepto').change(function(){
	cambiaConcepto();
});

function cambiaConcepto(){
	if($('.selectConcepto').val() == 1 || $('.selectConcepto').val() == 0) {
		$('.lineaServicio').hide();	
		$('.nuevaFecServicios').prop('required',false);
		$('.nuevaFecServicios').val('');
		$('.editaFecServicios').val('');
		// $('#nuevaFecDesde').prop('required',false);
		// $('#nuevaFecHasta').prop('required',false);
		// $('#nuevaFecVto').prop('required',false);
	} else {
		$('.lineaServicio').show();
		$('.nuevaFecServicios').prop('required',true);
		$('.nuevaFecServicios').datepicker("setDate", new Date());	
		// $('#nuevaFecDesde').prop('required',true);
		// $('#nuevaFecHasta').prop('required',true);
		// $('#nuevaFecVto').prop('required',true);
		//$("input").prop('required',true);
	} 
}

/*=============================================
LISTAR VENTAS (ventas.php)
=============================================*/
//AGREGA UN INPUT TEXT PARA BUSCAR EN CADA COLUMNA
$("#tablaListarVentas tfoot th").each(function (i) {
  var title = $(this).text();
  if(title != ""){
    $(this).html('<input type="text" size="5" placeholder="Filtro ' + title + '" />');
  }

});

/*=============================================
TABLA LISTAR VENTAS (ventas.php)
=============================================*/
var tablaListarVtas = $("#tablaListarVentas").DataTable({
	"order": [[ 0, "desc" ]],
	"pageLength": 50,
	"language": GL_DATATABLE_LENGUAJE,
	"dom": 'Bfrtip',
	"buttons": GL_DATATABLE_BOTONES,
	"footerCallback": function (row, data, start, end, display) {
          
            var api = this.api();

            var intVal = function (i) {
                return typeof i === 'string' ?
                    i.replace(/[\$]/g, '').replace(/,/g, '.') * 1 :
                    typeof i === 'number' ?
                        i : 0;
            };

            var total = api
                .column(7)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            var totalPage = api
                .column(7, {search:'applied'})
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            $(api.column(7).footer()).html(
                ` ${totalPage.toFixed(2)}`
            )

        }

});

tablaListarVtas.columns().every(function () {
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
BOTON EDITAR VENTA
=============================================*/
$("#tablaListarVentas").on("click", ".btnEditarVenta", function(){

	var idVenta = $(this).attr("idVenta");

	window.location = "index.php?ruta=editar-venta&idVenta="+idVenta;


});

/*=============================================
BORRAR VENTA
=============================================*/
$("#tablaListarVentas").on("click", ".btnEliminarVenta", function(){

  var idVenta = $(this).attr("idVenta");

  swal({
        title: '¿Está seguro de borrar la venta?',
        text: "¡Si no lo está puede cancelar la accíón!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Si, borrar venta!'
      }).then(function(result){
        if (result.value) {
            window.location = "index.php?ruta=ventas&idVenta="+idVenta;
        }
  })

})

//DESCARGAR FACTURA
$("#tablaListarVentas").on("click", ".btnDescargarFactura", function(){
	var codigoVenta = $(this).attr("codigoVenta");
	window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/comprobante.php?descargarFactura=true&codigo="+codigoVenta, "_blank");
});

//IMPRIMIR FACTURA
$("#tablaListarVentas").on("click", ".btnImprimirFactura", function(){
	var codigoVenta = $(this).attr("codigoVenta");
	window.open("comprobante/"+codigoVenta, "_blank");
});

//IMPRIMIR REMITO
$("#tablaListarVentas").on("click", ".btnImprimirRemito", function(){
	var codigoVenta = $(this).attr("codigoVenta");
	window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/remito.php?codigo="+codigoVenta, "_blank");
});

//IMPRIMIR TICKET
$("#tablaListarVentas").on("click", ".btnImprimirTicket", function(){
	var datosVentaCaja = new FormData();
	datosVentaCaja.append("idVentaConCliente", $(this).attr("idVenta"));
	$.ajax({
     	url:"ajax/ventas.ajax.php",
      	method: "POST",
      	data: datosVentaCaja,
      	cache: false,
      	contentType: false,
      	processData: false,
      	dataType:"json",
      	success:function(respuesta){
      		console.log(respuesta);
      		var jsonProductos = JSON.parse(respuesta["productos"]);
      		var subto = 0;
      		$("#tckDetalleVentaCaja").empty();
	        for(var i = 0; i < jsonProductos.length; i++){
            	subto = subto + Number(jsonProductos[i]["total"]);
                $("#tckDetalleVentaCaja").append("<tr><td><center>"+jsonProductos[i]["cantidad"]+" * $" + redondear(jsonProductos[i]["precio"],2) +  "</center></td><td><center>"+jsonProductos[i]["descripcion"]+"</center></td><td><center>$ "+redondear(jsonProductos[i]["total"],2)+"</center></td></tr>")
            }
            //$("#tckControlCbte").text($("#nuevaVentaCaja").val());
            $("#tckDatosFacturaFecha").text('FECHA: ' + respuesta["fecha"]);
            var tipoDoc ="";
            switch(Number(respuesta["tipo_documento"])){
                case 96: tipoDoc = "DNI"; break;
                case 80: tipoDoc = "CUIT"; break;
                case 86: tipoDoc = "CUIL"; break;
                case 87: tipoDoc = "CDI"; break;
                case 89: tipoDoc = "LE"; break;
                case 90: tipoDoc = "LC"; break;
                case 92: tipoDoc = "En trámite"; break;
                case 93: tipoDoc = "Acta nacimiento"; break;
                case 94: tipoDoc = "Pasaporte"; break;
                case 91: tipoDoc = "CI extranjera"; break;
                default: tipoDoc = "Otro"; break;
            }
        	$("#tckDatosFacturaNumDoc").text(tipoDoc + ' N°: ' + respuesta["documento"]);
        	$("#tckDatosFacturaNombreCliente").text(' ' + respuesta["nombre"]);

            $("#tckSubtotalVentaCaja").text(redondear(subto,2));
            
            //medio pago
            var tipoMedioElegido =  JSON.parse(respuesta["metodo_pago"]);
            var medioEvaluado = "";
            var tckMedio = "<ul>";
            for(let rec = 0; rec < tipoMedioElegido.length; rec++) {
                console.log(tipoMedioElegido[rec]);
                medioEvaluado = tipoMedioElegido[rec]["tipo"].split('-');
                console.log(medioEvaluado[0])
                switch(medioEvaluado[0]){
                    case "TD": tckMedio += "<li>Tarjeta Débito "  + medioEvaluado[1] + " ( $ " + tipoMedioElegido[rec]["entrega"] + ")</li>"; break;
                    case "MP": tckMedio += "<li>Mercado Pago "  + medioEvaluado[1] + " ( $ " + tipoMedioElegido[rec]["entrega"] + ")</li>"; break;
                    case "TC": tckMedio += "<li>Tarjeta Crédito " + medioEvaluado[1] + " ( $ " + tipoMedioElegido[rec]["entrega"] + ")</li>"; break;
                    case "TR": tckMedio += "<li>Transferencia" + " ( $ " + tipoMedioElegido[rec]["entrega"] + ")</li>"; break;
                    case "CC": tckMedio += "<li>Cuenta Corriente" + " ( $ " + tipoMedioElegido[rec]["entrega"] + ")</li>"; break;
                    case "CH": tckMedio += "<li>Cheque" + " ( $ " + tipoMedioElegido[rec]["entrega"] + ")</li>"; break;
                    default: tckMedio += "<li>Efectivo" + " ( $ " + tipoMedioElegido[rec]["entrega"] + ")</li>"; break;
                }
            }
            tckMedio += "</ul>";
            $("#tckMedioPagoVentaCaja").html(tckMedio);

            var tckDto = Number(respuesta["neto"]) - Number(respuesta["total"]);
            $("#campoDtoTexto").text('Descuento');
            if (tckDto != 0) {
             	if(tckDto < 0) $("#campoDtoTexto").text('Recargo');
             	tckDto = Math.abs(tckDto);
             	$("#tckDescuentoVentaCaja").text(redondear(tckDto,2));
            } else {
             	$("#tckDescuentoVentaCaja").text('0,00');
            }
            
            $("#tckTotalVentaCaja").text(respuesta["total"]);

            //TIPO DE TICKET
            var letraCbte = (respuesta["cbte_tipo"] == "999") ? "Devolucion" : "X";
            var ptoVta = respuesta["pto_vta"];
            //venta autorizada
            if (respuesta["cae"]) {
                $("#tckDatosFacturaEmisorReceptor").css('display', '');
                $("#tckDatosFacturaCAE").css('display', '');
            	
            	if(respuesta["cbte_tipo"] == "1" || respuesta["cbte_tipo"] == "2" || respuesta["cbte_tipo"] == "3" || respuesta["cbte_tipo"] == "4") {
            	    //comprobantes A
            		letraCbte = "A";
            		$("#tckDetalleFacturaA").append(
        				'<span>Neto Gravado: $' +respuesta["neto_gravado"]+'</span><br>'); 
                	var facDet = JSON.parse(respuesta["impuesto_detalle"]);
            		for (var i = 0; i < facDet.length; i++) {
            			if(facDet[i].id != 3) {
                			$("#tckDetalleFacturaA").append(
                				'<span>' +
                					facDet[i].descripcion + ' : $' + facDet[i].iva +
                				'</span><br>'
            				);
            			}
            		}
            	} else if(respuesta["cbte_tipo"] == "6" || respuesta["cbte_tipo"] == "7" || respuesta["cbte_tipo"] == "8" || respuesta["cbte_tipo"] == "9") {
                    //Comprobantes B
                	letraCbte = "B";
                	$("#tckDetalleFacturaA").append('<span>IVA contenido (Ley 27.743): $ '+respuesta["impuesto"]+'</span>');
            	} else if(respuesta["cbte_tipo"] == "11" || respuesta["cbte_tipo"] == "12" || respuesta["cbte_tipo"] == "13" || respuesta["cbte_tipo"] == "15") {
                    //Comprobantes C
                	letraCbte = "C";
            	} else if(respuesta["cbte_tipo"] == "201" || respuesta["cbte_tipo"] == "202" || respuesta["cbte_tipo"] == "203") {
                	//factura de credito electronica miPyme
                	letraCbte = "FCE - A"; 
                	$("#tckDetalleFacturaA").append(
            			'<span>Subtotal: $' +respuesta["neto_gravado"]+
						'</span><br>'); 
                	var facDet = JSON.parse(respuesta["impuesto_detalle"]);
            		for (var i = 0; i < facDet.length; i++) {
            			if(facDet[i].id != 3) {
                			$("#tckDetalleFacturaA").append(
                				'<span>' +
                					facDet[i].descripcion + ' : $' + facDet[i].iva +
                				'</span><br>'
            				);
            			}
            		}
            	} else if(respuesta["cbte_tipo"] == "206" || respuesta["cbte_tipo"] == "207" || respuesta["cbte_tipo"] == "208") {
            		//factura de credito electronica miPyme
            		letraCbte = "FCE - B";
            	} else if(respuesta["cbte_tipo"] == "211" || respuesta["cbte_tipo"] == "212" || respuesta["cbte_tipo"] == "213") {
            		//factura de credito electronica miPyme
            		letraCbte = "FCE - C";
            	} else {
            		letraCbte = "";
            	}
            	var letraTipoCbte = "";
				switch(respuesta["cbte_tipo"]){
          			case "1": 
	      			case "6": 
	      			case "11":
	      			case "201":
	      			case "206":
	      			case "211": 
	      				letraTipoCbte = "Factura ";
	     				break;
	      			case "2": 
	      			case "7": 
	      			case "12":
	      			case "202":
	      			case "207":
	      			case "212": 
	      				letraTipoCbte = "Nota débito ";
	      				break;
	      			case "3": 
	       			case "8": 
		   			case "13":
		   			case "203":
	      			case "208":
	      			case "213": 
	      				letraTipoCbte = "Nota crédito ";
	      				break;
	      			case "4": 
	      			case "9": 
	      			case "15":
	      				letraTipoCbte = "Recibo ";
	      				break;
			      	}
			      	letraCbte = letraTipoCbte + letraCbte;
                	var numfac = respuesta["nro_cbte"];
	                $("#tckDatosFacturaNumCbte").text('N°: ' + PadLeft(ptoVta,5) + '-' + PadLeft(numfac,8));
	                $("#tckDatosFacturaNumCAE").text('CAE: ' + respuesta["cae"]);
	                $("#tckDatosFacturaVtoCAE").text('Vto. CAE: ' + respuesta["fec_vto_cae"]);

					//IMPRIMO CODIGO QR
					var fechaQR = respuesta["fecha"];
					fechaQR = fechaQR.split(" ");
					var jsonQR = '{"ver":1,"fecha":"'+fechaQR[0]+'","cuit":'+Number($("#cuitEmpresaEmisora").text())+',"ptoVta":'+ptoVta+',"tipoCmp":'+Number(respuesta["cbte_tipo"])+',"nroCmp":'+numfac+',"importe":'+respuesta["total"]+',"moneda":"PES","ctz":1,"tipoDocRec":'+respuesta["tipo_documento"]+',"nroDocRec":'+respuesta["documento"]+',"tipoCodAut":"E","codAut":'+respuesta["cae"]+'}';
					var urlAFIPQR = 'https://www.afip.gob.ar/fe/qr/?p=' + btoa(jsonQR);
                    document.getElementById("dibujoCodigoQR").innerHTML = '';
					var qrcodePLG = new QRCode(document.getElementById("dibujoCodigoQR"), {
						width : 150,
						height : 150
					});

					qrcodePLG.makeCode(urlAFIPQR);

            } else {
            	$("#tckDatosFacturaEmisorReceptor").css('display', 'none');
            	$("#tckDatosFacturaCAE").css('display', 'none');
            	$("#tckDatosFacturaNumCbte").text('N°: ' + PadLeft(ptoVta,5) + '-' + PadLeft(respuesta["codigo"],8));
            }

            $("#tckDatosFacturaTipoCbte").text(letraCbte);
			$("#btnImprimirA4Control").attr('codigoVta', respuesta["codigo"]);
			$("#btnEnviarMailA4").attr('codigoVta', respuesta["codigo"]);

      	},

		error: function(xhr, status, error) {
			console.log( xhr.responseText);
			console.log( xhr);
			console.log( status);
			console.log( error);
			swal({
		      title: "Ventas",
		      text: "Error (500 - descripcion en consola)",
		      type: "error",
			  toast: true,
			  position: 'top',
			  showConfirmButton: false,
			  timer: 3000
			});
		}
    });

});

/*=============================================
COBRAR VENTA
=============================================*/
$("#tablaListarVentas").on("click", ".btnCobrarVenta", function(){

	var idVenta = $(this).attr("idVenta");

	var datos = new FormData();
    datos.append("idVenta", idVenta);

    $.ajax({

      url:"ajax/ventas.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType:"json",
      success:function(respuesta){
      
     	   $("#ingresoCajaidVenta").val(respuesta["id"]);
     	   $("#ingresoCajaPuntoVenta").val(respuesta["pto_vta"]);
	       $("#ingresoCajaMonto").val(respuesta["total"]);
	       $("#ingresoCajaDescripcion").val("Ingreso por venta - N° " + respuesta["codigo"]);
	       $("#ingresoCajaCodVenta").val(respuesta["codigo"]);
	       $("#ingresoMedioPago").val(respuesta["metodo_pago"]);

	       var metPago = JSON.parse(respuesta["metodo_pago"]);
	       $("#ingresoMedioPagoVisual").val(metPago[0]["tipo"]);
	       //$("#ingresoMedioPagoVisual").val(respuesta["metodo_pago"]);

	  }

  	})

});

/*=============================================
ATUORIZAR COMPROBANTE
=============================================*/
$("#tablaListarVentas").on("click", ".btnAutorizarCbte", function(){

	var idVenta = $(this).attr("idVenta");

	var datos = new FormData();
    datos.append("idVentaConCliente", idVenta);

    console.log(idVenta)

    $.ajax({

      url:"ajax/ventas.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType:"json",
      success:function(respuesta){

     	   $("#autorizarCbteIdVenta").val(respuesta["id"]);
	       $("#autorizarCbteMonto").val(respuesta["total"]);
	       $("#autorizarCbtePtoVta").val(respuesta["pto_vta"]);
	       $("#autorizarCbteCliente").val(respuesta["id_cliente"]);
	       $("#autorizarCbteTipoCbte").val(respuesta["cbte_tipo"]);
	       $("#autorizarCbteCodVenta").val(respuesta["codigo"]);
	       $("#autorizarCbteFecha").val(respuesta["fecha"]);

	       var datosCliente = respuesta["id_cliente"] + "-" +respuesta["nombre"] + " " + respuesta["documento"];
	       $("#autocompletarCliente").val(datosCliente);

	  }

  	})

});

/*=============================================
RANGO DE FECHAS - VENTAS
=============================================*/
$('#daterange-btn').daterangepicker(
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
    $('#daterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

    var fechaInicial = start.format('YYYY-MM-DD');

    var fechaFinal = end.format('YYYY-MM-DD');

    var capturarRango = $("#daterange-btn span").html();
   
   	localStorage.setItem("capturarRango", capturarRango);

   	window.location = "index.php?ruta=ventas&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;

  }

)

/*=============================================
CANCELAR RANGO DE FECHAS VENTAS
=============================================*/
$(".daterangepicker.opensleft .range_inputs .cancelBtn").on("click", function(){

	localStorage.removeItem("capturarRango");
	localStorage.clear();
	window.location = "ventas";

})

/*=============================================
CAPTURAR HOY - VENTAS
=============================================*/
$(".daterangepicker.opensleft .ranges li").on("click", function(){

	var textoHoy = $(this).attr("data-range-key");

	if(textoHoy == "Hoy"){

		var d = new Date();
		
		var dia = d.getDate();
		var mes = d.getMonth()+1;
		var año = d.getFullYear();

		dia = ("0"+dia).slice(-2);
		mes = ("0"+mes).slice(-2);

		var fechaInicial = año+"-"+mes+"-"+dia;
		var fechaFinal = año+"-"+mes+"-"+dia;	

    	localStorage.setItem("capturarRango", "Hoy");

    	window.location = "index.php?ruta=ventas&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;

	}

});

///VENTAS RENTABILIDAD////////////////////////////
/*=============================================
VARIABLE LOCAL STORAGE
=============================================*/
if(localStorage.getItem("rangoVentasRent") != null){

    $("#daterangeVentasRentabilidad span").html(localStorage.getItem("rangoVentasRent"));
    localStorage.removeItem("rangoVentasRent");

}else{

    $("#daterangeVentasRentabilidad span").html('<i class="fa fa-calendar"></i> Rango de fecha')

}

/*=============================================
RANGO DE FECHAS
=============================================*/
$('#daterangeVentasRentabilidad').daterangepicker({
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

    $('#daterangeVentasRentabilidad span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

    var fechaInicial = start.format('YYYY-MM-DD');

    var fechaFinal = end.format('YYYY-MM-DD');

    var capturarRango = $("#daterangeVentasRentabilidad span").html();
   
    localStorage.setItem("rangoVentasRent", capturarRango);

    window.location = "index.php?ruta=ventas-rentabilidad&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;

  })

/*=============================================
CANCELAR RANGO DE FECHAS
=============================================*/
$(".daterangepicker.opensright .range_inputs .cancelBtn").on("click", function(){

    localStorage.removeItem("rangoVentasRent");
    localStorage.clear();
    window.location = "index.php?ruta=ventas-rentabilidad";
})

/*=============================================
CAPTURAR HOY
=============================================*/
$(".daterangepicker.opensright .ranges li").on("click", function(){

    var textoHoy = $(this).attr("data-range-key");

    if(textoHoy == "Hoy"){

    var d = new Date();
    
    var dia = d.getDate();
    var mes = d.getMonth()+1;
    var año = d.getFullYear();

    if(mes < 10){

      var fechaInicial = año+"-0"+mes+"-"+dia;
      var fechaFinal = año+"-0"+mes+"-"+dia;

    }else if(dia < 10){

      var fechaInicial = año+"-"+mes+"-0"+dia;
      var fechaFinal = año+"-"+mes+"-0"+dia;

    }else if(mes < 10 && dia < 10){

      var fechaInicial = año+"-0"+mes+"-0"+dia;
      var fechaFinal = año+"-0"+mes+"-0"+dia;

    }else{

      var fechaInicial = año+"-"+mes+"-"+dia;
        var fechaFinal = año+"-"+mes+"-"+dia;

    } 

    localStorage.setItem("rangoVentasRent", "Hoy");

    window.location = "index.php?ruta=ventas-rentabilidad&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;

    }

});


////LIBRO IVA VENTAS
/*=============================================
RANGO DE FECHAS - VENTAS
=============================================*/
$('#daterange-btnLibroIvaVentas').daterangepicker(
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
    $('#daterange-btnLibroIvaVentas span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

    var fechaInicial = start.format('YYYY-MM-DD');

    var fechaFinal = end.format('YYYY-MM-DD');

    var capturarRango = $("#daterange-btnLibroIvaVentas span").html();
   
   	localStorage.setItem("capturarRangoLibroIva", capturarRango);

   	window.location = "index.php?ruta=libro-iva-ventas&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;

  }

)

/*=============================================
CANCELAR RANGO DE FECHAS VENTAS
=============================================*/
$(".claseRangoLibroIva .daterangepicker.opensleft .range_inputs .cancelBtn").on("click", function(){

	localStorage.removeItem("capturarRangoLibroIva");
	localStorage.clear();
	window.location = "ventas";

})

/*=============================================
CAPTURAR HOY - VENTAS
=============================================*/
$(".claseRangoLibroIva .daterangepicker.opensleft .ranges li").on("click", function(){

	var textoHoy = $(this).attr("data-range-key");

	if(textoHoy == "Hoy"){

		var d = new Date();
		
		var dia = d.getDate();
		var mes = d.getMonth()+1;
		var año = d.getFullYear();

		dia = ("0"+dia).slice(-2);
		mes = ("0"+mes).slice(-2);

		var fechaInicial = año+"-"+mes+"-"+dia;
		var fechaFinal = año+"-"+mes+"-"+dia;	

    	localStorage.setItem("capturarRangoLibroIva", "Hoy");

    	window.location = "index.php?ruta=libro-iva-ventas&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;

	}

});

/*=============================================
BOTON EDITAR VENTA
=============================================*/
$("#tablaListarPresupuestos").on("click", ".btnPresupuestoAVenta", function(){

	var idPresupuesto = $(this).attr("idPresupuesto");

	window.location = "index.php?ruta=presupuesto-venta&idPresupuesto="+idPresupuesto;


});

/*=============================================
LISTAR PRESUPUESTOS (presupuestos.php)
=============================================*/
//AGREGA UN INPUT TEXT PARA BUSCAR EN CADA COLUMNA
$("#tablaListarPresupuestos tfoot th").each(function (i) {
  var title = $(this).text();
  if(title != ""){
    $(this).html('<input type="text" placeholder="Filtrar por ' + title + '" />');
  }

});

/*=============================================
TABLA LISTAR VENTAS (ventas.php)
=============================================*/
var tablaListarPresupuestos = $("#tablaListarPresupuestos").DataTable({
    "order": [[ 0, "desc" ]],
    "pageLength": 50,
	"language": GL_DATATABLE_LENGUAJE,
    "dom": 'Bfrtip',
    "buttons": GL_DATATABLE_BOTONES

});

tablaListarPresupuestos.columns().every(function () {
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
BORRAR VENTA
=============================================*/
$("#tablaListarPresupuestos").on("click", ".btnEliminarPresupuesto", function(){

  var idPresupu = $(this).attr("idPresupuesto");

  swal({
        title: '¿Está seguro de borrar el presupuesto?',
        text: "¡Si no lo está puede cancelar la accíón!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Si, borrar venta!'
      }).then(function(result){
        if (result.value) {
          
            window.location = "index.php?ruta=presupuestos&idPresupuesto="+idPresupu;
        }

  })

})

/*=============================================
RANGO DE FECHAS - VENTAS
=============================================*/
$('#PresupuestosDaterange-btn').daterangepicker(
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
    $('#PresupuestosDaterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

    var fechaInicial = start.format('YYYY-MM-DD');

    var fechaFinal = end.format('YYYY-MM-DD');

    var capturarRango = $("#daterange-btn span").html();
   
   	localStorage.setItem("capturarRango", capturarRango);

   	window.location = "index.php?ruta=presupuestos&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;

  }

)

/*=============================================
CANCELAR RANGO DE FECHAS VENTAS
=============================================*/
$(".daterangepicker.opensleft .range_inputs .cancelBtn").on("click", function(){

	localStorage.removeItem("capturarRango");
	localStorage.clear();
	window.location = "presupuestos";

})

/*=============================================
CAPTURAR HOY - VENTAS
=============================================*/
$(".daterangepicker.opensleft .ranges li").on("click", function(){

	var textoHoy = $(this).attr("data-range-key");

	if(textoHoy == "Hoy"){

		var d = new Date();
		
		var dia = d.getDate();
		var mes = d.getMonth()+1;
		var año = d.getFullYear();

		dia = ("0"+dia).slice(-2);
		mes = ("0"+mes).slice(-2);

		var fechaInicial = año+"-"+mes+"-"+dia;
		var fechaFinal = año+"-"+mes+"-"+dia;	

    	localStorage.setItem("capturarRango", "Hoy");

    	window.location = "index.php?ruta=presupuestos&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;

	}

});

/*=============================================
IMPRIMIR FACTURA
=============================================*/
$("#tablaListarPresupuestos").on("click", ".btnImprimirPresupuesto", function(){

	var idPresupuesto = $(this).attr("idPresupuesto");

	window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/presupuesto.php?idPresupuesto="+idPresupuesto, "_blank");

});

//--------------------------------------------------------------------

/*=============================================
BOTON EDITAR VENTA
=============================================*/
$("#tablaListarPresupuestos").on("click", ".btnEditarVenta", function(){

	var idVenta = $(this).attr("idVenta");

	window.location = "index.php?ruta=editar-venta&idVenta="+idVenta;


});


/*=============================================
IMPRIMIR TICKET
=============================================*/
$("#tablaListarPresupuestos").on("click", ".btnImprimirTicket", function(){

	var codigoVenta = $(this).attr("codigoVenta");

	window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/ticket.php?codigo="+codigoVenta, "_blank");

});

/*=============================================
ENVIAR COMPROBANTE POR MAIL
=============================================*/
$("#tablaListarVentas").on("click", ".btnMailComprobante", function(){

	var codigoVenta = $(this).attr("codigoVenta");

	var email = prompt('Introduzca dirección de e-mail', $(this).attr("mailCliente") );

	function validateEmail(email) {
	  var re = /\S+@\S+\.\S+/;
	  return re.test(email);
	}

	if(validateEmail(email)){
		window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/comprobanteMail.php?codigo="+codigoVenta+"&email="+email, "_blank");
	} else {
		alert("Dirección de e-mail inválida");
	}

});

//informe de ventas por categoria | proveedor | productos
$('.inputVentasCategorias').datepicker({
    dateFormat: 'yy-mm-dd',
    todayBtn: "linked",
    autoclose: true,
    todayHighlight: true,
    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá']
});