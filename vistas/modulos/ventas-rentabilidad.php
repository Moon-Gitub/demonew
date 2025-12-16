<?php
  require_once __DIR__ . "/../../controladores/ventas.controlador.php";
  require_once __DIR__ . "/../../controladores/cajas.controlador.php";
  require_once __DIR__ . "/../../controladores/clientes.controlador.php";
?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar ventas <small>- <b> Informe rentabilidad </b> </small>
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar ventas</li>
    
    </ol>

  </section>

  <section class="content">

    <style>
      /* ============================
         Estilos modernos para informe de rentabilidad
         ============================ */

      .vr-summary-row {
        margin-bottom: 20px;
      }

      .vr-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: 1px solid #f0f0f0;
        margin-bottom: 15px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
      }

      .vr-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
      }

      .vr-card-title {
        font-size: 13px;
        text-transform: uppercase;
        color: #7f8c8d;
        margin-bottom: 8px;
        font-weight: 600;
        letter-spacing: 0.5px;
      }

      .vr-card-value {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 5px;
      }

      .vr-card-sub {
        font-size: 12px;
        color: #95a5a6;
      }

      .vr-card-positive {
        border-left: 4px solid #2ecc71;
      }

      .vr-card-negative {
        border-left: 4px solid #e74c3c;
      }

      .vr-card-warning {
        border-left: 4px solid #f39c12;
      }

      .vr-card-info {
        border-left: 4px solid #3498db;
      }

      .vr-chart-container {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: 1px solid #f0f0f0;
        margin-bottom: 20px;
      }

      .vr-chart-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #2c3e50;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
      }

      .vr-table-container {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: 1px solid #f0f0f0;
        margin-bottom: 20px;
      }

      .vr-margin-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 10px;
      }

      .vr-margin-good {
        background: #d4edda;
        color: #155724;
      }

      .vr-margin-medium {
        background: #fff3cd;
        color: #856404;
      }

      .vr-margin-bad {
        background: #f8d7da;
        color: #721c24;
      }

      #tablaRentabilidadProductos thead tr {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
      }

      #tablaRentabilidadProductos thead tr th {
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        border: none;
        padding: 12px 8px;
      }
    </style>

    <div class="box" style="border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: 1px solid #e0e0e0;">

      <div class="box-header with-border" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-bottom: 1px solid #e0e0e0; padding: 15px 20px;">
  
        <a class="btn btn-primary" href="ventas" style="border-radius: 8px;">
          
          <i class="fa fa-arrow-left"></i> Volver

        </a>

        <div class="btn-group pull-right">
          <button type="button" class="btn btn-default btn-sm" id="daterangeVentasRentabilidad" style="border-radius: 8px;">
       
              <span>
              <i class="fa fa-calendar"></i> 

              <?php

                if(isset($_GET["fechaInicial"])){

                  echo $_GET["fechaInicial"]." - ".$_GET["fechaFinal"];
                
                }else{
                 
                  echo 'Rango de fecha';

                }

              ?>
            </span>

            <i class="fa fa-caret-down"></i>

          </button>
        </div>

      </div>

      <div class="box-body" style="padding: 25px;">

        <?php

          date_default_timezone_set('America/Argentina/Mendoza');

          if(isset($_GET["fechaInicial"])){

            $desdeFecha = $_GET["fechaInicial"];
            $hastaFecha = $_GET["fechaFinal"];

          }else{

            $desdeFecha = date('Y-m-d');
            $hastaFecha = $desdeFecha;

          }

          // Calcular totales principales
          $totalVentas = ControladorVentas::ctrRangoFechasTotalVentas($desdeFecha, $hastaFecha);
          $ventas = ControladorVentas::ctrRangoFechasVentas($desdeFecha, $hastaFecha);

          $costoTotal = 0;
          $productosRentabilidad = [];
          $categoriasRentabilidad = [];
          $clientesRentabilidad = [];

          foreach ($ventas as $key => $value) {
            
            $productos = json_decode($value["productos"], true);
            $costoVenta = 0;
            
            foreach ($productos as $keyp => $valuep) {
              $costoProducto = $valuep["cantidad"] * ($valuep["precio_compra"] ?? 0);
              $ventaProducto = $valuep["total"] ?? 0;
              $costoVenta += $costoProducto;

              // Agrupar por producto
              $descProducto = $valuep["descripcion"] ?? "Sin descripción";
              if (!isset($productosRentabilidad[$descProducto])) {
                $productosRentabilidad[$descProducto] = [
                  "venta" => 0,
                  "costo" => 0,
                  "cantidad" => 0
                ];
              }
              $productosRentabilidad[$descProducto]["venta"] += $ventaProducto;
              $productosRentabilidad[$descProducto]["costo"] += $costoProducto;
              $productosRentabilidad[$descProducto]["cantidad"] += $valuep["cantidad"] ?? 0;
            }
            
            $costoTotal += $costoVenta;

            // Agrupar por cliente
            $idCliente = $value["id_cliente"] ?? 1;
            if (!isset($clientesRentabilidad[$idCliente])) {
              $cliente = ControladorClientes::ctrMostrarClientes("id", $idCliente);
              $clientesRentabilidad[$idCliente] = [
                "nombre" => $cliente ? $cliente["nombre"] : "Cliente #" . $idCliente,
                "venta" => 0,
                "costo" => 0
              ];
            }
            $clientesRentabilidad[$idCliente]["venta"] += $value["total"] ?? 0;
            $clientesRentabilidad[$idCliente]["costo"] += $costoVenta;
          }

          $rentabilidadBruta = ($totalVentas["total"] ?? 0) - $costoTotal;
          $gastos = ControladorCajas::ctrRangoTotalesGastos($desdeFecha, $hastaFecha);
          $gastosTotal = $gastos["gastos"] ?? 0;
          $rentabilidadNeta = $rentabilidadBruta - $gastosTotal;

          // Calcular margen de ganancia
          $margenPorcentaje = ($totalVentas["total"] ?? 0) > 0 
            ? (($rentabilidadBruta / ($totalVentas["total"] ?? 1)) * 100) 
            : 0;

          // Ordenar productos por rentabilidad
          foreach ($productosRentabilidad as $key => $value) {
            $productosRentabilidad[$key]["rentabilidad"] = $value["venta"] - $value["costo"];
            $productosRentabilidad[$key]["margen"] = $value["venta"] > 0 
              ? (($productosRentabilidad[$key]["rentabilidad"] / $value["venta"]) * 100) 
              : 0;
          }
          uasort($productosRentabilidad, function($a, $b) {
            return $b["rentabilidad"] <=> $a["rentabilidad"];
          });

          // Ordenar clientes por rentabilidad
          foreach ($clientesRentabilidad as $key => $value) {
            $clientesRentabilidad[$key]["rentabilidad"] = $value["venta"] - $value["costo"];
            $clientesRentabilidad[$key]["margen"] = $value["venta"] > 0 
              ? (($clientesRentabilidad[$key]["rentabilidad"] / $value["venta"]) * 100) 
              : 0;
          }
          uasort($clientesRentabilidad, function($a, $b) {
            return $b["rentabilidad"] <=> $a["rentabilidad"];
          });

        ?>

        <!-- =======================
             Cards de resumen
             ======================= -->
        <div class="row vr-summary-row">
          <div class="col-md-3 col-sm-6">
            <div class="vr-card vr-card-info">
              <div class="vr-card-title">Total Ventas</div>
              <div class="vr-card-value">
                $ <?php echo number_format($totalVentas["total"] ?? 0, 2, ',', '.'); ?>
              </div>
              <div class="vr-card-sub">Ingresos totales del período</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="vr-card vr-card-negative">
              <div class="vr-card-title">Total Costos</div>
              <div class="vr-card-value">
                $ <?php echo number_format($costoTotal, 2, ',', '.'); ?>
              </div>
              <div class="vr-card-sub">Costo de productos vendidos</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="vr-card vr-card-positive">
              <div class="vr-card-title">Rentabilidad Bruta</div>
              <div class="vr-card-value">
                $ <?php echo number_format($rentabilidadBruta, 2, ',', '.'); ?>
              </div>
              <div class="vr-card-sub">
                Margen: <?php echo number_format($margenPorcentaje, 2, ',', '.'); ?>%
                <span class="vr-margin-badge <?php 
                  echo $margenPorcentaje >= 30 ? 'vr-margin-good' : 
                       ($margenPorcentaje >= 15 ? 'vr-margin-medium' : 'vr-margin-bad'); 
                ?>">
                  <?php 
                    echo $margenPorcentaje >= 30 ? 'Excelente' : 
                         ($margenPorcentaje >= 15 ? 'Bueno' : 'Bajo'); 
                  ?>
                </span>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="vr-card vr-card-warning">
              <div class="vr-card-title">Rentabilidad Neta</div>
              <div class="vr-card-value">
                $ <?php echo number_format($rentabilidadNeta, 2, ',', '.'); ?>
              </div>
              <div class="vr-card-sub">
                Después de gastos ($ <?php echo number_format($gastosTotal, 2, ',', '.'); ?>)
              </div>
            </div>
          </div>
        </div>

        <!-- =======================
             Gráficos
             ======================= -->
        <div class="row">
          <div class="col-md-6">
            <div class="vr-chart-container">
              <div class="vr-chart-title">
                <i class="fa fa-pie-chart"></i> Distribución de Ingresos
              </div>
              <div class="chart-responsive" style="height: 300px;">
                <canvas id="vrChartDistribucion"></canvas>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="vr-chart-container">
              <div class="vr-chart-title">
                <i class="fa fa-bar-chart"></i> Top 10 Productos más Rentables
              </div>
              <div class="chart-responsive" style="height: 300px;">
                <canvas id="vrChartProductos"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- =======================
             Tabla de productos
             ======================= -->
        <div class="vr-table-container">
          <div class="vr-chart-title">
            <i class="fa fa-cube"></i> Rentabilidad por Producto
          </div>
          <table class="table table-bordered table-striped" id="tablaRentabilidadProductos" width="100%">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Venta Total</th>
                <th>Costo Total</th>
                <th>Rentabilidad</th>
                <th>Margen %</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $topProductos = array_slice($productosRentabilidad, 0, 20, true);
                foreach ($topProductos as $producto => $datos) {
                  echo '<tr>';
                  echo '<td><strong>' . htmlspecialchars($producto) . '</strong></td>';
                  echo '<td>' . number_format($datos["cantidad"], 0, ',', '.') . '</td>';
                  echo '<td>$ ' . number_format($datos["venta"], 2, ',', '.') . '</td>';
                  echo '<td style="color: #e74c3c;">$ ' . number_format($datos["costo"], 2, ',', '.') . '</td>';
                  $colorRenta = $datos["rentabilidad"] >= 0 ? '#2ecc71' : '#e74c3c';
                  echo '<td style="color: ' . $colorRenta . '; font-weight: 600;">$ ' . number_format($datos["rentabilidad"], 2, ',', '.') . '</td>';
                  $colorMargen = $datos["margen"] >= 30 ? '#2ecc71' : ($datos["margen"] >= 15 ? '#f39c12' : '#e74c3c');
                  echo '<td style="color: ' . $colorMargen . '; font-weight: 600;">' . number_format($datos["margen"], 2, ',', '.') . '%</td>';
                  echo '</tr>';
                }
              ?>
            </tbody>
          </table>
        </div>

        <!-- =======================
             Tabla de clientes
             ======================= -->
        <div class="vr-table-container">
          <div class="vr-chart-title">
            <i class="fa fa-users"></i> Rentabilidad por Cliente
          </div>
          <table class="table table-bordered table-striped" id="tablaRentabilidadClientes" width="100%">
            <thead>
              <tr>
                <th>Cliente</th>
                <th>Venta Total</th>
                <th>Costo Total</th>
                <th>Rentabilidad</th>
                <th>Margen %</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $topClientes = array_slice($clientesRentabilidad, 0, 20, true);
                foreach ($topClientes as $idCliente => $datos) {
                  echo '<tr>';
                  echo '<td><strong>' . htmlspecialchars($datos["nombre"]) . '</strong></td>';
                  echo '<td>$ ' . number_format($datos["venta"], 2, ',', '.') . '</td>';
                  echo '<td style="color: #e74c3c;">$ ' . number_format($datos["costo"], 2, ',', '.') . '</td>';
                  $colorRenta = $datos["rentabilidad"] >= 0 ? '#2ecc71' : '#e74c3c';
                  echo '<td style="color: ' . $colorRenta . '; font-weight: 600;">$ ' . number_format($datos["rentabilidad"], 2, ',', '.') . '</td>';
                  $colorMargen = $datos["margen"] >= 30 ? '#2ecc71' : ($datos["margen"] >= 15 ? '#f39c12' : '#e74c3c');
                  echo '<td style="color: ' . $colorMargen . '; font-weight: 600;">' . number_format($datos["margen"], 2, ',', '.') . '%</td>';
                  echo '</tr>';
                }
              ?>
            </tbody>
          </table>
        </div>

      </div>

    </div>

  </section>

