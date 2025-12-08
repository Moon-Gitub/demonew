<?php
// Cargar variables de entorno desde .env PRIMERO (si existe y si Dotenv está instalado)
if (file_exists(__DIR__ . '/../.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Cargar helpers (incluye función env() para leer variables)
require_once "../helpers.php";

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
			
			// El modelo devuelve un objeto único cuando busca por ID, o null si no encuentra
			if($respuesta !== null && is_array($respuesta)){
				echo json_encode($respuesta);
			} else {
				echo json_encode(['error' => 'No se encontró la integración con ID: ' . $valor]);
			}
		} catch (Exception $e) {
			error_log("Error en ajaxEditarIntegracion: " . $e->getMessage());
			error_log("Stack trace: " . $e->getTraceAsString());
			echo json_encode(['error' => 'Error al obtener los datos: ' . $e->getMessage()]);
		} catch (Error $e) {
			error_log("Error fatal en ajaxEditarIntegracion: " . $e->getMessage());
			error_log("Stack trace: " . $e->getTraceAsString());
			echo json_encode(['error' => 'Error fatal: ' . $e->getMessage()]);
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

