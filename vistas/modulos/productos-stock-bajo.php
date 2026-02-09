<?php
  $productos = ControladorProductos::ctrMostrarStockBajo();
  $cantidad = is_array($productos) ? count($productos) : 0;
  $totalUnidades = 0;
  if (is_array($productos)) {
    foreach ($productos as $v) {
      $totalUnidades += (isset($v["stock"]) && $v["stock"] > 0) ? floatval($v["stock"]) : 0;
    }
  }
?>
<div class="content-wrapper informes-stock">
  <section class="content-header">
    <h1><i class="fa fa-area-chart"></i> Informes <small>Productos con stock bajo</small></h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="productos-stock-bajo">Informes</a></li>
      <li class="active">Stock bajo</li>
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
      .informes-stock #tablaStockBajo thead tr:first-child { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
      .informes-stock #tablaStockBajo thead th { color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px; }
      .informes-stock .box-body { padding: 25px; }
    </style>

    <div class="row vp-summary-row">
      <div class="col-md-4 col-sm-6">
        <div class="inf-card">
          <div class="inf-card-title">Productos con stock bajo</div>
          <div class="inf-card-value"><?php echo $cantidad; ?></div>
          <div class="inf-card-sub">Cantidad de ítems a reponer</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="inf-card">
          <div class="inf-card-title">Unidades en total</div>
          <div class="inf-card-value"><?php echo number_format($totalUnidades, 0, ',', '.'); ?></div>
          <div class="inf-card-sub">Suma del stock actual de estos productos</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="inf-card">
          <div class="inf-card-title">Acción</div>
          <div class="inf-card-value" style="font-size: 16px;"><i class="fa fa-shopping-cart text-info"></i> Revisar pedido</div>
          <div class="inf-card-sub"><a href="crear-compra">Crear compra</a> o <a href="productos">Administrar productos</a></div>
        </div>
      </div>
    </div>

    <div class="box">
      <div class="box-header with-border">
        <a class="btn btn-primary" href="productos"><i class="fa fa-arrow-left"></i> Volver a productos</a>
      </div>
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive tablasBotones" id="tablaStockBajo" width="100%">
          <thead>
            <tr>
              <th>Código</th>
              <th>Descripción</th>
              <th>Stk</th>
              <th>Stk TOTAL</th>
              <th>Stock Medio</th>
              <th>Stock Bajo</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if (is_array($productos)) {
                foreach ($productos as $key => $value) {
                  $value["stock"] = (isset($value["stock"]) && $value["stock"] < 0) ? 0 : (isset($value["stock"]) ? $value["stock"] : 0);
                  $totXproducto = $value["stock"];
                  echo '<tr>';
                  echo '<td>'.htmlspecialchars($value["codigo"] ?? '').'</td>';
                  echo '<td>'.htmlspecialchars($value["descripcion"] ?? '').'</td>';
                  echo '<td>'.$value["stock"].'</td>';
                  echo '<td>'.$totXproducto.'</td>';
                  echo '<td>'.($value["stock_medio"] ?? '').'</td>';
                  echo '<td>'.($value["stock_bajo"] ?? '').'</td>';
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
