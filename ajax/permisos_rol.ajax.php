<?php
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar(false);

require_once dirname(__DIR__) . "/controladores/permisos_rol.controlador.php";
require_once dirname(__DIR__) . "/modelos/permisos_rol.modelo.php";

$accion = $_POST["accion"] ?? $_GET["accion"] ?? "";

if ($accion === "listarPantallas") {
	$agrupado = ControladorPermisosRol::ctrListarPantallasAgrupadas();
	echo json_encode(["ok" => true, "pantallas" => $agrupado]);
	exit;
}

if ($accion === "permisosPorRol") {
	$rol = trim($_POST["rol"] ?? $_GET["rol"] ?? "");
	if ($rol === "") {
		echo json_encode(["ok" => false, "mensaje" => "Rol requerido"]);
		exit;
	}
	$ids = ControladorPermisosRol::ctrIdsPermitidosPorRol($rol);
	echo json_encode(["ok" => true, "ids_pantallas" => $ids]);
	exit;
}

if ($accion === "guardarPermisos") {
	SeguridadAjax::verificarCSRF();
	$rol = trim($_POST["rol"] ?? "");
	$ids = isset($_POST["ids_pantallas"]) && is_array($_POST["ids_pantallas"])
		? array_map('intval', array_filter($_POST["ids_pantallas"]))
		: [];
	if ($rol === "") {
		echo json_encode(["ok" => false, "mensaje" => "Rol requerido"]);
		exit;
	}
	$resultado = ControladorPermisosRol::ctrGuardarPermisosRol();
	if ($resultado === "ok") {
		echo json_encode(["ok" => true, "mensaje" => "Permisos guardados correctamente."]);
	} else {
		echo json_encode(["ok" => false, "mensaje" => "Error al guardar permisos."]);
	}
	exit;
}

if ($accion === "listarRoles") {
	$roles = ControladorPermisosRol::ctrListarRoles();
	echo json_encode(["ok" => true, "roles" => $roles]);
	exit;
}

echo json_encode(["ok" => false, "mensaje" => "Acción no válida"]);
