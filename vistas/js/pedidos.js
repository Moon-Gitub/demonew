/*=============================================
CARGAR LA TABLA DINÁMICA DE VENTAS
=============================================*/

$('#tablaPedidos').DataTable( {
    "ajax": "ajax/datatable-pedidos.ajax.php",
    "deferRender": true,
	"retrieve": true,
	"processing": true,
	"columnDefs": [
		{ "targets": [5], "visible": false, "searchable": false },
		{ "targets": [4], "className": 'text-center' }
    ],
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

} );

/*=============================================
AGREGANDO PRODUCTOS LA MIMOSA A LA VENTA DESDE LA TABLA
=============================================*/
function llamar(value){

if($("#urlActual").val() == 1){
var datos = new FormData();
    datos.append("idProducto", value);

     $.ajax({

     	url:"ajax/productos.ajax.php",
      	method: "POST",
      	data: datos,
      	cache: false,
      	contentType: false,
      	processData: false,
      	dataType:"json",
      	success:function(respuesta){

          	$(".nuevoProducto").prepend(

          	'<div class="row" style="padding:5px 15px;">'+

			  '<!-- Descripción del producto -->'+
	          
	          '<div class="col-xs-6" style="padding-right:0px">'+
	          
	            '<div class="input-group">'+
	              
	              '<span class="input-group-btn"><button type="button" class="btn btn-danger btn-sm quitarProducto" idProducto="'+value+'"><i class="fa fa-times"></i></button></span>'+

	              '<input type="text" class="form-control input-sm nuevaDescripcionProducto" idProducto="'+value+'" name="agregarProducto" value="'+respuesta[4]+'" sucursal="sucursal1" readonly required>'+

				 '</div>'+

	          '</div>'+

	          '<!-- Cantidad del producto -->'+
				
			  '<div class="col-xs-2">'+
	            
	             '<input type="text" step="any" class="form-control input-sm nuevoCodigo" style="text-align:center;" name="nuevoCodigo"  value="'+value+'" readonly required>'+

	          '</div>' +	
				
	          '<div class="col-xs-2">'+
	            
	             '<input type="text" step="any" class="form-control input-sm nuevaCantidadProductoValidar" style="text-align:center;" name="nuevaCantidadProductoValidar" value="0" readonly required>'+

	          '</div>' +

				'<div class="col-xs-2">'+
	            
	             '<input type="text" step="any" class="form-control input-sm nuevaCantidadProducto" style="text-align:center;" name="nuevaCantidadProducto" value="1" required>'+

	          '</div>' +

	          '<!-- Precio del producto -->'+
	          

	        '</div>') 

	        // AGRUPAR PRODUCTOS EN FORMATO JSON

	        listarProductosPedidos()

			sumarArticulosPedido()

	        // PONER FORMATO AL PRECIO DE LOS PRODUCTOS

	        //$(".nuevoPrecioProducto").number(true, 2);


			localStorage.removeItem("quitarProducto");
		}
	
     })



}
else{
	var datos = new FormData();
    datos.append("idProducto", value);

     $.ajax({

     	url:"ajax/productos.ajax.php",
      	method: "POST",
      	data: datos,
      	cache: false,
      	contentType: false,
      	processData: false,
      	dataType:"json",
      	success:function(respuesta){

          	$(".nuevoProducto").prepend(

          	'<div class="row" style="padding-left:25px;padding-bottom:5px;">'+

			  '<!-- Descripción del producto -->'+
	          
	          '<div class="col-xs-6" style="padding-right:0px">'+
	          
	            '<div class="input-group">'+
	              
	              '<span class="input-group-btn"><button type="button" class="btn btn-danger btn-sm quitarProducto" idProducto="'+value+'"><i class="fa fa-times"></i></button></span>'+

	              '<input type="text" class="form-control input-sm nuevaDescripcionProducto" idProducto="'+value+'" name="agregarProducto" value="'+respuesta[4]+'" sucursal="sucursal1" readonly required>'+

				 '</div>'+

	          '</div>'+

	          '<!-- Cantidad del producto -->'+

	          '<div class="col-xs-3">'+
	            
	             '<input type="text" step="any" class="form-control input-sm codigoVer" style="text-align:center;" name="codigoVer"  value="'+value+'" readonly required>'+

	          '</div>' +

		'<div class="col-xs-3">'+
	            
	             '<input type="text" step="any" class="form-control input-sm nuevaCantidadProducto" style="text-align:center;" name="nuevaCantidadProducto" value="1" required>'+

	          '</div>' +

	          '<!-- Precio del producto -->'+
	          

	        '</div>') 

	        // AGRUPAR PRODUCTOS EN FORMATO JSON

	        listarProductosPedidos()

			sumarArticulosPedido()

	        // PONER FORMATO AL PRECIO DE LOS PRODUCTOS

	        //$(".nuevoPrecioProducto").number(true, 2);


			localStorage.removeItem("quitarProducto");
		}
	
     })


}

}
/*=============================================
CUANDO CARGUE LA TABLA CADA VEZ QUE NAVEGUE EN ELLA
=============================================*/

