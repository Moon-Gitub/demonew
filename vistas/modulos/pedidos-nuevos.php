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
      
      Administrar pedidos internos
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar pedidos internos</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive tablasPedidosInternosNuevos" width="100%">
         
        <thead>
         
         <tr>
           
           <th style="width:10px">#</th>
           <th><center>Usuario</center></th>
		   <th><center>Origen</center></th>
		   <th><center>Destino</center></th>
		   <th><center>Articulos Pedidos</center></th>
		   <th><center>Resumen Pedidos</center></th>
		   <th><center>Fecha</center></th>
           <th><center>Acciones</center></th>

         </tr> 

        </thead>

        <tbody>

         <?php

          $item = null;
          $valor = null;

          $pedidos = ControladorPedidos::ctrMostrarPedidos($item, $valor);
		  	
          foreach ($pedidos as $key => $value) {
            
			$resultado = ""; 
			$precioCompraActual = 0;
			$cantidadProductos = 0;
			$detallePedido = "";
			// Obtener productos desde tabla relacional (o JSON legacy si no existe)
			$listaProducto = ControladorVentas::ctrObtenerProductosVentaLegacy($value["id"]);
			
			// Si no hay productos en tabla relacional, intentar desde JSON (compatibilidad)
			if (empty($listaProducto) && !empty($value["productos"])) {
				$listaProducto = json_decode($value["productos"], true);
			}

			if (is_array($listaProducto)) {
                foreach ($listaProducto as $key2 => $value2) {

                  $item = "id";
                  $valor = $value2["id"];
                  $orden = "id";
				   
                   $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);
				   $cantidadProductos = $cantidadProductos + $value2["cantidad"];	
				   $precioCompraActual = $precioCompraActual + ($respuesta["precio_venta"] * $value2["cantidad"]);
				   $resultado = $resultado.'<b> Cod: </b>' . $respuesta["codigo"]. '<b> Desc: </b>' .$value2["descripcion"]. '<b> Precio Actual: $</b>' .$respuesta["precio_venta"]. '<b> Cantidad: </b>' .$value2["cantidad"]. '</br>'; 
				  
				   $detallePedido = "<b>Cant. Items: </b>" . count($listaProducto);
				   $detallePedido = $detallePedido . "<br/><b>Cant. Productos: </b>" . $cantidadProductos;

                   $detallePedido = $detallePedido . "<br/><b>Total Pedido: $</b>" . $precioCompraActual;
				}
			}

            echo '<tr>
					
                    <td><center>'.$value["id"].'</center></td>

                    <td><center>'.$value["id_vendedor"].'</center></td>

                    <td><center>'.$value["origen"].'</center></td>

					<td><center>'.$value["destino"].'</center></td>

					<td>'.$resultado.'</td>
					
					<td>'.$detallePedido.'</td>
					
					<td>'.$value["fecha"].'</td>
					
                   	<td class="text-center">

                      <div class="acciones-tabla">
                        <a class="btn-accion btnImprimirPedidoParcial" title="Imprimir pedido" href="#" codigoPedido="'.$value["id"].'"><i class="fa fa-print"></i></a>
                        <a class="btn-accion btnEditarPedido" title="Editar pedido" href="#" idPedido="'.$value["id"].'"><i class="fa fa-pencil"></i></a>
                        <a class="btn-accion btn-danger btnEliminarPedido" title="Borrar pedido" href="#" idPedido="'.$value["id"].'"><i class="fa fa-times"></i></a>
                      </div>';

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

<!--=====================================
MODAL EDITAR PEDIDO
======================================-->

<div id="modalEditarPedido" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Validar Pedido</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 

                <input type="text" class="form-control input-lg" name="editarCategoria" id="editarCategoria" required>
				<input type="text" class="form-control input-lg" name="editarCategoria" id="editarCategoria" required>
				<input type="text" class="form-control input-lg" name="editarCategoria" id="editarCategoria" required>

                 <input type="hidden"  name="idCategoria" id="idCategoria" required>

              </div>

            </div>
  
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar cambios</button>

        </div>

      </form>

    </div>

  </div>

</div>

<?php

  $borrarPedido = new ControladorPedidos();
 $borrarPedido -> ctrEliminarPedido();

?>


