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

<style>
  /* ============================
     Estilos modernos para cajas
     ============================ */

  .cajas-box {
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #e0e0e0;
  }

  .cajas-box-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 1px solid #e0e0e0;
    padding: 15px 20px;
    border-radius: 12px 12px 0 0;
  }

  .cajas-box-body {
    padding: 25px;
  }

  /* Cards de ingresos/egresos mejoradas */
  .cajas-card-ingresos {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    border-radius: 12px;
    padding: 25px;
    color: white;
    box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 20px;
  }

  .cajas-card-ingresos:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
  }

  .cajas-card-egresos {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border-radius: 12px;
    padding: 25px;
    color: white;
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 20px;
  }

  .cajas-card-egresos:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
  }

  .cajas-card-title {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .cajas-card-detail {
    font-size: 18px;
    margin-bottom: 8px;
    font-weight: 500;
  }

  .cajas-card-detail b {
    font-weight: 700;
    font-size: 20px;
  }

  .cajas-card-icon {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 60px;
    opacity: 0.3;
  }

  /* Botones mejorados */
  .cajas-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
    color: white;
  }

  .cajas-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
  }

  .cajas-btn-date {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
  }

  .cajas-btn-date:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
  }

  /* Selector de caja mejorado */
  .cajas-selector-wrapper {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    padding: 15px 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #f0f0f0;
  }

  /* Tabla responsive */
  .cajas-table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    width: 100%;
    margin-top: 20px;
  }

  #tablaCajaCentral {
    width: 100% !important;
    min-width: 900px;
  }

  #tablaCajaCentral thead tr {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }

  #tablaCajaCentral thead tr th {
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    border: none;
    padding: 12px 8px;
    white-space: nowrap;
  }

  #tablaCajaCentral tfoot th {
    background: #f8f9fa;
    padding: 8px;
    border-top: 2px solid #e0e0e0;
  }

  #tablaCajaCentral tfoot th input {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 13px;
    transition: all 0.3s ease;
  }

  #tablaCajaCentral tfoot th input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
  }

  #tablaCajaCentral tbody tr {
    transition: background-color 0.2s ease;
  }

  #tablaCajaCentral tbody tr:hover {
    background-color: #f8f9fa;
  }

  #tablaCajaCentral tbody td {
    vertical-align: middle;
    padding: 12px 8px;
  }

  /* Mejorar buscador */
  #tablaCajaCentral_filter {
    margin-bottom: 20px !important;
  }

  #tablaCajaCentral_filter label {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    font-weight: 600 !important;
    color: #2c3e50 !important;
  }

  #tablaCajaCentral_filter input {
    border: 2px solid #e0e0e0 !important;
    border-radius: 8px !important;
    padding: 10px 15px !important;
    font-size: 14px !important;
    transition: all 0.3s ease !important;
    width: 300px !important;
    max-width: 100% !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
  }

  #tablaCajaCentral_filter input:focus {
    border-color: #667eea !important;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2) !important;
    outline: none !important;
  }

  /* Responsive para móviles */
  @media (max-width: 768px) {
    .cajas-box-body {
      padding: 15px;
    }

    .cajas-table-wrapper {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    #tablaCajaCentral {
      min-width: 1000px;
    }

    .cajas-card-ingresos,
    .cajas-card-egresos {
      padding: 20px;
    }

    .cajas-card-icon {
      font-size: 40px;
    }
  }
</style>

