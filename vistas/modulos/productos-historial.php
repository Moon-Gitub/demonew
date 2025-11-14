<?php

  $productoSeleccionado = ControladorProductos::ctrMostrarProductos('id', $_GET["idProducto"], 'id'); 

?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar productos  <small> - <b>Historial de cambios</b></small>
    
    </h1>

    <h1>  <small> <?php   echo $productoSeleccionado["codigo"] . ' - ' . $productoSeleccionado["descripcion"]; ?> </small></h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar productos - Historial cambios</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
        <a class="btn btn-primary" href="productos">
          
          Volver

        </a>

      </div>

      <div class="box-body">
        
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

  </section>

</div>