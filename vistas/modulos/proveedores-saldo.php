<?php
if ($_SESSION["perfil"] == "Especial") {
  echo '<script>window.location = "inicio";</script>';
  return;
}

if (isset($_GET["fechaInicial"])) {
  $fechaInicial = $_GET["fechaInicial"];
} else {
  $fechaInicial = date("Y-m-d") . " 00:00:00";
}

$item = null;
$valor = null;
$proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);
$saldoTotal = 0;
$proveedoresConSaldo = 0;
$filasSaldo = [];
if (is_array($proveedores)) {
  foreach ($proveedores as $value) {
    $idProv = $value["id"];
    $compras = ControladorProveedoresCtaCte::ctrSumarComprasListado($idProv, $fechaInicial);
    $remitos = ControladorProveedoresCtaCte::ctrSumarRemitosListado($idProv, $fechaInicial);
    $pagos = ControladorProveedoresCtaCte::ctrSumarPagosListado($idProv, $fechaInicial);
    $notas = ControladorProveedoresCtaCte::ctrNotasCreditosListado($idProv, $fechaInicial);
    $saldo = ($compras["compras"] ?? 0) + ($remitos["compras"] ?? 0) - ($pagos["pagos"] ?? 0) - ($notas["cuentas"] ?? 0);
    $saldoTotal += $saldo;
    if (round($saldo, 2) != 0) $proveedoresConSaldo++;
    $filasSaldo[] = [
      "organizacion" => $value["organizacion"] ?? '',
      "nombre" => $value["nombre"] ?? '',
      "saldo" => $saldo
    ];
  }
}
?>
<div class="content-wrapper informes-proveedores">
  <section class="content-header">
    <h1><i class="fa fa-area-chart"></i> Informes <small>Saldo proveedores</small></h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="proveedores-saldo">Informes</a></li>
      <li class="active">Saldos proveedores</li>
    </ol>
  </section>

  <section class="content">
    <style>
      .informes-proveedores .inf-card { background: #fff; border-radius: 10px; padding: 18px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 15px; }
      .informes-proveedores .inf-card-title { font-size: 12px; text-transform: uppercase; color: #7f8c8d; margin-bottom: 5px; }
      .informes-proveedores .inf-card-value { font-size: 22px; font-weight: 700; color: #2c3e50; }
      .informes-proveedores .inf-card-sub { font-size: 11px; color: #95a5a6; }
      .informes-proveedores .box { border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: 1px solid #e0e0e0; }
      .informes-proveedores .box-header { background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%); border-bottom: 1px solid #e0e0e0; padding: 15px 20px; }
      .informes-proveedores #tablaSaldoProveedor thead tr { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
      .informes-proveedores #tablaSaldoProveedor thead th { color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px; }
      .informes-proveedores .box-body { padding: 25px; }
      .informes-proveedores .btn-filtro { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; padding: 8px 16px; font-weight: 600; }
    </style>

    <div class="row vp-summary-row">
      <div class="col-md-4 col-sm-6">
        <div class="inf-card">
          <div class="inf-card-title">Saldo total</div>
          <div class="inf-card-value">$ <?php echo number_format(round($saldoTotal, 2), 2, ',', '.'); ?></div>
          <div class="inf-card-sub">Suma de saldos al corte de fecha</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="inf-card">
          <div class="inf-card-title">Proveedores con saldo</div>
          <div class="inf-card-value"><?php echo $proveedoresConSaldo; ?></div>
          <div class="inf-card-sub">Con saldo distinto de cero</div>
        </div>
      </div>
      <div class="col-md-4 col-sm-6">
        <div class="inf-card">
          <div class="inf-card-title">Corte de fecha</div>
          <div class="inf-card-value" style="font-size: 16px;"><?php echo date('d/m/Y', strtotime($fechaInicial)); ?></div>
          <div class="inf-card-sub">Cambiar fecha y consultar</div>
        </div>
      </div>
    </div>

    <div class="box">
      <div class="box-header with-border">
        <div class="row">
          <div class="col-xs-12 col-sm-4 col-md-3">
            <input type="text" class="form-control" style="text-align:center;" name="fechaInicial" id="fechaInicial" value="<?php echo htmlspecialchars($fechaInicial); ?>" />
          </div>
          <div class="col-xs-12 col-sm-4 col-md-2">
            <button type="button" onclick="mostrarSaldos();" class="btn btn-filtro"><i class="fa fa-search"></i> Consultar</button>
          </div>
          <div class="col-xs-12 col-sm-4">
            <a class="btn btn-default" href="proveedores"><i class="fa fa-arrow-left"></i> Volver a proveedores</a>
          </div>
        </div>
      </div>
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive tablaSaldoProveedor" id="tablaSaldoProveedor" width="100%">
          <thead>
            <tr>
              <th><center>Organización</center></th>
              <th><center>Nombre</center></th>
              <th><center>Saldo</center></th>
            </tr>
          </thead>
          <tbody>
            <?php
              foreach ($filasSaldo as $row) {
                echo '<tr>';
                echo '<td><center>'.htmlspecialchars($row["organizacion"]).'</center></td>';
                echo '<td><center>'.htmlspecialchars($row["nombre"]).'</center></td>';
                echo '<td><center>'.number_format(round($row["saldo"], 2), 2, ',', '.').'</center></td>';
                echo '</tr>';
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
