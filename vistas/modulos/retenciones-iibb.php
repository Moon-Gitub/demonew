<?php

$fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : date('Y-m-01');
$fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : date('Y-m-d');
$idProveedorFiltro = isset($_GET["id_proveedor"]) ? (int)$_GET["id_proveedor"] : null;

$retenciones = ControladorRetencionesIibb::ctrListarRetenciones($fechaInicial, $fechaFinal, $idProveedorFiltro);
$agenteActivo = !empty($arrayEmpresa['agente_retencion_iibb']);

$proveedoresLista = ControladorProveedores::ctrMostrarProveedores(null, null);

?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>Retenciones IIBB (SIRCAR)</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="proveedores">Proveedores</a></li>
      <li class="active">Retenciones IIBB</li>
    </ol>
  </section>

  <section class="content">

    <?php if (!$agenteActivo): ?>
    <div class="alert alert-warning">
      <i class="fa fa-warning"></i>
      El agente de retención IIBB no está habilitado. Configúrelo en <a href="empresa">Datos Empresa</a>.
    </div>
    <?php endif; ?>

    <div class="box">
      <div class="box-header with-border">
        <button type="button" class="btn btn-default" id="daterangeRetenciones-btn">
          <span><i class="fa fa-calendar"></i> <?php echo $fechaInicial . ' - ' . $fechaFinal; ?></span>
          <i class="fa fa-caret-down"></i>
        </button>

        <div class="form-inline pull-right" style="margin-left:10px">
          <select class="form-control input-sm" id="filtroProveedorRetenciones">
            <option value="">Todos los proveedores</option>
            <?php foreach ($proveedoresLista as $prov): ?>
              <option value="<?php echo (int)$prov['id']; ?>" <?php echo ($idProveedorFiltro === (int)$prov['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($prov['nombre']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="btn-group pull-right">
          <a class="btn btn-success" id="btnExportarTxtRetenciones" href="#">
            <i class="fa fa-file-text-o"></i> Exportar TXT
          </a>
          <a class="btn btn-primary" id="btnExportarZipRetenciones" href="#">
            <i class="fa fa-file-archive-o"></i> Exportar ZIP
          </a>
        </div>
      </div>

      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive tablasRetencionesIibb" width="100%">
          <thead>
            <tr>
              <th>Fecha retención</th>
              <th>Nº recibo</th>
              <th>Proveedor</th>
              <th>CUIT</th>
              <th>Nº factura</th>
              <th>Monto sujeto</th>
              <th>Alícuota %</th>
              <th>Monto retenido</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($retenciones)): ?>
              <tr><td colspan="8" class="text-center text-muted">No hay retenciones en el período seleccionado.</td></tr>
            <?php else: ?>
              <?php foreach ($retenciones as $row):
                $fechaRet = !empty($row['fecha_retencion']) ? $row['fecha_retencion'] : substr($row['fecha_movimiento'], 0, 10);
                $montoSujeto = $row['factura_neto'] !== null ? $row['factura_neto'] : $row['importe'];
              ?>
              <tr>
                <td><?php echo htmlspecialchars($fechaRet); ?></td>
                <td><?php echo htmlspecialchars($row['numero_recibo']); ?></td>
                <td><?php echo htmlspecialchars($row['proveedor_nombre']); ?></td>
                <td><?php echo htmlspecialchars($row['cuit']); ?></td>
                <td><?php echo htmlspecialchars($row['factura_numero']); ?></td>
                <td>$ <?php echo number_format((float)$montoSujeto, 2, ',', '.'); ?></td>
                <td><?php echo number_format((float)$row['alicuota_retencion'], 2, ',', '.'); ?></td>
                <td>$ <?php echo number_format((float)$row['monto_retencion'], 2, ',', '.'); ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </section>
</div>

<input type="hidden" id="fechaInicialRetenciones" value="<?php echo htmlspecialchars($fechaInicial); ?>">
<input type="hidden" id="fechaFinalRetenciones" value="<?php echo htmlspecialchars($fechaFinal); ?>">
