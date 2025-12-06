<!--begin::App Main-->
<main class="app-main">
  <!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Administrar productos  <small> - <b>Stock Valorizado</b></small></h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
<li><a href="inicio"><i class="bi bi-speedometer2"></i> Inicio</a></li>
      
      <li class="active">Administrar productos</li>
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

      </div>
    <!--end::Container-->
  </div>
  <!--end::App Content-->
</main>
<!--end::App Main-->