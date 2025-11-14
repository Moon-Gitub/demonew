<?php

  $saldoTotal = ControladorProveedoresCtaCte::ctrMostrarSaldoTotal();
  //$saldoTotal["saldo"] = $saldoTotal["saldo"];
  $colorBox = ($saldoTotal["saldo"] < 0) ? 'bg-warning' : 'bg-success';

?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar proveedores <small><b>Saldo en cuenta corriente</b></small>
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar proveedores</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
      <div class="row">

        <div class="col-lg-3 col-xs-6">
          <a class="btn btn-primary" href="proveedores">
            
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

  </section>

</div>