</div>

<script>
// ============================================
// Rango de fechas
// ============================================
$('#daterangeVentasRentabilidad').daterangepicker(
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
    $('#daterangeVentasRentabilidad span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    var fechaInicial = start.format('YYYY-MM-DD');
    var fechaFinal = end.format('YYYY-MM-DD');
    window.location = "index.php?ruta=ventas-rentabilidad&fechaInicial="+fechaInicial+"&fechaFinal="+fechaFinal;
  }
);

// ============================================
// Gráfico de distribución
// ============================================
$(document).ready(function() {
  var totalVentas = <?php echo $totalVentas["total"] ?? 0; ?>;
  var costoTotal = <?php echo $costoTotal; ?>;
  var gastosTotal = <?php echo $gastosTotal; ?>;
  var rentabilidadNeta = <?php echo $rentabilidadNeta; ?>;

  if (typeof Chart !== 'undefined') {
    // Gráfico de distribución
    var ctxDist = document.getElementById('vrChartDistribucion');
    if (ctxDist) {
      var chartDist = new Chart(ctxDist.getContext('2d'));
      var distData = [
        {
          value: rentabilidadNeta,
          color: "#2ecc71",
          highlight: "#27ae60",
          label: "Rentabilidad Neta"
        },
        {
          value: costoTotal,
          color: "#e74c3c",
          highlight: "#c0392b",
          label: "Costos"
        },
        {
          value: gastosTotal,
          color: "#f39c12",
          highlight: "#e67e22",
          label: "Gastos"
        }
      ];
      
      var distOptions = {
        segmentShowStroke: true,
        segmentStrokeColor: '#fff',
        segmentStrokeWidth: 2,
        percentageInnerCutout: 50,
        animationSteps: 80,
        animationEasing: 'easeOutQuad',
        animateRotate: true,
        animateScale: false,
        responsive: true,
        maintainAspectRatio: false
      };
      
      chartDist.Doughnut(distData, distOptions);
    }

    // Gráfico de productos
    var ctxProd = document.getElementById('vrChartProductos');
    if (ctxProd) {
      var productosData = <?php 
        $top10 = array_slice($productosRentabilidad, 0, 10, true);
        $labels = [];
        $rentas = [];
        foreach ($top10 as $prod => $datos) {
          $labels[] = substr($prod, 0, 20);
          $rentas[] = $datos["rentabilidad"];
        }
        echo json_encode(["labels" => $labels, "data" => $rentas]);
      ?>;

      var chartProd = new Chart(ctxProd.getContext('2d'));
      var barData = {
        labels: productosData.labels,
        datasets: [{
          label: "Rentabilidad",
          fillColor: "rgba(102, 126, 234, 0.8)",
          strokeColor: "rgba(102, 126, 234, 1)",
          highlightFill: "rgba(102, 126, 234, 0.9)",
          highlightStroke: "rgba(102, 126, 234, 1)",
          data: productosData.data
        }]
      };
      
      var barOptions = {
        scaleBeginAtZero: true,
        scaleShowGridLines: true,
        scaleGridLineColor: "rgba(0,0,0,.05)",
        scaleGridLineWidth: 1,
        barShowStroke: true,
        barStrokeWidth: 2,
        barValueSpacing: 5,
        barDatasetSpacing: 1,
        responsive: true,
        maintainAspectRatio: false
      };
      
      chartProd.Bar(barData, barOptions);
    }
  }
});

