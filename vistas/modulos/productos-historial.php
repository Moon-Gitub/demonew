<?php

  $productoSeleccionado = ControladorProductos::ctrMostrarProductos('id', $_GET["idProducto"], 'id'); 

?>

<!--begin::App Main-->
<main class="app-main">
  <!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Administrar productos  <small> - <b>Historial de cambios</b></small>
    
    </h1>

    <h1>  <small> <?php   echo $productoSeleccionado["codigo"] . ' - ' . $productoSeleccionado["descripcion"]; ?> </small></h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
<li><a href="inicio"><i class="bi bi-speedometer2"></i> Inicio</a></li>
      
      <li class="active">Administrar productos - Historial cambios</li>
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
  
        <a class="btn btn-primary" href="productos">
          
          Volver

        </a>

      </div>

      <div class="card-body">
        
        <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
         
        <thead>
         
         <tr>
           
<!--            <th>Código</th>
           <th>Descripción</th> -->
           <th>Fecha Hora</th>
           <th>Acción</th>
           <th>Stk </th>
           <th>$ compra </th>
           <th>$ venta</th>
           <th>Usuario</th>
           <th>Desde</th>
           
         </tr> 

        </thead>

        <tbody>

        <?php

          $idProducto = $_GET["idProducto"];

          $productos = ControladorProductos::ctrMostrarProductosHistorial($idProducto);

          date_default_timezone_set('America/Argentina/Mendoza');

          foreach ($productos as $key => $value) {

              echo '<tr>';

              // echo '<td>'.$value["codigo"].'</td>

              //       <td>'.$value["descripcion"].'</td>';

              echo '<td data-sort='.date('Ymd', strtotime($value["fecha_hora"])).'>'.date('d-m-Y H:i:s', strtotime($value["fecha_hora"])).'</td>

                    <td>'.$value["accion"].'</td>

                    <td>'.$value["stock"].'</td>

                    <td>'.$value["precio_compra"].'</td>

                    <td>'.$value["precio_venta"].'</td>

                    <td>'.$value["nombre_usuario"].'</td>

                    <td>'.$value["cambio_desde"].'</td>';

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
</main>
<!--end::App Main-->