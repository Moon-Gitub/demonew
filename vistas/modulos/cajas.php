<?php

 if($_SESSION["perfil"] != "Administrador"){
     echo '<script>window.location = "cajas-cajero";</script>';
 }

  $objCaja = new ControladorCajas();
  $objCierreCaja = new ControladorCajaCierres();

  $arrPuntos = json_decode($arrayEmpresa['ptos_venta'], true);
  $arrPuntosHabilitados = explode(',', $_SESSION['puntos_venta']);

  if($_SESSION["perfil"] != "Administrador"){
    $numeroCaja = (isset($_GET["numCaja"]) ) ? $_GET['numCaja'] : $arrPuntosHabilitados[0];
  } else {
    $cajaDefecto = (count($arrPuntos) > 1) ? 0 : $arrPuntosHabilitados[0];
    $numeroCaja = (isset($_GET["numCaja"]) ) ? $_GET['numCaja'] : $cajaDefecto;
  }

  date_default_timezone_set('America/Argentina/Mendoza');

  //$mediosPagos = $objCaja->ctrMediosPagosUsados();

  $mediosPagos = array('Efectivo', 'TC', 'TD', 'TR', 'CH', 'MP');

  $tituloAdmin = "Administrar caja";

  $muestroSaldo = '';

  $habilitoCierre = ' pointer-events: none;  cursor: default; ';

  $totalIngresos = 0;
  $totalEgresos = 0;

  if($numeroCaja == 0){

    $desdeFecha = (isset($_GET["fechaInicial"])) ? $_GET["fechaInicial"] : date('Y-m-d') . ' 00:00';
    $hastaFecha = (isset($_GET["fechaFinal"])) ? $_GET["fechaFinal"] : date('Y-m-d') . ' 23:59';

    $tituloAdmin = "Todas las cajas <small> (" . date('d-m-Y', strtotime($desdeFecha)) . ' - ' . date('d-m-Y', strtotime($hastaFecha)) . ")</small>";

    $arrayCaja = $objCaja->ctrRangoFechasCajas($desdeFecha, $hastaFecha, 0);
    $saldoInicio = 0;

  } elseif(isset($_GET["fechaInicial"])) {

    $desdeFecha = $_GET["fechaInicial"];
    $hastaFecha = $_GET["fechaFinal"];

    $tituloAdmin = "Administrar caja <small> (" . date('d-m-Y', strtotime($desdeFecha)) . ' - ' . date('d-m-Y', strtotime($hastaFecha)) . ")</small>";

    $saldoInicio = $objCaja->ctrSaldoCajaAl($desdeFecha, $numeroCaja);

    $muestroSaldo = '
          <tr style="background-color: #d2d8e0">
            <td>'. $desdeFecha.' </td>
            <td >Saldo inicial</td>
            <td ></td>
            <td ></td>
            <td ></td>
            <td ></td>
            <td ></td>
            <td ></td>
            <td> ' . round($saldoInicio, 2) . '</td>
          </tr>';

    $arrayCaja = $objCaja->ctrRangoFechasCajas($desdeFecha, $hastaFecha, $numeroCaja); 

  }else{

    $habilitoCierre = '';

    $cierre = $objCierreCaja->ctrUltimoCierreCaja($numeroCaja);

    $desdeFecha = $cierre["fecha_hora"];
    $hastaFecha = date('Y-m-d') . ' 23:59:59';

    $saldoInicio = $cierre["apertura_siguiente_monto"];
    $muestroSaldo = '
          <tr style="background-color: #d2d8e0">
            <td>'. $cierre["fecha_hora"].' </td>
            <td >Apertura caja</td>
            <td ></td>
            <td ></td>
            <td ></td>
            <td >Efectivo</td>
            <td >' . round($saldoInicio, 2) . '</td>
            <td ></td>
            <td> ' . round($saldoInicio, 2) . '</td>
          </tr>';

    $arrayCaja = $objCaja->ctrRangoFechasCajasUltimoCierre($cierre["ultimo_id_caja"], $numeroCaja);

  }

?>

<input type="hidden" id="numCaja" value="<?php echo $numeroCaja; ?>"> <!-- Este hidden lo uso para el rango de fechas -->

