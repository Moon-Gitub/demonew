<?php

if($_SESSION["perfil"] == "Vendedor"){

  echo '<script>

    window.location = "inicio";

  </script>';

  return;

}

?>

<!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Administrar pedidos internos validados</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
<li class="breadcrumb-item"><a href="inicio"><i class="bi bi-speedometer2"></i> Inicio</a></li>
      
      <li class="breadcrumb-item active" aria-current="page">Administrar pedidos internos validados</li>
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
  
      </div>

      <div class="card-body">
        
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
	
								<i class="bi bi-printer"></i>
	
							</button>   
		
                    </td>

                  </tr>';
          
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