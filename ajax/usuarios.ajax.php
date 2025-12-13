<?php
// ✅ Seguridad AJAX
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar();

require_once "../controladores/usuarios.controlador.php";
require_once "../modelos/usuarios.modelo.php";

class AjaxUsuarios{

	/*=============================================
	EDITAR USUARIO
	=============================================*/	

	public $idUsuario;

	public function ajaxEditarUsuario(){

		try {
			$item = "id";
			$valor = $this->idUsuario;

			// Log para debugging
			$logFile = __DIR__ . "/error_log";
			error_log("[" . date('Y-m-d H:i:s') . "] ajaxEditarUsuario - ID recibido: " . $valor . "\n", 3, $logFile);

			// Validar que el ID sea numérico
			if(!is_numeric($valor) || $valor <= 0){
				error_log("[" . date('Y-m-d H:i:s') . "] ajaxEditarUsuario - ID inválido: " . $valor . "\n", 3, $logFile);
				echo json_encode(array("error" => "ID de usuario inválido"));
				return;
			}

			$respuesta = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

			if($respuesta === false || $respuesta === null || empty($respuesta)){
				error_log("[" . date('Y-m-d H:i:s') . "] ajaxEditarUsuario - Usuario no encontrado. ID: " . $valor . "\n", 3, $logFile);
				echo json_encode(array("error" => "Usuario no encontrado"));
				return;
			}

			error_log("[" . date('Y-m-d H:i:s') . "] ajaxEditarUsuario - Usuario encontrado: " . ($respuesta["usuario"] ?? "N/A") . "\n", 3, $logFile);
			echo json_encode($respuesta);

		} catch (Exception $e) {
			$logFile = __DIR__ . "/error_log";
			error_log("[" . date('Y-m-d H:i:s') . "] ajaxEditarUsuario - Excepción: " . $e->getMessage() . "\n", 3, $logFile);
			error_log("[" . date('Y-m-d H:i:s') . "] Stack trace: " . $e->getTraceAsString() . "\n", 3, $logFile);
			echo json_encode(array("error" => "Error al obtener datos del usuario: " . $e->getMessage()));
		} catch (Error $e) {
			$logFile = __DIR__ . "/error_log";
			error_log("[" . date('Y-m-d H:i:s') . "] ajaxEditarUsuario - Error fatal: " . $e->getMessage() . "\n", 3, $logFile);
			error_log("[" . date('Y-m-d H:i:s') . "] Stack trace: " . $e->getTraceAsString() . "\n", 3, $logFile);
			echo json_encode(array("error" => "Error fatal: " . $e->getMessage()));
		}

	}

	/*=============================================
	ACTIVAR USUARIO
	=============================================*/	

	public $activarUsuario;
	public $activarId;


	public function ajaxActivarUsuario(){

		$tabla = "usuarios";

		$item1 = "estado";
		$valor1 = $this->activarUsuario;

		$item2 = "id";
		$valor2 = $this->activarId;

		$respuesta = ModeloUsuarios::mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2);

	}

	/*=============================================
	VALIDAR NO REPETIR USUARIO
	=============================================*/	

	public $validarUsuario;

	public function ajaxValidarUsuario(){

		$item = "usuario";
		$valor = $this->validarUsuario;

		$respuesta = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

		echo json_encode($respuesta);

	}
}

/*=============================================
EDITAR USUARIO
=============================================*/
if(isset($_POST["idUsuario"])){

	$logFile = __DIR__ . "/error_log";
	error_log("[" . date('Y-m-d H:i:s') . "] ===== PETICIÓN RECIBIDA =====\n", 3, $logFile);
	error_log("[" . date('Y-m-d H:i:s') . "] POST idUsuario: " . ($_POST["idUsuario"] ?? "NO DEFINIDO") . "\n", 3, $logFile);
	error_log("[" . date('Y-m-d H:i:s') . "] SESSION iniciarSesion: " . (isset($_SESSION["iniciarSesion"]) ? $_SESSION["iniciarSesion"] : "NO DEFINIDO") . "\n", 3, $logFile);

	$editar = new AjaxUsuarios();
	$editar -> idUsuario = $_POST["idUsuario"];
	$editar -> ajaxEditarUsuario();

} else {
	$logFile = __DIR__ . "/error_log";
	error_log("[" . date('Y-m-d H:i:s') . "] ERROR: No se recibió idUsuario en POST\n", 3, $logFile);
	error_log("[" . date('Y-m-d H:i:s') . "] POST completo: " . print_r($_POST, true) . "\n", 3, $logFile);
	echo json_encode(array("error" => "No se recibió el ID del usuario"));
}

/*=============================================
ACTIVAR USUARIO
=============================================*/	

if(isset($_POST["activarUsuario"])){

	$activarUsuario = new AjaxUsuarios();
	$activarUsuario -> activarUsuario = $_POST["activarUsuario"];
	$activarUsuario -> activarId = $_POST["activarId"];
	$activarUsuario -> ajaxActivarUsuario();

}

/*=============================================
VALIDAR NO REPETIR USUARIO
=============================================*/

if(isset( $_POST["validarUsuario"])){

	$valUsuario = new AjaxUsuarios();
	$valUsuario -> validarUsuario = $_POST["validarUsuario"];
	$valUsuario -> ajaxValidarUsuario();

}