$(".tablaPedidos").on("draw.dt", function(){

	if(localStorage.getItem("quitarProducto") != null){

		var listaIdProductos = JSON.parse(localStorage.getItem("quitarProducto"));

		for(var i = 0; i < listaIdProductos.length; i++){

			$("button.recuperarBoton[idProducto='"+listaIdProductos[i]["idProducto"]+"']").removeClass('btn-default');
			$("button.recuperarBoton[idProducto='"+listaIdProductos[i]["idProducto"]+"']").addClass('btn-primary agregarProducto');

		}


	}


})


/*=============================================
QUITAR PRODUCTOS DE LA VENTA Y RECUPERAR BOTÓN
=============================================*/

var idQuitarProducto = [];

localStorage.removeItem("quitarProducto");

$(".formularioPedido").on("click", "button.quitarProducto", function(){

	$(this).parent().parent().parent().parent().remove();

	var idProducto = $(this).attr("idProducto");

	/*=============================================
	ALMACENAR EN EL LOCALSTORAGE EL ID DEL PRODUCTO A QUITAR
	=============================================*/

	if(localStorage.getItem("quitarProducto") == null){

		idQuitarProducto = [];
	
	}else{

		idQuitarProducto.concat(localStorage.getItem("quitarProducto"))

	}

	idQuitarProducto.push({"idProducto":idProducto});

	localStorage.setItem("quitarProducto", JSON.stringify(idQuitarProducto));

	$("button.recuperarBoton[idProducto='"+idProducto+"']").removeClass('btn-default');

	$("button.recuperarBoton[idProducto='"+idProducto+"']").addClass('btn-primary agregarProducto');
	
	if($(".nuevoProducto").children().length == 0){
	//document.getElementById("nuevoOrigenVer").value='';
	//document.getElementById("nuevoOrigen").value='';
	$("#listaProductosPedidos").val('');
	$("#cantidadArticulosPedido").val(0);
	listarProductosPedidos()
	sumarArticulosPedido()
	
	}else{

   	 listarProductosPedidos()
   	 sumarArticulosPedido()
	
	}

})

/*=============================================
QUITAR PRODUCTOS DE LA VENTA Y RECUPERAR BOTÓN
=============================================*/

var idQuitarProducto = [];

localStorage.removeItem("quitarProductoValidar");

