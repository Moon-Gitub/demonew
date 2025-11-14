//LISTADO CLIENTES
$(".tablasBotonesCtaCteCliente").DataTable({
  "dom": 'Bfrtip',
  "buttons":GL_DATATABLE_BOTONES, 
  "language": GL_DATATABLE_LENGUAJE
});

//EDITAR CLIENTE
$(".tablasBotonesCtaCteCliente").on("click", ".btnEditarCliente", function(){
	var idCliente = $(this).attr("idCliente");
	var datos = new FormData();
  datos.append("idCliente", idCliente);
  $.ajax({
    url:"ajax/clientes.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType:"json",
    success:function(respuesta){
      	 $("#idCliente").val(respuesta["id"]);
	       $("#editarTipoDocumento").val(respuesta["tipo_documento"]);
         $("#editarDocumentoId").val(respuesta["documento"]);
         $("#editarCliente").val(respuesta["nombre"]);
	       $("#editarCondicionIva").val(respuesta["condicion_iva"]);
	       $("#editarEmail").val(respuesta["email"]);
	       $("#editarTelefono").val(respuesta["telefono"]);
	       $("#editarDireccion").val(respuesta["direccion"]);
         $("#editarFechaNacimiento").val(respuesta["fecha_nacimiento"]);
         $("#editarObservacionesCliente").val(respuesta["observaciones"]);
      }
  	})
});

//ELIMINAR CLIENTE
$(".tablasBotonesCtaCteCliente").on("click", ".btnEliminarCliente", function(){
	var idCliente = $(this).attr("idCliente");
	swal({
        title: '¿Está seguro de borrar el cliente?',
        text: "¡Si no lo está puede cancelar la acción!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Si, borrar cliente!'
      }).then(function(result){
        if (result.value) {
            window.location = "index.php?ruta=clientes&idCliente="+idCliente;
        }
    })
});

//BOTON BUSCAR EN PADRON DE AFIP (CREAR VENTA)
$("#vtabtnNuevoDocumentoId").click(function(){
  const p1 = new Promise(resolve => {
      resolve(cuitsAfip($("#vtanuevoDocumentoId").val()))
  });
  
  Promise.all([p1]).then(arrDatos => {
    arrDatos = arrDatos[0]
    if(arrDatos){
        var nombre = (arrDatos['personaReturn']['persona'].hasOwnProperty('apellido')) ? arrDatos["personaReturn"]["persona"]["apellido"] + ' ' + arrDatos["personaReturn"]["persona"]["nombre"] : arrDatos["personaReturn"]["persona"]["razonSocial"];
        $("#vtanuevoCliente").val(nombre);
        var tipoClave = arrDatos['personaReturn']["persona"]['tipoClave'];
        if($("#vtanuevoDocumentoId").val().length > 8) {
            $("#vtanuevoTipoDocumento option").filter(function() {return $(this).text() == tipoClave; }).prop('selected', true);
        } else {
            $("#vtanuevoTipoDocumento").val(96);
        }
        if(arrDatos['personaReturn']["persona"].hasOwnProperty('domicilio')){
          var direccion = arrDatos['personaReturn']["persona"]['domicilio'];
          direccion = (Array.isArray(direccion)) ? direccion[0]['direccion'] +' - '+ direccion[0]['localidad'] +' | '+ direccion[0]['descripcionProvincia'] : direccion['direccion'] +' - '+ direccion['localidad'] +' | '+ direccion['descripcionProvincia'];
          $("#vtanuevaDireccion").val(direccion);
        }
        if(arrDatos['personaReturn']["persona"].hasOwnProperty('email')){
          var email = arrDatos['personaReturn']["persona"]['email'];
          email = (Array.isArray(email)) ? email[0]['direccion'] : email['direccion'];
          $("#vtanuevoEmail").val(email);
        }
        if(arrDatos['personaReturn']["persona"].hasOwnProperty('telefono')){
          var telefono = arrDatos['personaReturn']["persona"]['telefono'];
          telefono = (Array.isArray(telefono)) ? telefono[0]['numero'] : telefono['numero'];
          $("#vtanuevoTelefono").val(telefono);
        }
    }  
  });
});

