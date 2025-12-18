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

$(".tablaCierresCaja").on("click", "button.btnCierreCaja", function(){ 
  var valor = $(this).attr('idCierreCaja');
  var datos = new FormData();
  datos.append("esteCierre", valor);  
  $.ajax({
    url:"ajax/cajas.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType:"json",
    success:function(respuesta){
      console.log("Respuesta completa:", respuesta);
      
      // Validar que la respuesta tenga la estructura esperada
      if(!respuesta || !respuesta["otros"]) {
        console.error("Error: Respuesta inválida", respuesta);
        swal({
          type: "error",
          title: "Error",
          text: "No se pudieron cargar los datos del cierre",
          showConfirmButton: true,
          confirmButtonText: "Cerrar"
        });
        return;
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
      console.log( xhr.responseText);
      console.log( xhr);
      console.log( status);
      console.log( error);
    }, timeout: 5000
  });
});

$(".tablaCierresCaja").on("click", "button.btnListadoCierreCaja", function(){ 
  var valor = $(this).attr('idCierreCaja');
  var datos = new FormData();
  datos.append("esteCierreListado", valor);  
  $.ajax({
    url:"ajax/cajas.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType:"json",
    success:function(respuesta){
        $("#listadoMovCierreCajaContenedor").css('display', '');
        tableBody = $("#listadoMovCierreCajaTabla tbody");
        tableBody.empty();
        respuesta.forEach(function (item, index){
            markup = "<tr>";
         	markup += "<td>" + item.fecha + "</td>";
         	markup += "<td>" + item.id + "</td>";
         	markup += "<td>" + item.nombre + "</td>";
         	markup += "<td>" + item.punto_venta + "</td>";
         	markup += "<td>" + item.descripcion + "</td>";
         	markup += "<td>" + item.medio_pago + "</td>";
         	if(item.tipo === "0"){
         	    markup += "<td></td>";
         	    markup += "<td>" + item.monto + "</td>";
         	} else {
         	    markup += "<td>" + item.monto + "</td>";
         	    markup += "<td></td>";
         	}
         	markup += "</tr>";
            tableBody.append(markup);
         });
    },
    error: function(xhr, status, error) {
      console.log( xhr.responseText);
      console.log( xhr);
      console.log( status);
      console.log( error);
    }, timeout: 5000
  });
});