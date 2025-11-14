/*=============================================
CARGAR LA TABLA DINÁMICA DE PRODUCTOS
=============================================*/
//AGREGA UN INPUT TEXT PARA BUSCAR EN CADA COLUMNA
$("#tablaProductos tfoot th").each(function (i) {
  var title = $(this).text();
  if(title !== ""){
    $(this).html('<input type="text" size="5" placeholder="Filtro ' + title + '" />');
  }

});

var perfilOculto = $("#perfilOculto").val();

var tablaProd = $('#tablaProductos').DataTable({
	//"ajax": "ajax/datatable-productos.ajax.php?perfilOculto="+perfilOculto,
	"ajax": {
	    "url" : "ajax/datatable-productos.serverside.php",
	    /*"data":function(outData){
             // what is being sent to the server
             console.log(outData);
             return outData;
         },
         dataFilter:function(inData){
             // what is being sent back from the server (if no error)
             console.log(inData);
             return inData;
         },
         error:function(xhr, status, error){
             // what error is seen(it could be either server side or client side.
            console.log(status);
            console.log( xhr.responseText);
            console.log( error);
            alert("Error! revisar consola");
         },*/
	},
	"deferRender": true,
	"retrieve": true,
	"processing": true,
	"serverSide": true,
	"dom": 'Bfrtip',
	"buttons":GL_DATATABLE_BOTONES , 
	"language":GL_DATATABLE_LENGUAJE,
	"columnDefs": [
			{ "targets": [7,11,12,13], "visible": false, "searchable": false }
	],
	"lengthMenu": [
        [10, 25, 50, -1],
        ['10 filas', '25 filas', '50 filas', 'Todas'],
    ],
    "stateSave": true
});