// ============================================
// DataTables
// ============================================
var tablaRentabilidadProductos = $("#tablaRentabilidadProductos").DataTable({
  "order": [[ 4, "desc" ]],
  "pageLength": 25,
  "language": {
    "sProcessing": "Procesando...",
    "sLengthMenu": "Mostrar _MENU_ registros",
    "sZeroRecords": "No se encontraron resultados",
    "sEmptyTable": "Ningún dato disponible",
    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
    "sSearch": "Buscar:",
    "oPaginate": {
      "sFirst": "Primero",
      "sLast": "Último",
      "sNext": "Siguiente",
      "sPrevious": "Anterior"
    }
  },
  dom: 'Bfrtip',
  "buttons": [
    {
      extend: 'excelHtml5',
      text: '<i class="fa fa-file-excel-o"></i>',
      titleAttr: 'Exportar a Excel',
      className: 'btn btn-success'
    },
    {
      extend: 'pdfHtml5',
      text: '<i class="fa fa-file-pdf-o"></i> ',
      titleAttr: 'Exportar a PDF',
      className: 'btn btn-danger'
    },
    {
      extend: 'print',
      text: '<i class="fa fa-print"></i> ',
      titleAttr: 'Imprimir',
      className: 'btn btn-info'
    }
  ]
});

var tablaRentabilidadClientes = $("#tablaRentabilidadClientes").DataTable({
  "order": [[ 3, "desc" ]],
  "pageLength": 25,
  "language": {
    "sProcessing": "Procesando...",
    "sLengthMenu": "Mostrar _MENU_ registros",
    "sZeroRecords": "No se encontraron resultados",
    "sEmptyTable": "Ningún dato disponible",
    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
    "sSearch": "Buscar:",
    "oPaginate": {
      "sFirst": "Primero",
      "sLast": "Último",
      "sNext": "Siguiente",
      "sPrevious": "Anterior"
    }
  },
  dom: 'Bfrtip',
  "buttons": [
    {
      extend: 'excelHtml5',
      text: '<i class="fa fa-file-excel-o"></i>',
      titleAttr: 'Exportar a Excel',
      className: 'btn btn-success'
    },
    {
      extend: 'pdfHtml5',
      text: '<i class="fa fa-file-pdf-o"></i> ',
      titleAttr: 'Exportar a PDF',
      className: 'btn btn-danger'
    },
    {
      extend: 'print',
      text: '<i class="fa fa-print"></i> ',
      titleAttr: 'Imprimir',
      className: 'btn btn-info'
    }
  ]
});

</script>
