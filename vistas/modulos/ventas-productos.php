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

    <style>
      /* ============================
         Estilos modernos para el informe
         ============================ */

      .vp-summary-row {
        margin-bottom: 15px;
      }

      .vp-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 15px 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid #f0f0f0;
        margin-bottom: 15px;
      }

      .vp-card-title {
        font-size: 13px;
        text-transform: uppercase;
        color: #7f8c8d;
        margin-bottom: 5px;
      }

      .vp-card-value {
        font-size: 22px;
        font-weight: 700;
        color: #2c3e50;
      }

      .vp-card-sub {
        font-size: 12px;
        color: #95a5a6;
      }

      .vp-chart-container {
        background: #ffffff;
        border-radius: 10px;
        padding: 15px 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid #f0f0f0;
        margin-bottom: 20px;
      }

      .vp-chart-title {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 10px;
        color: #2c3e50;
      }

      .vp-chart-subtitle {
        font-size: 12px;
        color: #95a5a6;
        margin-left: 5px;
      }

      .vp-chart-legend {
        list-style: none;
        padding-left: 0;
        margin: 0;
      }

      .vp-chart-legend li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 0;
        font-size: 12px;
        border-bottom: 1px solid #f4f4f4;
      }

      .vp-chart-legend li:last-child {
        border-bottom: none;
      }

      .vp-chart-legend .vp-legend-label {
        display: flex;
        align-items: center;
        gap: 6px;
        max-width: 75%;
      }

      .vp-chart-legend .vp-legend-color {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
      }

      .vp-chart-legend .vp-legend-percent {
        font-weight: 600;
      }
    </style>

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

       <!-- =======================
            Resumen + gráfico
            ======================= -->
       <div class="row vp-summary-row">
         <div class="col-md-3 col-sm-6">
           <div class="vp-card">
             <div class="vp-card-title">Unidades vendidas</div>
             <div class="vp-card-value" id="vpTotalUnidades">0</div>
             <div class="vp-card-sub">Cantidad total de ítems</div>
           </div>
         </div>
         <div class="col-md-3 col-sm-6">
           <div class="vp-card">
             <div class="vp-card-title">Venta total</div>
             <div class="vp-card-value" id="vpTotalVenta">$ 0,00</div>
             <div class="vp-card-sub">Suma de Venta x Cantidad</div>
           </div>
         </div>
         <div class="col-md-3 col-sm-6">
           <div class="vp-card">
             <div class="vp-card-title">Compra total</div>
             <div class="vp-card-value" id="vpTotalCompra">$ 0,00</div>
             <div class="vp-card-sub">Suma de Compra x Cantidad</div>
           </div>
         </div>
         <div class="col-md-3 col-sm-6">
           <div class="vp-card">
             <div class="vp-card-title">Margen estimado</div>
             <div class="vp-card-value" id="vpTotalMargen">$ 0,00</div>
             <div class="vp-card-sub">Venta - Compra</div>
           </div>
         </div>
       </div>

       <div class="row">
         <div class="col-md-7">
           <div class="vp-chart-container">
             <div class="vp-chart-title">
               Productos más vendidos
               <span class="vp-chart-subtitle">(según monto vendido)</span>
             </div>
             <div class="chart-responsive">
               <canvas id="vpProductosPie" height="150"></canvas>
             </div>
           </div>
         </div>
         <div class="col-md-5">
           <div class="vp-chart-container">
             <div class="vp-chart-title">Top productos</div>
             <ul id="vpProductosLegend" class="vp-chart-legend"></ul>
           </div>
         </div>
       </div>

       <!-- =======================
            Tabla detalle
            ======================= -->
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
                    <td><a href="#" class="verDetalleVenta" data-id-venta="'.$value["id"].'" data-codigo-venta="'.$value["codigo"].'" style="cursor: pointer; color: #3498db; font-weight: 600;">' . $value["codigo"] . '</a></td>
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

<!-- ============================================
     MODAL DETALLE DE VENTA - MODERNO Y VISUAL
     ============================================ -->
