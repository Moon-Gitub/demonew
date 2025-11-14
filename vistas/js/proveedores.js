/*=============================================
EDITAR PROVEEDOR
=============================================*/
$(".tablas").on("click", ".btnEditarProveedor", function(){

  var idProveedor = $(this).attr("idProveedor");

  var datos = new FormData();
    datos.append("idProveedor", idProveedor);

    $.ajax({

      url:"ajax/proveedores.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType:"json",
      success:function(respuesta){

        console.log(respuesta)
      
        $("#idProveedor").val(respuesta["id"]);
        $("#editarNombre").val(respuesta["nombre"]);
        $("#editarInicioActividades").val(respuesta["inicio_actividades"]);
        $("#editarTipoDocumento").val(respuesta["tipo_documento"]);
        $("#editarCuit").val(respuesta["cuit"]);
        $("#editarIngresosBrutos").val(respuesta["ingresos_brutos"]);
        $("#editarLocalidad").val(respuesta["localidad"]);
        $("#editarTelefono").val(respuesta["telefono"]);
        $("#editarDireccion").val(respuesta["direccion"]);
        $("#editarEmail").val(respuesta["email"]);
        $("#editarObservacionesProveedor").val(respuesta["observaciones"]);
      }

    })

})

/*=============================================
ELIMINAR PROVEEDOR
=============================================*/
$(".tablas").on("click", ".btnEliminarProveedor", function(){

  var idProveedor = $(this).attr("idProveedor");
  
  swal({
        title: '¿Está seguro de borrar el Proveedor?',
        text: "¡Si no lo está puede cancelar la acción!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Si, borrar proveedor!'
      }).then(function(result){
        if (result.value) {
          
            window.location = "index.php?ruta=proveedores&idProveedor="+idProveedor;
        }

  })

})

function llevarValor(id,total){

  document.getElementById('idCompra').value=id;
  document.getElementById('total').value=total;
  document.getElementById('ingresoCajaDescripcion').value="Pago Compra. Cbte N°:"+id;

}

/*=============================================
IMPRIMIR FACTURA
=============================================*/
$(".tablaCtaCteProveedores").on("click", ".btnImprimirNotaDeCredito", function(){

  var idPago = $(this).attr("idPago");

  window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/nota-credito.php?idPago="+idPago, "_blank");

})

$(".tablaCtaCteProveedores").on("click", ".btnImprimirNotaDeDebito", function(){

  var idPago = $(this).attr("idPago");

  window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/nota-debito.php?idPago="+idPago, "_blank");

})

/*$(".tablaCtaCteProveedores").on("click", ".btnImprimirNotaDeDebitoInterna", function(){

  var idPago = $(this).attr("idPago");

  window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/nota-debito-interna.php?idPago="+idPago, "_blank");

})*/  


$(".tablasBotonesCtaCteProveedor").on("click", ".btnImprimirCompraCtaCte", function(){

  var idPago = $(this).attr("idPago");

  window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/compraCtaCte.php?codigo="+idPago, "_blank");

});

$(".tablaCtaCteProveedores").on("click", ".btnImprimirOrdenPago", function(){

  var idPago = $(this).attr("idPago");

  window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/orden-pago.php?idPago="+idPago, "_blank");

})

/*=============================================
BORRAR COMPRA
=============================================*/
$(".tablaCtaCteProveedores").on("click", ".btnEliminarMovimiento", function(){

  var idMovimiento = $(this).attr("idMovimiento");
  var id_proveedor = $("#idProveedor").val();

  swal({
        title: '¿Está seguro de borrar el movimiento?',
        text: "¡Si no lo está puede cancelar la accíón!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Si, borrar movimiento!'
      }).then(function(result){
        if (result.value) {
          
            window.location = "index.php?ruta=proveedores_cuenta&idMovimiento="+idMovimiento+"&id_proveedor="+id_proveedor;
        }

  })

});

