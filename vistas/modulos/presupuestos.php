<?php 

  if(isset($_GET["fechaInicial"])){

    $fechaInicial = $_GET["fechaInicial"];
    $fechaFinal = $_GET["fechaFinal"];

  }else{

    $hoy = date('Y-m-d');

     $fechaInicial = $hoy . ' 00:00';
     $fechaFinal = $hoy . ' 23:59';

  }

  $respuestaPre = ControladorPresupuestos::ctrRangoFechasPresupuestos($fechaInicial, $fechaFinal);

 ?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar presupuestos
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar presupuestos</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
        <a href="crear-presupuesto-caja" class="btn btn-primary">Agregar presupuesto</a>

                <div class="btn-group">
          <?php 

/*
            echo '<a href="index.php?ruta=ventas&tipoLista=0&fechaInicial='.$fechaInicial.'&fechaFinal='.$fechaFinal.'" class="btn btn-primary" ><i class="fa fa-book fa-fw"></i> Ventas</a>
            <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
              <span class="fa fa-caret-down" title="Toggle dropdown menu"></span>
            </a>

            <ul class="dropdown-menu">
              
              <li>
                <a href="index.php?ruta=ventas&tipoLista=1&fechaInicial='.$fechaInicial.'&fechaFinal='.$fechaFinal.'" ><i class="fa fa-circle-o fa-fw"></i> Adeudadas</a>
              </li>

              <li>
                <a class="" href="index.php?ruta=ventas&tipoLista=2&fechaInicial='.$fechaInicial.'&fechaFinal='.$fechaFinal.'"><i class="fa fa-circle-o fa-fw"></i> Cta. Cte.</a>
              </li>

              <li>
                <a class="" href="index.php?ruta=ventas&tipoLista=3&fechaInicial='.$fechaInicial.'&fechaFinal='.$fechaFinal.'"><i class="fa fa-circle-o fa-fw"></i> Autorizadas</a>
              </li>              
            </ul>';
*/
          ?>
        </div>

         <button type="button" class="btn btn-default pull-right" id="PresupuestosDaterange-btn">
           
            <span>
              <i class="fa fa-calendar"></i> 

              <?php

                if(isset($_GET["fechaInicial"])){

                  echo $_GET["fechaInicial"]." - ".$_GET["fechaFinal"];
                
                }else{
                 
                  echo 'Hoy';

                }

              ?>
            </span>

            <i class="fa fa-caret-down"></i>

         </button>

      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive" id="tablaListarPresupuestos" width="100%">
         
        <thead>
         
         <tr>

           <th>Fecha</th>
           <th>Nro. Int.</th>
           <th>Cliente</th>
           <th>Medio pago</th>
           <th>Total</th> 
           <th>Acciones</th>

         </tr> 

        </thead>

        <tfoot>

          <tr>
            <th>Fecha</th>
            <th>Nro. Int.</th>
            <th>Cliente</th>
            <th>Medio pago</th>
            <th></th>
            <th></th>
          </tr>

        </tfoot>        

        <tbody>

        <?php

          foreach ($respuestaPre as $key => $value) {
           
             echo '<tr>

                    <td>'.$value["fecha"].'</td>';

              echo '<td><a href="index.php?ruta=editar-presupuesto&idPresupuesto='.$value["id"].'">' . $value["id"] . '</a></td>';

                if($value["id_cliente"] == 1){
                  echo '<td>'.$value["cliente"].'</td>';
                } else {
                  echo '<td><a href="index.php?ruta=clientes_cuenta&id_cliente='.$value["id_cliente"].'">'.$value["cliente"].'</a></td>';
                }

                $arrMetodoPago = json_decode($value["metodo_pago"]);

                echo '<td>'.$arrMetodoPago[0]->tipo.'</td>';

                echo '<td>'.round($value["total"],2).'</td>

                <td width="150px">
                
                  <center>

                    <div class="btn-group">
                      
                       <button title="Pasar a venta" class="btn btn-primary btnPresupuestoAVenta" idPresupuesto="'.$value["id"].'"><i class="fa fa-check"></i></button>';

                        echo '<button class="btn btn-primary btnImprimirPresupuesto" idPresupuesto="'.$value["id"].'"><i class="fa fa-print"></i></button>';

                        echo '<button class="btn btn-danger btnEliminarPresupuesto" idPresupuesto="'.$value["id"].'"><i class="fa fa-times"></i></button>


                    </div>

                  </center>

                </td>

              </tr>';
            }

        ?>
               
        </tbody>

       </table>

       <?php

        $eliminarPre = new ControladorPresupuestos();
        $eliminarPre -> ctrEliminarPresupuesto();

       ?>

      </div>

    </div>

  </section>

</div>