<div class="content-wrapper">

  <section class="content-header">
      <h1>
      
      <?php echo $tituloAdmin; ?>
    
    </h1>
    
    <!-- COMBO PARA SELECCIONAR CAJA -->  
    <div class="row" style="padding: 10px">
      <div class="col-xs-4">
        
        <div class="input-group">
          <?php 

            echo '<select title="Seleccione el punto de cobro/pago" class="form-control input-sm" id="cajasListadoPuntosVta" name="cajasListadoPuntosVta">';

            foreach ($arrPuntos as $key => $value) {

              if (in_array($value["pto"], $arrPuntosHabilitados)) {
                if($value["pto"] == $numeroCaja) {
                  echo '<option value="' . $value["pto"] . '" selected>' . $value["pto"] . "-" . $value["det"]  . '</option>';
                } else {
                  echo '<option value="' . $value["pto"] . '" >' . $value["pto"] . "-" . $value["det"]  . '</option>';
                }
              } else {
                echo '<option value="' . $value["pto"] . '" disabled>' . $value["pto"] . "-" . $value["det"]  . '</option>';
              }

            }

            $sele = ($numeroCaja == 0) ? 'selected' : '';

            echo ($_SESSION['perfil'] == 'Administrador') ? '<option value="0" '.$sele.'>TODOS</option>' : '';
             echo '</select>';

             echo '<span class="input-group-btn"><a id="aCajaVerCajas" class="btn btn-default btn-sm">Ir</a></span>';
          ?>
      </div >
    </div>

    </div>
    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar caja </li>
      
    </ol>
    <div class="row">
    <!--
      <div class="col-lg-3 col-xs-6">

        <div class="small-box bg-green">
          
          <div class="inner">
            
            <h3>$ <?php echo number_format($objCaja->ctrSaldoCajaAl(date('Y-m-d 23:59:59'), 0), 2, ',','.'); ?> </h3>

            <p>Total caja</p>
          
          </div>
          
          <div class="icon">
            
            <i class="ion ion-social-usd"></i>
          
          </div>

        </div>

      </div>
    -->

    <?php if($numeroCaja <> 0) { ?>
      <div class="col-lg-3 col-xs-6">

        <div class="small-box bg-green">
          
          <div class="inner">

            <span style="font-size: 25px;">Ingresos</span><br>

            <?php 

              $totMedio = 0;
              
              $detalleIngresos = array();
              $vistaIngresos = '';
              foreach ($mediosPagos as $key => $value) {

                $totMedio =  $objCaja->ctrSumatoriaMedios(1, $value, $desdeFecha, $hastaFecha, $numeroCaja)["total"];

                if($totMedio > 0){
                  $totalIngresos += $totMedio;
                  array_push($detalleIngresos, array($value => $totMedio));
                  $vistaIngresos .= '<span style="font-size:20px">' . $value . ': $<b>' . $totMedio . '</b></span><br>';
                  echo '<span style="font-size:20px">' . $value . ': $<b>' . $totMedio . '</b></span><br>';
                }

              }

              $detalleIngresos = json_encode($detalleIngresos);

            ?>

          </div>
          
          <div class="icon">
            
            <i class="ion ion-social-usd"></i>
          
          </div>

        </div>

      </div>

      <div class="col-lg-3 col-xs-6">

        <div class="small-box bg-red">
          
          <div class="inner">

            <span style="font-size: 25px;">Egresos</span><br>

            <?php 

              $totMedio = 0;
              $detalleEgresos = array();
              $vistaEgresos = '';
              foreach ($mediosPagos as $key => $value) {

                $totMedio =  $objCaja->ctrSumatoriaMedios(0, $value, $desdeFecha, $hastaFecha, $numeroCaja)["total"];

                if($totMedio > 0){
                  $totalEgresos += $totMedio;
                  array_push($detalleEgresos, array($value => $totMedio));
                  $vistaEgresos .= '<span style="font-size:20px">' . $value . ': $<b>' . $totMedio . '</b></span><br>';
                  echo '<span style="font-size:20px">' . $value . ': $<b>' . $totMedio . '</b></span><br>';
                }

              }

              $detalleEgresos = json_encode($detalleEgresos);
            ?>

          </div>
          
          <div class="icon">
            
            <i class="ion ion-social-usd"></i>
          
          </div>

        </div>

      </div>

    <?php } ?>

    </div>

  </section>

  <section class="content" style="padding-top: 0px"> 

      <div class="box">

       <nav class="navbar">
          <div class="container-fluid">
            <div class="pull-left" data-example-id="split-button-dropdown">
              
              <div class="btn-group">
                <a href="#" data-toggle="modal" data-target="#modalAgregarMovimientoCaja" data-dismiss="modal" class="btn btn-primary btn-sm menuCajaCentral">Agregar Movimientos</a>
              </div><!-- /btn-group -->

              <div class="btn-group">
                <a href="#" data-toggle="modal" data-target="#modalAgregarCierreCaja" data-dismiss="modal" class="btn btn-primary btn-sm" style="<?php echo $habilitoCierre; ?> ">Cierre caja</a>
              </div>
          
              <div class="btn-group">
                <button type="button" class="btn btn-default btn-sm" id="daterangeCajaCentral">
           
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

              </div><!-- /btn-group -->

          </div>
        </div>

      <div class="box-body">

       <table class="table table-bordered table-striped dt-responsive" id="tablaCajaCentral" width="100%">

        <thead>

      <tr>

        <th>Fecha</th>
        <th>Control</th>
        <th>Usuario</th>
        <th>Punto</th>
        <th>Detalle</th>
        <th>Medio</th>
        <th>Entrada</th>
        <th>Salida</th>
        <th>Saldo</th>

      </tr>

        </thead>

        <tfoot>

            <tr>
                <th></th>
                <th>Control</th>
                <th>Usuario</th>
                <th>Punto</th>
                <th>Detalle</th>
                <th>Medio</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>

        </tfoot>

        <tbody>

        <?php 

          echo $muestroSaldo;

          $saldoBucle = $saldoInicio;

          foreach ($arrayCaja as $key => $value) {

            echo '<tr>

                  <td>'.$value["fecha"].'</td>

                  <td>'.$value["id"].'</td>';

            echo '<td>'.$value["nombre"].'</td>';

            echo '<td>'.$value["punto_venta"].'</td>';

            echo '<td>'.$value["descripcion"].'</td>';

                //$arrMetodoPago = json_decode($value["medio_pago"]);

                //echo '<td>'.$arrMetodoPago[0]->tipo.'</td>';

            echo '<td>'.$value["medio_pago"].'</td>';

            if($value["tipo"] == 1) {

                echo '<td style="color: green">'. round($value["monto"], 2) .'</td>
                      <td></td>';
                $saldoBucle = $saldoBucle + $value["monto"];

            } else {

                echo '<td></td>
                      <td style="color: red">'.round($value["monto"], 2) .'</td>';
                $saldoBucle = $saldoBucle - $value["monto"];

            }

            $colorTd = ($saldoBucle >= 0) ? "green" : "red";
                    
            echo '<td style="color:'.$colorTd.'">'.round($saldoBucle, 2).'</td>

            </tr>';
          }

          $cierre = (isset($cierre)) ? $cierre : null;
          $idParaCierre = (isset($value["id"])) ? $value["id"] : $cierre["ultimo_id_caja"];

        ?>

        </tbody>

       </table>

      </div>

    </div>

  </section>

