<?php

    $item = 'id';
  
    $valor = $_GET["id_proveedor"];

    $proveedor = ControladorProveedores::ctrMostrarProveedores($item, $valor);

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

            <p><b>II.BB.</b>: <?php echo $proveedor["ingresos_brutos"]; ?>- <b>Inicio Act.</b>: <?php echo $proveedor["inicio_actividades"]; ?></p>
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
  
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarMovimiento">
          
          Agregar movimiento

        </button>

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
MODAL INGRESAR MOVIMIENTO
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

            <!-- ENTRADA PARA USUARIO QUE REALIZA OPERACION  -->
            <input type="hidden" name="idUsuarioMovimientoCtaCteProveedor" value="<?php echo $_SESSION["id"]; ?>">

            <!-- ENTRADA PARA PROVEEDOR -->
            <input type="hidden" name="idProveedorMovimientoCtaCteProveedor" value="<?php echo $proveedor["id"]; ?>">

            <!-- ENTRADA PARA NOMBRE Proveedor --->
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-user"></i></span> 

                <input type="text" class="form-control" name="ingresoCajaProveedor" value="<?php echo $proveedor["nombre"];?>" id="ingresoCajaProveedor" readonly> 

              </div>

            </div>

            <!-- ENTRADA PARA TIPO MOVIMIENTO -->
            <div class="form-group">
              
              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-code"></i></span> 

                <select class="form-control" name="tipoMovimientoCtaCteProveedor" id="tipoMovimientoCtaCteProveedor">

                  <option value="1">Débitos</option> 
                  <option value="0">Pagos</option>

                </select> 
              </div> 

            </div>

            <!--PUNTO VENTA / COBRO -->
            <div class="form-group ctacteProveedorCaja" style="display: none">

              <div class="input-group">

                <span title="Puntos de venta" class="input-group-addon"><i class="fa fa-terminal"></i></span>
                <?php

                  $arrPuntos = json_decode($arrayEmpresa['ptos_venta'], true);
                  $arrPuntosHabilitados = explode(',', $_SESSION['puntos_venta']);

                  echo '<select title="Seleccione el punto de venta" class="form-control input-sm" id="nuevaPtoVta" name="puntoVentaMovimientoCtaCteProveedor">';
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

            <div class="form-group ctacteProveedorCaja" style="display: none">

              <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span> 

                <select class="form-control" id="nuevoMetodoPagoCtaCteProveedor" name="nuevoMetodoPagoCtaCteProveedor">
                  <option value="">Medio de pago</option>
                  <option value="Efectivo">Efectivo</option>
                  <!-- <option value="TD">Tarjeta Débito</option>     
                  <option value="TC">Tarjeta Crédito</option> -->
                  <option value="CH">Cheque</option>
                  <option value="TR">Transferencia</option>
                  <option value="BO">Bonificación</option>

                </select>
              </div>

              <input type="hidden" id="metodoPagoCtaCteProveedor" name="ingresoMedioPagoCtaCteProveedor">

            </div>

            <div class="form-group row">
              <div class="cajasMetodoPagoCtaCteProveedor"></div>
            </div>

            <!-- ENTRADA PARA DESCRIPCION -->
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 

                <input type="text" class="form-control" name="detalleMovimientoCtaCteProveedor" id="detalleMovimientoCtaCteProveedor" placeholder="Ingrese descripcion"> 

              </div>

            </div>

            <!-- ENTRADA PARA MONTO -->            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-usd"></i></span> 

                <input type="number" min="0" step="0.01" class="form-control input-lg" style="text-align: center; font-size: 20px; font-weight:bold" name="montoMovimientoCtaCteProveedor" id="montoMovimientoCtaCteProveedor" placeholder="Ingrese monto" >

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

        $movimiento = new ControladorProveedoresCtaCte();
        $movimiento -> ctrCrearRegistroProveedores();

      ?>

    </div>

  </div>

</div>

<?php

  $eliminarMovimiento = new ControladorProveedoresCtaCte();
  $eliminarMovimiento -> ctrEliminarCtaCteProveedores();

?>