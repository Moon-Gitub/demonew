<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar productos  <small> - <b>Stock Valorizado</b></small>
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar productos</li>
    
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
        
        <table class="table table-bordered table-striped dt-responsive tablasBotones" width="100%">
         
        <thead>

          <tr>
           
           <th></th>
           <th></th>
           <th></th>
           <th></th>
           <th>$ <?php echo ControladorProductos::ctrMostrarStockValorizadoTotales()["invertido"]; ?> </th>
           <th></th>
           <th>$ <?php echo ControladorProductos::ctrMostrarStockValorizadoTotales()["valorizado"]; ?> </th>
           
         </tr> 
         
         <tr>
           
           <th>Código</th>
           <th>Descripción</th>
           <th>Stock</th>
           <th>$ Compra</th>
           <th>Invertido</th>
           <th>$ Venta</th>
           <th>Valorizado</th>
           
         </tr> 

        </thead>

        <tbody>

        <?php

          $item = null;
          $valor = null;

          $productos = ControladorProductos::ctrMostrarStockValorizado();

          foreach ($productos as $key => $value) {

              echo '<tr>

                    <td>'.$value["codigo"].'</td>

                    <td>'.$value["descripcion"].'</td>';

              echo '<td>'.$value["stock"].'</td>

                    <td>'.$value["precio_compra"].'</td>

                    <td>'.$value["invertido"].'</td>

                    <td>'.$value["precio_venta"].'</td>

                    <td>'.$value["valorizado"].'</td>';

              echo '</tr>';

            }

        ?>

        </tbody>

       </table>

      </div>

    </div>

  </section>

</div>