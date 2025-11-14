<?php

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";


class TablaProductosCompras{

 	/*=============================================
 	 MOSTRAR LA TABLA DE PRODUCTOS
  	=============================================*/ 

	public function mostrarTablaProductosCompras(){

		$item = null;
    	$valor = null;
    	$orden = "id";

  		$productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);
 		
  		if(count($productos) == 0){

  			echo '{"data": []}';

		  	return;
  		}	
		
  		$datosJson = '{
		  "data": [';

		$cantProd = (count($productos) > 3000) ? 3000 : count($productos);
		  //for($i = 0; $i < count($productos); $i++){
	  	for($i = 0; $i < $cantProd; $i++){

		  	if($productos[$i]["id"] != 1 && 
		  		$productos[$i]["id"] != 2 &&
		  		$productos[$i]["id"] != 3 &&
		  		$productos[$i]["id"] != 4 &&
		  		$productos[$i]["id"] != 5 &&
		  		$productos[$i]["id"] != 6 &&
		  		$productos[$i]["id"] != 7 &&
		  		$productos[$i]["id"] != 8 &&
		  		$productos[$i]["id"] != 9 &&
		  		$productos[$i]["id"] != 10){

				//$promedioVenta = ControladorVentas::ctrRangoFechasVentasProducto($productos[$i]["codigo"]);
			  	/*=============================================
	 	 		TRAEMOS LA IMAGEN
	  			=============================================*/ 
			  	$imagen = "<img src='".$productos[$i]["imagen"]."' width='40px'>";

			  	/*=============================================
	 	 		STOCK
	  			=============================================*/ 
				$sumaStock = $productos[$i]["stock"];  
	 			$stock = "<button class='btn btn-success agregarProductoCompra recuperarBoton' idProducto='".$productos[$i]["id"]."'>".$sumaStock."</button>";
				
				$producto = "<div class='detalleProductoCompras' idProducto='". $productos[$i]["id"] ."'>" . $productos[$i]["descripcion"]."</div>";

			  	/*=============================================
	 	 		TRAEMOS LAS ACCIONES
	  			=============================================*/ 

			  	$datosJson .='[
				      "'.$productos[$i]["codigo"].'",
					  "'.$producto .'",
					  "'.$productos[$i]["precio_compra"].'",
					  "'.$stock.'"
				    ],';

			}

		}

		$datosJson = substr($datosJson, 0, -1);

		$datosJson .=   '] 

		}';
		
		echo $datosJson;

	}

}

/*=============================================
ACTIVAR TABLA DE PRODUCTOS
=============================================*/ 
$activarProductosCompras = new TablaProductosCompras();
$activarProductosCompras -> mostrarTablaProductosCompras();