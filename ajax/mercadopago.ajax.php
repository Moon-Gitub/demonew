<?php
// ✅ Seguridad AJAX
require_once "seguridad.ajax.php";

// Para peticiones GET (verificar pago) no requerir CSRF, solo sesión y AJAX
// Para peticiones POST (crear preferencia) sí requerir CSRF
if (isset($_GET["verificarPago"])) {
    SeguridadAjax::inicializar(false); // false = no verificar CSRF para GET
} else {
    SeguridadAjax::inicializar(); // Verificar CSRF para POST
}

require_once "../controladores/mercadopago.controlador.php";

/*=============================================
CREAR PREFERENCIA DE PAGO PARA VENTA
=============================================*/
if(isset($_POST["crearPreferenciaVenta"])){
	$monto = isset($_POST["monto"]) ? floatval($_POST["monto"]) : 0;
	$descripcion = isset($_POST["descripcion"]) ? $_POST["descripcion"] : "Venta POS";
	$externalReference = isset($_POST["external_reference"]) ? $_POST["external_reference"] : null;

	if($monto <= 0){
		http_response_code(400);
		echo json_encode(array("error" => true, "mensaje" => "El monto debe ser mayor a 0"));
		exit;
	}

	$respuesta = ControladorMercadoPago::ctrCrearPreferenciaVenta($monto, $descripcion, $externalReference);
	echo json_encode($respuesta);
	exit;
}

/*=============================================
VERIFICAR ESTADO DE PAGO
=============================================*/
if(isset($_GET["verificarPago"]) || isset($_POST["verificarPago"])){
	$preferenceId = isset($_GET["preference_id"]) ? $_GET["preference_id"] : (isset($_POST["preference_id"]) ? $_POST["preference_id"] : null);

	if(!$preferenceId){
		http_response_code(400);
		echo json_encode(array("error" => true, "mensaje" => "Preference ID requerido"));
		exit;
	}

	$respuesta = ControladorMercadoPago::ctrVerificarEstadoPago($preferenceId);
	echo json_encode($respuesta);
	exit;
}

/*=============================================
OBTENER O CREAR POS ESTÁTICO (QR ESTÁTICO)
=============================================*/
if(isset($_GET["obtenerQREstatico"]) || isset($_POST["obtenerQREstatico"])){
	$respuesta = ControladorMercadoPago::ctrObtenerOcrearPOSEstatico();
	echo json_encode($respuesta);
	exit;
}

/*=============================================
VERIFICAR PAGO POR EXTERNAL REFERENCE (QR ESTÁTICO)
=============================================*/
if(isset($_GET["verificarPagoPorReference"]) || isset($_POST["verificarPagoPorReference"])){
	$externalReference = isset($_GET["external_reference"]) ? $_GET["external_reference"] : (isset($_POST["external_reference"]) ? $_POST["external_reference"] : null);

	if(!$externalReference){
		http_response_code(400);
		echo json_encode(array("error" => true, "mensaje" => "External Reference requerido"));
		exit;
	}

	$respuesta = ControladorMercadoPago::ctrVerificarPagoPorExternalReference($externalReference);
	echo json_encode($respuesta);
	exit;
}