tablaProd.columns().every(function () {
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

$("#tablaProductos_filter input").bind('keyup',function (e) {

	if(e.keyCode == 13) {
	//tablaProd.fnFilter(this.value);

		var codProducto = $(this).val(); //valor que existe en el input search
	
		var datos = new FormData();
		datos.append("codigoProducto", codProducto);

	     $.ajax({

	      url:"ajax/productos.ajax.php",
	      method: "POST",
	      data: datos,
	      cache: false,
	      contentType: false,
	      processData: false,
	      dataType:"json",
	      success:function(respuesta){
	          
			if(respuesta) {

                $("#modalEditarProducto").modal('show');
                $("#editarId").val(respuesta["id"]);
                $("#editarCategoria").val(respuesta["id_categoria"]);
                $("#editarProveedor").val(respuesta["id_proveedor"]);
                $("#editarCodigo").val(respuesta["codigo"]);
                $("#editarDescripcion").val(respuesta["descripcion"]);
                $("#editarStock").val(respuesta["stock"]);
                $("#editarStockMedio").val(respuesta["stock_medio"]);
                $("#editarStockBajo").val(respuesta["stock_bajo"]);
                $("#editarPrecioCompraNeto").val(respuesta["precio_compra"]);
		         if(respuesta["margen_ganancia"] == 0) {

		         		$("#editarPorcentajeChk").prop('checked', false);
		         		$('#editarPrecioVenta').prop("readonly", false);
								$('#editarPorcentajeText').prop("readonly", true);

		         } else {

		         		$("#editarPorcentajeChk").prop('checked', true);
		         		$('#editarPrecioVenta').prop("readonly", true);
								$('#editarPorcentajeText').prop("readonly", false);

		         }

		         $("#editarIvaVenta").val(respuesta["tipo_iva"]).change();
		         $("#editarPrecioVentaIvaIncluido").val(respuesta["precio_venta"]);

		         if(respuesta["imagen"] != ""){

		           	$("#imagenActual").val(respuesta["imagen"]);

		           	$(".previsualizar").attr("src",  respuesta["imagen"]);

		         }

		         $("#modalEditarProducto").show();

					} else {

		      			swal({
					      title: "Producto no encontrado",
					      toast: true,
					      timer: 3000,
					      position: 'top',
					      type: "warning",
					      confirmButtonText: "¡Cerrar!"
					    });

					}

					$("tablaProductos_filter input").val("");
					$("tablaProductos_filter input").focus();
					$("tablaProductos_filter input").keyup();

	      }

	  })
	}

});

//MUESTRA U OCULTA LAS COLMNAS EN LA TABLA PRODUCTOS
$('a.toggle-vis').on( 'click', function (e) {
      e.preventDefault();

      // Get the column API object
      var column = tablaProd.column( $(this).attr('data-column') );

      // Toggle the visibility
      column.visible( ! column.visible() );
});

/*=============================================
SUBIENDO LA FOTO DEL PRODUCTO
=============================================*/

$(".nuevaImagen").change(function(){

	var imagen = this.files[0];
	
	/*=============================================
  	VALIDAMOS EL FORMATO DE LA IMAGEN SEA JPG O PNG
  	=============================================*/

  	if(imagen["type"] != "image/jpeg" && imagen["type"] != "image/png"){

  		$(".nuevaImagen").val("");

  		 swal({
		      title: "Error al subir la imagen",
		      text: "¡La imagen debe estar en formato JPG o PNG!",
		      type: "error",
		      confirmButtonText: "¡Cerrar!"
		    });

  	}else if(imagen["size"] > 2000000){

  		$(".nuevaImagen").val("");

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
});

/*=============================================
ELIMINAR PRODUCTO
=============================================*/
$("#tablaProductos tbody").on("click", "button.btnEliminarProducto", function(){

	var idProducto = $(this).attr("idProducto");
	var codigo = $(this).attr("codigo");
	var imagen = $(this).attr("imagen");
	
	swal({

		title: '¿Está seguro de borrar el producto?',
		text: "¡Si no lo está puede cancelar la accíón!",
		type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Si, borrar producto!'
        }).then(function(result) {
        if (result.value) {

        	window.location = "index.php?ruta=productos&idProducto="+idProducto+"&imagen="+imagen+"&codigo="+codigo;

        }

	})

});

/*============================================
				AGREGAR PRODUCTO
=============================================*/

/*============================================
AGREGANDO PRECIO DE VENTA
=============================================*/
$("#nuevoPrecioCompraNeto").keyup(function(){
	calcularPrecioVentaNuevo();
});

$('#nuevoPorcentajeChk').click(function(){
	if(this.checked) {
		$('#nuevoPrecioVenta').prop("readonly", true);
		$('#nuevoPorcentajeText').prop("readonly", false);
		$('#nuevoPorcentajeText').val(40);
	} else {
		$('#nuevoPrecioVenta').prop("readonly", false);
		$('#nuevoPorcentajeText').prop("readonly", true);
		$('#nuevoPorcentajeText').val(0);
	}
});

$('#nuevoPorcentajeText').keyup(function(){
	calcularPrecioVentaNuevo();
});

$("#nuevoPrecioVenta").keyup(function(){
	calcularPrecioVentaNuevo();
});

$('#nuevoIvaVenta').change(function(){
	calcularPrecioVentaNuevo();
});

function calcularPrecioVentaNuevo() {
	var precioNetoCompra = Number($("#nuevoPrecioCompraNeto").val()); 
	var chkProcentaje = $("#nuevoPorcentajeChk").prop('checked'); //true si esta chekeado y false si no
	var margenGanancia = Number($("#nuevoPorcentajeText").val()) / 100;
	var tipoIva = Number($("#nuevoIvaVenta").val()) / 100;
	var precioVentaNeto;

	if(chkProcentaje){
		precioVentaNeto = precioNetoCompra + precioNetoCompra * margenGanancia;
		precioVentaNeto = Math.floor(precioVentaNeto * 100) / 100; //Dos decimales sin aproximar
		$("#nuevoPrecioVenta").val(precioVentaNeto);
	} else {
		precioVentaNeto = Number($("#nuevoPrecioVenta").val());
	}

	//precio PUBLICO
	var precioVentaIvaIncluido = precioVentaNeto + precioVentaNeto * tipoIva;
	precioVentaIvaIncluido = Math.floor(precioVentaIvaIncluido * 100) / 100;
    $("#nuevoPrecioVentaIvaIncluido").val(precioVentaIvaIncluido);
}

/*============================================
				EDITAR PRODUCTO
=============================================*/
$("#tablaProductos tbody").on("click", "button.btnEditarProducto", function(){
	var idProducto = $(this).attr("idProducto");
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

        $("#editarId").val(respuesta["id"]);
		$("#editarCategoria").val(respuesta["id_categoria"]);
        $("#editarCodigo").val(respuesta["codigo"]);
        $("#editarProveedor").val(respuesta["id_proveedor"]);
        $("#editarDescripcion").val(respuesta["descripcion"]);
        $("#editarStock").val(respuesta["stock"]);
        $("#editarStockMedio").val(respuesta["stock_medio"]);
        $("#editarStockBajo").val(respuesta["stock_bajo"]);
		if(respuesta["precio_compra_dolar"] == 0) {
			$("#radioPrecioCompraPeso").prop('checked', true);
			$("#editarPrecioCompraNeto").prop('readonly', false);
			$("#editarPrecioCompraNetoDolar").prop('readonly', true);
		} else {
			$("#editarPrecioCompraNeto").prop('readonly', true);
			$("#editarPrecioCompraNetoDolar").prop('readonly', false);
			$("#radioPrecioCompraDolar").prop('checked', true);
		}		
		$("#editarPrecioCompraNeto").val(respuesta["precio_compra"]);
		$("#editarPrecioCompraNetoDolar").val(respuesta["precio_compra_dolar"]);		
		if(respuesta["margen_ganancia"] == 0 || respuesta["margen_ganancia"] == null) {
			$("#editarPorcentajeChk").prop('checked', false);
			$('#editarPrecioVenta').prop("readonly", false);
			$('#editarPorcentajeText').prop("readonly", true);
		} else {
			$("#editarPorcentajeChk").prop('checked', true);
			$('#editarPrecioVenta').prop("readonly", true);
			$('#editarPorcentajeText').prop("readonly", false);
		}

		$("#editarPorcentajeText").val(respuesta["margen_ganancia"]);
		$("#editarIvaVenta").val(respuesta["tipo_iva"]).change();
		$("#editarPrecioVentaIvaIncluido").val(respuesta["precio_venta"]);
		if(respuesta["imagen"] != ""){
			$("#imagenActual").val(respuesta["imagen"]);
			$(".previsualizar").attr("src",  respuesta["imagen"]);
		}
      }
  })
});

