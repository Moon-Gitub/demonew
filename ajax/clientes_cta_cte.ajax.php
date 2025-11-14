<?php

require_once "../controladores/clientes_cta_cte.controlador.php";
require_once "../modelos/clientes_cta_cte.modelo.php";

require_once "../modelos/empresa.modelo.php";

class AjaxClientesCtaCte{

	/*=============================================
	MOSTRAR LISTA ENTREGAS POR VENTA
	=============================================*/	
	public $idVenta;
	public function ajaxListarEntregasXVenta(){
		$item = "id_venta";
		$valor = $this->idVenta;
		$respuesta = ControladorClientesCtaCte::ctrMostrarEntregasXVenta($item, $valor);
		echo json_encode($respuesta);
	}

    public function ajaxEnviarMail($arr){
		$respuesta = ControladorClientesCtaCte::ctrEnviarMailInformeSaldo($arr);
		echo json_encode($respuesta);
	}
	
	public function ajaxSaldoTotalXCliente($id){
		$respuesta = ControladorClientesCtaCte::ctrMostrarSaldoTotalXCliente($id);
		echo json_encode($respuesta);
	}
}

/*=============================================
LISTAR CLIENTES
=============================================*/	
if(isset($_POST["idVentaCtaCte"])){
	$cliente = new AjaxClientesCtaCte();
	$cliente -> idVenta = $_POST["idVentaCtaCte"];
	$cliente -> ajaxListarEntregasXVenta();
}

/*=============================================
MAIL A CLIENTES
=============================================*/	
if(isset($_POST["mailCliente"])){
	$cliente = new AjaxClientesCtaCte();
	$cliente -> ajaxEnviarMail($_POST);
}

/*=============================================
SALDO CLIENTE
=============================================*/	
if(isset($_POST["idClienteCtaCte"])){
	$cliente = new AjaxClientesCtaCte();
	$cliente -> ajaxSaldoTotalXCliente($_POST["idClienteCtaCte"]);
}