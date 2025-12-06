<!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Administrar clientes <small><b>Deudas en cuenta corriente</b></small></h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
<li class="breadcrumb-item"><a href="inicio"><i class="bi bi-speedometer2"></i> Inicio</a></li>
      
      <li class="breadcrumb-item active" aria-current="page">Administrar clientes</li>
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
  
        <a class="btn btn-primary" href="clientes">
          
          Volver

        </a>

      </div>

      <div class="card-body">
        
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

      </div>
    <!--end::Container-->
  </div>
  <!--end::App Content-->