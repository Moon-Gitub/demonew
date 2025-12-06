<!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Administrar productos <small> - <b>Productos con stock medio</b></small></h3>
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
           
           <th>Código</th>
           <th>Descripción</th>
           <th>Stk </th>
           <th>Stk TOTAL</th>
           <th>Stock Medio</th>
           <th>Stock Bajo</th>
           
         </tr> 

        </thead>

        <tbody>

        <?php

          $item = null;
          $valor = null;

          $productos = ControladorProductos::ctrMostrarStockMedio();
          $totXproducto = 0;
          foreach ($productos as $key => $value) {

            $value["stock"] = ($value["stock"]<0) ? 0 : $value["stock"];
            $totXproducto = $value["stock"];

              echo '<tr>

                    <td>'.$value["codigo"].'</td>

                    <td>'.$value["descripcion"].'</td>

                    <td>'.$value["stock"].'</td>

                    <td>'.$totXproducto.'</td> 

                    <td>'.$value["stock_medio"].'</td>

                    <td>'.$value["stock_bajo"].'</td>';

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