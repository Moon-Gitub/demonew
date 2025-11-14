<?php

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";


class TablaProductosVentas{

 	/*=============================================
 	 MOSTRAR LA TABLA DE PRODUCTOS
  	=============================================*/ 

	public function mostrarTablaProductosVentas(){

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

		  for($i = 0; $i < count($productos); $i++){

		  	/*=============================================
 	 		TRAEMOS LA IMAGEN
  			=============================================*/ 

		  	$imagen = "<img src='".$productos[$i]["imagen"]."' width='40px'>";

		  	/*=============================================
 	 		STOCK
  			=============================================*/ 

  			if($productos[$i]["id"] != 1) {

	  			if($productos[$i]["stock"] <= $productos[$i]["stock_bajo"]){

	  				$stock = "<button class='btn btn-danger'>".$productos[$i]["stock"]."</button>";

	  			}else if($productos[$i]["stock"] > $productos[$i]["stock_bajo"] && $productos[$i]["stock"] <= $productos[$i]["stock_medio"]){

	  				$stock = "<button class='btn btn-warning'>".$productos[$i]["stock"]."</button>";

	  			}else{

	  				$stock = "<button class='btn btn-success'>".$productos[$i]["stock"]."</button>";

	  			}

  			} else {

  				$stock = "-";

  			}

		  	/*=============================================
 	 		TRAEMOS LAS ACCIONES
  			=============================================*/ 

		  	$botones =  "<div class='btn-group'><button class='btn btn-primary agregarProducto recuperarBoton' idProducto='".$productos[$i]["codigo"]."'><i class='fa fa-plus'></i></button></div>"; 

		  	// $descripcion = "<div class='detalleProductoVentas' style='cursor: zoom-in;'  data-toggle='modal' data-target='#modalDetProd' idProducto='". $productos[$i]["id"] ."'>" . $productos[$i]["descripcion"] . "</div>";

		  	$datosJson .='[
		  		  "'.$imagen.'",
			      "'.$productos[$i]["codigo"].'",
			      "'.$productos[$i]["descripcion"].'",
			      "'.$stock.'",
			      "$ '.number_format($productos[$i]["precio_venta"],2,',','.').'",
			      "'.$botones.'"
			    ],';

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
$activarProductosVentas = new TablaProductosVentas();
$activarProductosVentas -> mostrarTablaProductosVentas();