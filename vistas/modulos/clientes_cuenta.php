<style>
  /* ============================
     Estilos modernos para cuenta corriente cliente
     ============================ */

  .ccc-box {
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #e0e0e0;
  }

  .ccc-box-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 1px solid #e0e0e0;
    padding: 15px 20px;
    border-radius: 12px 12px 0 0;
  }

  .ccc-box-body {
    padding: 25px;
  }

  /* Card de información del cliente con gradiente */
  .ccc-card-cliente {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 25px;
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
  }

  .ccc-card-cliente::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    pointer-events: none;
  }

  .ccc-card-cliente-icon {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 80px;
    opacity: 0.2;
  }

  .ccc-card-cliente-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .ccc-card-cliente-info {
    font-size: 14px;
    line-height: 1.8;
    margin-bottom: 8px;
  }

  .ccc-card-cliente-info b {
    font-weight: 600;
    opacity: 0.9;
  }

  /* Card de saldo total */
  .ccc-card-saldo {
    background: linear-gradient(135deg, #1abc9c 0%, #16a085 100%);
    border-radius: 12px;
    padding: 25px;
    color: white;
    box-shadow: 0 4px 15px rgba(26, 188, 156, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .ccc-card-saldo.negativo {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
  }

  .ccc-card-saldo:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(26, 188, 156, 0.4);
  }

  .ccc-card-saldo.negativo:hover {
    box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
  }

  .ccc-card-saldo-icon {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 60px;
    opacity: 0.3;
  }

  .ccc-card-saldo-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.9;
  }

  .ccc-card-saldo-value {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 5px;
  }

  /* Botones modernos */
  .ccc-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
    color: white;
  }

  .ccc-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
  }

  /* Tabla responsive */
  .ccc-table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    width: 100%;
    margin-top: 20px;
  }

  .tablasBotonesCtaCteCliente2 {
    width: 100% !important;
    min-width: 800px;
  }

  .tablasBotonesCtaCteCliente2 thead tr {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }

  .tablasBotonesCtaCteCliente2 thead tr th {
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    border: none;
    padding: 12px 8px;
    white-space: nowrap;
  }

  .tablasBotonesCtaCteCliente2 tbody tr {
    transition: background-color 0.2s ease;
  }

  .tablasBotonesCtaCteCliente2 tbody tr:hover {
    background-color: #f8f9fa;
  }

  .tablasBotonesCtaCteCliente2 tbody td {
    vertical-align: middle;
    padding: 12px 8px;
  }

  /* Mejorar buscador */
  .tablasBotonesCtaCteCliente2_filter {
    margin-bottom: 20px !important;
  }

  .tablasBotonesCtaCteCliente2_filter label {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    font-weight: 600 !important;
    color: #2c3e50 !important;
  }

  .tablasBotonesCtaCteCliente2_filter input {
    border: 2px solid #e0e0e0 !important;
    border-radius: 8px !important;
    padding: 10px 15px !important;
    font-size: 14px !important;
    transition: all 0.3s ease !important;
    width: 300px !important;
    max-width: 100% !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
  }

  .tablasBotonesCtaCteCliente2_filter input:focus {
    border-color: #667eea !important;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2) !important;
    outline: none !important;
  }

  /* Responsive para móviles */
  @media (max-width: 768px) {
    .ccc-box-body {
      padding: 15px;
    }

    .ccc-table-wrapper {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    .tablasBotonesCtaCteCliente2 {
      min-width: 900px;
    }

    .ccc-card-cliente,
    .ccc-card-saldo {
      padding: 20px;
    }

    .ccc-card-cliente-icon {
      font-size: 50px;
    }

    .ccc-card-saldo-icon {
      font-size: 40px;
    }
  }
</style>

<?php

    $item = 'id';
    $valor = $_GET["id_cliente"];

    $cliente = ControladorClientes::ctrMostrarClientes($item, $valor);

    // Calcular saldo total del cliente
    $respuesta = ControladorClientesCtaCte::ctrMostrarCtaCteCliente("id_cliente", $valor);
    $saldoTotalCliente = 0;
    
    foreach ($respuesta as $key => $value) {
      if($value["tipo"] == 0) {
        $saldoTotalCliente = $saldoTotalCliente - $value["importe"];
      } elseif ($value["tipo"] == 1) {
        $saldoTotalCliente = $saldoTotalCliente + $value["importe"];
      }
    }

    $esSaldoNegativo = $saldoTotalCliente < 0;

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

    <!-- Card de información del cliente -->
    <div class="row">
      <div class="col-md-8 col-sm-12">
        <div class="ccc-card-cliente">
          <div class="ccc-card-cliente-icon">
            <i class="fa fa-user-circle"></i>
          </div>
          <div class="ccc-card-cliente-title">
            <i class="fa fa-address-card-o"></i> Información del Cliente
          </div>
          <div class="ccc-card-cliente-info">
            <b>Domicilio:</b> <?php echo $cliente["direccion"] ?? 'N/A'; ?>
          </div>
          <div class="ccc-card-cliente-info">
            <b>Email:</b> <?php echo $cliente["email"] ?? 'N/A'; ?> - <b>Teléfono:</b> <?php echo $cliente["telefono"] ?? 'N/A'; ?>
          </div>
          <?php if(!empty($cliente["observaciones"])): ?>
          <div class="ccc-card-cliente-info">
            <b>Observaciones:</b> <?php echo $cliente["observaciones"]; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-md-4 col-sm-12">
        <div class="ccc-card-saldo <?php echo $esSaldoNegativo ? 'negativo' : ''; ?>">
          <div class="ccc-card-saldo-icon">
            <i class="fa fa-usd"></i>
          </div>
          <div class="ccc-card-saldo-title">Saldo Total</div>
          <div class="ccc-card-saldo-value">
            $<?php echo number_format($saldoTotalCliente, 2, ',', '.'); ?>
          </div>
        </div>
      </div>
    </div>

    <div class="box ccc-box">

      <div class="box-header with-border ccc-box-header">
        <a href="#" data-toggle="modal" data-target="#modalAgregarMovimiento" data-dismiss="modal">
          <button class="btn ccc-btn-primary">
            <i class="fa fa-plus"></i> Agregar movimiento
          </button>
        </a>
      </div>

      <div class="box-body ccc-box-body">
        
       <div class="ccc-table-wrapper">
       <table class="table table-bordered table-striped dt-responsive tablasBotonesCtaCteCliente2" width="100%">
         
        <thead>
         
         <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
           
           <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Fecha</th>
           <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Descripción</th>
           <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">$ Venta/ND</th>
           <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">$ Pago/NC</th>
           <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">$ Saldo</th>           
           <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Acciones</th>

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

              echo '<td class="text-center">
                    <div class="acciones-tabla">';

                  if($value["tipo"]==1)    {
                    if(isset($value["numero_recibo"])) {
                      echo '<a class="btn-accion" title="Imprimir recibo" href="recibo/'.$value["id"].'" target="_blank"><i class="fa fa-print"></i></a>';
                    }
                  } else {
                    if(isset($value["id_venta"])) {
                      echo '<a class="btn-accion" title="Imprimir comprobante" href="comprobante/'.$venta["codigo"].'" target="_blank"><i class="fa fa-print"></i></a>';
                    }
                  }

                  echo '</div>
                  </td>

              </tr>';
          }

        ?>
               
        </tbody>

       </table>
       </div>

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

        <!--CABEZA DEL MODAL-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Movimientos Cta. Cte</h4>
        </div>

        <!--CUERPO DEL MODAL-->
        <div class="modal-body">
          <div class="box-body">

            <!-- ENTRADA PARA USUARIO QUE REALIZA OPERACION  -->
            <input type="hidden" name="idUsuarioMovimientoCtaCteCliente" value="<?php echo $_SESSION["id"]; ?>">

            <!-- ENTRADA PARA CLIENTE  -->
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

            <!-- ENTRADA PARA TIPO MOV (DEB/CRE) -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-code"></i></span> 
                <select class="form-control" name="tipoMovimientoCtaCteCliente" id="tipoMovimientoCtaCteCliente">
                  <option value="0">Débito</option>
                  <option value="1">Crédito/Cobro</option>
                </select> 
              </div> 
            </div>

            <!-- ENTRADA PARA MONTO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                <input type="number" min="0" step="0.01" class="form-control input-lg" style="text-align: center; font-size: 20px; font-weight:bold" id="montoMovimientoCtaCteCliente" name="montoMovimientoCtaCteCliente" placeholder="Ingrese monto" >
              </div>
            </div>


            <!-- ENTRADA PARA MEDIO PAGO -->
            <div class="form-group ctacteClienteCaja" style="display: none">

              <!--<div class="input-group">
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
              </div>-->
              
              <div class="input-group">
                  <span title="Agregar medio de pago" class="input-group-btn"><button id="agregarMedioPagoCC" type="button" class="btn btn-success" ><i class="fa fa-plus"></i></button></span>
                  <select class="form-control" id="nuevoMetodoPagoCtaCteCliente" name="nuevoMetodoPagoCtaCteCliente">
                    
                    <option value="Efectivo" selected>Efectivo</option>
                    <option value="MP-" >Mercado Pago</option>
                    <option value="TD-">Tarjeta Débito</option>     
                    <option value="TC-">Tarjeta Crédito</option>
                    <option value="CH--">Cheque</option>
                    <option value="TR--">Transferencia</option>
                    <option value="BO">Bonificación</option>
                  </select>    
              </div>
              
              <div class="cajasMetodoPagoCajaCC"></div>
              
              <div class="row" style="display: none;" id="divImportesPagoMixtoCC">
                <table class="table" id="listadoMetodosPagoMixtoCC" cantidadFilas="0">
                  <thead>
                    <tr>
                      <th><i class="fa fa-minus-square"></i> </th>
                    <th>Metodo</th>
                    <th>Importe</th>
                  </tr>
                </thead>
                
                <tbody>
                </tbody>
                
                <tfoot>
                  <tr>
                    <td></td>
                    <td></td>
                    <td style="font-size: 18px">
                      <b>TOTAL: $</b> <span id="nuevoValorSaldoCC" style="color:green">0</span>
                      <input type="hidden" id="nuevoValorSaldoCCPost" >
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>

              <input type="hidden" id="mxMediosPagos" name="ingresoMedioPagoCtaCteCliente">
            </div>

            <div class="form-group row">
              <div class="cajasMetodoPagoCtaCteCliente"></div>
            </div>

            <!-- ENTRADA PARA DESCRIPCION -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <input type="text" autocomplete="off" class="form-control" name="detalleMovimientoCtaCteCliente" id="detalleMovimientoCtaCteCliente" placeholder="Ingrese descripcion"> 
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
        $ctacte = new ControladorClientesCtaCte();
        $ctacte -> ctrIngresarCtaCte();
      ?>
    </div>
  </div>
</div>