<div class="content-wrapper">

  <section class="content-header">
      <h1>
      
      <?php echo $tituloAdmin; ?>
    
    </h1>
    
    <!-- COMBO PARA SELECCIONAR CAJA -->  
    <div class="cajas-selector-wrapper">
      <div class="row">
        <div class="col-md-4 col-sm-6">
          <label style="font-weight: 600; color: #2c3e50; margin-bottom: 8px; display: block;">
            <i class="fa fa-building"></i> Seleccionar caja
          </label>
          <div class="input-group">
            <?php 

              echo '<select title="Seleccione el punto de cobro/pago" class="form-control" id="cajasListadoPuntosVta" name="cajasListadoPuntosVta" style="border-radius: 8px 0 0 8px;">';

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

               echo '<span class="input-group-btn"><a id="aCajaVerCajas" class="btn cajas-btn-primary" style="border-radius: 0 8px 8px 0; color: white;">Ir</a></span>';
            ?>
          </div>
        </div>
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
      <div class="col-lg-6 col-md-6 col-sm-12">

        <div class="cajas-card-ingresos" style="position: relative;">
          
          <div class="cajas-card-title">
            <i class="fa fa-arrow-up"></i> Ingresos
          </div>

          <div class="cajas-card-icon">
            <i class="fa fa-usd"></i>
          </div>

          <div class="inner" style="position: relative; z-index: 1;">

            <?php 

              $totMedio = 0;
              
              $detalleIngresos = array();
              $vistaIngresos = '';
              foreach ($mediosPagos as $key => $value) {

                $totMedio =  $objCaja->ctrSumatoriaMedios(1, $value, $desdeFecha, $hastaFecha, $numeroCaja)["total"];

                if($totMedio > 0){
                  $totalIngresos += $totMedio;
                  array_push($detalleIngresos, array($value => $totMedio));
                  $vistaIngresos .= '<div class="cajas-card-detail">' . $value . ': $<b>' . number_format($totMedio, 2, ',', '.') . '</b></div>';
                  echo '<div class="cajas-card-detail">' . $value . ': $<b>' . number_format($totMedio, 2, ',', '.') . '</b></div>';
                }

              }

              $detalleIngresos = json_encode($detalleIngresos);

            ?>

          </div>

        </div>

      </div>

      <div class="col-lg-6 col-md-6 col-sm-12">

        <div class="cajas-card-egresos" style="position: relative;">
          
          <div class="cajas-card-title">
            <i class="fa fa-arrow-down"></i> Egresos
          </div>

          <div class="cajas-card-icon">
            <i class="fa fa-usd"></i>
          </div>

          <div class="inner" style="position: relative; z-index: 1;">

            <?php 

              $totMedio = 0;
              $detalleEgresos = array();
              $vistaEgresos = '';
              foreach ($mediosPagos as $key => $value) {

                $totMedio =  $objCaja->ctrSumatoriaMedios(0, $value, $desdeFecha, $hastaFecha, $numeroCaja)["total"];

                if($totMedio > 0){
                  $totalEgresos += $totMedio;
                  array_push($detalleEgresos, array($value => $totMedio));
                  $vistaEgresos .= '<div class="cajas-card-detail">' . $value . ': $<b>' . number_format($totMedio, 2, ',', '.') . '</b></div>';
                  echo '<div class="cajas-card-detail">' . $value . ': $<b>' . number_format($totMedio, 2, ',', '.') . '</b></div>';
                }

              }

              $detalleEgresos = json_encode($detalleEgresos);
            ?>

          </div>

        </div>

      </div>

    <?php } ?>

    </div>

  </section>

  <section class="content" style="padding-top: 0px"> 

      <div class="box cajas-box">

       <div class="box-header with-border cajas-box-header">
          <div class="row">
            <div class="col-md-8 col-sm-12">
              <div class="btn-group" style="margin-bottom: 10px;">
                <a href="#" data-toggle="modal" data-target="#modalAgregarMovimientoCaja" data-dismiss="modal" class="btn cajas-btn-primary menuCajaCentral" style="margin-right: 10px;">
                  <i class="fa fa-plus"></i> Agregar Movimientos
                </a>
                <a href="#" data-toggle="modal" data-target="#modalAgregarCierreCaja" data-dismiss="modal" class="btn cajas-btn-primary" style="<?php echo $habilitoCierre; ?>">
                  <i class="fa fa-lock"></i> Cierre caja
                </a>
              </div>
            </div>
            <div class="col-md-4 col-sm-12 text-right">
              <button type="button" class="btn cajas-btn-date" id="daterangeCajaCentral" style="color: white;">
         
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
        </div>

      <div class="box-body cajas-box-body">

       <div class="cajas-table-wrapper">
       <table class="table table-bordered table-striped dt-responsive" id="tablaCajaCentral" width="100%">

        <thead>

      <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">

        <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Fecha</th>
        <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Control</th>
        <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Usuario</th>
        <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Punto</th>
        <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Detalle</th>
        <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Medio</th>
        <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Entrada</th>
        <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Salida</th>
        <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Saldo</th>

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
            <input type="hidden" name="csrf_token" value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
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

            <input type="hidden" name="csrf_token" value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
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