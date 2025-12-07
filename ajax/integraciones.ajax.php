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
		$respuesta = ControladorIntegraciones::ctrMostrarIntegraciones($item, $valor);
		echo json_encode($respuesta[0]);
	}

}

//EDITAR INTEGRACIÓN
if(isset($_POST["idIntegracion"])){
	$integracion = new AjaxIntegraciones();
	$integracion -> idIntegracion = $_POST["idIntegracion"];
	$integracion -> ajaxEditarIntegracion();
}