$(".formularioPedidoValidar").on("click", "button.quitarProductoValidar", function(){

	$(this).parent().parent().parent().parent().remove();

	var idProducto = $(this).attr("idProducto");

	/*=============================================
	ALMACENAR EN EL LOCALSTORAGE EL ID DEL PRODUCTO A QUITAR
	=============================================*/

	if(localStorage.getItem("quitarProductoValidar") == null){

		idQuitarProducto = [];
	
	}else{

		idQuitarProducto.concat(localStorage.getItem("quitarProductoValidar"))

	}

	idQuitarProducto.push({"idProducto":idProducto});

	localStorage.setItem("quitarProductoValidar", JSON.stringify(idQuitarProducto));

	$("button.recuperarBoton[idProducto='"+idProducto+"']").removeClass('btn-default');

	$("button.recuperarBoton[idProducto='"+idProducto+"']").addClass('btn-primary agregarProducto');
	
	if($(".nuevoProducto").children().length == 0){
	//document.getElementById("nuevoOrigenVer").value='';
	//document.getElementById("nuevoOrigen").value='';
	$("#listaProductosPedidos").val('');
	$("#cantidadArticulosPedido").val(0);
	listarProductosPedidosValidar()
	sumarArticulosPedido()
	
	}else{

   	 listarProductosPedidosValidar()
   	 sumarArticulosPedido()
	
	}

})

/*=============================================
AGREGANDO PRODUCTOS DESDE EL BOTÓN PARA DISPOSITIVOS
=============================================*/

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

	            '</div>'+

	          '</div>'+

	          '<!-- Cantidad del producto -->'+

	          '<div class="col-xs-3 ingresoCantidad">'+
	            
	             '<input type="text" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="0" stock nuevoStock required>'+

	          '</div>' +

	          '<!-- Precio del producto -->'+

	          '<div class="col-xs-3 ingresoPrecio" style="padding-left:0px">'+

	            '<div class="input-group">'+

	              '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'+
	                 
	              '<input type="text" class="form-control nuevoPrecioProducto" precioReal="" name="nuevoPrecioProducto" readonly required>'+
	 
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

    		// AGREGAR IMPUESTO
	        
	        listarProductosPedidos()

	        // PONER FORMATO AL PRECIO DE LOS PRODUCTOS

	       // $(".nuevoPrecioProducto").number(true, 2);


      	}

	})

})

/*=============================================
SELECCIONAR PRODUCTO
=============================================*/

$(".formularioPedido").on("change", "select.nuevaDescripcionProducto", function(){

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

	        listarProductosPedidos()

      	}

      })
})

/*=============================================
MODIFICAR LA CANTIDAD
=============================================*/

$(".formularioPedido").on("keyup", "input.nuevaCantidadProducto", function(){

	var precio = $(this).parent().parent().children(".ingresoPrecio").children().children(".nuevoPrecioProducto");

	var precioFinal = $(this).val() * precio.attr("precioReal");
	
	precio.val(precioFinal);

	var nuevoStock = Number($(this).attr("stock")) - $(this).val();

	$(this).attr("nuevoStock", nuevoStock);

    listarProductosPedidos()

	sumarArticulosPedido()
})

/*=============================================
MODIFICAR LA CANTIDAD
=============================================*/

$(".formularioPedidoValidar").on("keyup", "input.nuevaCantidadProducto", function(){

	var precio = $(this).parent().parent().children(".ingresoPrecio").children().children(".nuevoPrecioProducto");

	var precioFinal = $(this).val() * precio.attr("precioReal");
	
	precio.val(precioFinal);

	var nuevoStock = Number($(this).attr("stock")) - $(this).val();

	$(this).attr("nuevoStock", nuevoStock);

    listarProductosPedidosValidar()

	sumarArticulosPedido()
})

/*=============================================
LISTAR TODOS LOS PRODUCTOS
=============================================*/

function listarProductosPedidos(){

	var listaProductosPedidos = [];

	var descripcion = $(".nuevaDescripcionProducto");

	var cantidad = $(".nuevaCantidadProducto");

	var precio = $(".nuevoPrecioProducto");

	for(var i = 0; i < descripcion.length; i++){
	
		listaProductosPedidos.push({ "id" : $(descripcion[i]).attr("idProducto"), 
							  "descripcion" : $(descripcion[i]).val(),
							  "recibida" : 0,
							  "cantidad" : $(cantidad[i]).val()})

	}

	$("#listaProductosPedidos").val(JSON.stringify(listaProductosPedidos)); 

}

