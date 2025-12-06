<?php

  $saldoTotal = ControladorProveedoresCtaCte::ctrMostrarSaldoTotal();
  //$saldoTotal["saldo"] = $saldoTotal["saldo"];
  $colorBox = ($saldoTotal["saldo"] < 0) ? 'bg-warning' : 'bg-success';

?>

<!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Administrar proveedores <small><b>Saldo en cuenta corriente</b></small></h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
<li><a href="inicio"><i class="bi bi-speedometer2"></i> Inicio</a></li>
      
      <li class="active">Administrar proveedores</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <!--end::App Content Header-->
  <!--begin::App Content-->
  <div class="app-content">
    <div class="container-fluid">

    <div class="card">

      <div class="card-header with-border">
  
      <div class="row">

        <div class="col-lg-3 col-xs-6">
          <a class="btn btn-primary" href="proveedores">
            
            Volver

          </a>
        </div>
      
        <div class="float-end col-lg-3 col-xs-6">

          <div class="small-box <?php echo $colorBox; ?>">
            
            <div class="inner">
              
              <h3>$<?php echo number_format($saldoTotal["saldo"], 2, ',', '.'); ?></h3>

              <p><b>Saldo total</b></p>
            
            </div>
            
            <div class="icon">
              
              <i class="ion ion-social-usd"></i>
            
            </div>
            
            <!--<a href="clientes-cuenta-saldo" class="small-card-footer">
              
              Más info <i class="fa fa-arrow-circle-right"></i>
            
            </a>-->

          </div>

        </div>

      </div>

      </div>

      <div class="card-body">
        
       <table class="table table-bordered table-striped dt-responsive tablasBotones" width="100%">
         
        <thead>
         
         <tr>
           
           <!-- <th style="width:10px">#</th> -->
           <th>Nombre</th>
           <th>Telefono</th>
           <th>Total compras</th>
           <th>Total pagos</th>
           <th>Saldo</th>

         </tr>

        </thead>

        <tbody>

        <?php

          $item = null;
          $valor = null;

          $proveedores = ControladorProveedoresCtaCte::ctrMostrarSaldos();

          foreach ($proveedores as $key => $value) {

              echo '<tr>

                    <td><a href="index.php?ruta=proveedores_cuenta&id_proveedor='.$value["id_proveedor"].'">'.$value["nombre"].'</a></td>

                    <td>'.$value["telefono"].'</td>';

              echo '<td>'.$value["compras"].'</td>

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
    <!--end::Container-->
  </div>
  <!--end::App Content-->