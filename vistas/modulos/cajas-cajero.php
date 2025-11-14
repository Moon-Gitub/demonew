<?php
    date_default_timezone_set('America/Argentina/Mendoza');

    $objCaja = new ControladorCajas();
    $objCierreCaja = new ControladorCajaCierres();
    
    $arrPuntos = json_decode($arrayEmpresa['ptos_venta'], true);
    $arrPuntosHabilitados = explode(',', $_SESSION['puntos_venta']);
    
    $numeroCaja = (isset($_GET["numCaja"]) ) ? $_GET['numCaja'] : $arrPuntosHabilitados[0];

    $mediosPagos = array('Efectivo', 'TC', 'TD', 'TR', 'CH', 'MP');
    $muestroSaldo = '';
    $totalIngresos = 0;
    $totalEgresos = 0;
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

?>

<!-- Este hidden lo uso para el rango de fechas -->
<input type="hidden" id="numCaja" value="<?php echo $numeroCaja; ?>"> 

<div class="content-wrapper">
    <section class="content-header">
        <h1> <?php echo "Administrar caja <small> (Punto de Venta: " . $numeroCaja . ")</small>"; ?> </h1>
        <ol class="breadcrumb">
          <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
          <li class="active">Administrar caja </li>
        </ol>

        <div class="row">
        <?php 
            if($numeroCaja <> 0) {
                $totMedio = 0;
                $detalleIngresos = array();
                //$vistaIngresos = '';
                foreach ($mediosPagos as $key => $value) {
                    $totMedio =  $objCaja->ctrSumatoriaMedios(1, $value, $desdeFecha, $hastaFecha, $numeroCaja)["total"];
                    if($totMedio > 0){
                      $totalIngresos += $totMedio;
                      array_push($detalleIngresos, array($value => $totMedio));
                      //$vistaIngresos .= '<span style="font-size:20px">' . $value . ': $<b>' . $totMedio . '</b></span><br>';
                      //echo '<span style="font-size:20px">' . $value . ': $<b>' . $totMedio . '</b></span><br>';
                    }
                }
                
                $detalleIngresos = json_encode($detalleIngresos);
                $totMedio = 0;
                $detalleEgresos = array();
                //$vistaEgresos = '';
                foreach ($mediosPagos as $key => $value) {
                    $totMedio =  $objCaja->ctrSumatoriaMedios(0, $value, $desdeFecha, $hastaFecha, $numeroCaja)["total"];
                    if($totMedio > 0){
                      $totalEgresos += $totMedio;
                      array_push($detalleEgresos, array($value => $totMedio));
                      //$vistaEgresos .= '<span style="font-size:20px">' . $value . ': $<b>' . $totMedio . '</b></span><br>';
                      //echo '<span style="font-size:20px">' . $value . ': $<b>' . $totMedio . '</b></span><br>';
                    }
                }
                $detalleEgresos = json_encode($detalleEgresos);
            } 
        ?>

        </div>
    </section>

    <section class="content" > 
      <div class="box">
            <nav class="navbar">
                <div class="container-fluid">
                    <div class="pull-left" data-example-id="split-button-dropdown">

                        <div class="btn-group">
                            <a href="#" data-toggle="modal" data-target="#modalAgregarMovimientoCaja" data-dismiss="modal" class="btn btn-primary btn-sm ">Agregar Movimientos</a>
                        </div>
                        
                        <div class="btn-group">
                            <a href="#" data-toggle="modal" data-target="#modalContadorBilletes" data-dismiss="modal" class="btn btn-primary btn-sm ">Contador Billetes</a>
                        </div>
                        
                        <div class="btn-group">
                            <a href="#" data-toggle="modal" data-target="#modalAgregarDatosManual" data-dismiss="modal" class="btn btn-primary btn-sm ">Cierre caja</a>
                        </div>
                        
                    </div>
                </div>
            </nav>

      <div class="box-body">

       <!--TABLA COMPLETA DE MOVIMIENTOS -->
       <div id="divTablaCierreUsuario">
           <table class="table table-bordered table-striped dt-responsive" id="tablaCajaCentral"  width="100%">
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
                    //echo '<tr style="display:none;">
                    echo '<tr>
                          <td>'.$value["fecha"].'</td>
                          <td>'.$value["id"].'</td>';
                    echo '<td>'.$value["nombre"].'</td>';
                    echo '<td>'.$value["punto_venta"].'</td>';
                    echo '<td>'.$value["descripcion"].'</td>';
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
       
       <!--TABLA CON SOLO EGRESOS -->
       <div id="divTablaCierreUsuarioEgresos">
           <table class="table table-bordered table-striped dt-responsive " id="tablaSoloEgresos"  width="100%">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Control</th>
                <th>Usuario</th>
                <th>Punto</th>
                <th>Detalle</th>
                <th>Medio</th>
                <th>Salida</th>
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
                </tr>
            </tfoot>
    
            <tbody>
                <?php 
                  foreach ($arrayCaja as $key => $value) {
                    if($value["tipo"] == 0){
                        echo '<tr>
                              <td>'.$value["fecha"].'</td>
                              <td>'.$value["id"].'</td>';
                        echo '<td>'.$value["nombre"].'</td>';
                        echo '<td>'.$value["punto_venta"].'</td>';
                        echo '<td>'.$value["descripcion"].'</td>';
                        echo '<td>'.$value["medio_pago"].'</td>';
                        echo '<td style="color: red">'.round($value["monto"], 2) .'</td>';
                              
                        echo '</tr>';
                    }
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

<!--MODAL CONTADOR BILLETES -->
<div id="modalContadorBilletes" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">

        <!--CABEZA DEL MODAL-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">1-Contador Billetes</h4>
        </div>

        <!--CUERPO DEL MODAL-->
        <div class="modal-body">
          <div class="box-body">

            <!-- ENTRADAS PARA BILLETES--->
            <table>
                
                <tr>
                    <td style="padding: 5px">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"> <img src="vistas/img/billete10000.gif" width="75px" ></span> 
                                <input type="text" class="form-control" id="billete10000" style="height: 50px; width: 75px" onkeyup="contarBilletes()">
                            </div>
                        </div>
                    </td>
                    
                    <td>
                        <!--
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"> <img src="vistas/img/billete20000.gif" width="75px" >$ 20.000<b></span> 
                                <input type="text" class="form-control" id="billete20000" onkeyup="contarBilletes()">
                            </div>
                        </div>
                        -->
                    </td>
                </tr>
                
                <tr>
                    <td style="padding: 5px">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"> <img src="vistas/img/billete2000.gif" width="75px" ></span> 
                                <input type="text" class="form-control" id="billete2000" style="height: 50px; width: 75px" onkeyup="contarBilletes()">
                            </div>
                        </div>
                        
                    </td>
                    
                    <td>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"> <img src="vistas/img/billete100.gif" width="75px" ></span> 
                                <input type="text" class="form-control" id="billete100" style="height: 50px; width: 75px" onkeyup="contarBilletes()">
                            </div>
                        </div>
                        
                    </td>
                </tr>
                
                <tr>
                    <td style="padding: 5px">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"> <img src="vistas/img/billete1000.gif" width="75px" ></span> 
                                <input type="text" class="form-control" id="billete1000" style="height: 50px; width: 75px" onkeyup="contarBilletes()">
                            </div>
                        </div>
                        
                    </td>
                    
                    <td>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"> <img src="vistas/img/billete50.gif" width="75px" ></span> 
                                <input type="text" class="form-control" id="billete50" style="height: 50px; width: 75px" onkeyup="contarBilletes()">
                            </div>
                        </div>
                        
                    </td>
                </tr>
                
                <tr>
                    <td style="padding: 5px">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"> <img src="vistas/img/billete500.gif" width="75px" ></span> 
                                <input type="text" class="form-control" id="billete500" style="height: 50px; width: 75px" onkeyup="contarBilletes()">
                            </div>
                        </div>
                        
                    </td>
                    
                    <td>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"> <img src="vistas/img/billete20.gif" width="75px" ></span> 
                                <input type="text" class="form-control" id="billete20" style="height: 50px; width: 75px" onkeyup="contarBilletes()">
                            </div>
                        </div>
                        
                    </td>
                </tr>
                
                <tr>
                    <td style="padding: 5px">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"> <img src="vistas/img/billete200.gif" width="75px" ></span> 
                                <input type="text" class="form-control" id="billete200" style="height: 50px; width: 75px" onkeyup="contarBilletes()">
                            </div>
                        </div>
                        
                    </td>
                    
                    <td>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"> <img src="vistas/img/billete10.gif" width="75px" ></span> 
                                <input type="text" class="form-control" id="billete10" style="height: 50px; width: 75px" onkeyup="contarBilletes()">
                            </div>
                        </div>
                        
                    </td>
                </tr>
                
            </table>

            <div class="form-group">
		    	<div class="input-group">
		      		<div class="input-group-addon"><i class="fa fa-dollar"></i></div>
		      		<input type="text" class="form-control" id="totalContadorBillete" readonly>
		      		<div class="input-group-addon"><button id="copiarTotalContadorBillete" type="button" data-dismiss="modal"><i class="fa fa-copy"></i></button></div>
		    	</div>
		  	</div>

          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!--MODAL CIERRE CAJA -->
<div id="modalAgregarDatosManual" class="modal fade" role="dialog" >
  <div class="modal-dialog">
    <div class="modal-content">

         <!--=CABEZA DEL MODAL-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">X</button>
          <h4 class="modal-title">Cierre Caja - Ingreso manual de datos</h4>
        </div>
        
         <!--<form role="form" method="post">-->

            <!-- CUERPO DEL MODAL-->
            <div class="modal-body">
              <div class="box-body">
                  
                <input type="hidden" id="idUsuarioCierre" name="idUsuarioCierre" value="<?php echo $_SESSION["id"]; ?>">
                <input type="hidden" id="ultimoIdCajaCierre" name="ultimoIdCajaCierre" value="<?php echo $idParaCierre; ?>">
                <input type="hidden" id="totalIngresosCierre" name="totalIngresosCierre" value="<?php echo $totalIngresos; ?>">
                <input type="hidden" id="totalEgresosCierre" name="totalEgresosCierre" value="<?php echo $totalEgresos; ?>">
                <input type="hidden" id="detalleIngresosCierre" name="detalleIngresosCierre" value="<?php echo htmlspecialchars($detalleIngresos); ?>">
                <input type="hidden" id="detalleEgresosCierre" name="detalleEgresosCierre" value="<?php echo htmlspecialchars($detalleEgresos); ?>">
                <input type="hidden" id="puntoVentaCierre" name="puntoVentaCierre" value="<?php echo $numeroCaja; ?>">
                <input type="hidden" id="saldoInicioCaja" name="saldoInicioCaja" value="<?php echo $saldoInicio; ?>">
    
                <?php 
                  $buscoPto = array_search($numeroCaja, array_column( $arrPuntos, 'pto'));
                ?>
    
                <div class="row">
                    <div class="col-md-6">
                        <!--PUNTO DE VENTA-->
                        <div class="form-group">
                          <div class="input-group">
                            <span class="input-group-addon">Punto Vta/Cobro </span> 
                            <input type="text" class="form-control" value=" <?php echo $numeroCaja . '-' . $arrPuntos[$buscoPto]["det"]; ?> " readonly>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!--FECHA-->
                        <div class="form-group">
                          <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span> 
                            <input type="date" class="form-control" name="ingresoFechaCierreSistema" id="ingresoFechaCierreSistema" value="<?php echo date("Y-m-d"); ?>" placeholder="Fecha" readonly>
                          </div>
                        </div>
                    </div>
                </div>
                
                <!-- COBROS --->
                <hr style="margin-bottom: 1px">
                <section class="content-header" >Estado de caja</section>
                <section class="content" style="min-height: 0px">
                    <div class="row">
                        <div class="col-xs-6">
                            <!-- ENTRADA PARA  (EFECTIVO) --->
                            <div class="form-group">
                              <div class="input-group">
                                <span class="input-group-addon" style="background-color: #00a65a80;"><i class="fa fa-usd"></i></span> 
                                <input type="number" min="0" lang="es" step="0.01" class="form-control" name="ingresoEfectivoManual" id="ingresoEfectivoManual" placeholder="Efectivo en caja" onkeyup="jsonIngresosEgresosManuales()">
                              </div>
                            </div>
                        </div>
                        <div class="col-xs-6"> 
                            <!-- ENTRADA PARA (TRANSFERENCIAS) --->
                            <div class="form-group">
                              <div class="input-group">
                                <span class="input-group-addon" style="background-color: #00a65a80;"><i class="fa fa-bank"></i></span> 
                                <input type="number" min="0" lang="es" step="0.01" class="form-control" name="ingresoTransferenciasManual" id="ingresoTransferenciasManual" placeholder="Transferencias" onkeyup="jsonIngresosEgresosManuales()">
                              </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <!-- ENTRADA PARA  (DEBITO) --->
                            <div class="form-group">
                              <div class="input-group">
                                <span class="input-group-addon" style="background-color: #00a65a80;"><i class="fa fa-credit-card"></i></span> 
                                <input type="number" min="0" lang="es" step="0.01" class="form-control" name="ingresoDebitoManual" id="ingresoDebitoManual" placeholder="Tarjeta debito" onkeyup="jsonIngresosEgresosManuales()">
                              </div>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <!-- ENTRADA PARA (CREDITO) --->
                            <div class="form-group">
                              <div class="input-group">
                                <span class="input-group-addon" style="background-color: #00a65a80;"><i class="fa fa-credit-card-alt"></i></span> 
                                <input type="number" min="0" lang="es" step="0.01" class="form-control" name="ingresoCreditoManual" id="ingresoCreditoManual" placeholder="Tarjeta credito" onkeyup="jsonIngresosEgresosManuales()">
                              </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <!-- ENTRADA PARA  (CHEQUE) --->
                            <div class="form-group">
                              <div class="input-group">
                                <span class="input-group-addon" style="background-color: #00a65a80;"><img src="vistas/img/cheque.png"></span>
                                <input type="number" min="0" lang="es" step="0.01" class="form-control" name="ingresoChequeManual" id="ingresoChequeManual" placeholder="Cheques" onkeyup="jsonIngresosEgresosManuales()">
                              </div>
                            </div>
                        </div>
                        <div class="col-xs-6">       
                            <!-- ENTRADA PARA (Mercado pago) --->
                            <div class="form-group">
                              <div class="input-group">
                                <span class="input-group-addon" style="background-color: #00a65a80;"><img src="vistas/img/mp.png"></span> 
                                <input type="number" min="0" lang="es" step="0.01" class="form-control" name="ingresoMercadoPagoManual" id="ingresoMercadoPagoManual" placeholder="Mercado Pago" onkeyup="jsonIngresosEgresosManuales()">
                              </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="totalIngresosCierreManual" id="totalIngresosCierreManual">
                </section>
                
                <!-- PAGOS 
                <hr style="margin-bottom: 1px">
                <section class="content-header" >Egresos</section>--->
                <section class="content" style="min-height: 0px; display: none;">
                    <div class="row">
                        <div class="col-xs-6">
                            <!-- ENTRADA PARA  (EFECTIVO) --->
                            <div class="form-group">
                              <div class="input-group">
                                <span class="input-group-addon" style="background-color: #dd4b399e;"><i class="fa fa-usd"></i></span> 
                                <input type="number" min="0" lang="es" step="0.01" class="form-control" name="egresoEfectivoManual" id="egresoEfectivoManual" placeholder="Total pagos efectivo" onkeyup="jsonIngresosEgresosManuales()">
                              </div>
                            </div>
                        </div>
                        <div class="col-xs-6">    
                            <!-- ENTRADA PARA (TRANSFERENCIAS) --->
                            <div class="form-group">
                              <div class="input-group">
                                <span class="input-group-addon" style="background-color: #dd4b399e;"><i class="fa fa-bank"></i></span> 
                                <input type="number" min="0" lang="es" step="0.01" class="form-control" name="egresoTransferenciasManual" id="egresoTransferenciasManual" placeholder="Total pagos en transferencias" onkeyup="jsonIngresosEgresosManuales()">
                              </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <!-- ENTRADA PARA  (CHEQUES) --->
                            <div class="form-group">
                              <div class="input-group">
                                <span class="input-group-addon" style="background-color: #dd4b399e;"><img src="vistas/img/cheque.png"></span> 
                                <input type="number" min="0" lang="es" step="0.01" class="form-control" name="egresoChequesManual" id="egresoChequesManual" placeholder="Total pagos en cheques" onkeyup="jsonIngresosEgresosManuales()">
                              </div>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <!-- ENTRADA PARA  (MERCADO PAGO) --->
                            <div class="form-group">
                              <div class="input-group">
                                <span class="input-group-addon" style="background-color: #dd4b399e;"><img src="vistas/img/mp.png"></span> 
                                <input type="number" min="0" lang="es" step="0.01" class="form-control" name="egresoMercadoPagoManual" id="egresoMercadoPagoManual" placeholder="Total pagos en mercado pago" onkeyup="jsonIngresosEgresosManuales()">
                              </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="totalEgresosCierreManual" id="totalEgresosCierreManual">
                </section>

                <input type="hidden" name="totalDiferenciasCierre" id="totalDiferenciasCierre" value="[]">

                <!-- ENTRADA PARA CAMBIO --->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-usd"></i></span> 
                    <input type="number" min="0" lang="es" step="0.01" class="form-control" name="aperturaSiguienteMonto" id="aperturaSiguienteMonto" placeholder="Ingrese cambio próximo turno">
                  </div>
                </div>
                
                <!-- ENTRADA PARA DETALLE OBSERVACIONES --->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                    <textarea class="form-control" name="cierreCajaDetalle" id="cierreCajaDetalle"  placeholder="Ingrese Detalle/observaciones"></textarea>
                  </div>
                </div>
    
              </div>
            </div>
    
            <!--PIE DEL MODAL-->
            <div class="modal-footer">
              <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
              <button type="button" id="btnCierreCajaCajero" class="btn btn-primary" >Guardar</button>
            </div>
        
        <!--</form>-->
        <?php
            //$objCierreCaja -> ctrCrearCierreCaja();
        ?>

    </div>
  </div>
</div>

<!--MODAL INGRESAR MOVIMIENTO-->
<div id="modalAgregarMovimientoCaja" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">

        <!--CABEZA DEL MODAL-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Movimiento</h4>
        </div>

        <!--CUERPO DEL MODAL-->
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

            <!-- ENTRADA PARA TIPO (INGRESO / EGRESO)
            <div class="form-group">
                <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-dot-circle-o"></i></span> 
                <select class="form-control" name="ingresoCajaTipo" id="ingresoCajaTipo" readonly>
                  <option>Seleccionar Tipo</option>
                  <option value="1">Ingreso</option>
                  <option value="0" selected>Egreso</option>
                </select>
                </div>
            </div> --->
            <div class="form-group">
                <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-dot-circle-o"></i></span> 
                    <input type="text" class="form-control" value="Egreso" readonly>
                    <input type="hidden" name="ingresoCajaTipo" id="ingresoCajaTipo" value="0">
                </div>
            </div> 
      
            <!-- ENTRADA PARA MONTO --->
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

<!-- TICKET -->
<div id="modalImprimirCierre" class="modal fade" role="dialog" style="overflow-y: scroll;">
    <div class="modal-dialog">
        <div class="modal-content">

        <!--CUERPO DEL MODAL-->
        <div class="modal-body">
            <div class="box-body">
                <div id="impTicketCierreCaja" style="font-size: 15px;">
                    <br>
                    <div style="text-align: center">===========================================================================</div>
                    <div style="text-align: center">Ticket Cierre de Caja<br><b><?php echo $arrayEmpresa["razon_social"]; ?></b></div>
                    <div>
                        <span><?php echo 'Usuario: ' . $_SESSION['nombre'] ?></span> <span style="padding-left:205px" id="txtFechaHoraTicket"> FECHA</span> <br>
                    </div>
                    <div style="text-align: center">===========================================================================</div>
                    <center><b>Detalle (sistema)</b></center>
                    <br>
                        <table width="100%" id="tckDetalleMovimientos"></table>
                    <br>
                    <div style="text-align: center">===========================================================================</div>
                    <center><b>Recuento manual</b></center>
                    <br>
                        <table width="100%" id="tckDetalleMovimientosManual"></table>
                    <br>
                    <div style="text-align: center">===========================================================================</div>
                    <center><b>Diferencias</b></center>
                    <br>
                        <table width="100%" id="tckDetalleMovimientosDiferencias"></table>
                    <br>
                    <div style="text-align: center">===========================================================================</div>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <div style="text-align: center">------------------------------------------------------</div>
                    <div style="text-align: center">Firma y sello de responsable</div> 
                    <div style="text-align: center">===========================================================================</div> 
                </div>
            </div>
        </div>

        <!--PIE DEL MODAL-->
        <div class="modal-footer">
            <button type="button" id="btnSalirTicketUsuario" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
            <button type="button" id="btnImprimirTicketUsuario" class="btn btn-primary"><i class="fa fa-ticket" aria-hidden="true"></i> Ticket</button>
        </div>
    </div>
</div>

<script>

//AGREGA UN INPUT TEXT PARA BUSCAR EN CADA COLUMNA
$("#tablaSoloEgresos tfoot th").each(function (i) {
  var title = $(this).text();
  if(title != ""){
    $(this).html('<input type="text" placeholder="Filtrar por ' + title + '" />');
  }

});

var tablaSoloEgresos = $("#tablaSoloEgresos").DataTable( {
    "pageLength": 50,
    "columnDefs": [
      { "targets": [1,2,3,4,5], "orderable": false }],
    "dom": 'Bfrtip',
    "footerCallback": function (row, data, start, end, display) {
        var api = this.api();
        var intVal = function (i) {
            return typeof i === 'string' ?
                i.replace(/[\$]/g, '').replace(/,/g, '.') * 1 :
                typeof i === 'number' ?
                    i : 0;
        };
        var totalPageI = api
            .column(6, {search:'applied'})
            .data()
            .reduce(function (a, b) {
                return intVal(a) + intVal(b);
            }, 0);
        $(api.column(6).footer()).html(
            ` ${totalPageI.toFixed(2)}`
        )
    }
});

 tablaSoloEgresos.columns().every(function () {
      var that = this;
      $('input', this.footer()).on('keyup change', function () {
        if (that.search() !== this.value) {  
            that
                .column($(this).parent().index() + ':visible')
                .search(this.value)
                .draw(); 
        }
      });
});

$("#divTablaCierreUsuario").css('display', 'none');

//CONTADOR DE BILLETES
function contarBilletes () {

  //var veintemil = $("#billete20000").val();
  var diezmil = $("#billete10000").val();
  var dosmil = $("#billete2000").val();
  var mil = $("#billete1000").val();
  var quin = $("#billete500").val();
  var dosc = $("#billete200").val();
  var cien = $("#billete100").val();
  var cinc = $("#billete50").val();
  var vein = $("#billete20").val();
  var diez = $("#billete10").val();
  
  var total = (10000 * diezmil) + (2000 * dosmil) + (1000 * mil) + (500 * quin) + (200 * dosc) + (100 * cien) + (50 * cinc) + (20 * vein) + (10 * diez);

  $("#totalContadorBillete").val(total);

}

$("#copiarTotalContadorBillete").click(function(){

  var copyText = document.getElementById("totalContadorBillete");
  copyText.select();
  copyText.setSelectionRange(0, 99999);
  document.execCommand("copy");
  
  $("#ingresoEfectivoManual").val($("#totalContadorBillete").val());
  jsonIngresosEgresosManuales();

  swal({
    title: "Copiado!",
    text: copyText.value,
    toast: true,
    timer: 2000,
    position: 'top',
    type: "success",
    confirmButtonText: "Cerrar!"
  });

});

//ACA REALIZO TODO EL RECUENTO DE LOS INGRESOS MANUALES, LOS COMPARO CON SISTEMA Y CALCULO DIFERENCIAS
function jsonIngresosEgresosManuales(){
    var ingresos = [];
    //var egresos = [];

    //Ingresos manuales
    ($("#ingresoEfectivoManual").val() > 0) ? ingresos.push({"Efectivo":Number($("#ingresoEfectivoManual").val())}) : null;
    ($("#ingresoTransferenciasManual").val() > 0) ? ingresos.push({"TR":Number($("#ingresoTransferenciasManual").val())}) : null;
    ($("#ingresoDebitoManual").val() > 0) ? ingresos.push({"TD":Number($("#ingresoDebitoManual").val())}) : null;
    ($("#ingresoCreditoManual").val() > 0) ? ingresos.push({"TC":Number($("#ingresoCreditoManual").val())}) : null;
    ($("#ingresoChequeManual").val() > 0) ? ingresos.push({"CH":Number($("#ingresoChequeManual").val())}) : null;
    ($("#ingresoMercadoPagoManual").val() > 0) ? ingresos.push({"MP":Number($("#ingresoMercadoPagoManual").val())}) : null;
    $("#totalIngresosCierreManual").val(JSON.stringify(ingresos)); 

    //Egresos manuales - EN PRINCIPIO NO LO USARIA
    /*
    ($("#egresoEfectivoManual").val() > 0) ? egresos.push({"Efectivo":Number($("#egresoEfectivoManual").val())}) : null;
    ($("#egresoTransferenciasManual").val() > 0) ? egresos.push({"TR":Number($("#egresoTransferenciasManual").val())}) : null;
    ($("#egresoChequesManual").val() > 0) ? egresos.push({"CH":Number($("#egresoChequesManual").val())}) : null;
    ($("#egresoMercadoPagoManual").val() > 0) ? egresos.push({"MP":Number($("#egresoMercadoPagoManual").val())}) : null;    
    $("#totalEgresosCierreManual").val(JSON.stringify(egresos)); 
    */

    var ingresosSistema = JSON.parse($("#detalleIngresosCierre").val());

    var egresosSistema = JSON.parse($("#detalleEgresosCierre").val());

    var diferencias = [];
    var inicioCaja = 0;
    var mediosPago = ["Efectivo", "TR", "MP", "CH", "TD", "TC"];
 
    var ingSis=0;
    var ingMan=0;
    var egSis=0;
        
    //recorro los medios de pago
    for(let i=0; i < mediosPago.length; i++){

        ingSis=0;
        ingMan=0;
        egSis=0;

        //si es efectivo guardo el inicio de caja anterior
        inicioCaja = (mediosPago[i] == "Efectivo") ? Number($("#saldoInicioCaja").val()) : 0;

        //itero ingresos sistema
        for(let j=0; j < ingresosSistema.length; j++){
            //si el medio de pago iterado está cargado en el sistema 
            if(mediosPago[i] === Object.keys(ingresosSistema[j])[0]) {
                ingSis = Number(ingresosSistema[j][mediosPago[i]]);
            }
        }

        //itero egresos sistema
        for(let l=0; l < egresosSistema.length; l++){
            if(mediosPago[i] === Object.keys(egresosSistema[l])[0]) {
                egSis = Number(egresosSistema[l][mediosPago[i]]);
            }
        }

        //itero datos manuales
        for(let k=0; k < ingresos.length; k++){     
            if(mediosPago[i] === Object.keys(ingresos[k])[0]) {
                ingMan = Number(ingresos[k][mediosPago[i]]);
            }
        }
        
        let calculoDiferencia = (ingSis + inicioCaja) - egSis - ingMan;
        //console.log("Medio de pago: (importe en sistema + importe inicio de caja) - importe en egresos - importe recuento caja = DIFERENCIA")
        //console.log(mediosPago[i] + ": ("+ingSis+ " + " + inicioCaja + ") - " + egSis + " - " + ingMan + "=" + calculoDiferencia );
        if(calculoDiferencia != 0 ) {
            diferencias.push({[mediosPago[i]] : calculoDiferencia });
        }
    }

    $("#totalDiferenciasCierre").val(JSON.stringify(diferencias));

}

//BOTON PARA CERRAR CAJA Y ARMAR TICKET
$("#btnCierreCajaCajero").click(function (e) {
    e.preventDefault();

    var datosCierreCajaCajero = new FormData();
    datosCierreCajaCajero.append("cierre_caja_rol_cajero", 1);
    datosCierreCajaCajero.append("fecha_hora", $("#ingresoFechaCierreSistema").val());
    datosCierreCajaCajero.append("punto_venta_cobro", $("#puntoVentaCierre").val());
    datosCierreCajaCajero.append("ultimo_id_caja", $("#ultimoIdCajaCierre").val());
    datosCierreCajaCajero.append("total_ingresos", $("#totalIngresosCierre").val());
    datosCierreCajaCajero.append("total_egresos", $("#totalEgresosCierre").val());
    datosCierreCajaCajero.append("detalle_ingresos", $("#detalleIngresosCierre").val());
    datosCierreCajaCajero.append("detalle_egresos", $("#detalleEgresosCierre").val());
    datosCierreCajaCajero.append("apertura_siguiente_monto", $("#aperturaSiguienteMonto").val());
    datosCierreCajaCajero.append("id_usuario_cierre", $("#idUsuarioCierre").val());
    datosCierreCajaCajero.append("detalle", $("#cierreCajaDetalle").val());
    datosCierreCajaCajero.append("detalle_ingresos_manual", $("#totalIngresosCierreManual").val());
    datosCierreCajaCajero.append("detalle_egresos_manual", $("#totalEgresosCierreManual").val());
    datosCierreCajaCajero.append("diferencias", $("#totalDiferenciasCierre").val());
    
    //ingresos por SISTEMA
    let detalleIngresosCierre = JSON.parse($("#detalleIngresosCierre").val());

    //egresos por SISTEMA
    let detalleEgresosCierre = JSON.parse($("#detalleEgresosCierre").val());

    $.ajax({

        url:"ajax/cajas.ajax.php",
        method: "POST",
        data: datosCierreCajaCajero,
        cache: false,
        contentType: false,
        processData: false,
        dataType:"json",
        success:function(respuesta){
            
            var mesajes = "";
            if(respuesta == "ok") {
                swal({
                  title: "Cajas",
                  text: "Cierre de caja guardado correctamente",
                  type: "success",
                  toast: true,
                  position: 'top',
                  showConfirmButton: false,
                  timer: 3000
                });;

                $("#modalAgregarDatosManual").modal('hide');

                let fechaCompleta = $("#ingresoFechaCierreSistema").val().split('-');
                let dia = fechaCompleta[2];
                let mes = fechaCompleta[1];
                let anio = fechaCompleta[0];
                $("#txtFechaHoraTicket").text("FECHA: " + dia + '/' + mes + '/' + anio);

                //var mediosPago = ["Efectivo", "TR", "MP", "CH", "TD", "TC"];
                var mediosPago = [
                        { name:"Efectivo", value:"Efectivo" },
                        { name:"TR", value:"Transferencia" },
                        { name:"MP", value:"Mercado Pago" },
                        { name:"CH", value:"Cheque" },
                        { name:"TD", value:"Tarjeta Débito" },
                        { name:"TC", value:"Tarjeta Crédito" },
                    ]
            
                let inicioAnterior = 0;
                let elementoEncontradoI;
                let elementoEncontradoE;
                let elementoEncontradoD;
                let saldoIngEg = 0;

                var arrDif = JSON.parse($("#totalDiferenciasCierre").val());
                
                $("#tckDetalleMovimientosManual").append("<tr><td><b>Medio Pago</b></td><td><b>Recuento</b></td></tr>");
                //saldos en caja MANUAL
                $("#tckDetalleMovimientosManual").append("<tr><td>Efectivo</td><td>"+moneda(Number($("#ingresoEfectivoManual").val()))+"</td></tr>");
                $("#tckDetalleMovimientosManual").append("<tr><td>Transferencia</td><td>"+moneda(Number($("#ingresoTransferenciasManual").val()))+"</td></tr>");
                $("#tckDetalleMovimientosManual").append("<tr><td>Mercado Pago</td><td>"+moneda(Number($("#ingresoMercadoPagoManual").val()))+"</td></tr>");
                $("#tckDetalleMovimientosManual").append("<tr><td>Cheque</td><td>"+moneda(Number($("#ingresoChequeManual").val()))+"</td></tr>");
                $("#tckDetalleMovimientosManual").append("<tr><td>Tarjeta Débito</td><td>"+moneda(Number($("#ingresoDebitoManual").val()))+"</td></tr>");
                $("#tckDetalleMovimientosManual").append("<tr><td>Tarjeta Crédito</td><td>"+moneda(Number($("#ingresoCreditoManual").val()))+"</td></tr>");

                $("#tckDetalleMovimientos").append("<tr><td><b>Medio Pago</b></td><td><b>Ingresos</b></td><td><b>Egresos</b></td><td><b>Saldo</b></td></tr>");
                
                $("#tckDetalleMovimientosDiferencias").append("<tr><td><b>Medio Pago</b></td><td><b>Diferencia</b></td></tr>");
                for(var i = 0; i < mediosPago.length; i++){
                   
                    //SISTEMA
                    if(mediosPago[i]["name"] === "Efectivo") {
                        inicioAnterior = Number($("#saldoInicioCaja").val());
                        $("#tckDetalleMovimientos").append("<tr><td>Saldo inicio (Efectivo)</td><td>"+moneda(Number($("#saldoInicioCaja").val()))+"</td><td></td><td></td></tr>")
                    } else {
                        inicioAnterior = 0;
                    }
                   
                    elementoEncontradoI = detalleIngresosCierre.find(obj => obj.hasOwnProperty(mediosPago[i]["name"]));
                    elEncontradoI = (elementoEncontradoI != undefined) ? Number(elementoEncontradoI[mediosPago[i]["name"]]) : 0;
                    
                    elementoEncontradoE = detalleEgresosCierre.find(obj => obj.hasOwnProperty(mediosPago[i]["name"]));
                    elEncontradoE = (elementoEncontradoE != undefined) ? Number(elementoEncontradoE[mediosPago[i]["name"]]) : 0;
                    
                    saldoIngEg = elEncontradoI - elEncontradoE + inicioAnterior;
                    
                    if(saldoIngEg != 0) {
                        elEncontradoI = moneda(elEncontradoI);
                        elEncontradoE = moneda(elEncontradoE);
                        saldoIngEg = moneda(saldoIngEg);
                        $("#tckDetalleMovimientos").append("<tr><td>"+mediosPago[i]["value"]+"</td><td>"+elEncontradoI+"</td><td>"+elEncontradoE+"</td><td>"+saldoIngEg+"</td></tr>");
                    }
                    
                    //MANUAL
                    
                    
                    //DIFERENCIAS entre SISTEMA y MANUAL
                    elementoEncontradoD = arrDif.find(obj => obj.hasOwnProperty(mediosPago[i]["name"]));
                    elEncontradoD = (elementoEncontradoD != undefined) ? Number(elementoEncontradoD[mediosPago[i]["name"]]) : 0;
                    if(elEncontradoD !== 0) {
                        $("#tckDetalleMovimientosDiferencias").append("<tr><td>"+mediosPago[i]["value"]+"</td><td>"+moneda(Number(elEncontradoD))+"</td></tr>");
                    }
                    
                    
                }
                
                $("#modalImprimirCierre").modal("show");
                            
            } else {

                swal({
                  title: "Cajas",
                  text: "Error al procesar el cierre de caja",
                  type: "error",
                  toast: true,
                  position: 'top',
                  showConfirmButton: false,
                  timer: 3000
                });

            }
        },

        error: function(xhr, status, error) {
          
            console.log( xhr.responseText);
            console.log( xhr);
            console.log( status);
            console.log( error);

            swal({
                  title: "Cajas",
                  text: "Error (500 - descripcion en consola)",
                  type: "error",
                  toast: true,
                  position: 'top',
                  showConfirmButton: false,
                  timer: 3000
                });
        }
    });

});

//boton para imprimir ticket
$("#btnImprimirTicketUsuario").click(function(){
    impTicketCaja2("impTicketCierreCaja");
});

$("#btnSalirTicketUsuario").click(function(){
    window.location = 'cajas-cajero';
});

function impTicketCaja2(el){
    var mywindow = window.open('', 'PRINT', 'height=400,width=600');
    mywindow.document.write('<html><head>');
    mywindow.document.write('<style>'+
        '.tabla{' +
            'width:100%;' +
            'border-collapse:collapse;' +
            'margin:16px 0 16px 0;}' +
        '.tabla th{'+
            'border:1px solid #ddd;'+
            'padding:4px;'+
            'background-color:#d4eefd;'+
            'text-align:left;'+
            'font-size:20px;}'+
        '.tabla td{'+
            'border:1px solid #ddd;'+
            'text-align:left;'+
            'padding:6px;}'+
            '</style>');
    mywindow.document.write('</head><body style="font-family: Arial; font-size: 20px">');
    mywindow.document.write(document.getElementById(el).innerHTML);
    mywindow.document.write('</body></html>');

    mywindow.print();
    mywindow.close();
    return true;
}

function moneda(x) {
  return x.toLocaleString('es-CL');
}

</script>