<div class="modal fade" id="modalDetalleVenta" tabindex="-1" role="dialog" aria-labelledby="modalDetalleVentaLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
      
      <!-- Header del modal con gradiente -->
      <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 20px 25px;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.9;">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="modalDetalleVentaLabel" style="font-weight: 700; font-size: 20px;">
          <i class="fa fa-shopping-cart"></i> Detalle de Venta
          <span id="modalCodigoVenta" style="font-weight: 300; font-size: 16px; margin-left: 10px;"></span>
        </h4>
      </div>

      <!-- Body del modal -->
      <div class="modal-body" style="padding: 25px; background: #f8f9fa;">
        
        <!-- Loading spinner -->
        <div id="ventaDetalleLoading" style="text-align: center; padding: 40px;">
          <i class="fa fa-spinner fa-spin fa-3x" style="color: #667eea;"></i>
          <p style="margin-top: 15px; color: #7f8c8d;">Cargando detalles de la venta...</p>
        </div>

        <!-- Contenido de la venta -->
        <div id="ventaDetalleContent" style="display: none;">
          
          <!-- Cards de resumen -->
          <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-4 col-sm-6">
              <div class="vp-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div style="font-size: 12px; opacity: 0.9; margin-bottom: 5px;">FECHA</div>
                <div style="font-size: 18px; font-weight: 700;" id="ventaDetalleFecha">-</div>
              </div>
            </div>
            <div class="col-md-4 col-sm-6">
              <div class="vp-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                <div style="font-size: 12px; opacity: 0.9; margin-bottom: 5px;">CLIENTE</div>
                <div style="font-size: 18px; font-weight: 700;" id="ventaDetalleCliente">-</div>
              </div>
            </div>
            <div class="col-md-4 col-sm-6">
              <div class="vp-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                <div style="font-size: 12px; opacity: 0.9; margin-bottom: 5px;">VENDEDOR</div>
                <div style="font-size: 18px; font-weight: 700;" id="ventaDetalleVendedor">-</div>
              </div>
            </div>
          </div>

          <!-- Productos vendidos -->
          <div class="vp-chart-container" style="margin-bottom: 20px;">
            <div class="vp-chart-title">
              <i class="fa fa-cube"></i> Productos vendidos
              <span class="vp-chart-subtitle" id="ventaDetalleCantProductos">(0 productos)</span>
            </div>
            <div id="ventaDetalleProductos" style="margin-top: 15px;">
              <!-- Se llena dinámicamente -->
            </div>
          </div>

          <!-- Resumen financiero -->
          <div class="row">
            <div class="col-md-6">
              <div class="vp-chart-container">
                <div class="vp-chart-title">Resumen financiero</div>
                <table class="table" style="margin-bottom: 0;">
                  <tr>
                    <td style="border: none; padding: 8px 0;"><strong>Subtotal:</strong></td>
                    <td style="border: none; padding: 8px 0; text-align: right;" id="ventaDetalleSubtotal">$ 0,00</td>
                  </tr>
                  <tr>
                    <td style="border: none; padding: 8px 0;"><strong>Descuento:</strong></td>
                    <td style="border: none; padding: 8px 0; text-align: right;" id="ventaDetalleDescuento">$ 0,00</td>
                  </tr>
                  <tr>
                    <td style="border: none; padding: 8px 0;"><strong>Interés:</strong></td>
                    <td style="border: none; padding: 8px 0; text-align: right;" id="ventaDetalleInteres">$ 0,00</td>
                  </tr>
                  <tr style="background: #f8f9fa; border-top: 2px solid #667eea;">
                    <td style="border: none; padding: 12px 0;"><strong style="font-size: 16px;">TOTAL:</strong></td>
                    <td style="border: none; padding: 12px 0; text-align: right;">
                      <strong style="font-size: 18px; color: #667eea;" id="ventaDetalleTotal">$ 0,00</strong>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
            <div class="col-md-6">
              <div class="vp-chart-container">
                <div class="vp-chart-title">Medios de pago</div>
                <div id="ventaDetalleMediosPago" style="margin-top: 10px;">
                  <!-- Se llena dinámicamente -->
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>

      <!-- Footer del modal -->
      <div class="modal-footer" style="background: #f8f9fa; border-top: 1px solid #e0e0e0; padding: 15px 25px;">
        <button type="button" class="btn btn-default" data-dismiss="modal">
          <i class="fa fa-times"></i> Cerrar
        </button>
        <a href="#" id="ventaDetalleLinkEditar" class="btn btn-primary" target="_blank">
          <i class="fa fa-edit"></i> Editar venta
        </a>
        <a href="#" id="ventaDetalleLinkImprimir" class="btn btn-success" target="_blank">
          <i class="fa fa-print"></i> Imprimir
        </a>
      </div>

    </div>
  </div>
