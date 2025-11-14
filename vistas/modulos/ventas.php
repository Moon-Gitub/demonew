<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Administrar ventas
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar ventas</li>
    </ol>
  </section>
  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        <a href="crear-venta-caja" class="btn btn-primary">Agregar venta</a>
        <a href="libro-iva-ventas" class="btn btn-primary">IVA Ventas</a>
         <button type="button" class="btn btn-default pull-right" id="daterange-btn">
            <span>
              <i class="fa fa-calendar"></i> 

              <?php

                if(isset($_GET["fechaInicial"])){

                  echo $_GET["fechaInicial"]." - ".$_GET["fechaFinal"];
                
                }else{
                 
                  echo 'Hoy';

                }

              ?>
            </span>

            <i class="fa fa-caret-down"></i>

         </button>

      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive" width="100%" id="tablaListarVentas">

        <thead>
         
         <tr>

           <th>Fecha</th>
           <th>Nro. Int.</th>
           <th>Sucursal</th>
           <th>Cbte.</th>
           <th>Cliente</th>
           <th>Medio pago</th>
           <th>Estado</th>
           <th>Total</th> 
           <th>Acciones</th>

         </tr> 

        </thead>

        <tfoot>

          <tr>
            <th></th>
            <th>Nro. Int.</th>
            <th>Sucursal</th>
            <th>Cbte.</th>
            <th>Cliente</th>
            <th>Medio pago</th>
            <th></th>
            <th></th>
            <th></th>
          </tr>

        </tfoot>

        <tbody>

        <?php

          date_default_timezone_set('America/Argentina/Mendoza');

          if(isset($_GET["fechaInicial"])){

            $fechaInicial = $_GET["fechaInicial"];
            $fechaFinal = $_GET["fechaFinal"];

          }else{

            $hoy = date('Y-m-d');

             $fechaInicial = $hoy . ' 00:00';
             $fechaFinal = $hoy . ' 23:59';

          }

          $tiposCbtes = array(
            0 => 'X',
            999 => 'Devolucion',
            1 => 'Factura A',
            6 => 'Factura B', 
            11 => 'Factura C',
            //'Factura E' => 0, 
            51 => 'Factura M',
            2 => 'Nota Débito A',
            7 => 'Nota Débito B',
            12 => 'Nota Débito C',
            //'Nota Débito E' => 0, 
            52 => 'Nota Débito M',
            3 => 'Nota Crédito A',
            8 => 'Nota Crédito B',
            13 => 'Nota Crédito C',
            //'Nota Crédito E' => 0,
            53 => 'Nota Crédito M',
            4 => 'Recibo A',
            9 => 'Recibo B',
            15 => 'Recibo C',
            //'Recibo E' => 0, 
            54 => 'Recibo M',
            '' => 'no definido'
          );

          $arrPuntos = json_decode($arrayEmpresa['ptos_venta'], true);

          $respuestaVta = ControladorVentas::ctrRangoFechasVentas($fechaInicial, $fechaFinal);

          foreach ($respuestaVta as $key => $value) {

            $facturada = ControladorVentas::ctrVentaFacturadaDatos($value['id']);

            if($facturada) {

              $deshAutorizarA = 'pointer-events: none; opacity: 0.4';
              $deshAutorizarSpan = 'cursor:not-allowed; ';

              $href= '#';
              $imgAut='<i class="fa fa-check" style="color: green;"></i>';
              $ptoVta = str_pad($value["pto_vta"], 5, "0", STR_PAD_LEFT);
              $numCte = str_pad($facturada["nro_cbte"], 8, "0", STR_PAD_LEFT);

              $numFact = $ptoVta . '-' . $numCte;

            } else {

              $deshAutorizarA = '';
              $deshAutorizarSpan = '';

              $href= 'index.php?ruta=ventas&idven='.$value["id"];
              if(strlen(isset($value["observaciones"])) > 1){
                $imgAut='<i class="fa fa-exclamation-triangle" style="color: #f39c12;"></i>';
              } else{
                if($value["cbte_tipo"] == 0 || $value["cbte_tipo"] == 999 ) {
                  $imgAut = '';
                } else {
                  $imgAut='<i class="fa fa-times" style="color: red;"></i>';
                }
              }
              // $imgAut='<i class="fa fa-times" style="color: red;"></i>';
              // $ptoVta = str_pad($value["pto_vta"], 5, "0", STR_PAD_LEFT);
              // $numCte = str_pad($value["codigo"], 8, "0", STR_PAD_LEFT);
              $numFact = "";

            }

            $signoVta = ($value["cbte_tipo"] == 3 || $value["cbte_tipo"] == 8 || $value["cbte_tipo"] == 13 || $value["cbte_tipo"] == 999 || $value["cbte_tipo"] == 203 || $value["cbte_tipo"] == 208 || $value["cbte_tipo"] == 213) ? '-' : ''; //es nota credito o devolucion
            $tpCbte = $imgAut . ' ' . $tiposCbtes[$value["cbte_tipo"]] ;

            $lblEstado = '';
            $botonCobro='pointer-events: none;';
            $btnCobroLi = 'cursor:not-allowed;';

            //Estado pagada - adeudada - cta. cte.
            if($signoVta == ''){
            
              if($value["estado"] == 0) { //Adeudada

                $lblEstado = '<span style="cursor: pointer" class="label label-danger btnCobrarVenta" data-toggle="modal" data-target="#modalCobrarVenta" data-dismiss="modal" idVenta="'.$value["id"].'">Adeudado</span>' ;
                $botonCobro = 'cursor: pointer;';
                $btnCobroLi = '';

              } elseif($value["estado"] == 1) { //Pagada

                $lblEstado='<span class="label label-success">Pagado</span>';
                $botonCobro='pointer-events: none;';
                $btnCobroLi = 'cursor:not-allowed;';

              } elseif ($value["estado"] == 2) {

                $lblEstado='<span class="label label-warning">Cta. Cte.</span>';
                $botonCobro='pointer-events: none;';
                $btnCobroLi = 'cursor:not-allowed;';
               
              }
            } else {
              $deshAutorizarA = 'pointer-events: none; opacity: 0.4';
              $deshAutorizarSpan = 'cursor:not-allowed; ';
            }
            
            //$botonMail = ($value["id_cliente"] == 1) ? 'pointer-events: none;' : 'cursor: pointer;' ;
            //$botonMailLi = ($value["id_cliente"] == 1) ? 'cursor:not-allowed;' : '' ;

             echo '<tr>

                    <td>'.$value["fecha"].'</td>';

              echo '<td><a href="index.php?ruta=editar-venta&idVenta='.$value["id"].'">' . $value["codigo"] . '</a></td>';

              $buscoPto = array_search($value["pto_vta"], array_column( $arrPuntos, 'pto'));

              echo '<td>' .  $arrPuntos[$buscoPto]["det"] . '</td>';
              //echo '<td>' .  $arrPuntos[$value["pto_vta"]] . '</td>';

              echo '<td><center>' . $tpCbte .'<br>'. $numFact. '<c/enter></td>';

              $itemCliente = "id";
              $valorCliente = $value["id_cliente"];

              $respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

              if($value["id_cliente"] == 1){
                echo '<td>'.$respuestaCliente["nombre"].'</td>';
              } else {
                echo '<td><a href="index.php?ruta=clientes_cuenta&id_cliente='.$value["id_cliente"].'">'.$respuestaCliente["nombre"].'</a></td>';
              }

              $arrMetodoPago = json_decode($value["metodo_pago"]);
              $metPago ="";// (count($arrMetodoPago) > 1) ? 'Mixto' : $arrMetodoPago[0]->tipo;

              for ($i=0; $i < count($arrMetodoPago); $i++) { 
                $metPago .= $arrMetodoPago[$i]->tipo . '<br>';
              }

              echo '<td>'.$metPago.'</td>';

              echo '<td style="text-align: center">'.$lblEstado.'</td>

              <td>'. $signoVta . round($value["total"],2).'</td>

              <td>
              
              <center>

                <div class="btn-group">
                      <span style="'.$deshAutorizarSpan.'"><a class="btn btn-primary btnAutorizarCbte" style="' . $deshAutorizarA . '" title="Autorizar comprobante." data-toggle="modal" data-target="#modalAutorizarComprobante" data-dismiss="modal" idVenta="'.$value["id"].'"><i class="fa fa-exchange fa-fw"></i> </a><span>
                      <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
                        <span class="fa fa-caret-down" title="Toggle dropdown menu"></span>
                      </a>
                      <ul class="dropdown-menu" style="background-color: #f4f4f4">
                        <li style="'.$btnCobroLi.'"><a title="Cobrar venta" style="' .$botonCobro. '" class="btnCobrarVenta" data-toggle="modal" data-target="#modalCobrarVenta" data-dismiss="modal" idVenta="'.$value["id"].'"><i class="fa fa-usd fa-fw"></i> Cobrar</a></li>';
                      echo '<li><a title="Ver" style="cursor: pointer;" class="btnEditarVenta" idVenta="'.$value["id"].'"><i class="fa fa-pencil fa-fw"></i> Ver</a></li>';
                      
                      echo '<li role="separator" class="divider"></li>';                        

                      echo '<li><a class="btnDescargarFactura" style="cursor: pointer;" codigoVenta="'.$value["codigo"].'"><i class="fa fa-download fa-fw"></i> Descargar</a></li>';
                      
                      echo '<li role="separator" class="divider"></li>';                        

                      echo '<li><a class="btnImprimirFactura" style="cursor: pointer;" codigoVenta="'.$value["codigo"].'"><i class="fa fa-print fa-fw"></i> Imprimir</a></li>';
                      echo '<li><a class="btnImprimirRemito" style="cursor: pointer;" codigoVenta="'.$value["codigo"].'"><i class="fa fa-cubes fa-fw"></i> Remito</a></li>';
                      echo '<li><a class="btnImprimirTicket" style="cursor: pointer;" idVenta="'.$value["id"].'" data-toggle="modal" data-target="#modalImprimirTicketCajaVenta" data-dismiss="modal"><i class="fa fa-ticket fa-fw"></i> Ticket</a></li>';
                     
                     echo '<li role="separator" class="divider"></li>';
                     //echo '<li style="'.$botonMailLi.'"><a class="btnMailComprobante" style="'.$botonMail.'" codigoVenta="'.$value["codigo"].'" mailCliente="'.$respuestaCliente["email"].'"><i class="fa fa-envelope fa-fw"></i> Email</a></li>';
                     echo '<li><a class="btnMailComprobante" codigoVenta="'.$value["codigo"].'" mailCliente="'.$respuestaCliente["email"].'"><i class="fa fa-envelope fa-fw"></i> Email</a></li>';

                     if($_SESSION["perfil"] == "Administrador"){

                        echo '<li role="separator" class="divider"></li>';

                        //if($value["estado"] == 1 || $facturada) {
                        if($facturada) {

                          echo '<li><a style="cursor: not-allowed;" ><i class="fa fa-times fa-fw"></i> Eliminar</a></li>';

                        } else {

                          echo '<li><a class="btnEliminarVenta" style="cursor: pointer;" idVenta="'.$value["id"].'"><i class="fa fa-times fa-fw"></i> Eliminar</a></li>';

                        }

                    }
                        
                     echo '</ul>

                    </div>

                    </center>

                </td>

              </tr>';
            }

        ?>
               
        </tbody>

       </table>

       <?php

        $eliminarVenta = new ControladorVentas();
        $eliminarVenta -> ctrEliminarVenta();

       ?>

      </div>

    </div>

  </section>