/*============================================
AGREGANDO PRECIO DE VENTA
=============================================*/
$("#editarPrecioCompraNeto").keyup(function(){
	calcularPrecioVentaEditar();
});

$('#editarPorcentajeChk').click(function(){
	if(this.checked) {
		$('#editarPrecioVenta').prop("readonly", true);
		$('#editarPorcentajeText').prop("readonly", false);
		$('#editarPorcentajeText').val(40);
	} else {
		$('#editarPrecioVenta').prop("readonly", false);
		$('#editarPorcentajeText').prop("readonly", true);
		$('#editarPorcentajeText').val(0);
	}
});

$('#editarPorcentajeText').keyup(function(){
	calcularPrecioVentaEditar();
});

$("#editarPrecioVenta").keyup(function(){
	calcularPrecioVentaEditar();
});

$('#editarIvaVenta').change(function(){
	calcularPrecioVentaEditar();
});

function calcularPrecioVentaEditar() {
	var precioNetoCompra = Number($("#editarPrecioCompraNeto").val()); 
	var chkProcentaje = $("#editarPorcentajeChk").prop('checked'); //true si esta chekeado y false si no
	var margenGanancia = Number($("#editarPorcentajeText").val()) / 100;
	var tipoIva = Number($("#editarIvaVenta").val()) / 100;
	var precioVentaNeto;
	if(chkProcentaje){
		precioVentaNeto = precioNetoCompra + precioNetoCompra * margenGanancia;
		precioVentaNeto = Math.floor(precioVentaNeto * 100) / 100; //Dos decimales sin aproximar
		$("#editarPrecioVenta").val(precioVentaNeto);
	} else {
		precioVentaNeto = Number($("#editarPrecioVenta").val());
	}

	//precio venta PUBLICO
	var precioVentaIvaIncluido = precioVentaNeto + precioVentaNeto * tipoIva;
	precioVentaIvaIncluido = Math.floor(precioVentaIvaIncluido * 100) / 100;
	$("#editarPrecioVentaIvaIncluido").val(precioVentaIvaIncluido);
}