</div>

<style>
  /* Estilos adicionales para el modal de detalle */
  #ventaDetalleProductos .producto-card {
    background: white;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border-left: 4px solid #667eea;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  #ventaDetalleProductos .producto-card:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    transform: translateY(-1px);
    transition: all 0.2s ease;
  }

  #ventaDetalleProductos .producto-info {
    flex: 1;
  }

  #ventaDetalleProductos .producto-nombre {
    font-weight: 600;
    font-size: 14px;
    color: #2c3e50;
    margin-bottom: 5px;
  }

  #ventaDetalleProductos .producto-details {
    font-size: 12px;
    color: #7f8c8d;
  }

  #ventaDetalleProductos .producto-total {
    font-size: 16px;
    font-weight: 700;
    color: #667eea;
    text-align: right;
  }

  #ventaDetalleMediosPago .medio-pago-item {
    background: white;
    border-radius: 6px;
    padding: 10px 15px;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-left: 3px solid #2ecc71;
  }

  #ventaDetalleMediosPago .medio-pago-tipo {
    font-weight: 600;
    color: #2c3e50;
  }

  #ventaDetalleMediosPago .medio-pago-monto {
    font-weight: 700;
    color: #2ecc71;
  }
</style>

<script>
// ============================================
// Cargar detalle de venta al hacer clic
// ============================================
$(document).on('click', '.verDetalleVenta', function(e) {
  e.preventDefault();
  
  var idVenta = $(this).data('id-venta');
  var codigoVenta = $(this).data('codigo-venta');
  
  // Mostrar modal
  $('#modalDetalleVenta').modal('show');
  
  // Actualizar código en el header
  $('#modalCodigoVenta').text('N° ' + codigoVenta);
  
  // Mostrar loading, ocultar contenido
  $('#ventaDetalleLoading').show();
  $('#ventaDetalleContent').hide();
  
  // Cargar datos via AJAX
  var datos = new FormData();
  datos.append("idVenta", idVenta);
  
  $.ajax({
    url: "ajax/ventas.ajax.php",
    method: "POST",
    data: datos,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function(respuesta) {
      
      // Ocultar loading, mostrar contenido
      $('#ventaDetalleLoading').hide();
      $('#ventaDetalleContent').show();
      
      // Parsear productos
      var productos = JSON.parse(respuesta.productos);
      
      // Llenar datos básicos
      $('#ventaDetalleFecha').text(respuesta.fecha || '-');
      $('#ventaDetalleCliente').text(respuesta.id_cliente == 1 ? 'Consumidor Final' : (respuesta.cliente_nombre || 'Cliente #' + respuesta.id_cliente));
      $('#ventaDetalleVendedor').text(respuesta.vendedor_nombre || 'N/A');
      
      // Actualizar link de editar
      $('#ventaDetalleLinkEditar').attr('href', 'index.php?ruta=editar-venta&idVenta=' + idVenta);
      $('#ventaDetalleLinkImprimir').attr('href', 'comprobante/' + respuesta.codigo);
      
      // Llenar productos
      var htmlProductos = '';
      var totalProductos = 0;
      
      productos.forEach(function(prod) {
        totalProductos += parseInt(prod.cantidad || 1);
        
        htmlProductos += '<div class="producto-card">';
        htmlProductos += '<div class="producto-info">';
        htmlProductos += '<div class="producto-nombre">' + (prod.descripcion || 'Producto sin nombre') + '</div>';
        htmlProductos += '<div class="producto-details">';
        htmlProductos += 'Cantidad: ' + (prod.cantidad || 1) + ' | ';
        htmlProductos += 'P. Unit: $ ' + parseFloat(prod.precio || 0).toLocaleString('es-AR', {minimumFractionDigits: 2});
        htmlProductos += '</div>';
        htmlProductos += '</div>';
        htmlProductos += '<div class="producto-total">';
        htmlProductos += '$ ' + parseFloat(prod.total || 0).toLocaleString('es-AR', {minimumFractionDigits: 2});
        htmlProductos += '</div>';
        htmlProductos += '</div>';
      });
      
      $('#ventaDetalleProductos').html(htmlProductos);
      $('#ventaDetalleCantProductos').text('(' + productos.length + ' productos, ' + totalProductos + ' unidades)');
      
      // Llenar resumen financiero
      $('#ventaDetalleSubtotal').text('$ ' + parseFloat(respuesta.neto || 0).toLocaleString('es-AR', {minimumFractionDigits: 2}));
      $('#ventaDetalleDescuento').text('$ ' + parseFloat(respuesta.descuento || 0).toLocaleString('es-AR', {minimumFractionDigits: 2}));
      $('#ventaDetalleInteres').text('$ ' + parseFloat(respuesta.interes || 0).toLocaleString('es-AR', {minimumFractionDigits: 2}));
      $('#ventaDetalleTotal').text('$ ' + parseFloat(respuesta.total || 0).toLocaleString('es-AR', {minimumFractionDigits: 2}));
      
      // Llenar medios de pago (si están en el JSON)
      var htmlMedios = '';
      if (respuesta.metodo_pago) {
        var metodos = typeof respuesta.metodo_pago === 'string' ? JSON.parse(respuesta.metodo_pago) : respuesta.metodo_pago;
        
        if (Array.isArray(metodos)) {
          metodos.forEach(function(metodo) {
            htmlMedios += '<div class="medio-pago-item">';
            htmlMedios += '<span class="medio-pago-tipo">' + (metodo.tipo || 'Efectivo') + '</span>';
            htmlMedios += '<span class="medio-pago-monto">$ ' + parseFloat(metodo.monto || 0).toLocaleString('es-AR', {minimumFractionDigits: 2}) + '</span>';
            htmlMedios += '</div>';
          });
        } else {
          htmlMedios += '<div class="medio-pago-item">';
          htmlMedios += '<span class="medio-pago-tipo">' + (respuesta.metodo_pago || 'Efectivo') + '</span>';
          htmlMedios += '<span class="medio-pago-monto">$ ' + parseFloat(respuesta.total || 0).toLocaleString('es-AR', {minimumFractionDigits: 2}) + '</span>';
          htmlMedios += '</div>';
        }
      } else {
        htmlMedios += '<div class="medio-pago-item">';
        htmlMedios += '<span class="medio-pago-tipo">Efectivo</span>';
        htmlMedios += '<span class="medio-pago-monto">$ ' + parseFloat(respuesta.total || 0).toLocaleString('es-AR', {minimumFractionDigits: 2}) + '</span>';
        htmlMedios += '</div>';
      }
      
      $('#ventaDetalleMediosPago').html(htmlMedios);
      
    },
    error: function(xhr, status, error) {
      console.error("Error al cargar detalle de venta:", error);
      $('#ventaDetalleLoading').html('<div style="text-align: center; padding: 20px; color: #e74c3c;"><i class="fa fa-exclamation-triangle fa-2x"></i><p style="margin-top: 10px;">Error al cargar los datos de la venta</p></div>');
    }
  });
  
});

</script>