</div>

<!--=====================================
MODAL INGRESAR MOVIMIENTO
======================================-->
<div id="modalAgregarMovimientoCaja" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        
        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Movimiento</h4>
        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">
          <div class="box-body">
            <input type="hidden" id="idUsuarioMovimiento" name="idUsuarioMovimiento" value="<?php echo $_SESSION["id"]; ?>">
            <input type="hidden" id="ingresoCajaDesde" name="ingresoCajaDesde" value="cajas">
            <div class="form-group">
                <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-terminal"></i></span> 
                  <?php

                  $arrPuntos = json_decode($arrayEmpresa['ptos_venta'], true);
                  $arrPuntosHabilitados = explode(',', $_SESSION['puntos_venta']);

                  echo '<select title="Seleccione el punto de venta" class="form-control input-sm" id="ingresoCajaPtoVta" name="puntoVentaMovimiento">';
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

            <!-- ENTRADA PARA TIPO (INGRESO / EGRESO) --->
            <div class="form-group">
                <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-dot-circle-o"></i></span> 
                <select class="form-control" name="ingresoCajaTipo" id="ingresoCajaTipo" required>
                  <option>Seleccionar Tipo</option>
                  <option value="1">Ingreso</option>
                  <option value="0">Egreso</option>
                </select>
                </div>
            </div>
      
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-usd"></i></span> 

                <input type="number" min="0" lang="es" step="0.01" class="form-control" name="ingresoMontoCajaCentral" id="ingresoMontoCajaCentral" placeholder="Ingrese monto" >

              </div>

            </div>

            <!-- ENTRADA PARA MEDIO PAGO --->
            <div class="form-group">

                <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span> 
                <select class="form-control" name="ingresoMedioPago" id="ingresoMedioPago" required>
                  <option value="Efectivo" selected>Efectivo</option>
                  <option value="MP" >Mercado Pago</option>
                  <option value="TC" >Tarjeta Credito</option>
                  <option value="TD" >Tarjeta Debito</option>
                  <option value="TR" >Transferencia</option>
                  <option value="CH" >Cheque</option>
                </select>

                </div>

            </div>

            <!-- ENTRADA PARA DESCRIPCION -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <input type="text" class="form-control" name="ingresoDetalleCajaCentral" id="ingresoDetalleCajaCentral" placeholder="Ingrese detalle" >
              </div>
            </div>

          </div>
        </div>

        <!--PIE DEL MODAL-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>

      </form>

      <?php
        $objCaja -> ctrCrearCaja();
      ?>

    </div>
  </div>
