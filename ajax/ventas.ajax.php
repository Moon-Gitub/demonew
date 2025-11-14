<?php

require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/clientes.controlador.php";
require_once "../modelos/clientes.modelo.php";

require_once "../controladores/clientes_cta_cte.controlador.php";
require_once "../modelos/clientes_cta_cte.modelo.php";

require_once "../controladores/cajas.controlador.php";
require_once "../modelos/cajas.modelo.php";

require_once "../controladores/empresa.controlador.php";
require_once "../modelos/empresa.modelo.php";

require_once "../controladores/facturacion/wsaa.class.php";
require_once "../controladores/facturacion/wsfe.class.php";

class AjaxVentas{

	/*=============================================
	COBRAR VENTA
	=============================================*/	

	public $idVenta;
	public $postVentaCaja;	

	public function ajaxEditarVenta(){

		$item = "id";
		$valor = $this->idVenta;

		$respuesta = ControladorVentas::ctrMostrarVentas($item, $valor);

		echo json_encode($respuesta);

	}

	public function ajaxInsertarVenta(){

		$respuesta = ControladorVentas::ctrCrearVentaCaja($this->postVentaCaja);

		echo json_encode($respuesta);

	}

	public function ajaxMostrarVentaConCliente($idVenta){

		$respuesta = ControladorVentas::ctrMostrarVentaConCliente($idVenta);

		echo json_encode($respuesta);

	}
}

/*=============================================
COBRAR VENTA
=============================================*/	

if(isset($_POST["idVenta"])){

	$venta = new AjaxVentas();
	$venta -> idVenta = $_POST["idVenta"];
	$venta -> ajaxEditarVenta();

}

/*=============================================
INSERTAR VENTA CAJA
=============================================*/	

if(isset($_POST["nuevaVentaCaja"])){

	$venta = new AjaxVentas();
	$venta -> postVentaCaja = $_POST;
	$venta -> ajaxInsertarVenta();

}

/*=============================================
COBRAR VENTA
=============================================*/	

if(isset($_POST["idVentaConCliente"])){

	$venta = new AjaxVentas();
	$venta -> ajaxMostrarVentaConCliente($_POST["idVentaConCliente"]);

}
