<?php
/**
 * Vista: Dashboard Ejecutivo Diario
 * Métricas del día: ventas, transacciones, ticket promedio, top productos, medios de pago, saldo caja.
 */
@set_time_limit(90);
$fechaHoy = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHoy)) {
	$fechaHoy = date('Y-m-d');
}

$resumen = ModeloReporteDashboardEjecutivo::mdlResumenDia($fechaHoy);
$ventasHoy = $resumen ? (float)($resumen['ventas_totales']) : 0;
$cantidadTransacciones = $resumen ? (int)($resumen['cantidad_transacciones']) : 0;
$ticketPromedio = $resumen ? (float)($resumen['ticket_promedio']) : 0;
$clientesAtendidos = $resumen ? (int)($resumen['clientes_atendidos']) : 0;

$fechaAyer = date('Y-m-d', strtotime($fechaHoy . ' -1 day'));
$ventasAyer = ModeloReporteDashboardEjecutivo::mdlVentasDiaAnterior($fechaAyer);
$variacionAyer = $ventasAyer > 0 ? (($ventasHoy - $ventasAyer) / $ventasAyer) * 100 : ($ventasHoy > 0 ? 100 : 0);

$topProductos = ModeloReporteDashboardEjecutivo::mdlTopProductosDia($fechaHoy);
$mediosPago = ModeloReporteDashboardEjecutivo::mdlMediosPagoDia($fechaHoy);
$saldoCaja = ModeloReporteDashboardEjecutivo::mdlSaldoCajaAl($fechaHoy);

$totalMediosPago = 0;
foreach ($mediosPago as $mp) { $totalMediosPago += (float)$mp['monto_total']; }

