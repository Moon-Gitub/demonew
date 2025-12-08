<?php
// ✅ Seguridad AJAX
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar();

require_once "../controladores/integraciones.controlador.php";
require_once "../modelos/integraciones.modelo.php";

class AjaxIntegraciones{

	public $idIntegracion;

	public function ajaxEditarIntegracion(){
		try {
			$item = "id";
			$valor = $this->idIntegracion;
			
			if(empty($valor)){
				echo json_encode(['error' => 'ID de integración no válido']);
				return;
			}
			
			$respuesta = ControladorIntegraciones::ctrMostrarIntegraciones($item, $valor);
			
			// El modelo ahora devuelve fetch() cuando busca por ID, así que es un solo registro
			if($respuesta && is_array($respuesta)){
				echo json_encode($respuesta);
			} else {
				echo json_encode(['error' => 'No se encontró la integración']);
			}
		} catch (Exception $e) {
			error_log("Error en ajaxEditarIntegracion: " . $e->getMessage());
			echo json_encode(['error' => 'Error al obtener los datos: ' . $e->getMessage()]);
		}
	}

}

//EDITAR INTEGRACIÓN
if(isset($_POST["idIntegracion"])){
	$integracion = new AjaxIntegraciones();
	$integracion -> idIntegracion = $_POST["idIntegracion"];
	$integracion -> ajaxEditarIntegracion();
} else {
	echo json_encode(['error' => 'No se recibió el ID de la integración']);
}

