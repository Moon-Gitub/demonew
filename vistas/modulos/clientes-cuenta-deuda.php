<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar clientes <small><b>Deudas en cuenta corriente</b></small>
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar clientes</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
        <a class="btn btn-primary" href="clientes">
          
          Volver

        </a>

      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive tablasBotones" width="100%">
         
        <thead>
         
         <tr>
           
           <!-- <th style="width:10px">#</th> -->
           <th>Nombre</th>
           <th>Limite CC</th>
           <th>Nº Vta</th>
           <th>Fec. Vta.</th>
           <th>Vto.</th>
           <th>Vencido</th>
           <th>Total</th>

         </tr> 

        </thead>

        <tbody>

        <?php

          $item = null;
          $valor = null;

          $clientes = ControladorClientesCtaCte::ctrMostrarDeudas();

          foreach ($clientes as $key => $value) {

              echo '<tr>

                    <td><a href="index.php?ruta=clientes_cuenta&id_cliente='.$value["id_cliente"].'">'.$value["nombre"].'</a></td>

                    <td>30 días</td>';

              echo '<td><a href="index.php?ruta=editar-venta&idVenta='.$value["id_venta"].'">'.$value["codigo"].'</a></td>

                    <td>'.$value["fecha_venta"].'</td>

                    <td>'.$value["vencimiento_pago"].'</td>

                    <td>'.$value["dias_vencido"].' días</td>

                    <td>'.$value["total"].'</td>';

              echo '</tr>';

            }

        ?>

        </tbody>

       </table>

      </div>

    </div>

  </section>

</div>