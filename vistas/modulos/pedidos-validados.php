<?php

if($_SESSION["perfil"] == "Vendedor"){

  echo '<script>

    window.location = "inicio";

  </script>';

  return;

}

?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar pedidos internos validados
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar pedidos internos validados</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive tablasPedidosInternos" width="100%">
         
        <thead>
         
         <tr>
           
           <th style="width:10px">#</th>
           <th><center>U. Pedido</center></th>
		   <th><center>Origen</center></th>
		   <th><center>Destino</center></th>
		   <th><center>Articulos Pedidos</center></th>
		   <th><center>Resumen Pedidos</center></th>
           <th><center>U. Confirma</center></th>
		   <th><center>Fecha</center></th>
		   <th><center>Imprimir</center></th>

         </tr> 

        </thead>

        <tbody>

         <?php

          $item = null;
          $valor = null;

          $pedidos = ControladorPedidos::ctrMostrarPedidosValidados($item, $valor);

          foreach ($pedidos as $key => $value) {
			
            $resultado = ""; 
			$precioCompraActual = 0;
			$cantidadProductos = 0;
			$detallePedido = "";
			$listaProducto = json_decode($value["productos"], true);

                foreach ($listaProducto as $key2 => $value2) {

                  $item = "id";
                  $valor = $value2["id"];
                  $orden = "id";
					
                  $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);
				  $precioCompraActual = $precioCompraActual + ($respuesta["precio_venta"] * $value2["cantidad"]);
				  $cantidadProductos = $cantidadProductos + $value2["cantidad"];		
				  $resultado = $resultado.'<b> Cod: </b>' . $value2["id"]. '<b> Desc: </b>' .$value2["descripcion"]. '<b> Precio Actual: $</b>' .$value2["precio_venta"]. '<b> Cantidad: </b>' .$value2["cantidad"]. '</br>'; 
				  
				   $detallePedido = "<b>Cant. Items: </b>" . count($listaProducto);

				   $detallePedido = $detallePedido . "<br/><b>Cant. Productos: </b>" . $cantidadProductos;

                   $detallePedido = $detallePedido . "<br/><b>Total Pedido: $</b>" . $precioCompraActual;
				}

            echo '<tr>
					
                    <td><center>'.$value["id"].'</center></td>

                    <td><center>'.$value["id_vendedor"].'</center></td>

                    <td><center>'.$value["origen"].'</center></td>

					<td><center>'.$value["destino"].'</center></td>

					<td>'.$resultado.'</td>
					
					<td>'.$detallePedido.'</td>
					
					<td><center>'.$value["usuarioConfirma"].'</center></td>
                   	
					<td><center>'.$value["fecha"].'</center></td>
					
					<td>

                     		<button class="btn btn-info btnImprimirPedido" codigoPedido="'.$value["id"].'">
	
								<i class="fa fa-print"></i>
	
							</button>   
		
                    </td>

                  </tr>';
          
            }

        ?>

        </tbody>

       </table>

      </div>

    </div>

  </section>

</div>

