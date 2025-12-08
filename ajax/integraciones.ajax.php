<?php
// ✅ Seguridad AJAX
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar();

require_once "../controladores/integraciones.controlador.php";
require_once "../modelos/integraciones.modelo.php";

class AjaxIntegraciones{

	public $idIntegracion;

	public function ajaxEditarIntegracion(){
		$item = "id";
		$valor = $this->idIntegracion;
		
		// Log para debugging
		error_log("AJAX Editar Integración - ID recibido: " . $valor);
		
		$respuesta = ControladorIntegraciones::ctrMostrarIntegraciones($item, $valor);
		
		error_log("AJAX Editar Integración - Respuesta: " . print_r($respuesta, true));
		
		if($respuesta && count($respuesta) > 0){
			$resultado = $respuesta[0];
			error_log("AJAX Editar Integración - Datos a enviar: " . json_encode($resultado));
			echo json_encode($resultado);
		} else {
			error_log("AJAX Editar Integración - No se encontró la integración");
			echo json_encode(['error' => 'No se encontró la integración']);
		}
	}

}

//EDITAR INTEGRACIÓN
if(isset($_POST["idIntegracion"])){
	$integracion = new AjaxIntegraciones();
	$integracion -> idIntegracion = $_POST["idIntegracion"];
	$integracion -> ajaxEditarIntegracion();
}

