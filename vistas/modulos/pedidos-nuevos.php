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
          <h3 class="mb-0">Administrar pedidos internos</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
<li class="breadcrumb-item"><a href="inicio"><i class="bi bi-speedometer2"></i> Inicio</a></li>
      
      <li class="breadcrumb-item active" aria-current="page">Administrar pedidos internos</li>
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
			$listaProducto = json_decode($value["productos"], true);

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

            echo '<tr>
					
                    <td><center>'.$value["id"].'</center></td>

                    <td><center>'.$value["id_vendedor"].'</center></td>

                    <td><center>'.$value["origen"].'</center></td>

					<td><center>'.$value["destino"].'</center></td>

					<td>'.$resultado.'</td>
					
					<td>'.$detallePedido.'</td>
					
					<td>'.$value["fecha"].'</td>
					
                   	<td>

                      <center>
					  
							<button class="btn btn-info btnImprimirPedidoParcial" codigoPedido="'.$value["id"].'">
								<i class="bi bi-printer"></i>
							</button>   
							<button class="btn btn-warning btnEditarPedido" idPedido="'.$value["id"].'"><i class="fa fa-pencil"></i></button>
							
							<button class="btn btn-danger btnEliminarPedido" idPedido="'.$value["id"].'"><i class="bi bi-x"></i></button>';

                   
                      echo '</center>

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

          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>

          <h4 class="modal-title">Validar Pedido</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="card-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-text"><i class="bi bi-pencil-square"></i></span> 

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

          <button type="button" class="btn btn-default float-start" data-bs-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar cambios</button>

        </div>

      </form>

    </div>

  </div>

</div>
    </div>
    <!--end::Container-->
  </div>
  <!--end::App Content-->
<?php

  $borrarPedido = new ControladorPedidos();
 $borrarPedido -> ctrEliminarPedido();

?>