$(".precioCompraPesoDolar").click(function(){
	$("#nuevoPrecioCompraNetoDolar").val('');
	$("#nuevoPrecioCompraNeto").val('');
	if($(this).val() == "peso") {
		$("#nuevoPrecioCompraNeto").prop('readonly', false);
		$("#nuevoPrecioCompraNetoDolar").prop('readonly', true);
	} else {
		$("#nuevoPrecioCompraNeto").prop('readonly', true);
		$("#nuevoPrecioCompraNetoDolar").prop('readonly', false);
	}
	calcularPrecioVentaNuevo();
});

$("#nuevoPrecioCompraNetoDolar").keyup(function(){
	var cotizacion = Number($("#cabezoteCotizacionPesos").text());
	var dolares = Number($("#nuevoPrecioCompraNetoDolar").val()); //traigo cotizacion del "cabezote"
	$("#nuevoPrecioCompraNeto").val(dolares * cotizacion);
	calcularPrecioVentaNuevo();
});

$(".precioCompraPesoDolarEditar").click(function(){
	$("#editarPrecioCompraNetoDolar").val('');
	$("#editarPrecioCompraNeto").val('');
	if($(this).val() == "peso") {
		$("#editarPrecioCompraNeto").prop('readonly', false);
		$("#editarPrecioCompraNetoDolar").prop('readonly', true);
	} else {
		$("#editarPrecioCompraNeto").prop('readonly', true);
		$("#editarPrecioCompraNetoDolar").prop('readonly', false);
	}
	calcularPrecioVentaEditar();
});

$("#editarPrecioCompraNetoDolar").keyup(function(){
	var cotizacion = Number($("#cabezoteCotizacionPesos").text()); //traigo cotizacion del "cabezote"
	var dolares = Number($("#editarPrecioCompraNetoDolar").val());
	console.log(cotizacion);
	console.log(dolares);
	$("#editarPrecioCompraNeto").val(dolares * cotizacion);
	calcularPrecioVentaEditar();
});

/*=============================================
SELECCIONAR PRODUCTOS PARA IMPRIMIR
=============================================*/
var tblProdImpresion = $('#tablaImpresionProductos').DataTable({
	deferRender: true,
	retrieve: true,
	processing: true,
	"order": [[ 2, "asc" ]],
    "columnDefs": [
          { "targets": [0,1], "orderable": false },
          { "targets": [1], "visible": false }
          ],
	language: GL_DATATABLE_LENGUAJE
});

//
//	AL SELECCIONAR TODOS LOS PRODUCTOS 
//

$("#selTodImpPreciosProductos").change(function(){
    $("#tablaImpresionProductos input:checkbox.call-checkbox").prop('checked',this.checked);
});

/*=============================================
DESELECCIONAR PRODUCTOS PARA IMPRIMIR
=============================================*/
$("#deseleccionar_todo").click(function(){
	$("#tablaImpresionProductos input:checkbox.call-checkbox").prop('checked','true');
});

/*=============================================
DESELECCIONAR PRODUCTOS PARA IMPRIMIR
=============================================*/
$("#btnImprimirPreciosComunProductos").click(function(){
	var getLista = $("#arrayProductosImpresion").val();
	window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/impresion-precios.php?lista="+getLista, "_blank");
		$("#arrayProductosImpresion").val('');
		window.location = "index.php?ruta=impresion-precios";
});

$("#btnImprimirPreciosSuperProductos").click(function(){
	var getLista = $("#arrayProductosImpresion").val();
	window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/impresionPreciosOfertas.php?lista="+getLista, "_blank");
		$("#arrayProductosImpresion").val('');
		window.location = "index.php?ruta=impresion-precios";

});

$("#btnImprimirCodigosBarra").click(function(){
	var getLista = $("#arrayProductosImpresion").val();
	window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/imprimirCodigoBarra.php?lista="+getLista, "_blank");
		$("#arrayProductosImpresion").val('');
		window.location = "index.php?ruta=impresion-precios";
});

$("#btnImprimirCodigosBarraAuto").click(function(){
	var getLista = $("#arrayProductosImpresion").val();
	window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/imprimirCodigoBarraAuto.php?lista="+getLista, "_blank");
		$("#arrayProductosImpresion").val('');
		window.location = "index.php?ruta=impresion-precios";
});

