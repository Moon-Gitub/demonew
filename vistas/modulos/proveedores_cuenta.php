<?php

    $item = 'id';
  
    $valor = $_GET["id_proveedor"];

    $proveedor = ControladorProveedores::ctrMostrarProveedores($item, $valor);

    $agenteRetencionIibb = !empty($arrayEmpresa['agente_retencion_iibb']);
    $alicuotaProveedorIibb = isset($proveedor['tipo_alicuota_iibb']) ? $proveedor['tipo_alicuota_iibb'] : '';

    // Medios de pago: desde BD (tabla medios_pago), mismo criterio que crear-venta-caja y clientes cta cte
    $listaMediosPagoProveedor = [];
    if (class_exists('ModeloMediosPago')) {
      $listaMediosPagoProveedor = ModeloMediosPago::mdlMostrarMediosPagoActivos();
      if (!is_array($listaMediosPagoProveedor)) {
        $listaMediosPagoProveedor = [];
      }
    }

?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Cuenta Corriente proveedor - <span id="spanNombreProveedorCtaCte"><?php echo $proveedor["nombre"]; ?></span><?php echo ' - ' . $proveedor["cuit"]; ?>
    
    </h1>
  <input type="hidden" name="idProveedor" id="idProveedor" value="<?php echo $_GET["id_proveedor"];?>" />
  <input type="hidden" name="nombreProveedorInforme" id="nombreProveedorInforme" value="<?php echo $proveedor["nombre"];?>" />
    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Cuenta Corriente proveedor</li>
    
    </ol>

  </section>

  <section class="content">

<!--        <div class="row">

       <div class="col-lg-4 col-xs-6">

        <div class="small-box bg-red">

          <div class="inner">

          

            <p>Total Compras</p>

          </div>

       <div class="icon">
        
      <i class="ion ion-social-usd"></i>

            </div>

        </div>

      </div>
        <div class="col-lg-4 col-xs-6">

        <div class="small-box bg-green">

          <div class="inner">

           

            <p>Total Pagos</p>

          </div>

       <div class="icon">
        
        <i class="ion ion-social-usd"></i>
        
    </div>

        </div>

      </div>
    <div class="col-lg-3 col-xs-6">

        <div class="small-box bg-green">

          <div class="inner">

            <h3>$ -->