$labelsProductos = [];
$dataProductos = [];
$colores = array("#3498db","#e74c3c","#2ecc71","#9b59b6","#f1c40f","#1abc9c","#e67e22","#34495e","#ff7675","#6c5ce7");
foreach ($topProductos as $i => $p) {
	$labelsProductos[] = $p['nombre'];
	$dataProductos[] = (float)$p['monto_total'];
}
$labelsMedios = [];
$dataMedios = [];
$colorsMedios = array("#2ecc71","#3498db","#9b59b6","#e67e22","#1abc9c");
foreach ($mediosPago as $i => $m) {
	$labelsMedios[] = $m['nombre'];
	$dataMedios[] = (float)$m['monto_total'];
}
$labelsProductosJson = json_encode($labelsProductos);
$dataProductosJson = json_encode($dataProductos);
$labelsMediosJson = json_encode($labelsMedios);
$dataMediosJson = json_encode($dataMedios);
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Informes <small><b>Dashboard ejecutivo diario</b></small></h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="inicio">Informes</a></li>
      <li class="active">Dashboard ejecutivo</li>
    </ol>
  </section>

  <section class="content">
    <style>
      .ide-card { background: #fff; border-radius: 10px; padding: 15px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 15px; }
      .ide-card-title { font-size: 12px; text-transform: uppercase; color: #7f8c8d; margin-bottom: 5px; }
      .ide-card-value { font-size: 22px; font-weight: 700; color: #2c3e50; }
      .ide-card-sub { font-size: 11px; color: #95a5a6; }
      .ide-chart-container { background: #fff; border-radius: 10px; padding: 15px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 20px; }
      .ide-chart-title { font-size: 15px; font-weight: 600; margin-bottom: 10px; color: #2c3e50; }
      .ide-box { border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: 1px solid #e0e0e0; }
      .ide-btn-fecha { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; padding: 10px 20px; font-weight: 600; }
      #ideProductosBar, #ideMediosPie { min-height: 280px; }
    </style>

    <div class="box ide-box">
      <div class="box-header with-border">
        <form method="get" action="" class="form-inline">
          <input type="hidden" name="ruta" value="informe-dashboard-ejecutivo">
          <label class="control-label">Fecha:</label>
          <input type="date" name="fecha" class="form-control" value="<?php echo htmlspecialchars($fechaHoy); ?>" style="margin: 0 8px;">
          <button type="submit" class="btn ide-btn-fecha"><i class="fa fa-calendar"></i> Consultar</button>
        </form>
      </div>
      <div class="box-body">
        <div class="row">
          <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="ide-card">
              <div class="ide-card-title">Ventas del día</div>
              <div class="ide-card-value">$ <?php echo number_format($ventasHoy, 2, ',', '.'); ?></div>
              <div class="ide-card-sub">Total facturado</div>
            </div>
          </div>
          <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="ide-card">
              <div class="ide-card-title">Transacciones</div>
              <div class="ide-card-value"><?php echo number_format($cantidadTransacciones, 0, ',', '.'); ?></div>
              <div class="ide-card-sub">Cantidad de ventas</div>
            </div>
          </div>
          <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="ide-card">
              <div class="ide-card-title">Ticket promedio</div>
              <div class="ide-card-value">$ <?php echo number_format($ticketPromedio, 2, ',', '.'); ?></div>
              <div class="ide-card-sub">Por venta</div>
            </div>
          </div>
          <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="ide-card">
              <div class="ide-card-title">Clientes atendidos</div>
              <div class="ide-card-value"><?php echo number_format($clientesAtendidos, 0, ',', '.'); ?></div>
              <div class="ide-card-sub">Distintos clientes</div>
            </div>
          </div>
          <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="ide-card">
              <div class="ide-card-title">Vs. día anterior</div>
              <div class="ide-card-value" style="color: <?php echo $variacionAyer >= 0 ? '#27ae60' : '#c0392b'; ?>;">
                <?php echo $variacionAyer >= 0 ? '+' : ''; ?><?php echo number_format($variacionAyer, 1, ',', '.'); ?>%
              </div>
              <div class="ide-card-sub">Variación</div>
            </div>
          </div>
          <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="ide-card">
              <div class="ide-card-title">Saldo caja</div>
              <div class="ide-card-value" style="color: <?php echo $saldoCaja >= 0 ? '#27ae60' : '#c0392b'; ?>;">
                $ <?php echo number_format($saldoCaja, 2, ',', '.'); ?>
              </div>
              <div class="ide-card-sub">Acumulado al <?php echo date('d/m/Y', strtotime($fechaHoy)); ?></div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-7">
            <div class="ide-chart-container">
              <div class="ide-chart-title">Top 10 productos más vendidos (monto)</div>
              <div class="chart-responsive" style="position:relative;height:300px;">
                <canvas id="ideProductosBar"></canvas>
              </div>
            </div>
          </div>
          <div class="col-md-5">
            <div class="ide-chart-container">
              <div class="ide-chart-title">Distribución por medio de pago</div>
              <div class="chart-responsive" style="position:relative;height:300px;">
                <canvas id="ideMediosPie"></canvas>
              </div>
              <?php if (!empty($mediosPago)) { ?>
              <ul class="list-unstyled" style="margin-top:12px;font-size:12px;">
                <?php foreach ($mediosPago as $m) {
                  $pct = $totalMediosPago > 0 ? ((float)$m['monto_total'] / $totalMediosPago) * 100 : 0;
                ?>
                <li><strong><?php echo htmlspecialchars($m['nombre']); ?></strong>: $ <?php echo number_format($m['monto_total'], 2, ',', '.'); ?> (<?php echo number_format($pct, 1); ?>%)</li>
                <?php } ?>
              </ul>
              <?php } ?>
            </div>
          </div>
        </div>

        <?php if (!empty($topProductos)) { ?>
        <div class="row">
          <div class="col-xs-12">
            <div class="ide-chart-container">
              <div class="ide-chart-title">Detalle top productos</div>
              <table class="table table-bordered table-striped table-condensed">
                <thead>
                  <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <th style="color:white;">Producto</th>
                    <th style="color:white;text-align:right;">Cantidad</th>
                    <th style="color:white;text-align:right;">Monto</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($topProductos as $p) { ?>
                  <tr>
                    <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                    <td style="text-align:right;"><?php echo number_format($p['cantidad_vendida'], 2, ',', '.'); ?></td>
                    <td style="text-align:right;">$ <?php echo number_format($p['monto_total'], 2, ',', '.'); ?></td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>
    </div>
  </section>
</div>

<script>
(function() {
  var labelsProductos = <?php echo $labelsProductosJson; ?>;
  var dataProductos = <?php echo $dataProductosJson; ?>;
  var labelsMedios = <?php echo $labelsMediosJson; ?>;
  var dataMedios = <?php echo $dataMediosJson; ?>;

  if (typeof Chart !== 'undefined' && labelsProductos.length > 0) {
    var ctxBar = document.getElementById('ideProductosBar');
    if (ctxBar) {
      new Chart(ctxBar.getContext('2d'), {
        type: 'bar',
        data: {
          labels: labelsProductos,
          datasets: [{
            label: 'Monto vendido',
            data: dataProductos,
            backgroundColor: ['#3498db','#e74c3c','#2ecc71','#9b59b6','#f1c40f','#1abc9c','#e67e22','#34495e','#ff7675','#6c5ce7']
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: { yAxes: [{ ticks: { beginAtZero: true } }] },
          legend: { display: false }
        }
      });
    }
  }

  if (typeof Chart !== 'undefined' && labelsMedios.length > 0) {
    var ctxPie = document.getElementById('ideMediosPie');
    if (ctxPie) {
      new Chart(ctxPie.getContext('2d'), {
        type: 'pie',
        data: {
          labels: labelsMedios,
          datasets: [{ data: dataMedios, backgroundColor: ['#2ecc71','#3498db','#9b59b6','#e67e22','#1abc9c'] }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          legend: { position: 'bottom' }
        }
      });
    }
  }
})();
</script>
