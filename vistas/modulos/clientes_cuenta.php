<?php

    $item = 'id';
    $valor = $_GET["id_cliente"];

    $cliente = ControladorClientes::ctrMostrarClientes($item, $valor);

?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Cuenta Corriente cliente - <span id="spanNombreClienteCtaCte"><?php echo $cliente["nombre"]; ?></span> - <?php echo $cliente["documento"]; ?>
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Cuenta Corriente cliente</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="row">


      <div class="col-lg-12 col-xs-12">

        <div class="small-box bg-blue">

          <div class="inner">

            <p><b>Domicilio</b>: <?php echo $cliente["direccion"]; ?> 
            <p><b>Email</b>: <?php echo $cliente["email"]; ?> - <b>Telefono</b>: <?php echo $cliente["telefono"]; ?></p>
            <p><b>Observaciones</b>: <?php echo $cliente["observaciones"]; ?> </p>

          </div>

          <div class="icon">

            <i class="fa fa-address-card-o"></i>

          </div>

        </div>

      </div>

    </div>

    <div class="box">

      <div class="box-header with-border">

        <a href="#" data-toggle="modal" data-target="#modalAgregarMovimiento" data-dismiss="modal">

          <button class="btn btn-primary">Agregar movimiento</button>

        </a>

      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive tablasBotonesCtaCteCliente2" width="100%">
         
        <thead>
         
         <tr>
           
           <th>Fecha</th>
           <th>Descripcion</th>
           <th>$ Venta/ND</th>
           <th>$ Pago/NC</th>
           <th>$ Saldo</th>           
           <th>Acciones</th>

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
      CUENTA CORRIENTE CLIENTES - toda compra se carga como debe - haber
        Tipos: 
        0 - VENTA 
        1 - ENTREGA INICIAL / UN SOLO PAGO
        2 - CUOTAS
        3 - ENTREGA A CUENTA ?

      *************************************/

          $respuesta = ControladorClientesCtaCte::ctrMostrarCtaCteCliente("id_cliente", $valor);

          $saldoCliente = 0;

          foreach ($respuesta as $key => $value) {

            $venta = ModeloVentas::mdlMostrarVentas('ventas', 'id', $value["id_venta"]);

            echo '<tr>

                  <td style="text-align: center">'.date('Y-m-d', strtotime($value["fecha"])).'</td>';
              
                  if(isset($value["id_venta"])) {
                    echo '<td><a href="index.php?ruta=editar-venta&idVenta='.$value["id_venta"].'">'.$value["descripcion"].'</a></td>';
                  } else {
                    echo '<td>'.$value["descripcion"].'</td>';  
                  }
                  

                  if($value["tipo"] == 0) {

                    echo '<td>$ '. number_format($value["importe"], 2, ',', '.') .'</td>';
                    echo '<td></td>';
                    $saldoCliente = $saldoCliente - $value["importe"];

                  } elseif ($value["tipo"] == 1) {

                    echo '<td></td>';
                    echo '<td>$ '. number_format($value["importe"], 2, ',', '.') .'</td>';
                    $saldoCliente = $saldoCliente + $value["importe"];

                  } 

              echo '<td style="text-align: center">$ '.number_format($saldoCliente, 2, ',', '.').'</td>';

              echo '<td style="text-align: center">

                    <div class="btn-group">';

                  if($value["tipo"]==1)    {

                    if(isset($value["numero_recibo"])) {
                      echo '<a class="btn btn-success" href="recibo/'.$value["id"].'"  target="_blank"> <i class="fa fa-print"></i></a>';
                    }

                  } else {

                    if(isset($value["id_venta"])) {

                      echo '<a class="btn btn-primary" href="comprobante/'.$venta["codigo"].'"  target="_blank"> <i class="fa fa-print"></i></a>';

                    }

                  }

                  echo '</div>

                  </td>

              </tr>';
          }

        ?>
               
        </tbody>

       </table>

       <?php

      // $eliminarCaja = new ControladorCajas();
      // $eliminarCaja -> ctrEliminarCaja();

      ?>

      </div>

    </div>

  </section>

</div>

