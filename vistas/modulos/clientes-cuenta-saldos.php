<?php

  $saldoTotal = ControladorClientesCtaCte::ctrMostrarSaldoTotal();
  $colorBox = ($saldoTotal["saldo"] > 0) ? 'bg-warning' : 'bg-success';

?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar clientes <small><b>Saldo en cuenta corriente</b></small>
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar clientes</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  <div class="row">

      <div class="col-lg-3 col-xs-6">
        <a class="btn btn-primary" href="clientes">
          
          Volver

        </a>
      </div>
      
        <div class="pull-right col-lg-3 col-xs-6">

          <div class="small-box <?php echo $colorBox; ?>">
            
            <div class="inner">
              
              <h3>$<?php echo number_format($saldoTotal["saldo"], 2, ',', '.'); ?></h3>

              <p><b>Saldo total</b></p>
            
            </div>
            
            <div class="icon">
              
              <i class="ion ion-social-usd"></i>
            
            </div>
            
            <!--<a href="clientes-cuenta-saldo" class="small-box-footer">
              
              Más info <i class="fa fa-arrow-circle-right"></i>
            
            </a>-->

          </div>

        </div>
      </div>

      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive tablasBotones" width="100%">
         
        <thead>
         
         <tr>
           
           <!-- <th style="width:10px">#</th> -->
           <th>Nombre</th>
           <th>Telefono</th>
           <th>Mail</th>
           <th>Total ventas</th>
           <th>Total pagos</th>
           <th>Saldo</th>

         </tr> 

        </thead>

        <tbody>

        <?php

          $item = null;
          $valor = null;

          $clientes = ControladorClientesCtaCte::ctrMostrarSaldos();

          foreach ($clientes as $key => $value) {

              $tieneMail = (isset($value["email"]) && $value["email"] != "") ? '<i title="El cliente tiene Email configurado" style="color: green" class="fa fa-check"></i>' : '<i title="El cliente no tiene Email configurado" style="color: red" class="fa fa-times"></i>';

              echo '<tr>

                    <td><a href="index.php?ruta=clientes_cuenta&id_cliente='.$value["id_cliente"].'">'.$value["nombre"].'</a></td>

                    <td>'.$value["telefono"].'</td>

                    <td><center><a class="btnSobreCtaCteCliente" data-toggle="modal" data-target="#modalEnviarMail" idCliente="'.$value["id_cliente"].'" mailCliente="'.$value["email"].'" saldoCliente="'.$value["diferencia"].'"> <i class="fa fa-envelope fa-2x"></i>  ' .$tieneMail. '</a></center></td>';

              echo '<td>'.$value["ventas"].'</td>

                    <td>'.$value["pagos"].'</td>

                    <td>'.$value["diferencia"].'</td>';

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
MODAL ENVIAR MAIL
======================================-->
<div id="modalEnviarMail" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Enviar mail</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL EMAIL -->
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span> 

                <input autocomplete="off" type="email" class="form-control " id="emailConfiguradoCtaCteCliente" placeholder="Ingresar email">

              </div>

            </div>

            <!-- ENTRADA PARA OBSERVACIONES -->
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-list"></i></span> 

                <textarea class="form-control" id="mensajeCtaCteCliente" placeholder="Mensaje..." rows="10"></textarea>
                
                <!--<div style="display:none">
                    <p id="datosEmpresaCtaCteCliente"><b><?php echo $arrayEmpresa["razon_social"]; ?></b><br>
Domicilio: <?php echo $arrayEmpresa["domicilio"]; ?> <br>
Telefono: <?php echo $arrayEmpresa["telefono"]; ?> <br>
Email: <?php echo $arrayEmpresa["mail"]; ?>
                    </p>
                </div>--> 
                

              </div>

            </div>

            <!-- CHECK  
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="chkEnviarMailAdjunto">
              <label class="form-check-label" for="defaultCheck1"> Envio informe adjunto </label>
            </div> -->
  
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button id="btnEnviarMailCtaCteCliente" class="btn btn-primary">Enviar!</button>

        </div>


    </div>

  </div>

</div>