<?php 
//echo round($notas["cuentas"],2); 
?>
<!--</h3>

            <p>Notas De Credito</p>

          </div>

       <div class="icon">
        
        <i class="ion ion-social-usd"></i>
        
    </div>

        </div>

      </div>
        <div class="col-lg-4 col-xs-6">

        <div class="small-box bg-aqua">

          <div class="inner">

           

            <p>Saldo</p>

          </div>

     <div class="icon">
      
      <i class="ion ion-social-usd"></i>
    
    </div>

        </div>

      </div>

    </div>-->

    <div class="row">

      <div class="col-lg-12 col-xs-12">

        <div class="small-box bg-purple">

          <div class="inner">

            <p><b>II.BB.</b>: <?php echo $proveedor["ingresos_brutos"]; ?> - <b>Alícuota ret.</b>: <?php echo ($alicuotaProveedorIibb !== '' && $alicuotaProveedorIibb !== null) ? number_format((float)$alicuotaProveedorIibb, 2, ',', '.') . '%' : '-'; ?> - <b>Inicio Act.</b>: <?php echo $proveedor["inicio_actividades"]; ?></p>
            <p><b>Domicilio</b>: <?php echo $proveedor["direccion"]; ?>  - <b>Localidad</b>: <?php echo $proveedor["localidad"]; ?></p>
            <p><b>Email</b>: <?php echo $proveedor["email"]; ?> - <b>Telefono</b>: <?php echo $proveedor["telefono"]; ?></p>
            <p><b>Observaciones</b>: <?php echo $proveedor["observaciones"]; ?> </p>

          </div>

          <div class="icon">

            <i class="fa fa-address-card-o"></i>

          </div>

        </div>

      </div>

     <!--  <div class="col-lg-2 col-xs-6">

        <div class="small-box bg-yellow">

          <div class="inner">

           
            <p>Saldo</p>

          </div>

          <div class="icon">

            <i class="ion ion-bag"></i>

          </div>

        </div>

      </div>

      <div class="col-lg-2 col-xs-6">

        <div class="small-box bg-red">

          <div class="inner">

           

            <p>Vencido</p>

          </div>

          <div class="icon">

            <i class="ion ion-bag"></i>

          </div>

        </div>

      </div> -->

    </div>

  <div class="box">
     <div class="box-header with-border">
  
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarFactura">
          + Factura
        </button>

        <button class="btn btn-success" data-toggle="modal" data-target="#modalAgregarPago">
          + Pago
        </button>

        <?php if ($agenteRetencionIibb): ?>
        <a class="btn btn-warning" href="index.php?ruta=retenciones-iibb&id_proveedor=<?php echo (int)$proveedor['id']; ?>">
          Retenciones
        </a>
        <?php endif; ?>

        <!--<a class="btn btn-primary" href="proveedores">
          
          Proveedores

        </a>-->

        <button type="button" class="btn btn-default pull-right" id="daterangeCtaCte-btn">
           
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
      <div class="box-body">
        
      <table class="table table-bordered table-striped dt-responsive tablasBotonesCtaCteProveedor" width="100%">
        
        <thead>

         <tr>

           <th>Fecha</th>
           <th>Descripcion</th>
           <th>$ Compra/ND</th>
           <th>$ Pago/NC</th>
           <th>$ Saldo</th>
           <th></th>

         </tr> 

        </thead>

        <tbody>

        <?php

          if(isset($_GET["fechaInicial"])){

            $fechaInicial = $_GET["fechaInicial"];
            $fechaFinal = $_GET["fechaFinal"];

          }else{

            $fechaInicial = null;
            $fechaFinal = null;

          }

      /************************************
        CUENTA CORRIENTE PROVEEDORES - toda compra se carga como debe - haber
        Tipos: 
        0 - COMPRA
        1 - ENTREGA INICIAL / UN SOLO PAGO
        2 - CUOTAS
        3 - ENTREGA A CUENTA ?
        4 - SALDO INCIAL (puede sumar o restar dependiendo si es credito o debito)

      *************************************/

          $respuesta = ControladorProveedoresCtaCte::ctrMostrarCtaCteProveedor($valor);
          
          $saldoCtaCte = 0;

          foreach ($respuesta as $key => $value) {

            /*if($value['tipo']==0){
              $tipo="Compra. Cbte N°:";
            }
            if($value['tipo']==2){
              $tipo="Nota De Debito Interna. Por Compra N°:";
            }
            if($value['tipo']==1){
              $tipo="Pago Cargado:";
            }
            if($value['tipo']==3){
              $tipo="Remito:";
            }
            if($value['tipo']==4){
              $tipo="Saldo Inicial";
            }

            if($value['id_compra']==0){
              $valor = "";
            }
            if($value['id_compra']!=0){
              $valor = $value['id_compra'];
            }*/
  
            echo '<tr>';

              echo '<td style="text-align: center">'.date('Y-m-d', strtotime($value["fecha_movimiento"])).'</td>';

              echo '<td>'.$value["descripcion"].'</td>';

              // echo '<td style="text-align: left">$ '.number_format($value['importe'], 2, ',', '.').'</td>';
              
              // $saldoCtaCte = $saldoCtaCte - $value['importe'];

              if($value["tipo"] == 1) {

                echo '<td>$ '. number_format($value["importe"], 2, ',', '.') .'</td>';
                echo '<td></td>';
                $saldoCtaCte = $saldoCtaCte - $value["importe"];

              } elseif ($value["tipo"] == 0) {

                echo '<td></td>';
                echo '<td>$ '. number_format($value["importe"], 2, ',', '.') .'</td>';
                $saldoCtaCte = $saldoCtaCte + $value["importe"];

              } 
              
              echo '<td style="text-align: center">$ '.number_format($saldoCtaCte, 2, ',', '.').'</td>';              

              echo '<td style="text-align: center">
                     <!--<div class="btn-group">
                      <button class="btn btn-info btnImprimirCompraCtaCte" idCompra="'.$value['id_compra'].'"><i class="fa fa-print"></i></button>
                      <button class="btn btn-danger btnEliminarMovimiento" idMovimiento="'.$value["id"].'"><i class="fa fa-times"></i></button>
                     </div>-->
                    </td>';
            echo '</tr>';
          }

        ?>
               
        </tbody>

       </table>

      </div>

    </div>

  </section>

</div>

