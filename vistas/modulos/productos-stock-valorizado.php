<?php
  $totales = ControladorProductos::ctrMostrarStockValorizadoTotales();
  $productos = ControladorProductos::ctrMostrarStockValorizado();
  $cantidad = is_array($productos) ? count($productos) : 0;
  $invertido = isset($totales["invertido"]) ? $totales["invertido"] : 0;
  $valorizado = isset($totales["valorizado"]) ? $totales["valorizado"] : 0;
?>
<div class="content-wrapper informes-stock">
  <section class="content-header">
    <h1><i class="fa fa-area-chart"></i> Informes <small>Stock valorizado</small></h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="productos-stock-bajo">Informes</a></li>
      <li class="active">Stock valorizado</li>
    </ol>
  </section>

  <section class="content">
    <style>
      .informes-stock .inf-card { background: #fff; border-radius: 10px; padding: 18px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 15px; }
      .informes-stock .inf-card-title { font-size: 12px; text-transform: uppercase; color: #7f8c8d; margin-bottom: 5px; }
      .informes-stock .inf-card-value { font-size: 22px; font-weight: 700; color: #2c3e50; }
      .informes-stock .inf-card-sub { font-size: 11px; color: #95a5a6; }
      .informes-stock .box { border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: 1px solid #e0e0e0; }
      .informes-stock .box-header { background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%); border-bottom: 1px solid #e0e0e0; padding: 15px 20px; }
      .informes-stock #tablaStockValorizado thead tr:first-child { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
      .informes-stock #tablaStockValorizado thead th { color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px; }
      .informes-stock .box-body { padding: 25px; }
    </style>

    <div class="row vp-summary-row">
      <div class="col-md-3 col-sm-6">
        <div class="inf-card">
          <div class="inf-card-title">Productos</div>
          <div class="inf-card-value"><?php echo $cantidad; ?></div>
          <div class="inf-card-sub">Ítems con stock</div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="inf-card">
          <div class="inf-card-title">Invertido (costo)</div>
          <div class="inf-card-value">$ <?php echo number_format((float)$invertido, 2, ',', '.'); ?></div>
          <div class="inf-card-sub">Valor de compra del stock</div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="inf-card">
          <div class="inf-card-title">Valorizado (venta)</div>
          <div class="inf-card-value">$ <?php echo number_format((float)$valorizado, 2, ',', '.'); ?></div>
          <div class="inf-card-sub">Valor a precio de venta</div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="inf-card">
          <div class="inf-card-title">Diferencia</div>
          <div class="inf-card-value">$ <?php echo number_format((float)$valorizado - (float)$invertido, 2, ',', '.'); ?></div>
          <div class="inf-card-sub">Margen potencial del stock</div>
        </div>
      </div>
    </div>

    <div class="box">
      <div class="box-header with-border">
        <a class="btn btn-primary" href="productos"><i class="fa fa-arrow-left"></i> Volver a productos</a>
      </div>
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive tablasBotones" id="tablaStockValorizado" width="100%">
          <thead>
            <tr>
              <th>Código</th>
              <th>Descripción</th>
              <th>Stock</th>
              <th>$ Compra</th>
              <th>Invertido</th>
              <th>$ Venta</th>
              <th>Valorizado</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if (is_array($productos)) {
                foreach ($productos as $key => $value) {
                  echo '<tr>';
                  echo '<td>'.htmlspecialchars($value["codigo"] ?? '').'</td>';
                  echo '<td>'.htmlspecialchars($value["descripcion"] ?? '').'</td>';
                  echo '<td>'.($value["stock"] ?? '').'</td>';
                  echo '<td>'.($value["precio_compra"] ?? '').'</td>';
                  echo '<td>'.($value["invertido"] ?? '').'</td>';
                  echo '<td>'.($value["precio_venta"] ?? '').'</td>';
                  echo '<td>'.($value["valorizado"] ?? '').'</td>';
                  echo '</tr>';
                }
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
