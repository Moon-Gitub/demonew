/*=============================================
VARIABLE LOCAL STORAGE
=============================================*/
if(localStorage.getItem("rangoCajaCentral") != null){
    $("#daterangeCajaCentral span").html(localStorage.getItem("rangoCajaCentral"));
    localStorage.removeItem("rangoCajaCentral");
}else{
    $("#daterangeCajaCentral span").html('<i class="fa fa-calendar"></i> Rango de fecha')
}

/*=============================================
RANGO DE FECHAS
=============================================*/
$('#daterangeCajaCentral').daterangepicker({
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

    $('#daterangeCajaCentral span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    var idCaja = $("#numCaja").val();
    var fechaInicial = start.format('YYYY-MM-DD');
    var fechaFinal = end.format('YYYY-MM-DD');
    var capturarRango = $("#daterangeCajaCentral span").html();
    localStorage.setItem("rangoCajaCentral", capturarRango);
    window.location = "index.php?ruta=cajas&numCaja="+idCaja+"&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;
})

/*=============================================
CANCELAR RANGO DE FECHAS
=============================================*/
$(".daterangepicker.opensright .range_inputs .cancelBtn").on("click", function(){
    localStorage.removeItem("rangoCajaCentral");
    localStorage.clear();
    window.location = "index.php?ruta=cajas";
})

/*=============================================
RANGO DE FECHAS
=============================================*/
$('#daterangeCierresCajas').daterangepicker({
    ranges   : {
      'Ayer'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
      'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
      'Mes Anterior'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    },
    startDate: moment(),
    endDate  : moment()
  },
  function (start, end) {
    $('#daterangeCierresCajas span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    var idCaja = $("#numCaja").val();
    var fechaInicial = start.format('YYYY-MM-DD');
    var fechaFinal = end.format('YYYY-MM-DD');
    var capturarRango = $("#daterangeCierresCajas span").html();
    localStorage.setItem("daterangeCierresCajas", capturarRango);
    window.location = "index.php?ruta=cajas-cierre&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;
})
  
$(".menuCajaCentral").click(function(){
    localStorage.removeItem("rangoCajaCentral");  
});

//AGREGA UN INPUT TEXT PARA BUSCAR EN CADA COLUMNA
$("#tablaCajaCentral tfoot th").each(function (i) {
  var title = $(this).text();
  if(title !== ""){
    $(this).html('<input type="text" placeholder="Filtrar por ' + title + '" />');
  }
});

var cajaCentralTabla = $("#tablaCajaCentral").DataTable( {
    "pageLength": 50,
    "columnDefs": [
      { "targets": [1,2,3,4,5], "orderable": false }],
    "language": GL_DATATABLE_LENGUAJE,
    "dom": 'Bfrtip',
    "buttons":GL_DATATABLE_BOTONES,
    "footerCallback": function (row, data, start, end, display) {
          
        var api = this.api();

        var intVal = function (i) {
            return typeof i === 'string' ?
                i.replace(/[\$]/g, '').replace(/,/g, '.') * 1 :
                typeof i === 'number' ?
                    i : 0;
        };

        var totalPageI = api
            .column(6, {search:'applied'})
            .data()
            .reduce(function (a, b) {
                return intVal(a) + intVal(b);
            }, 0);

        $(api.column(6).footer()).html(
            ` ${totalPageI.toFixed(2)}`
        )

        var totalPageE = api
            .column(7, {search:'applied'}) //, page: current }) (calcula los subtotales solo en la pagina visible)
            .data()
            .reduce(function (a, b) {
                return intVal(a) + intVal(b);
            }, 0);

        $(api.column(7).footer()).html(
            ` ${totalPageE.toFixed(2)}`
        )

    }
});

 cajaCentralTabla.columns().every(function () {
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
AUTOCOMPLETAR DESCRIPCION CAJA
=============================================*/
$( "#ingresoDetalleCajaCentral" ).autocomplete({
  source: function( request, response ) {
    $.ajax( {
      url:"ajax/cajas.ajax.php",
      dataType: "json",
      data: {
        listadoDesc: request.term
      },
      success: function( data ) {
         response( data );
      }
    });        
  },
  minLength: 3,
  focus: function (event, ui) {
        event.preventDefault();
  }
});

/*=============================================
CREAR MOVIMIENTO DE CAJA (AJAX)
=============================================*/
var enviandoMovimientoCaja = false;

$("#formAgregarMovimientoCaja").on("submit", function(e){
  e.preventDefault();
  
  // Prevenir múltiples envíos
  if(enviandoMovimientoCaja) {
    return false;
  }
  
  // Validar campos requeridos
  if(!$("#ingresoCajaTipo").val() || $("#ingresoCajaTipo").val() == "Seleccionar Tipo") {
    swal({
      type: "warning",
      title: "Atención",
      text: "Por favor seleccioná el tipo de movimiento",
      showConfirmButton: true,
      confirmButtonText: "Cerrar"
    });
    return false;
  }
  
  if(!$("#ingresoMontoCajaCentral").val() || parseFloat($("#ingresoMontoCajaCentral").val()) <= 0) {
    swal({
      type: "warning",
      title: "Atención",
      text: "Por favor ingresá un monto válido",
      showConfirmButton: true,
      confirmButtonText: "Cerrar"
    });
    return false;
  }
  
  // Deshabilitar botón y marcar como enviando
  enviandoMovimientoCaja = true;
  $("#btnGuardarMovimientoCaja").prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
  
  // Obtener token CSRF
  var token = $('meta[name="csrf-token"]').attr('content') || $("#csrf_token_movimiento").val();
  
  // Preparar datos
  var datos = new FormData(this);
  datos.append("csrf_token", token);
  
  $.ajax({
    url: "ajax/cajas.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    headers: {
      'X-CSRF-TOKEN': token
    },
    success: function(respuesta){
      
      enviandoMovimientoCaja = false;
      $("#btnGuardarMovimientoCaja").prop("disabled", false).html('Guardar');
      
      if(respuesta && respuesta.status == "ok") {
        
        // Mostrar toast de éxito
        swal({
          type: "success",
          title: "Caja",
          text: respuesta.mensaje,
          toast: true,
          timer: 1500,
          position: "top",
          showConfirmButton: false,
          allowOutsideClick: false
        });
        
        // Cerrar modal y limpiar formulario
        $("#modalAgregarMovimientoCaja").modal("hide");
        $("#formAgregarMovimientoCaja")[0].reset();
        
        // Recargar página después del toast
        setTimeout(function(){
          window.location.reload();
        }, 1500);
        
      } else {
        
        // Mostrar error
        swal({
          type: "error",
          title: "Error",
          text: respuesta && respuesta.mensaje ? respuesta.mensaje : "Error al guardar el movimiento",
          showConfirmButton: true,
          confirmButtonText: "Cerrar"
        });
      }
    },
    error: function(xhr, status, error){
      
      enviandoMovimientoCaja = false;
      $("#btnGuardarMovimientoCaja").prop("disabled", false).html('Guardar');
      
      var mensajeError = "Error al guardar el movimiento";
      
      if(xhr.status == 403) {
        mensajeError = "Token CSRF inválido. Por favor, recargá la página.";
      } else if(xhr.status == 500) {
        mensajeError = "Error del servidor. Por favor, intentá nuevamente.";
      }
      
      swal({
        type: "error",
        title: "Error",
        text: mensajeError,
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
      });
      
      console.error("Error AJAX:", status, error, xhr.responseText);
    }
  });
  
  return false;
});

//BOTON PARA VISUALIZAR CAJAS 
$("#aCajaVerCajas").click(function(){
  var caja = $("#cajasListadoPuntosVta").val();
  $("#aCajaVerCajas").attr('href', 'index.php?ruta=cajas&numCaja='+caja);
});

//BOTON PARA VIZUALIZAR CIERRES
/*$(".btnCierreCaja").click(function(){

  var esteCierre = $(this).attr('idCierreCaja');
  window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/resumenCierre.php?idCierre="+esteCierre, "_blank");

});*/  

$(".tablaCierresCaja").on("click", "a.btnCierreCaja", function(e){ 
  e.preventDefault();
  var valor = $(this).attr('idCierreCaja');
  
  console.log("ID del cierre obtenido:", valor);
  
  if(!valor) {
    swal({
      type: "error",
      title: "Error",
      text: "No se pudo obtener el ID del cierre"
    });
    return;
  }
  
  // Abrir el modal primero
  $("#modalVerCierreCaja").modal("show");
  
  // Limpiar campos del modal
  $("#resumenCierreCajaFecha").text("");
  $("#resumenCierreCajaPunto").text("");
  $("#resumenCierreCajaUsuario").text("");
  $("#resumenCierreCajaApertura").text("");
  $("#resumenCierreCajaDetalle").text("");
  $("#resumenCierreTotalIngresos").text("");
  $("#resumenCierreTotalEgresos").text("");
  
  // Limpiar tablas
  $("#tblIngresosCategoriasResumenCierreCaja").empty();
  $("#tblIngresosClientesResumenCierreCaja").empty();
  $("#tblIngresosVariosResumenCierreCaja").empty();
  $("#tblIngresosDetalleMediosPago").empty();
  $("#tblEgresosComunesResumenCierreCaja").empty();
  $("#tblEgresosProveedoresResumenCierreCaja").empty();
  $("#tblEgresosDetalleMediosPago").empty();
  
  var datos = new FormData();
  datos.append("esteCierre", valor);
  
  // Agregar token CSRF si existe
  var token = $('meta[name="csrf-token"]').attr('content');
  if(token) {
    datos.append("csrf_token", token);
  }
  
  $.ajax({
    url:"ajax/cajas.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType:"json",
    headers: token ? { 'X-CSRF-TOKEN': token } : {},
    success:function(respuesta){
      console.log("Respuesta completa:", respuesta);
      
      // Validar que la respuesta tenga la estructura esperada
      if(!respuesta || typeof respuesta !== 'object') {
        console.error("Error: Respuesta inválida o no es un objeto", respuesta);
        swal({
          type: "error",
          title: "Error",
          text: "No se pudieron cargar los datos del cierre. La respuesta del servidor es inválida.",
          showConfirmButton: true,
          confirmButtonText: "Cerrar"
        });
        return;
      }
      
      // Si hay un error en la respuesta, mostrarlo
      if(respuesta["error"]) {
        console.error("Error del servidor:", respuesta["error"]);
        swal({
          type: "error",
          title: "Error",
          text: respuesta["error"],
          showConfirmButton: true,
          confirmButtonText: "Cerrar"
        });
        return;
      }
      
      // Validar que tenga la clave "otros"
      if(!respuesta["otros"]) {
        console.error("Error: No se encontraron datos del cierre", respuesta);
        swal({
          type: "error",
          title: "Error",
          text: "No se encontraron datos para este cierre. Puede que el cierre no exista o haya sido eliminado.",
          showConfirmButton: true,
          confirmButtonText: "Cerrar"
        });
        return;
      }
      
      // Si otros está vacío pero existe, intentar continuar con datos por defecto
      if(typeof respuesta["otros"] === 'object' && Object.keys(respuesta["otros"]).length === 0) {
        console.warn("Advertencia: El cierre existe pero no tiene datos completos", respuesta);
        // Continuar pero con valores por defecto
        respuesta["otros"] = {
          fecha_hora: "",
          punto_venta_cobro: "",
          id_usuario_cierre: "",
          apertura_siguiente_monto: "0",
          detalle: "",
          total_ingresos: "0",
          total_egresos: "0"
        };
      }
      
      // Llenar campos del resumen
      $("#resumenCierreCajaFecha").text(respuesta["otros"]["fecha_hora"] || "");
      $("#resumenCierreCajaPunto").text(respuesta["otros"]["punto_venta_cobro"] || "");
      $("#resumenCierreCajaUsuario").text(respuesta["otros"]["id_usuario_cierre"] || "");
      $("#resumenCierreCajaApertura").text(respuesta["otros"]["apertura_siguiente_monto"] || "0");
      $("#resumenCierreCajaDetalle").text(respuesta["otros"]["detalle"] || "");
      $("#resumenCierreTotalIngresos").text(respuesta["otros"]["total_ingresos"] || "0");
      $("#resumenCierreTotalEgresos").text(respuesta["otros"]["total_egresos"] || "0");

      // Obtener arrays de ingresos y egresos
      var jsonIngresos = respuesta["ingresos"] || [];
      var jsonEgresos = respuesta["egresos"] || [];
      var jsonOtrosIn = respuesta["otros"]["detalle_ingresos"] || "[]";
      var jsonOtrosEg = respuesta["otros"]["detalle_egresos"] || "[]";

      // Limpiar tablas
      $("#tblIngresosCategoriasResumenCierreCaja").empty();
      $("#tblIngresosClientesResumenCierreCaja").empty();
      $("#tblIngresosVariosResumenCierreCaja").empty();
      $("#tblIngresosDetalleMediosPago").empty();

      $("#tblEgresosComunesResumenCierreCaja").empty();
      $("#tblEgresosProveedoresResumenCierreCaja").empty();
      $("#tblEgresosDetalleMediosPago").empty();

      // Procesar ingresos
      if(Array.isArray(jsonIngresos)) {
        for(var i = 0; i < jsonIngresos.length; i++){
          if(jsonIngresos[i] && jsonIngresos[i]["monto"] > 0) {
            var monto = parseFloat(jsonIngresos[i]["monto"] || 0).toFixed(2);
            if(jsonIngresos[i]["tipo"] == "categoria") {
              $("#tblIngresosCategoriasResumenCierreCaja").append("<tr><td>"+(jsonIngresos[i]["descripcion"] || "")+"</td><td> <b>$ "+monto+"</b></td></tr>");
            } else if(jsonIngresos[i]["tipo"] == "cliente") {
              $("#tblIngresosClientesResumenCierreCaja").append("<tr><td>"+(jsonIngresos[i]["descripcion"] || "")+"</td><td> <b>$ "+monto+"</b></td></tr>");
            } else {
              $("#tblIngresosVariosResumenCierreCaja").append("<tr><td>"+(jsonIngresos[i]["descripcion"] || "")+"</td><td> <b>$ "+monto+"</b></td></tr>");
            }
          }
        }
      }

      // Procesar egresos
      if(Array.isArray(jsonEgresos)) {
        for(var i = 0; i < jsonEgresos.length; i++){
          if(jsonEgresos[i] && jsonEgresos[i]["monto"] > 0) {
            var monto = parseFloat(jsonEgresos[i]["monto"] || 0).toFixed(2);
            if(jsonEgresos[i]["tipo"] == "comun") {
              $("#tblEgresosComunesResumenCierreCaja").append("<tr><td>"+(jsonEgresos[i]["descripcion"] || "")+"</td><td> <b>$ "+monto+"</b></td></tr>");
            } else if(jsonEgresos[i]["tipo"] == "proveedor") {
              $("#tblEgresosProveedoresResumenCierreCaja").append("<tr><td>"+(jsonEgresos[i]["descripcion"] || "")+"</td><td> <b>$ "+monto+"</b></td></tr>");
            } 
          }
        }
      }
      
      // Procesar detalle de medios de pago (ingresos)
      try {
        if(typeof jsonOtrosIn === 'string' && jsonOtrosIn !== "") {
          jsonOtrosIn = JSON.parse(jsonOtrosIn);
        }
        if(Array.isArray(jsonOtrosIn)) {
          for(var i = 0; i < jsonOtrosIn.length; i++){
            if(jsonOtrosIn[i]) {
              var keys = Object.keys(jsonOtrosIn[i]);
              var values = Object.values(jsonOtrosIn[i]);
              if(keys.length > 0 && values.length > 0) {
                var monto = parseFloat(values[0] || 0).toFixed(2);
                $("#tblIngresosDetalleMediosPago").append("<tr><td>"+keys[0]+"</td><td> <b>$ "+monto+"</b></td></tr>");
              }
            }
          }
        }
      } catch(e) {
        console.error("Error al parsear detalle ingresos:", e);
      }
      
      // Procesar detalle de medios de pago (egresos)
      try {
        if(typeof jsonOtrosEg === 'string' && jsonOtrosEg !== "") {
          jsonOtrosEg = JSON.parse(jsonOtrosEg);
        }
        if(Array.isArray(jsonOtrosEg)) {
          for(var i = 0; i < jsonOtrosEg.length; i++){
            if(jsonOtrosEg[i]) {
              var keys = Object.keys(jsonOtrosEg[i]);
              var values = Object.values(jsonOtrosEg[i]);
              if(keys.length > 0 && values.length > 0) {
                var monto = parseFloat(values[0] || 0).toFixed(2);
                $("#tblEgresosDetalleMediosPago").append("<tr><td>"+keys[0]+"</td><td> <b>$ "+monto+"</b></td></tr>");
              }
            }
          }
        }
      } catch(e) {
        console.error("Error al parsear detalle egresos:", e);
      }
    },
    error: function(xhr, status, error) {
      console.error("Error al cargar cierre de caja:", {
        status: xhr.status,
        statusText: xhr.statusText,
        error: error,
        responseText: xhr.responseText
      });
      
      var mensajeError = "Error al cargar los datos del cierre";
      if(xhr.status === 403) {
        mensajeError = "Error de seguridad (CSRF). Por favor, recargá la página.";
      } else if(xhr.status === 404) {
        mensajeError = "No se encontró el cierre solicitado";
      } else if(xhr.responseText) {
        try {
          var errorResponse = JSON.parse(xhr.responseText);
          if(errorResponse.mensaje || errorResponse.error) {
            mensajeError = errorResponse.mensaje || errorResponse.error;
          }
        } catch(e) {
          // Si no es JSON, usar el mensaje por defecto
        }
      }
      
      swal({
        type: "error",
        title: "Error",
        text: mensajeError,
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
      });
    }, 
    timeout: 10000
  });
});

$(".tablaCierresCaja").on("click", "a.btnListadoCierreCaja", function(e){ 
  e.preventDefault();
  var valor = $(this).attr('idCierreCaja');
  
  if(!valor) {
    swal({
      type: "error",
      title: "Error",
      text: "No se pudo obtener el ID del cierre"
    });
    return;
  }
  
  // Obtener token CSRF
  var token = $('meta[name="csrf-token"]').attr('content');
  
  var datos = new FormData();
  datos.append("esteCierreListado", valor);
  if(token) {
    datos.append("csrf_token", token);
  }
  
  $.ajax({
    url:"ajax/cajas.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType:"json",
    headers: token ? { 'X-CSRF-TOKEN': token } : {},
    success:function(respuesta){
      
      // Validar que la respuesta sea un array
      if(!Array.isArray(respuesta)) {
        console.error("Error: Respuesta no es un array", respuesta);
        swal({
          type: "error",
          title: "Error",
          text: "No se pudieron cargar los movimientos del cierre",
          showConfirmButton: true,
          confirmButtonText: "Cerrar"
        });
        return;
      }
      
      // Mostrar contenedor y limpiar tabla
      $("#listadoMovCierreCajaContenedor").css('display', '');
      var tableBody = $("#listadoMovCierreCajaTabla tbody");
      tableBody.empty();
      
      // Si no hay movimientos
      if(respuesta.length === 0) {
        tableBody.append("<tr><td colspan='8' class='text-center'>No hay movimientos para este cierre</td></tr>");
        return;
      }
      
      // Llenar tabla con movimientos
      respuesta.forEach(function (item, index){
        if(!item) return;
        
        var markup = "<tr>";
        markup += "<td>" + (item.fecha || "") + "</td>";
        markup += "<td>" + (item.id || "") + "</td>";
        markup += "<td>" + (item.nombre || "") + "</td>";
        markup += "<td>" + (item.punto_venta || "") + "</td>";
        markup += "<td>" + (item.descripcion || "") + "</td>";
        markup += "<td>" + (item.medio_pago || "") + "</td>";
        
        if(item.tipo === "0" || item.tipo === 0){
          markup += "<td></td>";
          markup += "<td style='color: red;'>" + (parseFloat(item.monto || 0).toFixed(2)) + "</td>";
        } else {
          markup += "<td style='color: green;'>" + (parseFloat(item.monto || 0).toFixed(2)) + "</td>";
          markup += "<td></td>";
        }
        markup += "</tr>";
        tableBody.append(markup);
      });
      
      // Inicializar DataTable si no está inicializado
      if(!$.fn.DataTable.isDataTable("#listadoMovCierreCajaTabla")) {
        $("#listadoMovCierreCajaTabla").DataTable({
          "language": GL_DATATABLE_LENGUAJE,
          "pageLength": 25,
          "order": [[0, "desc"]]
        });
      } else {
        $("#listadoMovCierreCajaTabla").DataTable().ajax.reload();
      }
    },
    error: function(xhr, status, error) {
      console.error("Error al cargar movimientos de cierre:", {
        status: xhr.status,
        statusText: xhr.statusText,
        error: error,
        responseText: xhr.responseText
      });
      
      var mensajeError = "Error al cargar los movimientos del cierre";
      if(xhr.status === 403) {
        mensajeError = "Error de seguridad (CSRF). Por favor, recargá la página.";
      } else if(xhr.status === 404) {
        mensajeError = "No se encontraron movimientos para este cierre";
      }
      
      swal({
        type: "error",
        title: "Error",
        text: mensajeError,
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
      });
    }, 
    timeout: 10000
  });
});