$("#btnImprimirCodigosQr").click(function(){
	var getLista = $("#arrayProductosImpresion").val();
	window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/imprimirQR.php?lista="+getLista, "_blank");
		$("#arrayProductosImpresion").val('');
		window.location = "index.php?ruta=impresion-precios";
});

/*============================================
		EDITAR PRODUCTO - AJUSTE STOCK
=============================================*/
$("#tablaProductos tbody").on("click", "a.btnEditarProductoAjusteStock", function(){
    
	var idProducto = $(this).attr("idProducto");
	var almacen = $(this).attr("almacenDesde");
	
	$("#editarAjusteStockAlmacen").val(almacen);
	
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

       $("#editarCodigoAjusteStocksP").text(respuesta["codigo"]);
       $("#editarIdAjusteStock").val(respuesta["id"]);

       $("#editarDescripcionAjusteStock").text(respuesta["descripcion"]);

       $("#editarStockActualAjuste").text(respuesta[almacen]);
       $("#editarStockAnterior").val(respuesta[almacen]);

	}

  })

});

//PONER FOCO EN EL CAMPO CANTIDAD APENAS LEVANTE EL MODAL
$('#modalEditarProductoAjusteStock').on('shown.bs.modal', function() {

  $("#editarStockAjuste").focus();

});

/*=============================================
VALIDAR SI EL CODIGO DE PRODUCTO YA ESTÁ REGISTRADO
=============================================*/
$("#nuevoCodigo").change(function(){

	$(".alert").remove();

	var codigo = $(this).val();

	var datos = new FormData();
	datos.append("validarCodigoProducto", codigo);

	 $.ajax({
	    url:"ajax/productos.ajax.php",
	    method:"POST",
	    data: datos,
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: "json",
	    success:function(respuesta){
	        
	        console.log(respuesta)
	    	
	    	if(respuesta){

	    		$("#nuevoCodigo").parent().after('<div class="alert alert-warning">Este código de producto ya existe en la base de datos</div>');

	    		$("#nuevoCodigo").focus();

	    	}

	    }

	})
})

var tblProdImpresion = $('#tablaImpresionProductosImpresion').DataTable().clear().destroy();
var tblProdImpresion = $('#tablaImpresionProductosImpresion').dataTable({
	
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "ajax/productos-precios.php",
		"responsive": true,
		"pageLength": 10,
		"text-align": "center",
		"order": [[0, 'desc']],
		"columnDefs": [
    { "targets": [0], "visible": false, "searchable": true }
    ],
	
        "aoColumns": [
		       { mData: 'id', className: "uniqueClassName"},
               { mData: 'codigo', className: "uniqueClassName"},
               { mData: 'descripcion', className: "uniqueClassName"},
			   { mData: 'precio_venta', className: "uniqueClassName"},
			   {"mRender": function ( data, type, row ) {
						return '<center><button class="btn btn-danger" title="Agregar" onclick="borrarArrarPrecio('+row["id"]+')");"><i class="fa fa-times"></i></button></center>';}
              },
			  {"mRender": function ( data, type, row ) {
						return '<center><button class="btn btn-info" id="botonAgregar'+row["id"]+'" name="botonAgregar'+row["id"]+'" title="Agregar" onclick="cargarArrarPrecio('+row["id"]+')");"><i class="fa fa-print"></i></button></center>';}
              }
			  
	     ]

	   });
	   
var arrayProductosImpresion = [];
var contador=0;
function cargarArrarPrecio(valor){

	document.getElementById("precioPlace").style.display = "block";
	document.getElementById("detallePlace").style.display = "block";
			
	var datos = new FormData();
    datos.append("idPro", valor);

    $.ajax({

      url:"ajax/productos.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType:"json",
      success:function(respuesta){
		arrayProductosImpresion.push({ "id" : valor, "descripcion" : respuesta[4], "precio_venta" : respuesta[9]}); 
		contador++;
		$("#arrayProductosImpresion").val(JSON.stringify(arrayProductosImpresion)); 
		document.getElementById("contador").innerHTML="Precios Para Imprimir:"+contador;	
	}

  })  
}