</div>

<!--=====================================
MODAL CIERRE CAJA
======================================-->
<div id="modalAgregarCierreCaja" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">

        <!--CABEZA DEL MODAL-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Cierre Caja</h4>
        </div>

        <!--CUERPO DEL MODAL-->
        <div class="modal-body">
          <div class="box-body">

            <input type="hidden" id="idUsuarioCierre" name="idUsuarioCierre" value="<?php echo $_SESSION["id"]; ?>">
            <input type="hidden" id="ultimoIdCajaCierre" name="ultimoIdCajaCierre" value="<?php echo $idParaCierre; ?>">
            <input type="hidden" id="totalIngresosCierre" name="totalIngresosCierre" value="<?php echo $totalIngresos; ?>">
            <input type="hidden" id="totalEgresosCierre" name="totalEgresosCierre" value="<?php echo $totalEgresos; ?>">
            <input type="hidden" id="detalleIngresosCierre" name="detalleIngresosCierre" value="<?php echo htmlspecialchars($detalleIngresos); ?>">
            <input type="hidden" id="detalleEgresosCierre" name="detalleEgresosCierre" value="<?php echo htmlspecialchars($detalleEgresos); ?>">
            <input type="hidden" id="puntoVentaCierre" name="puntoVentaCierre" value="<?php echo $numeroCaja; ?>">

            <!-- PUNTO DE VENTA -->
            <?php 
              $buscoPto = array_search($numeroCaja, array_column( $arrPuntos, 'pto'));
            ?>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">Punto Vta/Cobro </span> 
                <input type="text" class="form-control" value=" <?php echo $numeroCaja . '-' . $arrPuntos[$buscoPto]["det"]; ?> " readonly>
              </div>
            </div>
      
            <!--CABECERA INGRESOS EGRESOS -->
            <div class="form-group row">
              <div class="col-xs-6" style="color: green; font-size:20px">INGRESOS: <b><?php echo '$ ' . $totalIngresos; ?></b></div>
              <div class="col-xs-6" style="color: red; font-size:20px">EGRESOS: <b><?php echo '$ ' . $totalEgresos; ?></b></div>
            </div>

            <!--DETALLE INGRESOS EGRESOS -->
            <div class="form-group row">
              <div class="col-xs-6"><?php echo $vistaIngresos; ?></div>
              <div class="col-xs-6"><?php echo $vistaEgresos; ?></div>
            </div>
                  
            <!--CAMBIO PROXIMO TURNO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon">Efectivo <i class="fa fa-usd"></i></span> 
                <input type="number" min="0" lang="es" step="0.01" class="form-control" name="aperturaSiguienteMonto" id="aperturaSiguienteMonto" placeholder="Cambio próximo turno" >
              </div>
            </div>

            <!-- ENTRADA PARA DESCRIPCION -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <input type="text" autocomplete="off" class="form-control" name="cierreCajaDetalle" id="cierreCajaDetalle" placeholder="Ingrese detalle" >
              </div>
            </div>

          </div>
        </div>

        <!--PIE DEL MODAL-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>

      </form>

      <?php
        $objCierreCaja -> ctrCrearCierreCaja();
      ?>

    </div>
  </div>
</div>