</div>


<!--=====================================
MODAL COBRAR VENTA
======================================-->
<div id="modalCobrarVenta" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Cobro de venta</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA ID VENTA  -->
            <input type="hidden" name="ingresoCajaDesde" value="ventas">

            <!-- ENTRADA PARA ID VENTA  -->
            <input type="hidden" name="ingresoCajaidVenta" id="ingresoCajaidVenta">

            <!-- ENTRADA PARA MEDIO PAGO  -->
            <input type="hidden" name="ingresoMedioPago" id="ingresoMedioPago">

            <!-- ENTRADA PARA TIPO (INGRESO / EGRESO)-  -->
            <input type="hidden" name="ingresoCajaTipo" id="ingresoCajaTipo" value="1">

            <!-- ENTRADA PARA USUARIO QUE REALIZA OPERACION  -->
            <input type="hidden" name="idUsuarioMovimiento" value="<?php echo $_SESSION["id"]; ?>">

            <!-- ENTRADA PARA PUNTO VENTA -->
            <div class="form-group">
              
              Punto de Venta.:
              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-code"></i></span> 

                <input type="text" class="form-control" name="puntoVentaMovimiento" id="ingresoCajaPuntoVenta" readonly>

              </div> 

            </div>

            <!-- ENTRADA PARA CODDIGO VENTA -->
            <div class="form-group">
              
              N° Cbte.:
              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-code"></i></span> 

                <input type="text" class="form-control" name="ingresoCajaCodVenta" id="ingresoCajaCodVenta" readonly>

              </div> 

            </div>

            <!-- ENTRADA PARA METODO PAGO -->
            <div class="form-group">

              Medio pago:
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span> 

                <input type="text" class="form-control" id="ingresoMedioPagoVisual" readonly>

              </div> 

            </div>

            <!-- ENTRADA PARA DESCRIPCION -->
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 

                <input type="text" class="form-control" name="ingresoDetalleCajaCentral" id="ingresoCajaDescripcion" readonly value="Ingresos por venta"> 

              </div>

            </div>

            <!-- ENTRADA PARA MONTO -->            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-usd"></i></span> 

                <input type="number" min="0" step="0.01" class="form-control input-lg" style="text-align: center; font-size: 20px; font-weight:bold" name="ingresoMontoCajaCentral" id="ingresoCajaMonto" readonly >

              </div>

            </div>

            <!-- ENTRADA PARA OBSERVACIONES -->
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 

                <textarea class="form-control" name="ingresoObservacionesCajaCentral" id="ingresoObservacionesCajaCentral" rows="3"></textarea>

              </div>

            </div>
  
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar</button>

        </div>

      </form>

      <?php

        $cobrar = new ControladorCajas();
        $cobrar -> ctrCrearCaja();

      ?>

    </div>

  </div>

