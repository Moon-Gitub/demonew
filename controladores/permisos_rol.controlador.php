<?php

class ControladorPermisosRol {

	/**
	 * Listar pantallas agrupadas para el panel
	 */
	static public function ctrListarPantallasAgrupadas() {
		return ModeloPermisosRol::mdlListarPantallasAgrupadas();
	}

	/**
	 * Listar roles disponibles
	 */
	static public function ctrListarRoles() {
		return ModeloPermisosRol::mdlListarRoles();
	}

	/**
	 * Obtener IDs de pantallas permitidas para un rol
	 */
	static public function ctrIdsPermitidosPorRol($rol) {
		return ModeloPermisosRol::mdlIdsPermitidosPorRol($rol);
	}

	/**
	 * Guardar permisos de un rol
	 */
	static public function ctrGuardarPermisosRol() {
		if (!isset($_POST["rol"]) || !isset($_POST["ids_pantallas"])) {
			return "error";
		}
		$rol = trim($_POST["rol"]);
		$ids = is_array($_POST["ids_pantallas"]) ? $_POST["ids_pantallas"] : [];
		if ($rol === "") return "error";
		return ModeloPermisosRol::mdlGuardarPermisosRol($rol, $ids);
	}
}
