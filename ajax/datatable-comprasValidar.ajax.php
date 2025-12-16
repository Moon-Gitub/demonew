<?php
// ✅ Seguridad AJAX
require_once "seguridad.ajax.php";
// Esta tabla se consume solo por GET (lectura desde DataTables),
// por lo que no requiere validación de token CSRF.
SeguridadAjax::inicializar(false);


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

		  for($i = 0; $i < count($productos); $i++){

		  	/*=============================================
 	 		TRAEMOS LA IMAGEN
  			=============================================*/ 

		  	$imagen = "<img src='".$productos[$i]["imagen"]."' width='40px'>";

		  	/*=============================================
 	 		STOCK
  			=============================================*/ 
			$sumaStock = $productos[$i]["stock"];  
 			$stock = "<button class='btn btn-success agregarProductoCompraValidar recuperarBoton' idProducto='".$productos[$i]["id"]."'>".$sumaStock."</button>";
			
			/*=============================================
 	 		TRAEMOS LAS ACCIONES
  			=============================================*/ 

		   	$datosJson .='[
			      "'.$imagen.'",
			      "'.$productos[$i]["codigo"].'",
				  "'.$productos[$i]["descripcion"].'",
				  "'.$stock.'"
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
$activarProductosCompras = new TablaProductosCompras();
$activarProductosCompras -> mostrarTablaProductosCompras();