<!--=====================================
MODAL AGREGAR FACTURA
======================================-->
<div id="modalAgregarFactura" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form role="form" method="post">
        <input type="hidden" name="accionCtaCteProveedor" value="factura">
        <input type="hidden" name="idUsuarioMovimientoCtaCteProveedor" value="<?php echo $_SESSION["id"]; ?>">
        <input type="hidden" name="idProveedorMovimientoCtaCteProveedor" value="<?php echo $proveedor["id"]; ?>">

        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar factura — <?php echo $proveedor["nombre"]; ?></h4>
        </div>

        <div class="modal-body">
          <div class="box-body">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Fecha factura</label>
                  <input type="date" class="form-control" name="fechaFacturaCtaCte" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Nº factura</label>
                  <input type="text" class="form-control" name="numeroFacturaCtaCte" placeholder="Ej: 0001-00000241" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Descripción</label>
                  <input type="text" class="form-control" name="detalleFacturaCtaCte" placeholder="Opcional">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Neto previo</label>
                  <input type="number" min="0" step="0.01" class="form-control calcFacturaCtaCte" name="netoPrevioFacturaCtaCte" id="netoPrevioFacturaCtaCte" required>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Descuento</label>
                  <input type="number" min="0" step="0.01" class="form-control calcFacturaCtaCte" name="descuentoFacturaCtaCte" id="descuentoFacturaCtaCte" value="0">
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>Neto</label>
                  <input type="number" min="0" step="0.01" class="form-control" name="netoFacturaCtaCte" id="netoFacturaCtaCte" readonly>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>IVA</label>
                  <input type="number" min="0" step="0.01" class="form-control calcFacturaCtaCte" name="ivaFacturaCtaCte" id="ivaFacturaCtaCte" value="0">
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>Total</label>
                  <input type="number" min="0" step="0.01" class="form-control" name="totalFacturaCtaCte" id="totalFacturaCtaCte" readonly>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar factura</button>
        </div>
      </form>
      <?php
        $facturaCtaCte = new ControladorProveedoresCtaCte();
        $facturaCtaCte->ctrCrearFacturaProveedor();
      ?>
    </div>
  </div>
</div>