/////////////////////////////////////Datatables  Proveedores///////////////////////////
$(".tablaSaldoProveedor").DataTable( {
   
   "language": GL_DATATABLE_LENGUAJE,
    responsive: "true",
    dom: 'Bfrtilp',       
    buttons:[ 
      {
        extend:    'excelHtml5',
        text:      '<i class="fa fa-file-excel-o"></i> ',
        titleAttr: 'Exportar a Excel',
        className: 'btn btn-success',
        messageTop: "Saldo de provedores al dia "+$("#fechaInicial").val(), 
      },
      {
        extend:    'pdfHtml5',
        text:      '<i class="fa fa-file-pdf-o"></i> ',
        titleAttr: 'Exportar a PDF',
        className: 'btn btn-danger',
        messageTop: "Saldo de provedores al dia "+$("#fechaInicial").val(), 
      },
      {
        extend:    'print',
        text:      '<i class="fa fa-print"></i> ',
        titleAttr: 'Imprimir',
        className: 'btn btn-info',
        messageTop: "Saldo de provedores al dia "+$("#fechaInicial").val(),
      },
    ] 
});

function mostrarProveedoresPagos(){
  
    window.location = "index.php?ruta=proveedores-pagos&fechaInicial="+document.getElementById("fechaInicial").value;
}

/*=============================================
MODIFICAr PRECIOS PRODUCTOS POR proveedor
=============================================*/
$(".tablas").on("click", ".btnModificarPrecioProveedor", function(){

  var idProveedor = $(this).attr("idProveedor");
  var nombreProv = $(this).attr("nombreProveedor");

  $("#nombreProveedor").text('Proveedor: ' + nombreProv);
  $("#idProveedorNuevoPrecio").val(idProveedor);

});

$(".tablasBotonesCtaCteProveedor").DataTable({

  "dom": 'Bfrtip',
  "buttons":GL_DATATABLE_BOTONES, 
  "language": GL_DATATABLE_LENGUAJE

});

$("#tipoMovimientoCtaCteProveedor").change(function(){

  if($(this).val() == 1) {

    //Debito (no se agrega en caja)
    $(".ctacteProveedorCaja").css('display', 'none');
    $("#nuevoMetodoPagoCtaCteProveedor").prop('required',false);
    $("#detalleMovimientoCtaCteProveedor").val('');

  } else {

    //Pago (agrega tambien en caja)
    $(".ctacteProveedorCaja").css('display', 'block');
    $("#nuevoMetodoPagoCtaCteProveedor").prop('required',true);
    $("#detalleMovimientoCtaCteProveedor").val('Egreso por pago Cta. Cte. proveedor: ' + $("#spanNombreProveedorCtaCte").text());
  
  }

});

