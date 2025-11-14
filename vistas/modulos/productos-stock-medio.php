<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar productos <small> - <b>Productos con stock medio</b></small>
    
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

  </section>

</div>