<!--=====================================
MODAL AGREGAR PAGO
======================================-->
<div id="modalAgregarPago" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form role="form" method="post" id="formPagoCtaCteProveedor">
        <input type="hidden" name="accionCtaCteProveedor" value="pago">
        <input type="hidden" name="idUsuarioMovimientoCtaCteProveedor" value="<?php echo $_SESSION["id"]; ?>">
        <input type="hidden" name="idProveedorMovimientoCtaCteProveedor" value="<?php echo $proveedor["id"]; ?>">
        <input type="hidden" id="agenteRetencionIibbActivo" value="<?php echo $agenteRetencionIibb ? '1' : '0'; ?>">
        <input type="hidden" id="alicuotaDefaultProveedor" value="<?php echo htmlspecialchars($alicuotaProveedorIibb); ?>">

        <div class="modal-header" style="background:#00a65a; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar pago — <?php echo $proveedor["nombre"]; ?></h4>
        </div>

        <div class="modal-body">
          <div class="box-body">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Fecha pago</label>
                  <input type="date" class="form-control" name="fechaPagoCtaCte" id="fechaPagoCtaCte" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
              </div>
              <div class="col-md-8">
                <div class="form-group">
                  <label>Descripción</label>
                  <input type="text" class="form-control" name="detalleMovimientoCtaCteProveedor" id="detalleMovimientoCtaCteProveedor" placeholder="Descripción del pago">
                </div>
              </div>
            </div>

            <?php if ($agenteRetencionIibb): ?>
            <div class="panel panel-default">
              <div class="panel-heading"><strong>Retención IIBB</strong></div>
              <div class="panel-body">
                <div class="checkbox">
                  <label>
                    <input type="checkbox" name="aplicarRetencionCtaCte" id="aplicarRetencionCtaCte" value="1">
                    Aplicar retención de Ingresos Brutos
                  </label>
                </div>
                <div id="camposRetencionPago" style="display:none">
                  <div class="row">
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>Nº factura (SIRCAR)</label>
                        <input type="text" class="form-control" name="numeroFacturaPagoCtaCte" id="numeroFacturaPagoCtaCte" placeholder="0001-00000241">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>Fecha retención</label>
                        <input type="date" class="form-control" name="fechaRetencionCtaCte" id="fechaRetencionCtaCte" value="<?php echo date('Y-m-d'); ?>">
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label>Monto sujeto</label>
                        <input type="number" min="0" step="0.01" class="form-control calcRetencionPago" name="montoSujetoPagoCtaCte" id="montoSujetoPagoCtaCte">
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label>Alícuota %</label>
                        <input type="number" min="0" step="0.01" class="form-control calcRetencionPago" name="alicuotaRetencionCtaCte" id="alicuotaRetencionCtaCte" value="<?php echo htmlspecialchars($alicuotaProveedorIibb); ?>">
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label>Monto retenido</label>
                        <input type="number" min="0" step="0.01" class="form-control" name="montoRetencionCtaCte" id="montoRetencionCtaCte" readonly>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>Monto neto pagado (egreso caja)</label>
                        <input type="number" min="0" step="0.01" class="form-control" name="montoNetoPagoCtaCte" id="montoNetoPagoCtaCte" readonly>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label>Total aplicado a cta. cte.</label>
                        <input type="number" min="0" step="0.01" class="form-control" id="totalAplicadoPagoCtaCte" readonly>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <div class="form-group ctacteProveedorCaja">
              <div class="input-group">
                <span title="Puntos de venta" class="input-group-addon"><i class="fa fa-terminal"></i></span>
                <?php
                  $arrPuntos = json_decode($arrayEmpresa['ptos_venta'], true);
                  $arrPuntosHabilitados = explode(',', $_SESSION['puntos_venta']);
                  echo '<select title="Seleccione el punto de venta" class="form-control input-sm" id="nuevaPtoVta" name="puntoVentaMovimientoCtaCteProveedor" required>';
                  echo '<option value="0">Seleccione punto de venta</option>';
                  foreach ($arrPuntos as $key => $value) {
                    if (in_array($value["pto"], $arrPuntosHabilitados)) {
                      echo '<option value="' . $value["pto"] . '" selected>' . $value["pto"] . "-" . $value["det"]  . '</option>';
                    } else {
                      echo '<option value="' . $value["pto"] . '" disabled>' . $value["pto"] . "-" . $value["det"]  . '</option>';
                    }
                  }
                  echo '</select>';
                ?>
              </div>
            </div>

            <div class="form-group ctacteProveedorCaja">
              <div class="input-group">
                <span title="Agregar medio de pago" class="input-group-btn"><button id="agregarMedioPagoProveedor" type="button" class="btn btn-success"><i class="fa fa-plus"></i></button></span>
                <select class="form-control" id="nuevoMetodoPagoCtaCteProveedor" name="nuevoMetodoPagoCtaCteProveedor" required>
                  <option value="">Medio de pago</option>
                  <?php
                  if (!empty($listaMediosPagoProveedor)) {
                    foreach ($listaMediosPagoProveedor as $mp) {
                      $cod = htmlspecialchars($mp['codigo'] ?? '');
                      $nom = htmlspecialchars($mp['nombre'] ?? $cod);
                      $rc = (int)($mp['requiere_codigo'] ?? 0);
                      $rb = (int)($mp['requiere_banco'] ?? 0);
                      $rn = (int)($mp['requiere_numero'] ?? 0);
                      $rf = (int)($mp['requiere_fecha'] ?? 0);
                      echo '<option value="' . $cod . '" data-requiere-codigo="' . $rc . '" data-requiere-banco="' . $rb . '" data-requiere-numero="' . $rn . '" data-requiere-fecha="' . $rf . '">' . $nom . '</option>';
                    }
                  }
                  ?>
                </select>
              </div>
              <div class="cajasMetodoPagoCtaCteProveedor"></div>
              <div class="row" style="display: none;" id="divImportesPagoMixtoProveedor">
                <table class="table" id="listadoMetodosPagoMixtoProveedor" cantidadFilas="0">
                  <thead><tr><th><i class="fa fa-minus-square"></i></th><th>Método</th><th>Importe</th></tr></thead>
                  <tbody></tbody>
                  <tfoot><tr><td></td><td></td><td style="font-size: 18px"><b>TOTAL: $</b> <span id="nuevoValorSaldoProveedor" style="color:green">0</span><input type="hidden" id="nuevoValorSaldoProveedorPost"></td></tr></tfoot>
                </table>
              </div>
              <input type="hidden" id="metodoPagoCtaCteProveedor" name="ingresoMedioPagoCtaCteProveedor">
              <input type="hidden" id="mxMediosPagosProveedor">
            </div>

            <div class="form-group" id="grupoMontoSimplePago" <?php echo $agenteRetencionIibb ? 'style="display:none"' : ''; ?>>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                <input type="number" min="0" step="0.01" class="form-control input-lg" style="text-align:center;font-size:20px;font-weight:bold" name="montoMovimientoCtaCteProveedor" id="montoMovimientoCtaCteProveedor" placeholder="Monto del pago" <?php echo $agenteRetencionIibb ? '' : 'required'; ?>>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-success">Guardar pago</button>
        </div>
      </form>
      <?php
        $pagoCtaCte = new ControladorProveedoresCtaCte();
        $pagoCtaCte->ctrCrearPagoProveedor();
      ?>
    </div>
  </div>
</div>

<?php

  $eliminarMovimiento = new ControladorProveedoresCtaCte();
  $eliminarMovimiento -> ctrEliminarCtaCteProveedores();

?>