$("#nuevoMetodoPagoCtaCteProveedor").change(function(){

  if($("#nuevoMetodoPagoCtaCteProveedor").val() == "BO") {
    $("#detalleMovimientoCtaCteProveedor").val('Bonificación: ' + $("#spanNombreProveedorCtaCte").text());
  } else {
    $("#detalleMovimientoCtaCteProveedor").val('Egreso por pago Cta. Cte. proveedor: ' + $("#spanNombreProveedorCtaCte").text());
  }

  var cero = 0;

  $('.cajasMetodoPagoCtaCteProveedor').html(
  
    '<div class="col-xs-6" style="padding-left:0px">'+

    '</div>');

  if($("#nuevoMetodoPagoCtaCteProveedor").val() == "TC"){

    $(".cajasMetodoPagoCtaCteProveedor").html(

      '<div class="col-xs-4" style="padding-left:0px">'+

       /* '<select class="form-control" id="seleccionarTarjeta" required>'+

          '<option value="">Tarjeta</option>'+

        '</select>'+

      '</div>' + 

      '<div class="col-xs-3" style="padding-left:0px">'+

        '<select class="form-control" required>'+

          '<option value="1">1</option>'+

        '</select>'+

      '</div>' +

      '<div class="col-xs-4" style="padding-left:0px">'+
              
      '<div class="input-group">'+
           
        '<input type="number" min="0" class="form-control" id="nuevoCodigoTransaccionCtaCteProveedor" placeholder="Código transacción">'+
             
        '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+
        
      '</div>'+*/

    '</div>');

    /*var datosTc = new FormData();
    datosTc.append("listaTarjetas", true);

    $.ajax({

      url:"ajax/tarjetas.ajax.php",
        method: "POST",
        data: datosTc,
        cache: false,
        contentType: false,
        processData: false,
        dataType:"json",
        success:function(respuesta){

          respuesta.forEach(function (item, index){

              $("#seleccionarTarjeta").append(

                  '<option idTarjeta="'+item.id+'" value="'+item.nombre+'">'+item.nombre+'</option>'

              );
          })
        }
      })*/
    }

  if($("#nuevoMetodoPagoCtaCteProveedor").val() == "TD"){

    $(".cajasMetodoPagoCtaCteProveedor").html(

      '<div class="col-xs-4" style="padding-left:0px">'+
              
      /*'<div class="input-group">'+
           
        '<input type="number" min="0" class="form-control" id="nuevoCodigoTransaccionCtaCteProveedor" placeholder="Código transacción">'+
             
        '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+
        
      '</div>'+*/

    '</div>');

  }

  if($("#nuevoMetodoPagoCtaCteProveedor").val() == "TR"){ //Transferencia

    $(".cajasMetodoPagoCtaCteProveedor").html(

      '<div class="col-xs-4" style="padding-left:0px">'+
        '<div class="input-group">'+
          '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+
          '<input type="text" autocomplete="off" class="form-control inputCtaCteProveedorMedioPago" id="bancoOrigenTransferencia" placeholder="Banco origen">'+
        '</div>'+
      '</div>' + 

      '<div class="col-xs-3" style="padding-left:0px">'+
        '<div class="input-group">'+
          '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+
          '<input type="text" autocomplete="off" class="form-control inputCtaCteProveedorMedioPago" id="numeroReferenciaTransferencia" placeholder="N° referencia">'+
        '</div>'+
      '</div>');

  }

  if($("#nuevoMetodoPagoCtaCteProveedor").val() == "CH"){ //Cheque

      $(".cajasMetodoPagoCtaCteProveedor").html(

      '<div class="col-xs-4" style="padding-left:0px">'+
        '<div class="input-group">'+
          '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+
          '<input type="text" autocomplete="off" class="form-control inputCtaCteProveedorMedioPago" id="bancoOrigenCheque" placeholder="Banco origen">'+
        '</div>'+
      '</div>' + 

      '<div class="col-xs-3" style="padding-left:0px">'+
        '<div class="input-group">'+
          '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+
          '<input type="text" autocomplete="off" class="form-control inputCtaCteProveedorMedioPago" id="numeroCheque" placeholder="N° cheque">'+
        '</div>'+
      '</div>' +
      
      '<div class="col-xs-3" style="padding-left:0px">'+
        '<div class="input-group">'+
          '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+
          '<input type="text" autocomplete="off" class="form-control inputCtaCteProveedorMedioPago" id="fechaCheque" placeholder="Fecha Vto. (dd/mm/aaaa)">'+
        '</div>'+
      '</div>');

  } 

  listarMetodosCtaCteProveedor();

});

function listarMetodosCtaCteProveedor(){

  var listaMetodos = "";

  switch($("#nuevoMetodoPagoCtaCteProveedor").val()) {

    case "Efectivo":

      $("#metodoPagoCtaCteProveedor").val("Efectivo");
          
    break;

    case "BO":

      $("#metodoPagoCtaCteProveedor").val("Bonificacion");
          
    break;
    
    case "TD":
        
        $("#metodoPagoCtaCteProveedor").val("TD-"+$("#nuevoCodigoTransaccionCtaCteProveedor").val());

    break;

    case "TC":

        $("#metodoPagoCtaCteProveedor").val("TC-"+$("#seleccionarTarjeta").val()+"-1");

    break;

    case "CH":

      $("#metodoPagoCtaCteProveedor").val("CH-"+$("#bancoOrigenCheque").val() + "-" + $("#numeroCheque").val() + "-" + $("#fechaCheque").val());

    break;

    case "TR":

      $("#metodoPagoCtaCteProveedor").val("TR-"+$("#bancoOrigenTransferencia").val() + "-" + $("#numeroReferenciaTransferencia").val());

    break;
      
  }

}

$(".cajasMetodoPagoCtaCteProveedor").on("change", "#nuevoCodigoTransaccionCtaCteProveedor", function(){  
  listarMetodosCtaCteProveedor();
});

$(".cajasMetodoPagoCtaCteProveedor").on("change", "#seleccionarTarjeta", function(){
  listarMetodosCtaCteProveedor();
});

$(".cajasMetodoPagoCtaCteProveedor").on("change", ".inputCtaCteProveedorMedioPago", function(){
  listarMetodosCtaCteProveedor();
});
