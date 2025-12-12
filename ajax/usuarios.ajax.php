<?php
// ✅ Seguridad AJAX - Solo verificación de sesión (sin CSRF ni verificación de header AJAX)
require_once dirname(__DIR__) . "/extensiones/vendor/autoload.php";

// Cargar variables de entorno
$raiz = dirname(__DIR__);
if (file_exists($raiz . "/.env")) {
    $dotenv = Dotenv\Dotenv::createImmutable($raiz);
    $dotenv->safeLoad();
}

// Verificar solo sesión (sin CSRF ni header AJAX)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok") {
    http_response_code(401);
    echo json_encode([
        'error' => true,
        'mensaje' => 'No autorizado'
    ]);
    exit;
}

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

	$editar = new AjaxUsuarios();
	$editar -> idUsuario = $_POST["idUsuario"];
	$editar -> ajaxEditarUsuario();

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