/*=============================================
BOTON EDITAR VENTA
=============================================*/
$(".tablasPedidosInternosNuevos").on("click", ".btnEditarPedido", function(){

	var idPedido = $(this).attr("idPedido");

	window.location = "index.php?ruta=editar-pedido&idPedido="+idPedido;


})

/*=============================================
FUNCIÓN PARA DESACTIVAR LOS BOTONES AGREGAR CUANDO EL PRODUCTO YA HABÍA SIDO SELECCIONADO EN LA CARPETA
=============================================*/

function quitarAgregarProductoPedidos(){

	//Capturamos todos los id de productos que fueron elegidos en la venta
	var idProductos = $(".quitarProducto");

	//Capturamos todos los botones de agregar que aparecen en la tabla
	var botonesTabla = $(".tablaPedidos tbody button.agregarProducto");

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
CADA VEZ QUE CARGUE LA TABLA CUANDO NAVEGAMOS EN ELLA EJECUTAR LA FUNCIÓN:
=============================================*/

$('.tablaPedidos').on( 'draw.dt', function(){

	quitarAgregarProductoPedidos();

})


/*=============================================
BORRAR PEDIDO
=============================================*/
$(".tablasPedidosInternosNuevos").on("click", ".btnEliminarPedido", function(){

  var idPedido = $(this).attr("idPedido");

  swal({
        title: '¿Está seguro de borrar el pedido?',
        text: "¡Si no lo está puede cancelar la accíón!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Si, borrar pedido!'
      }).then(function(result){
        if (result.value) {
          
            window.location = "index.php?ruta=pedidos-nuevos&idPedido="+idPedido;
        }

  })

})

/*=============================================
IMPRIMIR FACTURA
=============================================*/

$(".tablasPedidosInternos").on("click", ".btnImprimirPedido", function(){

	var codigoPedido = $(this).attr("codigoPedido");

	window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/pedido.php?codigo="+codigoPedido, "_blank");

})

/*=============================================
IMPRIMIR FACTURA
=============================================*/

$(".tablasPedidosInternosNuevos").on("click", ".btnImprimirPedidoParcial", function(){

	var codigoPedido = $(this).attr("codigoPedido");

	window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/pedidoParcial.php?codigo="+codigoPedido, "_blank");

})

/*=============================================
ABRIR ARCHIVO XML EN NUEVA PESTAÑA
=============================================*/

$(".abrirXML").click(function(){

	var archivo = $(this).attr("archivo");
	window.open(archivo, "_blank");


})

function sumarArticulosPedido(){

	var cantidadItem = $(".nuevaCantidadProducto");

	var arraySumaCantidad = [];  

	for(var i = 0; i < cantidadItem.length; i++){

		 arraySumaCantidad.push(Number($(cantidadItem[i]).val()));
		
		 
	}

	function sumaArrayCantidades(total, numero){

		return total + numero;

	}

	var sumaTotalCantidad = arraySumaCantidad.reduce(sumaArrayCantidades);
	
	$("#cantidadArticulosPedido").val(sumaTotalCantidad);

}

$(".tablasPedidosInternosNuevos").DataTable({
 "order": [[ 6, "desc" ]],
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

function listarProductosPedidosValidar(){

	var listaProductosPedidosValidar = [];

	var descripcion = $(".nuevaDescripcionProducto");

	var cantidad = $(".nuevaCantidadProducto");
	
	var recibido = $(".nuevaCantidadProductoValidar");

	var precio = $(".nuevoPrecioProducto");

	for(var i = 0; i < descripcion.length; i++){
	
		listaProductosPedidosValidar.push({ "id" : $(descripcion[i]).attr("idProducto"), 
							  "descripcion" : $(descripcion[i]).val(),
							  "cantidad" : $(recibido[i]).val(),
							  "recibida" : $(cantidad[i]).val()})

	}

	$("#listaProductosPedidosValidar").val(JSON.stringify(listaProductosPedidosValidar)); 

}