<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Informe ventas por productos
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Ventas por producto</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header with-border">
         <button type="button" class="btn btn-default pull-right" id="btnInformeVentaProductoRango">
            <span>
              <i class="fa fa-calendar"></i> 
              <?php
                if(isset($_GET["fechaInicial"])){
                  echo $_GET["fechaInicial"]." - ".$_GET["fechaFinal"];
                }else{
                  echo 'Hoy';
                }
              ?>
            </span>

            <i class="fa fa-caret-down"></i>
         </button>
      </div>
      <div class="box-body">
       <table class="table table-bordered table-striped " id="tablaListarProductosPorVenta" width="100%">
        <thead>
         <tr>
           <th>Fecha</th>
           <th>Nro. Int.</th>
           <th>Cant.</th>
           <th>Descripcion</th>
           <th>$ Compra (Compra x Cant)</th>
           <th>$ Venta (Venta x Cant)</th> 
         </tr> 
        </thead>
        <tfoot>
          <tr>
            <th>Fecha</th>
            <th>Nro. Int.</th>
            <th>Cant.</th>
            
            <th>Descripcion</th>
            <th>$ Compra</th>
            <th>$ Venta</th> 
          </tr>
        </tfoot>        
        <tbody>

        <?php

          date_default_timezone_set('America/Argentina/Mendoza');

          if(isset($_GET["fechaInicial"])){

            $fechaInicial = $_GET["fechaInicial"];
            $fechaFinal = $_GET["fechaFinal"];

          }else{

            $hoy = date('Y-m-d');

             $fechaInicial = $hoy . ' 00:00';
             $fechaFinal = $hoy . ' 23:59';

          }

          $respuestaVta = ControladorVentas::ctrRangoFechasVentas($fechaInicial, $fechaFinal);

          foreach ($respuestaVta as $key => $value) {

            $productos = json_decode($value["productos"], true);
            
            foreach ($productos as $keyPro => $valuePro) {
                
                
             
             echo '<tr>

                    <td>'.$value["fecha"].'</td>
                    <td><a href="index.php?ruta=editar-venta&idVenta='.$value["id"].'">' . $value["codigo"] . '</a></td>
                    <td>'.$valuePro["cantidad"].'</td>
                    <td>'.$valuePro["descripcion"].'</td>
                    <td>'.round($valuePro["precio_compra"],2).' ('.round($valuePro["precio_compra"] * $valuePro["cantidad"],2) .')</td>
                    <td>'.round($valuePro["precio"],2).' ('.round($valuePro["total"],2).') </td>

              </tr>';
            }
            
          }

        ?>
               
        </tbody>

       </table>

      </div>
    </div>
  </section>
</div>

<script>
//AGREGA UN INPUT TEXT PARA BUSCAR EN CADA COLUMNA
$("#tablaListarProductosPorVenta tfoot th").each(function (i) {
  var title = $(this).text();
  if(title != ""){
    $(this).html('<input type="text" placeholder="Filtrar por ' + title + '" />');
  }

});


var tablaListarProductosPorVenta = $("#tablaListarProductosPorVenta").DataTable({
    "order": [[ 0, "desc" ]],
    "pageLength": 50,
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

	},
    //dom: 'Blfrtip', Muestra el page lenth 
    dom: 'Bfrtip',
    buttons: [
        'copy', 'csv', 'excel', 'pdf', 'print', 'pageLength'
    ]

});

tablaListarProductosPorVenta.columns().every(function () {
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
RANGO DE FECHAS - VENTAS
=============================================*/
$('#btnInformeVentaProductoRango').daterangepicker(
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
    $('#btnInformeVentaProductoRango span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    var fechaInicial = start.format('YYYY-MM-DD');
    var fechaFinal = end.format('YYYY-MM-DD');
    var capturarRango = $("#btnInformeVentaProductoRango span").html();
   	window.location = "index.php?ruta=ventas-productos&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;
  }
)


</script>