<?php

require_once "../controladores/presupuestos.controlador.php";
require_once "../modelos/presupuestos.modelo.php";

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/clientes.controlador.php";
require_once "../modelos/clientes.modelo.php";

require_once "../controladores/empresa.controlador.php";
require_once "../modelos/empresa.modelo.php";

class AjaxPresupuestos{

	/*=============================================
	COBRAR VENTA
	=============================================*/	
	public $postVentaCaja;

	public function ajaxInsertarPresupuesto(){

		$respuesta = ControladorPresupuestos::ctrCrearPresupuestoCaja($this->postVentaCaja);

		echo json_encode($respuesta);

	}

}

/*=============================================
INSERTAR PRESUPUESTO CAJA
=============================================*/	
if(isset($_POST["nuevoPresupuestoCaja"])){

	$venta = new AjaxPresupuestos();
	$venta -> postVentaCaja = $_POST;
	$venta -> ajaxInsertarPresupuesto();

}