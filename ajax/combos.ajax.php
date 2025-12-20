<?php
// ✅ Seguridad AJAX
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar();

require_once "../controladores/combos.controlador.php";
require_once "../modelos/combos.modelo.php";
require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

class AjaxCombos{

	public $idCombo;
	public $idProducto;

	/*=============================================
	EDITAR COMBO
	=============================================*/	
	public function ajaxEditarCombo(){
		$item = "id";
		$valor = $this->idCombo;

		$respuesta = ControladorCombos::ctrMostrarCombos($item, $valor);
		
		// Obtener productos componentes
		if($respuesta){
			$productosCombo = ControladorCombos::ctrMostrarProductosCombo($this->idCombo);
			$respuesta["productos"] = $productosCombo;
		}

		echo json_encode($respuesta);
	}

	/*=============================================
	VERIFICAR SI PRODUCTO ES COMBO
	=============================================*/	
	public function ajaxEsCombo(){
		$respuesta = ControladorCombos::ctrEsCombo($this->idProducto);
		echo json_encode($respuesta ? $respuesta : false);
	}

	/*=============================================
	OBTENER PRODUCTOS COMPONENTES DE UN COMBO
	=============================================*/	
	public function ajaxObtenerProductosCombo(){
		$productos = ControladorCombos::ctrMostrarProductosCombo($this->idCombo);
		echo json_encode($productos);
	}

	/*=============================================
	OBTENER PRODUCTOS COMPONENTES POR ID PRODUCTO
	=============================================*/	
	public function ajaxObtenerProductosComboPorProducto(){
		// Primero obtener el combo por id_producto
		$combo = ControladorCombos::ctrEsCombo($this->idProducto);
		
		if($combo){
			$productos = ControladorCombos::ctrMostrarProductosCombo($combo["id"]);
			echo json_encode(array(
				"es_combo" => true,
				"combo" => $combo,
				"productos" => $productos
			));
		}else{
			echo json_encode(array(
				"es_combo" => false,
				"combo" => null,
				"productos" => array()
			));
		}
	}
}

/*=============================================
EDITAR COMBO
=============================================*/	
if(isset($_POST["idCombo"])){
	$combo = new AjaxCombos();
	$combo -> idCombo = $_POST["idCombo"];
	$combo -> ajaxEditarCombo();
}

/*=============================================
VERIFICAR SI PRODUCTO ES COMBO
=============================================*/	
if(isset($_POST["idProductoCombo"])){
	$combo = new AjaxCombos();
	$combo -> idProducto = $_POST["idProductoCombo"];
	$combo -> ajaxEsCombo();
}

/*=============================================
OBTENER PRODUCTOS COMPONENTES
=============================================*/	
if(isset($_GET["idCombo"])){
	$combo = new AjaxCombos();
	$combo -> idCombo = $_GET["idCombo"];
	$combo -> ajaxObtenerProductosCombo();
}

/*=============================================
OBTENER PRODUCTOS COMPONENTES POR ID PRODUCTO
=============================================*/	
if(isset($_GET["idProducto"])){
	$combo = new AjaxCombos();
	$combo -> idProducto = $_GET["idProducto"];
	$combo -> ajaxObtenerProductosComboPorProducto();
}