//CREAR CLIENTE DESDE VENTAS
$("#btnGuardarClienteVenta").click(function(e){
  e.preventDefault(); //Esta linea anula el submit para que no llame al controlador
  if($("#vtanuevoTipoDocumento").val() == "" || $("#vtanuevoDocumentoId").val() == "" || $("#vtanuevoCliente").val() == ""){
      swal({
          title: "Cliente",
          text: "Debe completar los campos obligatorios",
          type: "error",
          toast: true,
          position: 'top',
          showConfirmButton: false,
          timer: 3000
        }); 
    return;
  }

  var datosVentaCliente = new FormData();
  datosVentaCliente.append("nuevoTipoDocumento", $("#vtanuevoTipoDocumento").val());
  datosVentaCliente.append("nuevoDocumentoId", $("#vtanuevoDocumentoId").val());
  datosVentaCliente.append("nuevoCliente", $("#vtanuevoCliente").val());  
  datosVentaCliente.append("nuevoCondicionIva", $("#vtanuevoCondicionIva").val());
  datosVentaCliente.append("nuevoEmail", $("#vtanuevoEmail").val());
  datosVentaCliente.append("nuevoTelefono", $("#vtanuevoTelefono").val());
  datosVentaCliente.append("nuevaDireccion", $("#vtanuevaDireccion").val());
  datosVentaCliente.append("nuevaFechaNacimiento", $("#vtanuevaFechaNacimiento").val());
  datosVentaCliente.append("observaciones", $("#vtaObservacionesCliente").val());

  $.ajax({

    url:"ajax/clientes.ajax.php",
    method: "POST",
    data: datosVentaCliente,
    cache: false,
    contentType: false,
    processData: false,
    dataType:"json",
    success:function(respuesta){
                
      if(!isNaN(respuesta) && respuesta !== 0) {

        swal({
          title: "Cliente",
          text: "Cliente guardado correctamente",
          type: "success",
          toast: true,
          position: 'top',
          showConfirmButton: false,
          timer: 3000
        });

        $("#autocompletarClienteCaja").val($("#vtanuevoCliente").val() + ' ' + $('#vtanuevoTipoDocumento').find('option:selected').text()+': ' + $("#vtanuevoDocumentoId").val());
        $("#seleccionarCliente").val(respuesta);

      } else {

        var msjError = respuesta.hasOwnProperty(2) ? respuesta[2] : 'Error desconocido';

         swal({
          title: "Cliente",
          text: "Error al guardar el cliente: " + msjError,
          type: "error",
          toast: true,
          position: 'top',
          showConfirmButton: false,
          timer: 3000
        });

      }

      $("#modalAgregarCliente").modal('hide');
      $("#vtanuevoCliente").val("");
      $("#vtanuevoTipoDocumento").val("");
      $("#vtanuevoCondicionIva").val("");
      $("#vtanuevoDocumentoId").val("");
      $("#vtanuevoEmail").val("");
      $("#vtanuevoTelefono").val("");
      $("#vtanuevaDireccion").val("");
      $("#vtanuevaFechaNacimiento").val("");
      $("#vtaObservacionesCliente").val("");

    }, 
    error: function(xhr, status, error) {
      
      console.log( xhr.responseText);

      swal({
          title: "Ventas",
          text: "Error al guardar cliente",
          type: "error",
          toast: true,
          position: 'top',
          showConfirmButton: false,
          timer: 3000
        });
    }

  });

});

/*===============CUENTA CORRIENTE CLIENTE===========*/
//CUENTA CORRIENTE CLIENTE TIPO DE MOVIMIENTO
$(".tablasBotonesCtaCteCliente2").DataTable({
  "dom": 'Bfrtip',
  "buttons":GL_DATATABLE_BOTONES, 
  "language": GL_DATATABLE_LENGUAJE,
  "initComplete": function() { //esta funcion lleva a la ultima hoja de la tabla, para ver los ultimos movimientos
      var api = this.api();
      var info = api.page.info();
      api.page(info.pages - 1).draw(false);
    }
});

$("#tipoMovimientoCtaCteCliente").change(function(){
  if($(this).val() == 0) {
    //Debito (no se agrega en caja)
    $(".ctacteClienteCaja").css('display', 'none');
    $("#nuevoMetodoPagoCtaCteCliente").prop('required',false);
    $("#detalleMovimientoCtaCteCliente").val('');
  } else {
    //Pago (agrega tambien en caja)
    $(".ctacteClienteCaja").css('display', 'block');
    $("#nuevoMetodoPagoCtaCteCliente").prop('required',true);
    $("#detalleMovimientoCtaCteCliente").val('Ingreso por cobro Cta. Cte. cliente: ' + $("#spanNombreClienteCtaCte").text());
  }
});

