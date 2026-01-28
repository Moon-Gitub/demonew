<?php

class ControladorBalanzasFormatos {

	public static function ctrListar($id_empresa = null, $soloActivos = false) {
		return ModeloBalanzasFormatos::mdlListar($id_empresa, $soloActivos);
	}

	public static function ctrMostrarPorId($id) {
		return ModeloBalanzasFormatos::mdlMostrarPorId($id);
	}

	public static function ctrCrear() {
		if (!isset($_POST["nuevoNombre"]) || !isset($_POST["nuevoPrefijo"])) {
			return;
		}

		$id_empresa = isset($_POST["nuevoIdEmpresa"])
			? (int) $_POST["nuevoIdEmpresa"]
			: (isset($_SESSION["empresa"]) ? (int) $_SESSION["empresa"] : 1);

		$datos = [
			"id_empresa"        => $id_empresa,
			"nombre"            => trim($_POST["nuevoNombre"]),
			"prefijo"           => trim($_POST["nuevoPrefijo"]),
			"longitud_min"      => $_POST["nuevoLongitudMin"] ?? '',
			"longitud_max"      => $_POST["nuevoLongitudMax"] ?? '',
			"pos_producto"      => (int) ($_POST["nuevoPosProducto"] ?? 0),
			"longitud_producto" => (int) ($_POST["nuevoLongitudProducto"] ?? 0),
			"modo_cantidad"     => in_array($_POST["nuevoModoCantidad"] ?? 'ninguno', ['peso','unidad','ninguno']) ? $_POST["nuevoModoCantidad"] : 'ninguno',
			"pos_cantidad"      => $_POST["nuevoPosCantidad"] ?? '',
			"longitud_cantidad" => $_POST["nuevoLongitudCantidad"] ?? '',
			"factor_divisor"    => $_POST["nuevoFactorDivisor"] !== '' ? $_POST["nuevoFactorDivisor"] : '1.0000',
			"cantidad_fija"     => $_POST["nuevoCantidadFija"] !== '' ? $_POST["nuevoCantidadFija"] : '1.000',
			"orden"             => (int) ($_POST["nuevoOrden"] ?? 0),
			"activo"            => isset($_POST["nuevoActivo"]) ? 1 : 0,
		];

		$respuesta = ModeloBalanzasFormatos::mdlIngresar($datos);

		if ($respuesta === "ok") {
			echo '<script>swal({ type: "success", title: "Formatos de balanza", text: "Formato guardado correctamente." }).then(function(){ window.location = "balanzas-formatos"; });</script>';
		} else {
			$msg = is_array($respuesta) ? ($respuesta[2] ?? 'Error al guardar') : 'Error al guardar';
			echo '<script>swal({ type: "error", title: "Error", text: "'.addslashes($msg).'" });</script>';
		}
	}

	public static function ctrEditar() {
		if (!isset($_POST["idBalanzaFormato"]) || !isset($_POST["editarNombre"]) || !isset($_POST["editarPrefijo"])) {
			return;
		}

		$datos = [
			"id"                => (int) $_POST["idBalanzaFormato"],
			"nombre"            => trim($_POST["editarNombre"]),
			"prefijo"           => trim($_POST["editarPrefijo"]),
			"longitud_min"      => $_POST["editarLongitudMin"] ?? '',
			"longitud_max"      => $_POST["editarLongitudMax"] ?? '',
			"pos_producto"      => (int) ($_POST["editarPosProducto"] ?? 0),
			"longitud_producto" => (int) ($_POST["editarLongitudProducto"] ?? 0),
			"modo_cantidad"     => in_array($_POST["editarModoCantidad"] ?? 'ninguno', ['peso','unidad','ninguno']) ? $_POST["editarModoCantidad"] : 'ninguno',
			"pos_cantidad"      => $_POST["editarPosCantidad"] ?? '',
			"longitud_cantidad" => $_POST["editarLongitudCantidad"] ?? '',
			"factor_divisor"    => $_POST["editarFactorDivisor"] !== '' ? $_POST["editarFactorDivisor"] : '1.0000',
			"cantidad_fija"     => $_POST["editarCantidadFija"] !== '' ? $_POST["editarCantidadFija"] : '1.000',
			"orden"             => (int) ($_POST["editarOrden"] ?? 0),
			"activo"            => isset($_POST["editarActivo"]) ? 1 : 0,
		];

		$respuesta = ModeloBalanzasFormatos::mdlEditar($datos);

		if ($respuesta === "ok") {
			echo '<script>swal({ type: "success", title: "Formatos de balanza", text: "Formato actualizado correctamente." }).then(function(){ window.location = "balanzas-formatos"; });</script>';
		} else {
			$msg = is_array($respuesta) ? ($respuesta[2] ?? 'Error al actualizar') : 'Error al actualizar';
			echo '<script>swal({ type: "error", title: "Error", text: "'.addslashes($msg).'" });</script>';
		}
	}

	public static function ctrEliminar() {
		if (!isset($_GET["idBalanzaFormato"])) {
			return;
		}
		$id = (int) $_GET["idBalanzaFormato"];
		$respuesta = ModeloBalanzasFormatos::mdlEliminar($id);
		if ($respuesta === "ok") {
			echo '<script>swal({ type: "success", title: "Formatos de balanza", text: "Formato desactivado correctamente." }).then(function(){ window.location = "balanzas-formatos"; });</script>';
		} else {
			echo '<script>swal({ type: "error", title: "Error", text: "No se pudo desactivar el formato." });</script>';
		}
	}
}