<!--=====================================
MODAL AGREGAR MOVIMIENTO
======================================-->
<div id="modalAgregarMovimiento" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Movimientos Cta. Cte</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA MEDIO PAGO  -->
            <!-- <input type="hidden" name="ingresoMedioPago" id="ingresoMedioPago"> -->

            <!-- ENTRADA PARA USUARIO QUE REALIZA OPERACION  -->
            <input type="hidden" name="idUsuarioMovimientoCtaCteCliente" value="<?php echo $_SESSION["id"]; ?>">

            <!-- ENTRADA PARA USUARIO QUE REALIZA OPERACION  -->
            <input type="hidden" name="idClienteMovimientoCtaCteCliente" value="<?php echo $cliente["id"] ?>">

            <!--PUNTO VENTA / COBRO -->
            <div class="form-group">

              <div class="input-group">

                <span title="Puntos de venta" class="input-group-addon"><i class="fa fa-terminal"></i></span>
                <?php

                //$arrPuntos = explode(',', $arrayEmpresa['ptos_venta']);
                $arrPuntos = json_decode($arrayEmpresa['ptos_venta'], true);
                $arrPuntosHabilitados = explode(',', $_SESSION['puntos_venta']);

                echo '<select title="Seleccione el punto de venta" class="form-control input-sm" id="nuevaPtoVta" name="puntoVentaMovimientoCtaCteCliente">';
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

            <!-- ENTRADA PARA CODDIGO VENTA -->
            <div class="form-group">
              
              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-code"></i></span> 

                <select class="form-control" name="tipoMovimientoCtaCteCliente" id="tipoMovimientoCtaCteCliente">

                  <option value="0">Débito</option>
                  <option value="1">Crédito/Cobro</option>

                </select> 
              </div> 

            </div>

            <div class="form-group ctacteClienteCaja" style="display: none">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span> 

                <select class="form-control" id="nuevoMetodoPagoCtaCteCliente" name="nuevoMetodoPagoCtaCteCliente">
                  <option value="">Medio de pago</option>
                  <option value="Efectivo">Efectivo</option>
                  <option value="TD">Tarjeta Débito</option>     
                  <option value="TC">Tarjeta Crédito</option>
                  <option value="CH">Cheque</option>
                  <option value="TR">Transferencia</option>
                  <option value="BO">Bonificación</option>

                </select>
              </div>

              <input type="hidden" id="metodoPagoCtaCteCliente" name="ingresoMedioPagoCtaCteCliente">

            </div>

            <div class="form-group row">
              <div class="cajasMetodoPagoCtaCteCliente"></div>
            </div>

           <!-- ENTRADA PARA CAJA DESTINO -->
           <!--  <div class="form-group" id="ctacteClienteCaja" style="display: none">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-th"></i></span>  -->

                  <?php 
                  // if($_SESSION["perfil"] == "Administrador") {
                  
                  //   echo '<select class="form-control" name="cajaCentralDesde" >';
                  //       echo '<option value="">Seleccione caja</option>';
                  //       echo '<option value="1">General</option>';
                  //       echo '<option value="2">Autorradio</option>';
                  //       echo '<option value="3">Domiciliario</option>';
                  //   echo '</select>';
                  //   } else {

                  //     echo '<input type="text" class="form-control" value="Caja '.$_SESSION["perfil"].'" readonly>';
                  //     echo '<input type="hidden" name="cajaCentralDesde" value="'.$cajaDestino.'" >';
                  //   }
                    ?>
             <!--   </div>

            </div> -->

            <!-- ENTRADA PARA DESCRIPCION -->
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 

                <input type="text" autocomplete="off" class="form-control" name="detalleMovimientoCtaCteCliente" id="detalleMovimientoCtaCteCliente" placeholder="Ingrese descripcion"> 

              </div>

            </div>

            <!-- ENTRADA PARA MONTO -->            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-usd"></i></span> 

                <input type="number" min="0" step="0.01" class="form-control input-lg" style="text-align: center; font-size: 20px; font-weight:bold" name="montoMovimientoCtaCteCliente" id="montoMovimientoCtaCteCliente" placeholder="Ingrese monto" >

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

        $ctacte = new ControladorClientesCtaCte();
        $ctacte -> ctrIngresarCtaCte();

      ?>

    </div>

  </div>

</div>