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
      console.log(respuesta);
      $("#resumenCierreCajaFecha").text(respuesta["otros"]["fecha_hora"]);
      $("#resumenCierreCajaPunto").text(respuesta["otros"]["punto_venta_cobro"]);
      $("#resumenCierreCajaUsuario").text(respuesta["otros"]["id_usuario_cierre"]);
      $("#resumenCierreCajaApertura").text(respuesta["otros"]["apertura_siguiente_monto"]);
      $("#resumenCierreCajaDetalle").text(respuesta["otros"]["detalle"]);
      $("#resumenCierreTotalIngresos").text(respuesta["otros"]["total_ingresos"]);
      $("#resumenCierreTotalEgresos").text(respuesta["otros"]["total_egresos"]);

      //var jsonProductos = JSON.parse($("#listaProductosCaja").val());
      var jsonIngresos = respuesta["ingresos"];
      var jsonEgresos = respuesta["egresos"];
      var jsonOtrosIn = respuesta["otros"]["detalle_ingresos"];
      var jsonOtrosEg = respuesta["otros"]["detalle_egresos"];

      $("#tblIngresosCategoriasResumenCierreCaja").empty();
      $("#tblIngresosClientesResumenCierreCaja").empty();
      $("#tblIngresosVariosResumenCierreCaja").empty();
      $("#tblIngresosDetalleMediosPago").empty();

      $("#tblEgresosComunesResumenCierreCaja").empty();
      $("#tblEgresosProveedoresResumenCierreCaja").empty();
      $("#tblEgresosDetalleMediosPago").empty();

      for(var i = 0; i < jsonIngresos.length; i++){

        if(jsonIngresos[i]["tipo"] == "categoria") {
          $("#tblIngresosCategoriasResumenCierreCaja").append("<tr><td>"+jsonIngresos[i]["descripcion"]+"</td><td> <b>$ "+jsonIngresos[i]["monto"]+"</b></td></tr>");
        } else if(jsonIngresos[i]["tipo"] == "cliente") {
          $("#tblIngresosClientesResumenCierreCaja").append("<tr><td>"+jsonIngresos[i]["descripcion"]+"</td><td> <b>$ "+jsonIngresos[i]["monto"]+"</b></td></tr>");
        } else {
          $("#tblIngresosVariosResumenCierreCaja").append("<tr><td>"+jsonIngresos[i]["descripcion"]+"</td><td> <b>$ "+jsonIngresos[i]["monto"]+"</b></td></tr>");
        }
      }

      for(var i = 0; i < jsonEgresos.length; i++){
        if(jsonEgresos[i]["tipo"] == "comun") {
          $("#tblEgresosComunesResumenCierreCaja").append("<tr><td>"+jsonEgresos[i]["descripcion"]+"</td><td> <b>$ "+jsonEgresos[i]["monto"]+"</b></td></tr>");
        } else if(jsonEgresos[i]["tipo"] == "proveedor") {
          $("#tblEgresosProveedoresResumenCierreCaja").append("<tr><td>"+jsonEgresos[i]["descripcion"]+"</td><td> <b>$ "+jsonEgresos[i]["monto"]+"</b></td></tr>");
        } 
      }
      jsonOtrosIn = JSON.parse(jsonOtrosIn);
      //var valores = Object.values(jsonOtrosIn)
      for(var i = 0; i < jsonOtrosIn.length; i++){
          $("#tblIngresosDetalleMediosPago").append("<tr><td>"+Object.keys(jsonOtrosIn[i])+"</td><td>  <b>$ "+Object.values(jsonOtrosIn[i])+" </b></td></tr>");
      }
      jsonOtrosEg = JSON.parse(jsonOtrosEg);
      for(var i = 0; i < jsonOtrosEg.length; i++){
          $("#tblEgresosDetalleMediosPago").append("<tr><td>"+Object.keys(jsonOtrosEg[i])+"</td><td> <b>$ "+Object.values(jsonOtrosEg[i])+" </b></td></tr>");
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