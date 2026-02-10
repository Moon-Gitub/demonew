<?php
/**
 * Informe Gestión Inteligente de Pedidos - ¿Qué debo comprar?
 * Productos críticos, días de cobertura, cantidad sugerida, ROI, por proveedor, baja rotación.
 */
$errorInforme = null;
try {
	if (!class_exists('ModeloReporteGestionPedidos')) {
		throw new Exception('No se cargó el modelo del informe. Revisar que index.php incluya modelos/reporte-gestion-pedidos.modelo.php');
	}
	$diasAnalisis = isset($_GET['dias_analisis']) ? max(7, min(90, (int)$_GET['dias_analisis'])) : 30;
	$diasCobertura = isset($_GET['dias_cobertura']) ? max(7, min(90, (int)$_GET['dias_cobertura'])) : 30;

	// Una sola consulta pesada; resumen y por proveedor se calculan en PHP con el mismo resultado.
	@set_time_limit(120);
	$productos = ModeloReporteGestionPedidos::mdlProductosCriticos($diasAnalisis, $diasCobertura);
	$resumen = ModeloReporteGestionPedidos::mdlResumenInversion($diasAnalisis, $diasCobertura, $productos);
	$porProveedor = ModeloReporteGestionPedidos::mdlPedidoPorProveedor($diasAnalisis, $diasCobertura, $productos);
	$bajaRotacion = ModeloReporteGestionPedidos::mdlBajaRotacion(90);

	$criticos48h = array_filter($productos, function ($p) { return $p['dias_cobertura'] <= 2 && $p['dias_cobertura'] < 999; });
	$top10Ganancia = array_slice($productos, 0, 10);
	$gananciaTop10 = array_sum(array_column($top10Ganancia, 'ganancia_esperada'));

	$labelsCobertura = [];
	$dataCobertura = [];
	foreach (array_slice($productos, 0, 20) as $p) {
		$labelsCobertura[] = mb_substr($p['descripcion'], 0, 20) . (mb_strlen($p['descripcion']) > 20 ? '…' : '');
		$dataCobertura[] = $p['dias_cobertura'] == 999 ? 0 : $p['dias_cobertura'];
	}
} catch (Throwable $e) {
	$errorInforme = $e->getMessage();
	$productos = [];
	$resumen = ['inversion_total' => 0, 'inversion_criticos' => 0, 'ganancia_esperada' => 0, 'cantidad_productos' => 0, 'criticos_count' => 0, 'urgentes_count' => 0];
	$porProveedor = [];
	$bajaRotacion = [];
	$criticos48h = [];
	$gananciaTop10 = 0;
	$labelsCobertura = [];
	$dataCobertura = [];
	$diasAnalisis = 30;
	$diasCobertura = 30;
}
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Informes <small><b>Gestión inteligente de pedidos</b></small></h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="inicio">Informes</a></li>
      <li class="active">Gestión de pedidos</li>
    </ol>
  </section>

  <section class="content">
    <?php if ($errorInforme !== null) { ?>
    <div class="alert alert-danger">
      <strong>Error al cargar el informe</strong><br>
      <?php echo htmlspecialchars($errorInforme); ?>
      <br><small>Revisá el archivo error_log del servidor o la configuración de la base de datos (.env).</small>
    </div>
    <div class="box">
      <div class="box-header with-border">
        <form method="get" action="" class="form-inline">
          <input type="hidden" name="ruta" value="informe-gestion-pedidos">
          <label>Días de análisis:</label>
          <input type="number" name="dias_analisis" class="form-control" value="30" min="7" max="90" style="width:70px; margin:0 8px;">
          <label>Días de cobertura:</label>
          <input type="number" name="dias_cobertura" class="form-control" value="30" min="7" max="90" style="width:70px; margin:0 8px;">
          <button type="submit" class="btn btn-primary"><i class="fa fa-refresh"></i> Reintentar</button>
        </form>
      </div>
    </div>
    <?php } else { ?>
    <style>
      .igp-card { background:#fff; border-radius:10px; padding:15px 20px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:1px solid #f0f0f0; margin-bottom:15px; }
      .igp-card-title { font-size:12px; text-transform:uppercase; color:#7f8c8d; margin-bottom:5px; }
      .igp-card-value { font-size:20px; font-weight:700; color:#2c3e50; }
      .igp-alerta { padding:12px 16px; border-radius:8px; margin-bottom:12px; }
      .igp-alerta--danger { background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; }
      .igp-alerta--success { background:#d4edda; border:1px solid #c3e6cb; color:#155724; }
      .igp-alerta--warning { background:#fff3cd; border:1px solid #ffeaa7; color:#856404; }
      .igp-section { background:#fff; border-radius:10px; padding:15px 20px; box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:20px; }
      .igp-section h4 { margin-top:0; margin-bottom:12px; color:#2c3e50; }
      .igp-badge-critico { background:#c0392b; color:#fff; }
      .igp-badge-urgente { background:#f39c12; color:#fff; }
      .igp-badge-normal { background:#27ae60; color:#fff; }
      #igpChartCobertura { min-height:300px; }
    </style>

    <div class="box" style="border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.08);">
      <div class="box-header with-border">
        <form method="get" action="" class="form-inline">
          <input type="hidden" name="ruta" value="informe-gestion-pedidos">
          <label>Días de análisis (ventas):</label>
          <input type="number" name="dias_analisis" class="form-control" value="<?php echo $diasAnalisis; ?>" min="7" max="90" style="width:70px; margin:0 8px;">
          <label>Días de cobertura deseado:</label>
          <input type="number" name="dias_cobertura" class="form-control" value="<?php echo $diasCobertura; ?>" min="7" max="90" style="width:70px; margin:0 8px;">
          <button type="submit" class="btn btn-primary"><i class="fa fa-refresh"></i> Calcular</button>
        </form>
      </div>
      <div class="box-body">

        <!-- Alertas -->
        <?php if (count($criticos48h) > 0) { ?>
        <div class="igp-alerta igp-alerta--danger">
          <strong>⚠️ <?php echo count($criticos48h); ?> producto(s) se quedarán sin stock en las próximas 48 horas.</strong> Revisá la tabla de productos críticos.
        </div>
        <?php } ?>
        <?php if ($gananciaTop10 > 0) { ?>
        <div class="igp-alerta igp-alerta--success">
          <strong>💰 Si reponés los 10 productos más urgentes podés generar $ <?php echo number_format($gananciaTop10, 2, ',', '.'); ?> de ganancia estimada en <?php echo $diasCobertura; ?> días.</strong>
        </div>
        <?php } ?>
        <?php if ($resumen['inversion_criticos'] > 0) { ?>
        <div class="igp-alerta igp-alerta--warning">
          <strong>🎯 Con $ <?php echo number_format($resumen['inversion_criticos'], 2, ',', '.'); ?> podés reponer solo los productos críticos (≤3 días de cobertura).</strong>
        </div>
        <?php } ?>
        <?php if (count($bajaRotacion) > 0) { ?>
        <div class="igp-alerta igp-alerta--warning">
          <strong>📉 <?php echo count($bajaRotacion); ?> producto(s) con stock pero sin ventas en 90 días.</strong> Revisá la sección "Baja rotación".
        </div>
        <?php } ?>

        <!-- Resumen -->
        <div class="row">
          <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="igp-card">
              <div class="igp-card-title">Inversión total sugerida</div>
              <div class="igp-card-value">$ <?php echo number_format($resumen['inversion_total'], 2, ',', '.'); ?></div>
            </div>
          </div>
          <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="igp-card">
              <div class="igp-card-title">Solo críticos (≤3 días)</div>
              <div class="igp-card-value">$ <?php echo number_format($resumen['inversion_criticos'], 2, ',', '.'); ?></div>
            </div>
          </div>
          <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="igp-card">
              <div class="igp-card-title">Ganancia esperada (<?php echo $diasCobertura; ?> d)</div>
              <div class="igp-card-value" style="color:#27ae60;">$ <?php echo number_format($resumen['ganancia_esperada'], 2, ',', '.'); ?></div>
            </div>
          </div>
          <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="igp-card">
              <div class="igp-card-title">Productos a reponer</div>
              <div class="igp-card-value"><?php echo $resumen['cantidad_productos']; ?></div>
            </div>
          </div>
          <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="igp-card">
              <div class="igp-card-title">🔴 Críticos</div>
              <div class="igp-card-value" style="color:#c0392b;"><?php echo $resumen['criticos_count']; ?></div>
            </div>
          </div>
          <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="igp-card">
              <div class="igp-card-title">🟡 Urgentes</div>
              <div class="igp-card-value" style="color:#f39c12;"><?php echo $resumen['urgentes_count']; ?></div>
            </div>
          </div>
        </div>

        <!-- Top 20 productos por días de cobertura: gráfico + tabla de respaldo -->
        <?php if (!empty($dataCobertura)) {
          $top20Cobertura = array_slice($productos, 0, 20);
        ?>
        <div class="igp-section">
          <h4>Top 20 productos por días de cobertura (menor = más urgente)</h4>
          <p class="text-muted" style="margin-top:0;font-size:12px;">Gráfico de barras: días de cobertura por producto. Los mismos datos están en la tabla debajo.</p>
          <div style="height:320px; position:relative; margin-bottom:15px; background:#f9f9f9; border:1px solid #eee; border-radius:8px;">
            <canvas id="igpChartCobertura"></canvas>
            <p id="igpChartFallback" class="text-muted" style="display:none; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); margin:0; text-align:center; width:80%;">El gráfico no pudo cargarse. Consultá la tabla debajo para ver los datos.</p>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-condensed table-sm dt-responsive" id="tablaTopCobertura" width="100%">
              <thead>
                <tr style="background:#667eea; color:white;">
                  <th style="color:white;">#</th>
                  <th style="color:white;">Producto</th>
                  <th style="color:white;">Días cobertura</th>
                  <th style="color:white;">Stock</th>
                  <th style="color:white;">Venta/día</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($top20Cobertura as $i => $p) { ?>
                <tr>
                  <td><?php echo $i + 1; ?></td>
                  <td><?php echo htmlspecialchars(mb_substr($p['descripcion'], 0, 50)); ?><?php echo mb_strlen($p['descripcion']) > 50 ? '…' : ''; ?></td>
                  <td><strong><?php echo $p['dias_cobertura'] == 999 ? '-' : $p['dias_cobertura']; ?></strong></td>
                  <td><?php echo number_format($p['stock_actual'], 0, ',', '.'); ?></td>
                  <td><?php echo number_format($p['promedio_venta_diaria'], 2, ',', '.'); ?></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php } ?>

        <!-- Tabla productos críticos -->
        <div class="igp-section">
          <h4>Productos críticos – Cantidad sugerida e inversión</h4>
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-condensed dt-responsive" id="tablaProductosCriticos" width="100%">
              <thead>
                <tr style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white;">
                  <th style="color:white;">Estado</th>
                  <th style="color:white;">Producto</th>
                  <th style="color:white;">Proveedor</th>
                  <th style="color:white;">Stock</th>
                  <th style="color:white;">Venta/día</th>
                  <th style="color:white;">Días cob.</th>
                  <th style="color:white;">Sugerido</th>
                  <th style="color:white;">Inversión</th>
                  <th style="color:white;">Ganancia est.</th>
                  <th style="color:white;">ROI %</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($productos as $p) {
                  $badge = $p['estado_urgencia'] === 'critico' ? 'igp-badge-critico' : ($p['estado_urgencia'] === 'urgente' ? 'igp-badge-urgente' : 'igp-badge-normal');
                  $etiqueta = $p['estado_urgencia'] === 'critico' ? 'CRÍTICO' : ($p['estado_urgencia'] === 'urgente' ? 'URGENTE' : 'NORMAL');
                ?>
                <tr>
                  <td><span class="label <?php echo $badge; ?>"><?php echo $etiqueta; ?></span></td>
                  <td><?php echo htmlspecialchars($p['descripcion']); ?> <small>(<?php echo htmlspecialchars($p['codigo']); ?>)</small></td>
                  <td><?php echo htmlspecialchars($p['proveedor'] ?: '-'); ?></td>
                  <td><?php echo number_format($p['stock_actual'], 2, ',', '.'); ?></td>
                  <td><?php echo number_format($p['promedio_venta_diaria'], 2, ',', '.'); ?></td>
                  <td><?php echo $p['dias_cobertura'] == 999 ? '-' : $p['dias_cobertura']; ?></td>
                  <td><?php echo number_format($p['cantidad_sugerida'], 2, ',', '.'); ?></td>
                  <td>$ <?php echo number_format($p['inversion_necesaria'], 2, ',', '.'); ?></td>
                  <td>$ <?php echo number_format($p['ganancia_esperada'], 2, ',', '.'); ?></td>
                  <td><?php echo $p['roi']; ?>%</td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Por proveedor -->
        <div class="igp-section">
          <h4>Recomendación de pedido por proveedor</h4>
          <?php foreach ($porProveedor as $prov) { ?>
          <div class="panel panel-default">
            <div class="panel-heading">
              <strong><?php echo htmlspecialchars($prov['proveedor']); ?></strong>
              <span class="pull-right">Total: $ <?php echo number_format($prov['total_inversion'], 2, ',', '.'); ?></span>
            </div>
            <div class="panel-body">
              <table class="table table-condensed table-bordered">
                <thead><tr><th>Producto</th><th>Cant. sugerida</th><th>P. compra</th><th>Subtotal</th></tr></thead>
                <tbody>
                  <?php foreach ($prov['productos'] as $item) { ?>
                  <tr>
                    <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                    <td><?php echo number_format($item['cantidad_sugerida'], 2, ',', '.'); ?></td>
                    <td>$ <?php echo number_format($item['precio_compra'], 2, ',', '.'); ?></td>
                    <td>$ <?php echo number_format($item['inversion_necesaria'], 2, ',', '.'); ?></td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
          <?php } ?>
        </div>

        <!-- Baja rotación -->
        <?php if (!empty($bajaRotacion)) { ?>
        <div class="igp-section">
          <h4>Productos de baja rotación (stock pero sin ventas en 90 días)</h4>
          <p class="text-muted">No conviene pedir más; evaluar liquidar o reducir pedidos.</p>
          <table class="table table-bordered table-striped table-condensed dt-responsive" id="tablaBajaRotacion" width="100%">
            <thead>
              <tr><th>Código</th><th>Descripción</th><th>Stock</th><th>Valorizado</th></tr>
            </thead>
            <tbody>
              <?php foreach ($bajaRotacion as $br) { ?>
              <tr>
                <td><?php echo htmlspecialchars($br['codigo']); ?></td>
                <td><?php echo htmlspecialchars($br['descripcion']); ?></td>
                <td><?php echo number_format($br['stock_actual'], 2, ',', '.'); ?></td>
                <td>$ <?php echo number_format($br['valorizado'], 2, ',', '.'); ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <?php } ?>
      </div>
    </div>
    <?php } ?>
  </section>
</div>

<script>
(function() {
  // Gráfico de cobertura (solo si hay datos y no hubo error)
  <?php if (!$errorInforme && !empty($dataCobertura)) { ?>
  var labels = <?php echo json_encode($labelsCobertura); ?>;
  var data = <?php echo json_encode($dataCobertura); ?>;
  var graficoDibujado = false;
  function dibujarGrafico() {
    var ctx = document.getElementById('igpChartCobertura');
    var fallback = document.getElementById('igpChartFallback');
    if (!ctx) return;
    if (typeof Chart === 'undefined') {
      if (fallback) fallback.style.display = 'block';
      return;
    }
    try {
      var barData = {
        labels: labels,
        datasets: [{
          fillColor: 'rgba(52,152,219,0.6)',
          strokeColor: 'rgba(52,152,219,0.8)',
          highlightFill: 'rgba(52,152,219,0.8)',
          highlightStroke: 'rgba(52,152,219,1)',
          data: data
        }]
      };
      new Chart(ctx.getContext('2d')).Bar(barData, {
        scaleBeginAtZero: true,
        responsive: true,
        maintainAspectRatio: false,
        showScale: true
      });
      graficoDibujado = true;
    } catch (e) {
      if (fallback) fallback.style.display = 'block';
    }
  }
  function mostrarFallbackSiFalta() {
    if (!graficoDibujado) {
      var fallback = document.getElementById('igpChartFallback');
      if (fallback) fallback.style.display = 'block';
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() { dibujarGrafico(); setTimeout(mostrarFallbackSiFalta, 1500); });
  } else {
    setTimeout(function() { dibujarGrafico(); setTimeout(mostrarFallbackSiFalta, 1500); }, 100);
  }
  <?php } ?>

  // Inicializar DataTables en las tablas del informe
  function initDataTablesIGP() {
    if (typeof $.fn.DataTable === 'undefined') {
      return;
    }
    var opcionesComun = {
      language: {
        sProcessing:     "Procesando...",
        sLengthMenu:     "Mostrar _MENU_ registros",
        sZeroRecords:    "No se encontraron resultados",
        sEmptyTable:     "Ningún dato disponible en esta tabla",
        sInfo:           "Mostrando _START_ a _END_ de _TOTAL_",
        sInfoEmpty:      "Mostrando 0 a 0 de 0",
        sInfoFiltered:   "(filtrado de _MAX_ registros)",
        sSearch:         "Buscar:",
        sLoadingRecords: "Cargando...",
        oPaginate: {
          sFirst:    "Primero",
          sLast:     "Último",
          sNext:     "Siguiente",
          sPrevious: "Anterior"
        },
        oAria: {
          sSortAscending:  ": Activar para ordenar la columna ascendente",
          sSortDescending: ": Activar para ordenar la columna descendente"
        }
      },
      pageLength: 25,
      dom: 'Bfrtip',
      buttons: [
        { extend: 'excelHtml5', text: '<i class="fa fa-file-excel-o"></i>', titleAttr: 'Exportar a Excel', className: 'btn btn-success' },
        { extend: 'pdfHtml5',   text: '<i class="fa fa-file-pdf-o"></i>',   titleAttr: 'Exportar a PDF',   className: 'btn btn-danger' },
        { extend: 'print',      text: '<i class="fa fa-print"></i>',       titleAttr: 'Imprimir',         className: 'btn btn-info' },
        { extend: 'pageLength', text: '<i class="fa fa-list-alt"></i>',    titleAttr: 'Mostrar registros',className: 'btn btn-primary' }
      ]
    };

    // Top 20 cobertura
    if ($('#tablaTopCobertura').length) {
      $('#tablaTopCobertura').DataTable($.extend(true, {}, opcionesComun, {
        order: [[2, 'asc']]
      }));
    }

    // Productos críticos
    if ($('#tablaProductosCriticos').length) {
      $('#tablaProductosCriticos').DataTable($.extend(true, {}, opcionesComun, {
        order: [[5, 'asc']]
      }));
    }

    // Baja rotación
    if ($('#tablaBajaRotacion').length) {
      $('#tablaBajaRotacion').DataTable($.extend(true, {}, opcionesComun, {
        order: [[3, 'desc']]
      }));
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDataTablesIGP);
  } else {
    initDataTablesIGP();
  }
})();
</script>
