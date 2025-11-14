<?php

class ControladorPedidos{

	/*=============================================
	MOSTRAR Pedidos
	=============================================*/
	static public function ctrMostrarPedidos($item, $valor){
		$tabla = "pedidos";
		$respuesta = ModeloPedidos::mdlMostrarPedidos($tabla, $item, $valor);
		return $respuesta;
	}
	
	/*=============================================
	MOSTRAR PEdidos
	=============================================*/
	static public function ctrMostrarPedidosValidados($item, $valor){
		$tabla = "pedidos";
		$respuesta = ModeloPedidos::mdlMostrarPedidosValidados($tabla, $item, $valor);
		return $respuesta;

	}

	/*=============================================
	CREAR PEDIDO
	=============================================*/
	static public function ctrCrearPedido(){
		if(isset($_POST["nuevoPedido"])){
			if($_POST["listaProductosPedidos"] == ""){
					echo'<script>
					swal({
					  type: "error",
					  title: "El pedido no se ha ejecuta si no hay productos",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "pedidos";

								}
							})

				</script>';

				return;
			}

			$listaProductosPedidos = json_decode($_POST["listaProductosPedidos"], true);
			$totalProductosComprados = array();
			foreach ($listaProductosPedidos as $key => $value) {
			   array_push($totalProductosComprados, $value["cantidad"]);
			
			   $tablaProductos = "productos";

			    $item = "id";
			    $valor = $value["id"];
			    $orden = "id";

			    $traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);

			}

			$tabla = "pedidos";

			$datos = array("id_vendedor"=>$_POST["idVendedor"],
						   "codigo"=>$_POST["nuevoPedido"],
						   "productos"=>$_POST["listaProductosPedidos"],
						   "origen"=>$_POST["nuevoOrigen"],
						   "destino"=>$_POST["nuevoDestino"],
						   "usuarioConfirma"=>$_POST["usuarioConfirma"],
						   "estado"=>$_POST["estado"]);
			
			$respuesta = ModeloPedidos::mdlIngresarPedido($tabla, $datos);

			if($respuesta == "ok"){

				echo'<script>

				localStorage.removeItem("rango");

				swal({
					  type: "success",
					  title: "El pedido ha sido guardado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "pedidos-nuevos";

								}
							})

				</script>';

			}

		}

	}
//}
	/*=============================================
	EDITAR PEDIDO
	=============================================*/

	static public function ctrEditarPedido(){

		if(isset($_POST["editarPedido"])){
			
			$tabla = "pedidos";

			$item = "id";
			$valor = $_POST["editarPedido"];

			$traerPedido = ModeloPedidos::mdlMostrarPedidos($tabla, $item, $valor);

			/*=============================================
			REVISAR SI VIENE PRODUCTOS EDITADOS
			=============================================*/

			if($_POST["listaProductosPedidosValidar"] == ""){

				$listaProductosPedidosValidar = $traerPedido["productos"];
				$cambioProducto = false;

			}else{

				$listaProductosPedidosValidar = $_POST["listaProductosPedidosValidar"];
				$cambioProducto = true;
			}

				//if($cambioProducto){

				$productos =  json_decode($listaProductosPedidosValidar, true);

				$totalProductosComprados = array();

				foreach ($productos as $key => $value) {

					array_push($totalProductosComprados, $value["recibida"]);
					
					$tablaProductos = "productos";

					$item = "id";
					$valor = $value["id"];
					$orden = "id";

					$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);

					switch($traerProducto){
						case ($_POST['editarOrigen']=="deposito"):
						$stockOriginalOrigen = $traerProducto['deposito'];
						$origenDes = "deposito";
						break;
						case ($_POST['editarOrigen']=="stock"):
						$stockOriginalOrigen = $traerProducto['stock'];
						$origenDes = "stock";
						break;
					}
						
					switch($traerProducto){
						case ($_POST['editarDestino']=="deposito"):
						$stockOriginalDestino = $traerProducto['deposito'];
						$destinoDes = "deposito";
						break;
						case ($_POST['editarDestino']=="stock"):
						$stockOriginalDestino = $traerProducto['stock'];
						$destinoDes = "stock";
						break;
								
					}
					
					$item1a = "pedidos";
					$valor1a = $stockOriginalOrigen - $value["recibida"];
					$valor1aDestino = $stockOriginalDestino + $value["recibida"];
					$origen = $origenDes;
					$destino = $destinoDes;

					$item1b = $origen;
					$valor1b = $valor1a;

					$nuevoStockOrigen = ModeloProductos::mdlActualizarProductoDos($tablaProductos, $item1b, $valor1b, $valor);

					$item1bDestino = $destino;
					$valor1bDestino = $valor1aDestino;

					$nuevoStockDestino = ModeloProductos::mdlActualizarProductoDos($tablaProductos, $item1bDestino, $valor1bDestino, $valor);

					}

			/*=============================================
			GUARDAR CAMBIOS DE LA COMPRA
			=============================================*/	

			$datos = array("id_vendedor"=>$_POST["idVendedor"],
						   "id"=>$_POST["editarPedido"],
						   "productos"=>$listaProductosPedidosValidar,
						   "origen"=>$_POST["editarOrigen"],
						   "destino"=>$_POST["editarDestino"],
						   "usuarioConfirma"=>$_POST["usuarioConfirma"],
						   "estado"=>1);
			
			$respuesta = ModeloPedidos::mdlEditarPedido($tabla, $datos);

			if($respuesta == "ok"){
				
				echo'<script>

				localStorage.removeItem("rango");

				swal({
					  type: "success",
					  title: "El pedido se ha validado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then((result) => {
								if (result.value) {

								window.location = "pedidos-validados";
								
								}
							})

				</script>';

			}

		}

	}
//}

	/*=============================================
	ELIMINAR PEDIDO
	=============================================*/

	static public function ctrEliminarPedido(){

		if(isset($_GET["idPedido"])){

			$tabla = "pedidos";

			$item = "id";
			$valor = $_GET["idPedido"];

			$respuesta = ModeloPedidos::mdlEliminarPedido($tabla, $_GET["idPedido"]);

			if($respuesta == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "El pedido ha sido borrado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "pedidos-nuevos";

								}
							})

				</script>';

			}		
		}

	}

}