//METODOS DE PAGO EN CTA CTE CLIENTE
$("#nuevoMetodoPagoCtaCteCliente").change(function(){
  if($("#nuevoMetodoPagoCtaCteCliente").val() == "BO") {
    $("#detalleMovimientoCtaCteCliente").val('Bonificación: ' + $("#spanNombreClienteCtaCte").text());
  } else {
    $("#detalleMovimientoCtaCteCliente").val('Ingreso por cobro Cta. Cte. cliente: ' + $("#spanNombreClienteCtaCte").text());
  }

  var cero = 0;

  $('.cajasMetodoPagoCtaCteCliente').html('<div class="col-xs-6" style="padding-left:0px"></div>');

  if($("#nuevoMetodoPagoCtaCteCliente").val() == "TC"){

    $(".cajasMetodoPagoCtaCteCliente").html('<div class="col-xs-4" style="padding-left:0px"></div>');

  }

  if($("#nuevoMetodoPagoCtaCteCliente").val() == "TD"){

    $(".cajasMetodoPagoCtaCteCliente").html('<div class="col-xs-4" style="padding-left:0px"></div>');

  }

  if($("#nuevoMetodoPagoCtaCteCliente").val() == "TR"){ //Transferencia

    $(".cajasMetodoPagoCtaCteCliente").html(

      '<div class="col-xs-4" style="padding-left:0px">'+
        '<div class="input-group">'+
          '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+
          '<input type="text" autocomplete="off" class="form-control inputCtaCteClienteMedioPago" id="bancoOrigenTransferencia" placeholder="Banco origen">'+
        '</div>'+
      '</div>' + 

      '<div class="col-xs-3" style="padding-left:0px">'+
        '<div class="input-group">'+
          '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+
          '<input type="text" autocomplete="off" class="form-control inputCtaCteClienteMedioPago" id="numeroReferenciaTransferencia" placeholder="N° referencia">'+
        '</div>'+
      '</div>');

  }

  if($("#nuevoMetodoPagoCtaCteCliente").val() == "CH"){ //Cheque

      $(".cajasMetodoPagoCtaCteCliente").html(

      '<div class="col-xs-4" style="padding-left:0px">'+
        '<div class="input-group">'+
          '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+
          '<input type="text" autocomplete="off" class="form-control inputCtaCteClienteMedioPago" id="bancoOrigenCheque" placeholder="Banco origen">'+
        '</div>'+
      '</div>' + 

      '<div class="col-xs-3" style="padding-left:0px">'+
        '<div class="input-group">'+
          '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+
          '<input type="text" autocomplete="off" class="form-control inputCtaCteClienteMedioPago" id="numeroCheque" placeholder="N° cheque">'+
        '</div>'+
      '</div>' +
      
      '<div class="col-xs-3" style="padding-left:0px">'+
        '<div class="input-group">'+
          '<span class="input-group-addon"><i class="fa fa-lock"></i></span>'+
          '<input type="text" autocomplete="off" class="form-control inputCtaCteClienteMedioPago" id="fechaCheque" placeholder="Fecha Vto. (dd/mm/aaaa)">'+
        '</div>'+
      '</div>');
  } 
  listarMetodosCtaCteCliente();
});

function listarMetodosCtaCteCliente(){
  var listaMetodos = "";
  switch($("#nuevoMetodoPagoCtaCteCliente").val()) {
    case "Efectivo":
      $("#metodoPagoCtaCteCliente").val("Efectivo");
    break;
    case "BO":
      $("#metodoPagoCtaCteCliente").val("Bonificacion");
    break;
    case "TD":
        $("#metodoPagoCtaCteCliente").val("TD-"+$("#nuevoCodigoTransaccionCtaCteCliente").val());
    break;
    case "TC":
        $("#metodoPagoCtaCteCliente").val("TC-"+$("#seleccionarTarjeta").val()+"-1");
    break;
    case "CH":
      $("#metodoPagoCtaCteCliente").val("CH-"+$("#bancoOrigenCheque").val() + "-" + $("#numeroCheque").val() + "-" + $("#fechaCheque").val());
    break;
    case "TR":
      $("#metodoPagoCtaCteCliente").val("TR-"+$("#bancoOrigenTransferencia").val() + "-" + $("#numeroReferenciaTransferencia").val());
    break;   
  }
}

