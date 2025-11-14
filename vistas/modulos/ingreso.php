<?php

if($_SESSION["perfil"] == "Especial"){

  echo '<script>

    window.location = "inicio";

  </script>';

  return;

}

?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar compras <small> - Validar ingreso mercaderia</small>
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar compras</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
        <a href="crear-compra" class="btn btn-primary">Agregar compra</a>

         <button type="button" class="btn btn-default pull-right" id="daterange-btn">
           
            <span>
              <i class="fa fa-calendar"></i> 

              <?php

                if(isset($_GET["fechaInicial"])){

                  echo $_GET["fechaInicial"]." - ".$_GET["fechaFinal"];
                
                }else{
                 
                  echo 'Rango de fecha';

                }

              ?>
            </span>

            <i class="fa fa-caret-down"></i>

         </button>

      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
         
        <thead>
         
         <tr>

          <th><center>Fecha</center></th>
          <th><center>Nro. Int.</center></th>
          <th><center>Proveedor</center></th>
          <th><center>Usuario Pedido</center></th>
          <th><center>Acciones</center></th>

         </tr>

        </thead>

        <tbody>

        <?php

          if(isset($_GET["fechaInicial"])){

            $fechaInicial = $_GET["fechaInicial"];
            $fechaFinal = $_GET["fechaFinal"];

          }else{

            $fechaInicial = null;
            $fechaFinal = null;

          }

          $respuesta = ControladorCompras::ctrRangoFechasCompras($fechaInicial, $fechaFinal);
      
          foreach ($respuesta as $key => $value) {

            $item = "id";
            $valor = $value["id_proveedor"];
            $proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);

            echo '<tr>

                  <td><center>'.$value["fecha"].'</center></td>
                  
                  <td><center>'.$value["id"].'</center></td>

                  <td><center>'.$proveedores["nombre"].'</center></td>

                  <td><center>'.$value["usuarioPedido"].'</center></td>

                  <td>
                    <center>
                      <div class="btn-group">
                      <button class="btn btn-info btnImprimirCompraParcial" codigoCompra="'.$value["id"].'"><i class="fa fa-print"></i></button>
                      <button class="btn btn-warning btnEditarIngreso" idCompra="'.$value["id"].'"><i class="fa fa-pencil"></i></button>';
                      if($_SESSION["perfil"] == "Administrador"){

                          echo '<button class="btn btn-danger btnEliminarCompra" idCompra="'.$value["id"].'"><i class="fa fa-times"></i></button>';

                      }

                    echo '</div>
                    </center>

                  </td>

                </tr>';
            }

        ?>
               
        </tbody>

       </table>

        <?php
          $borrarCompra = new ControladorCompras();
          $borrarCompra -> ctrEliminarCompra();
        ?>

      </div>
    </div>
  </section>
</div>