<?php

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

class TablaProductos{

 	/*=============================================
 	 MOSTRAR LA TABLA DE PRODUCTOS
  	=============================================*/ 

	public function mostrarTablaProductos(){

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
		  
		  $soloVarios = true; //Si solo son los productos varios no los muestro en el listado

		  for($i = 0; $i < count($productos); $i++){

            if($productos[$i]["id"] > 10) {
                
                $soloVarios = false;
    		  	/*=============================================
     	 		TRAEMOS LA IMAGEN
      			=============================================*/ 
    		  	//$imagen = "<img src='".$productos[$i]["imagen"]."' width='40px'>";
    
    		  	/*=============================================
     	 		TRAEMOS LA CATEGORÍA
      			=============================================*/ 
    		  	// $item = "id";
    		  	// $valor = $productos[$i]["id_categoria"];
    
    		  	// $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
    
    		  	/*=============================================
     	 		STOCK
      			=============================================*/ 
      			if($productos[$i]["id"] != 1 && $productos[$i]["id"] != 2 && $productos[$i]["id"] != 3 && $productos[$i]["id"] != 4 && $productos[$i]["id"] != 5 && $productos[$i]["id"] != 6 && $productos[$i]["id"] != 7 && $productos[$i]["id"] != 8 && $productos[$i]["id"] != 9 && $productos[$i]["id"] != 10) {
    
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
    
      			if(isset($_GET["perfilOculto"]) && $_GET["perfilOculto"] == "Especial"){
    
      				$botones =  "<div class='btn-group'></div>"; 
    
      			}else{
    
      				 $botones =  "<div class='btn-group'><button class='btn btn-warning btnEditarProducto' idProducto='".$productos[$i]["id"]."' data-toggle='modal' data-target='#modalEditarProducto'><i class='fa fa-pencil'></i></button><button class='btn btn-danger btnEliminarProducto' idProducto='".$productos[$i]["id"]."' codigo='".$productos[$i]["codigo"]."' imagen='".$productos[$i]["imagen"]."'><i class='fa fa-times'></i></button></div>"; 
    
      			}
      			
      			$codigoProd = "<a href='index.php?ruta=productos-historial&idProducto=".$productos[$i]["id"]."'>".$productos[$i]["codigo"]."</a>";
    
    		  	$datosJson .='[
    		  		 
    				  "'.$codigoProd.'",
					  "'.$productos[$i]["nombre"].'",
    			      "'.$productos[$i]["descripcion"].'",
    			      "'.$stock.'",
    			      "'.$productos[$i]["precio_compra"].'",
    			      "'.number_format($productos[$i]["tipo_iva"], 2, ',', '.').' %",
    			      "'.$productos[$i]["precio_venta"].'",
    			      "'.$botones.'"
    			    ],';
             }
		  }

		  $datosJson = ($soloVarios) ? $datosJson : substr($datosJson, 0, -1);

		 $datosJson .=   '] 

		 }';
		
		echo $datosJson;

	}

}

/*=============================================
ACTIVAR TABLA DE PRODUCTOS
=============================================*/ 
$activarProductos = new TablaProductos();
$activarProductos -> mostrarTablaProductos();