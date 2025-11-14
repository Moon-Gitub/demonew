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
      
      Administrar compras
    
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

         <button type="button" class="btn btn-default pull-right" id="daterange-btnCompras">
           
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
        
       <table class="table table-bordered table-striped " id="tablaListarCompras">
         
        <thead>
         
         <tr>
           
          <th>Fecha</th>
          <th>Nro. Int.</th>
          <th>Fec. Emision</th>
          <th>Remito/Factura</th>
          <th>Proveedor</th>
          <th>Usuario Pedido</th>
          <th>Usuario Confirma</th>
          <th>Subtotal</th>
          <th>Descuento</th>
          <th>Neto</th>
          <th>IVA</th>
          <th>IIBB</th>
          <th>Perc. IVA</th>
          <th>Perc. Ganancia</th>
          <th>Imp. Int.</th>
          <th>Total</th>
          <th style="width:20px">Acciones</th>

         </tr> 

        </thead>

        <tfoot>
         
         <tr>
           
          <th>Fecha</th>
          <th>Nro. Int.</th>
          <th>Fec. Emision</th>
          <th>Remito/Factura</th>
          <th>Proveedor</th>
          <th>Usuario Pedido</th>
          <th>Usuario Confirma</th>
          <th>Subtotal</th>
          <th>Descuento</th>
          <th>Neto</th>
          <th>IVA</th>
          <th>IIBB</th>
          <th>Perc. IVA</th>
          <th>Perc. Ganancia</th>
          <th>Imp. Int.</th>
          <th>Total</th>
          <th></th>

         </tr> 

        </tfoot>

        <tbody>

        <?php

          if(isset($_GET["fechaInicial"])){

            $fechaInicial = $_GET["fechaInicial"];
            $fechaFinal = $_GET["fechaFinal"];

          }else{

            $fechaInicial = date('Y-m-d 00:00:00');
            $fechaFinal = date('Y-m-d 23:59:59');

          }

          $respuesta = ControladorCompras::ctrRangoFechasComprasIngresadas($fechaInicial, $fechaFinal);
      
          foreach ($respuesta as $key => $value) {

            $proveedores = ControladorProveedores::ctrMostrarProveedores("id", $value["id_proveedor"]);

            $subtotal = $value["totalNeto"] + $value["descuento"];

            echo '<tr>

              <td>'.$value["fecha"].'</td>

              <td>'.$value["id"].'</td>

              <td>'.$value["fechaEmision"].'</td>';

            echo ($value["remitoNumero"] == "") ? '<td>Fac.: '.$value["numeroFactura"].'</td>' : '<td>Rem.: '.$value["remitoNumero"].'</td>';

            echo '<td><a href="index.php?ruta=proveedores_cuenta&id_proveedor='.$proveedores["id"].'"> '.$proveedores["nombre"].'</a></td>

              <td>'.$value["usuarioPedido"].'</td>

              <td>'.$value["usuarioConfirma"].'</td>

              <td>'.$subtotal.'</td>
              <td>'.$value["descuento"].'</td>
              <td>'.$value["totalNeto"].'</td>
              <td>'.$value["iva"].'</td>
              <td>'.$value["precepcionesIngresosBrutos"].'</td>
              <td>'.$value["precepcionesIva"].'</td>
              <td>'.$value["precepcionesGanancias"].'</td>
              <td>'.$value["impuestoInterno"].'</td>
              <td>'.$value["total"].'</td>';

                echo '<td>
                   <div class="btn-group">
                      <button class="btn btn-info btnImprimirIngresoMercaderia" codigoCompra="'.$value["id"].'">
                       <i class="fa fa-print"></i>
                      </button>';

                      if($_SESSION["perfil"] == "Administrador"){

                          echo '<button class="btn btn-danger btnEliminarCompra" idCompra="'.$value["id"].'"><i class="fa fa-times"></i></button>';
                      }

                echo '</div></center>

                  </td>';
            
            echo '</tr>';

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
