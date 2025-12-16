<style>
  /* ============================
     Estilos modernos para clientes cuenta corriente
     ============================ */

  .ccs-box {
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #e0e0e0;
  }

  .ccs-box-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 1px solid #e0e0e0;
    padding: 15px 20px;
    border-radius: 12px 12px 0 0;
  }

  .ccs-box-body {
    padding: 25px;
  }

  /* Card de saldo total mejorada */
  .ccs-card-saldo {
    background: linear-gradient(135deg, #1abc9c 0%, #16a085 100%);
    border-radius: 12px;
    padding: 25px;
    color: white;
    box-shadow: 0 4px 15px rgba(26, 188, 156, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .ccs-card-saldo.negativo {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
  }

  .ccs-card-saldo:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(26, 188, 156, 0.4);
  }

  .ccs-card-saldo.negativo:hover {
    box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
  }

  .ccs-card-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.9;
  }

  .ccs-card-value {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 5px;
  }

  .ccs-card-icon {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 60px;
    opacity: 0.3;
  }

  .ccs-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
    color: white;
  }

  .ccs-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
  }

  /* Tabla responsive */
  .ccs-table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    width: 100%;
    margin-top: 20px;
  }

  .tablasBotones {
    width: 100% !important;
    min-width: 700px;
  }

  .tablasBotones thead tr {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }

  .tablasBotones thead tr th {
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    border: none;
    padding: 12px 8px;
    white-space: nowrap;
  }

  .tablasBotones tbody tr {
    transition: background-color 0.2s ease;
  }

  .tablasBotones tbody tr:hover {
    background-color: #f8f9fa;
  }

  .tablasBotones tbody td {
    vertical-align: middle;
    padding: 12px 8px;
  }

  /* Mejorar buscador */
  .tablasBotones_filter {
    margin-bottom: 20px !important;
  }

  .tablasBotones_filter label {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    font-weight: 600 !important;
    color: #2c3e50 !important;
  }

  .tablasBotones_filter input {
    border: 2px solid #e0e0e0 !important;
    border-radius: 8px !important;
    padding: 10px 15px !important;
    font-size: 14px !important;
    transition: all 0.3s ease !important;
    width: 300px !important;
    max-width: 100% !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
  }

  .tablasBotones_filter input:focus {
    border-color: #667eea !important;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2) !important;
    outline: none !important;
  }

  /* Responsive para móviles */
  @media (max-width: 768px) {
    .ccs-box-body {
      padding: 15px;
    }

    .ccs-table-wrapper {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    .tablasBotones {
      min-width: 800px;
    }

    .ccs-card-saldo {
      padding: 20px;
    }

    .ccs-card-icon {
      font-size: 40px;
    }
  }
</style>

<?php

  $saldoTotal = ControladorClientesCtaCte::ctrMostrarSaldoTotal();
  $esNegativo = $saldoTotal["saldo"] < 0;

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

    <div class="box ccs-box">

      <div class="box-header with-border ccs-box-header">
        <div class="row">
          <div class="col-md-6 col-sm-12">
            <a class="btn ccs-btn-primary" href="clientes">
              <i class="fa fa-arrow-left"></i> Volver
            </a>
          </div>
          <div class="col-md-6 col-sm-12 text-right">
            <div class="ccs-card-saldo <?php echo $esNegativo ? 'negativo' : ''; ?>">
              <div class="ccs-card-icon">
                <i class="fa fa-usd"></i>
              </div>
              <div class="ccs-card-title">Saldo total</div>
              <div class="ccs-card-value">
                $<?php echo number_format($saldoTotal["saldo"], 2, ',', '.'); ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="box-body ccs-box-body">
        
       <div class="ccs-table-wrapper">
       <table class="table table-bordered table-striped dt-responsive tablasBotones" width="100%">
         
        <thead>
         
         <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
           
           <!-- <th style="width:10px">#</th> -->
           <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Nombre</th>
           <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Telefono</th>
           <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Mail</th>
           <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Total ventas</th>
           <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Total pagos</th>
           <th style="color: white; font-weight: 600; text-transform: uppercase; border: none; padding: 12px 8px;">Saldo</th>

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