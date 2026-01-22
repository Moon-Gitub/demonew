<?php
// ✅ Seguridad AJAX
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar();

require_once "../controladores/medios_pago.controlador.php";
require_once "../modelos/medios_pago.modelo.php";

class AjaxMediosPago{

	/*=============================================
	EDITAR MEDIO DE PAGO
	=============================================*/	

	public $idMedioPago;

	public function ajaxEditarMedioPago(){

		$item = "id";
		$valor = $this->idMedioPago;

		$respuesta = ControladorMediosPago::ctrMostrarMediosPago($item, $valor);

		echo json_encode($respuesta);

	}
}

/*=============================================
EDITAR MEDIO DE PAGO
=============================================*/	
if(isset($_POST["idMedioPago"])){

	$medioPago = new AjaxMediosPago();
	$medioPago -> idMedioPago = $_POST["idMedioPago"];
	$medioPago -> ajaxEditarMedioPago();
}