$(".cajasMetodoPagoCtaCteCliente").on("change", "#nuevoCodigoTransaccionCtaCteCliente", function(){  
  listarMetodosCtaCteCliente();
});

$(".cajasMetodoPagoCtaCteCliente").on("change", "#seleccionarTarjeta", function(){
  listarMetodosCtaCteCliente();
});

$(".cajasMetodoPagoCtaCteCliente").on("change", ".inputCtaCteClienteMedioPago", function(){
  listarMetodosCtaCteCliente();
});

//ARMO MENSAJE MAIL EN MODAL (CTA CTE CLIENTE)
$(".tablasBotones").on("click", ".btnSobreCtaCteCliente", function(){
    var idCliente = $(this).attr('idCliente');
    var mailCliente = $(this).attr('mailCliente');
    var saldo = '$ ' + $(this).attr('saldoCliente');
    var txt = 'Estimado Cliente, le recordamos que al día de la fecha usted registra un saldo de ' + saldo + '.\nPedimos por favor regularizar su situación.\nMuchas gracias\n\n'+$("#datosEmpresaCtaCteCliente").text();
    $("#emailConfiguradoCtaCteCliente").val(mailCliente);
    $("#mensajeCtaCteCliente").text(txt);
});


$("#btnEnviarMailCtaCteCliente").click(function(){
   
	var datos = new FormData();
  datos.append("mailCliente", $("#emailConfiguradoCtaCteCliente").val());
  datos.append("textoCliente", $("#mensajeCtaCteCliente").text());
  datos.append("adjuntoCliente", $("#chkEnviarMailAdjunto").is(":checked"));

  $.ajax({

    url:"ajax/clientes_cta_cte.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType:"json",
    success:function(respuesta){
      console.log(respuesta);
      swal({
      	   title: "Ventas",
      	   text: "E-mail enviado correctamente",
      	   toast: true,
      	   timer: 3000,
      	   position: 'top',
      	   type: "success",
      	   confirmButtonText: "¡Cerrar!"
      	 });

    },
    error: function(xhr, status, error) {
        console.log( xhr.responseText);
        swal({
          title: "Error",
          text: "Error inesperado al intentar enviar email",
          toast: true,
          timer: 3000,
          position: 'top',
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });
        return false;
    }
	})
});

//BOTON BUSCAR EN PADRON DE AFIP
$("#btnNuevoDocumentoId").click(function(){
  
  const p1 = new Promise(resolve => {
      resolve(cuitsAfip($("#nuevoDocumentoId").val()))
  });
  
  Promise.all([p1]).then(arrDatos => {
    arrDatos = arrDatos[0]
    if(arrDatos){
        var nombre = (arrDatos['personaReturn']['persona'].hasOwnProperty('apellido')) ? arrDatos["personaReturn"]["persona"]["apellido"] + ' ' + arrDatos["personaReturn"]["persona"]["nombre"] : arrDatos["personaReturn"]["persona"]["razonSocial"];
        $("#nuevoCliente").val(nombre);
        var tipoClave = arrDatos['personaReturn']["persona"]['tipoClave'];
        if($("#nuevoDocumentoId").val().length > 8) {
            $("#nuevoTipoDocumento option").filter(function() {return $(this).text() == tipoClave; }).prop('selected', true);
        } else {
            $("#nuevoTipoDocumento").val(96);
        }
        if(arrDatos['personaReturn']["persona"].hasOwnProperty('domicilio')){
          var direccion = arrDatos['personaReturn']["persona"]['domicilio'];
          direccion = (Array.isArray(direccion)) ? direccion[0]['direccion'] +' - '+ direccion[0]['localidad'] +' | '+ direccion[0]['descripcionProvincia'] : direccion['direccion'] +' - '+ direccion['localidad'] +' | '+ direccion['descripcionProvincia'];
          $("#nuevaDireccion").val(direccion);
        }
        if(arrDatos['personaReturn']["persona"].hasOwnProperty('email')){
          var email = arrDatos['personaReturn']["persona"]['email'];
          email = (Array.isArray(email)) ? email[0]['direccion'] : email['direccion'];
          $("#nuevoEmail").val(email);
        }
        if(arrDatos['personaReturn']["persona"].hasOwnProperty('telefono')){
          var telefono = arrDatos['personaReturn']["persona"]['telefono'];
          telefono = (Array.isArray(telefono)) ? telefono[0]['numero'] : telefono['numero'];
          $("#nuevoTelefono").val(telefono);
        }
    }  
  });
});