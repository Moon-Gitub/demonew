<?php
  $saldoTotal = ControladorProveedoresCtaCte::ctrMostrarSaldoTotal();
  $colorBox = (isset($saldoTotal["saldo"]) && $saldoTotal["saldo"] < 0) ? 'bg-warning' : 'bg-success';
  $proveedores = ControladorProveedoresCtaCte::ctrMostrarSaldos();
  $cantidad = is_array($proveedores) ? count($proveedores) : 0;
?>
<div class="content-wrapper informes-proveedores">
  <section class="content-header">
    <h1><i class="fa fa-area-chart"></i> Informes <small>Ctas. ctes. proveedores</small></h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="proveedores-cuenta-saldos">Informes</a></li>
      <li class="active">Ctas. ctes. proveedores</li>
    </ol>
  </section>

  <section class="content">
    <style>
      .informes-proveedores .inf-card { background: #fff; border-radius: 10px; padding: 18px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 15px; }
      .informes-proveedores .inf-card-title { font-size: 12px; text-transform: uppercase; color: #7f8c8d; margin-bottom: 5px; }
      .informes-proveedores .inf-card-value { font-size: 22px; font-weight: 700; color: #2c3e50; }
      .informes-proveedores .box { border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: 1px solid #e0e0e0; }
      .informes-proveedores .box-header { background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%); border-bottom: 1px solid #e0e0e0; padding: 15px 20px; }
      .informes-proveedores #tablaCtaCteProveedores thead tr { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
      .informes-proveedores #tablaCtaCteProveedores thead th { color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px; }
      .informes-proveedores .box-body { padding: 25px; }
    </style>

    <div class="row vp-summary-row">
      <div class="col-md-4 col-sm-6">
        <div class="inf-card">
          <div class="inf-card-title">Saldo total</div>
          <div class="inf-card-value">$ <?php echo number_format(isset($saldoTotal["saldo"]) ? $saldoTotal["saldo"] : 0, 2, ',', '.'); ?></div>
          <div class="inf-card-sub">Suma de saldos en cuenta corriente</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="inf-card">
          <div class="inf-card-title">Proveedores</div>
          <div class="inf-card-value"><?php echo $cantidad; ?></div>
          <div class="inf-card-sub">Con movimiento en cuenta corriente</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="inf-card">
          <div class="inf-card-title">Acción</div>
          <div class="inf-card-value" style="font-size: 16px;"><i class="fa fa-address-book-o text-info"></i> Detalle</div>
          <div class="inf-card-sub"><a href="proveedores">Proveedores</a> · <a href="proveedores-saldo">Saldos por fecha</a></div>
        </div>
      </div>
    </div>

    <div class="box">
      <div class="box-header with-border">
        <a class="btn btn-primary" href="proveedores"><i class="fa fa-arrow-left"></i> Volver a proveedores</a>
      </div>
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive tablasBotones" id="tablaCtaCteProveedores" width="100%">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Teléfono</th>
              <th>Total compras</th>
              <th>Total pagos</th>
              <th>Saldo</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if (is_array($proveedores)) {
                foreach ($proveedores as $key => $value) {
                  echo '<tr>';
                  echo '<td><a href="index.php?ruta=proveedores_cuenta&id_proveedor='.urlencode($value["id_proveedor"] ?? '').'">'.htmlspecialchars($value["nombre"] ?? '').'</a></td>';
                  echo '<td>'.htmlspecialchars($value["telefono"] ?? '').'</td>';
                  echo '<td>'.($value["compras"] ?? '').'</td>';
                  echo '<td>'.($value["pagos"] ?? '').'</td>';
                  echo '<td>'.($value["diferencia"] ?? '').'</td>';
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