</div>

<!--=====================================
MODAL AUTOTIZAR COMPROBANTE
======================================-->
<div id="modalAutorizarComprobante" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Autorizar Comprobante</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA ID VENTA  -->
            <input type="hidden" name="autorizarCbteIdVenta" id="autorizarCbteIdVenta">

            <div class="row">

              <div class="col-md-4">
                <!-- ENTRADA PARA CODDIGO VENTA -->
                <div class="form-group">
                  
                  Punto Vta N°:
                  <div class="input-group">

                    <span class="input-group-addon"><i class="fa fa-terminal"></i></span> 

                    <input type="text" class="form-control" name="autorizarCbtePtoVta" id="autorizarCbtePtoVta" readonly>

                  </div> 

                </div>
              </div>

              <div class="col-md-4">
                <!-- ENTRADA PARA CODDIGO VENTA -->
                <div class="form-group">
                  
                  Venta N°:
                  <div class="input-group">

                    <span class="input-group-addon"><i class="fa fa-code"></i></span> 

                    <input type="text" class="form-control" name="autorizarCbteCodVenta" id="autorizarCbteCodVenta" readonly>

                  </div> 

                </div>
              </div>
              <div class="col-md-4">
                <!-- ENTRADA PARA CODDIGO VENTA -->
            <div class="form-group">
              
              Fecha:
              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-calendar"></i></span> 

                <input type="text" class="form-control" name="autorizarCbteFecha" id="autorizarCbteFecha" readonly>

              </div> 

            </div>
              </div>
            </div>

            <!-- ENTRADA PARA CLIENTE -->
            <div class="form-group">
              
              Cliente:
              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-user"></i></span> 

                 <input type="text" class="form-control" id="autocompletarClienteCaja" placeholder="1-Consumidor Final" required>
                 <input type="hidden" id="seleccionarCliente" name="autorizarCbteCliente" value="1">

              </div> 

            </div>            

            <!-- ENTRADA PARA TIPO COMPROBANTE -->

            <div class="row">
              
              <div class="col-md-6">
                <div class="form-group">

                  <div class="input-group">

                    <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 

                    <?php

                    $arrCbtes = json_decode($arrayEmpresa['tipos_cbtes']);

                    echo '<select title="Seleccione el tipo de comprobante" class="form-control input-sm" name="autorizarCbteTipoCbte" id="autorizarCbteTipoCbte" required>';
                    echo '<option value="">Seleccione comprobante</option>';
                    echo '<option value="0">X</option>';
                    foreach ($arrCbtes as $key => $value) {

                    echo '<option value="' . $value->codigo . '">' . $value->descripcion . '</option>';

                    }

                    echo '</select>';

                    ?>
                    </select>

                  </div>

                </div>
              </div>
            </div>

            <!-- ENTRADA PARA MONTO -->            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-usd"></i></span> 

                <input type="number" min="0" step="0.01" class="form-control input-lg" style="text-align: center; font-size: 20px; font-weight:bold" name="autorizarCbteMonto" id="autorizarCbteMonto" readonly >

              </div>

            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar</button>

        </div>

      </form>

      <?php

        $facturar = new ControladorVentas();
        $facturar -> ctrAutorizarCbte();

      ?>

    </div>

  </div>