function borrarArrarPrecio(idProducto) {

var array =  JSON.parse($("#arrayProductosImpresion").val());

//array.splice(array.findIndex(a => array.id !== idProducto) , 1);

for (var i = 0; i < array.length; i++) {
  if (array[i].id == idProducto) {
    array.splice(i, 1);
    break;
  }
}

document.getElementById("arrayProductosImpresion").value = JSON.stringify(array);
contador--;
document.getElementById("contador").innerHTML="Precios Para Imprimir:"+contador;
var cambioEstado = "botonAgregar"+idProducto;
document.getElementById(cambioEstado).removeAttribute('disabled');
if(contador==0){
		document.getElementById("precioPlace").style.display = "none";
		document.getElementById("detallePlace").style.display = "none";
	}else{
	}
}

function verProductosImpresion () {
	var array =  JSON.parse($("#arrayProductosImpresion").val());
	$('#tablaProductosImprimir').DataTable().clear().destroy();
	$('#tablaProductosImprimir tbody').detach();
	$('#tablaProductosImprimir').dataTable({
	paging: false,
            	language: {
                url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
				},
				dom: 'Bfrtip',
		   		data: array,
		columns: [
			{ data: 'id', className: "uniqueClassName"},
			{ data: 'descripcion', className: "uniqueClassName" },
			{ data: 'precio_venta', className: "uniqueClassName" }
		]
	});
}	

var arrayProductosBorrarMultiple = [];
var contador=0;
function cargarArrarBorrarMultiple(valor){

	document.getElementById("precioPlace").style.display = "block";
	document.getElementById("detallePlace").style.display = "block";
	document.getElementById("boronBorrado").style.display = "block";
		
	var datos = new FormData();
    datos.append("idProducto", valor);

     $.ajax({

      url:"ajax/productos.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType:"json",
      success:function(respuesta){

	arrayProductosBorrarMultiple.push({ "id" : valor, "descripcion" : respuesta[4]}); 

	contador++;
	$("#arrayProductosBorrarMultiple").val(JSON.stringify(arrayProductosBorrarMultiple)); 
	document.getElementById("contador").innerHTML=contador;	
	}

  })  
}

function verProductosBorrar () {
	var array =  JSON.parse($("#arrayProductosBorrarMultiple").val());

	$('#tablaProductosBorrarMultiple').DataTable().clear().destroy();
	$('#tablaProductosBorrarMultiple tbody').detach();
	$('#tablaProductosBorrarMultiple').dataTable({
		paging: false,
		language: {
		url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
		},
		dom: 'Bfrtip',
		data: array,
			columns: [
				{ data: 'id', className: "uniqueClassName"},
				{ data: 'descripcion', className: "uniqueClassName" }
			]
			
	});
}

function borradoMultiple() {
  try {
    var borrarDatos = JSON.parse($("#arrayProductosBorrarMultiple").val());
    var contadorEliminados = 0;

    for (let i = 0; i < borrarDatos.length; i++) {
      var datosBorrarMultiple = new FormData();
      datosBorrarMultiple.append("idProductoBorrar", borrarDatos[i]['id']);

      $.ajax({
        url: "ajax/productos.ajax.php",
        method: "POST",
        data: datosBorrarMultiple,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
          contadorEliminados++;
          if (contadorEliminados === borrarDatos.length) {
            swal({
              title: "Productos",
              text: "Productos Borrados "+contadorEliminados,
              type: "success",
              toast: true,
              position: 'top',
              showConfirmButton: false,
              timer: 3000
            });
            setTimeout(function() {
              window.location = "productos";
            }, 2000);
          }
        }
      });
    }
  } catch (error) {
    console.error("Error al parsear JSON:", error);
  }
}

function borradoMultipleMejorado() {
  try {
    var borrarDatos = JSON.parse($("#arrayProductosBorrarMultiple").val());
    var ids = [];

    for (let i = 0; i < borrarDatos.length; i++) {
      ids.push(borrarDatos[i]['id']);
    }

    var datosBorrarMultiple = new FormData();
    datosBorrarMultiple.append("ids", JSON.stringify(ids));

    $.ajax({
      url: "ajax/productos.ajax.php",
      method: "POST",
      data: datosBorrarMultiple,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function(respuesta) {
        // ...
      }
    });
  } catch (error) {
    console.error("Error al parsear JSON:", error);
  }
}