</div>

<!--=====================================
IMPRIMIR TICKET CAJA
======================================-->
<div id="modalImprimirTicketCajaVenta" class="modal fade" role="dialog" style="overflow-y: scroll;">
  <div class="modal-dialog">
    <div class="modal-content">
      <!--CABEZA DEL MODAL-->
      <div class="modal-header" style="background:#3c8dbc; color:white">
        <h4 class="modal-title">Ticket</h4>
      </div>
      <!--CUERPO DEL MODAL-->
      <div class="modal-body">
        <div class="box-body">
    		<div class="alert " id="divEventoObservacionAprobada" style="" role="alert"></div>
            <div id="impTicketCobroCaja" style="font-size: 15px;">
             <br>
             <?php 
                $condIva = array(
                1 => "IVA Responsable Inscripto ",
                2 => "IVA Sujeto Exento ",
                3 => "IVA Responsable no Inscripto ",
                4 => "IVA no Responsable ",
                5 => "Consumidor Final ",
                6 => "Responsable Monotributo ",
                7 => "Sujeto no Categorizado ",
                8 => "Proveedor del Exterior ",
                9 => "Cliente del Exterior ",
                10 => "IVA Liberado – Ley Nº 19.640 ",
                11 => "IVA Responsable Inscripto – Agente de Percepción ",
                12 => "Pequeño Contribuyente Eventual ",
                13 => "Monotributista Social ",
                14 => "Pequeño Contribuyente Eventual Social",
                ''=>"(no definido)"
                );

              echo '<b>'. $arrayEmpresa["razon_social"] . '</b> <br>';
              echo $arrayEmpresa["titular"] . '<br>';
              echo $arrayEmpresa["domicilio"] . '<br>';
              echo 'Localidad: ' . $arrayEmpresa["localidad"] . ' C.P.: ' . $arrayEmpresa["codigo_postal"] . '<br>';
              echo 'CUIT: <span id="cuitEmpresaEmisora">' . $arrayEmpresa["cuit"] . '</span> II.BB.: ' . $arrayEmpresa["numero_iibb"] . '<br>';
              echo 'Cond. I.V.A.: ' . $condIva[$arrayEmpresa["condicion_iva"]] . '<br> ';
              echo 'Defensa del consumidor Mendoza 0800-222-6678 <br>';

              ?>
              <hr>
                <!--FACTURA: DATOS RECEPTOR -->
                <span id="tckDatosFacturaFecha"></span><br>
                <b><span id="tckDatosFacturaTipoCbte"></span></b><br>
                <span id="tckDatosFacturaNumCbte"></span><br>
                <span id="tckDatosFacturaNumDoc"></span> <span id="tckDatosFacturaNombreCliente"></span><br>
             <hr>

                <center><b>Detalle</b></center>
                <table width="100%" id="tckDetalleVentaCaja">
                  <tr>
                    <th width="15%"><center>Cant. * Unit</center></th>
                    <th width="55%"><center>Descrip.</center></th>
                    <th width="30%"><center>Total</center></th>
                  </tr>
                </table>
              <br>
              <div>Subtotal: $ <span id="tckSubtotalVentaCaja"></span></div>
              <div><span id="campoDtoTexto"></span>: $ <span id="tckDescuentoVentaCaja"></span></div>
              <div id="tckDetalleFacturaA"></div>
              <div><b>TOTAL: $ <span id="tckTotalVentaCaja"></span></b></div>
              <div><b>Medio pago: </b><span id="tckMedioPagoVentaCaja"></span></div>
              <br>
              <!-- FACTURA: DATOS CAE - VTOCAE -->
              <div id="tckDatosFacturaCAE" style="display: none; font-size: 15px; font-style: italic;">
                <span id="tckDatosFacturaNumCAE"></span> - <span id="tckDatosFacturaVtoCAE"></span>
                <br>
                <center><div style="padding-top: 10px" id="dibujoCodigoQR"></div></center>
              </div>
              <div style="text-align: center">Controle su ticket antes de retirarse. No se aceptan devoluciones</div> 
            </div>
        </div>
      </div>
      <!--PIE DEL MODAL-->
      <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
        <button type="button" id="btnImprimirTicketControl" class="btn btn-primary"><i class="fa fa-ticket" aria-hidden="true"></i> Ticket</button>
        <button type="button" id="btnImprimirA4Control" class="btn btn-primary"><i class="fa fa-print" aria-hidden="true"></i> A4</button>
        <button type="button" id="btnEnviarMailA4" class="btn btn-primary"><i class="fa fa-envelope" aria-hidden="true"></i> Mail</button>
      </div>
    </div>
  </div>
</div>