<?php

class ControladorListasPrecio {

	static public function ctrListar($id_empresa = null, $soloActivas = false) {
		return ModeloListasPrecio::mdlListar($id_empresa, $soloActivas);
	}

	static public function ctrMostrarPorId($id) {
		return ModeloListasPrecio::mdlMostrarPorId($id);
	}

	static public function ctrCrearListaPrecio() {
		if (!isset($_POST["nuevoCodigo"]) || !isset($_POST["nuevoNombre"])) {
			return;
		}
		$id_empresa = isset($_POST["nuevoIdEmpresa"]) ? (int) $_POST["nuevoIdEmpresa"] : (isset($_SESSION["empresa"]) ? (int) $_SESSION["empresa"] : 1);
		$codigo = trim(preg_replace('/[^a-z0-9_]/i', '_', $_POST["nuevoCodigo"]));
		if ($codigo === '') {
			echo '<script>swal({ type: "error", title: "Error", text: "Código inválido." });</script>';
			return;
		}
		$valor_descuento = isset($_POST["nuevoValorDescuento"]) ? floatval($_POST["nuevoValorDescuento"]) : 0;
		$tipo_descuento = ($_POST["nuevoTipoDescuento"] ?? 'ninguno') === 'porcentaje' ? 'porcentaje' : 'ninguno';
		if ($tipo_descuento === 'ninguno') {
			$valor_descuento = 0;
		}
		$datos = [
			"id_empresa"     => $id_empresa,
			"codigo"         => $codigo,
			"nombre"         => $_POST["nuevoNombre"],
			"base_precio"    => $_POST["nuevoBasePrecio"] ?? 'precio_venta',
			"tipo_descuento" => $tipo_descuento,
			"valor_descuento"=> $valor_descuento,
			"orden"          => (int) ($_POST["nuevoOrden"] ?? 0),
			"activo"         => isset($_POST["nuevoActivo"]) ? 1 : 0
		];
		$respuesta = ModeloListasPrecio::mdlIngresar($datos);
		if ($respuesta === "ok") {
			echo '<script>swal({ type: "success", title: "Listas de precio", text: "Lista guardada correctamente." }).then(function(){ window.location = "listas-precio"; });</script>';
		} else {
			$msg = is_array($respuesta) ? ($respuesta[2] ?? 'Error') : 'Error al guardar';
			echo '<script>swal({ type: "error", title: "Error", text: "' . addslashes($msg) . '" });</script>';
		}
	}

	static public function ctrEditarListaPrecio() {
		if (!isset($_POST["editarCodigo"]) || !isset($_POST["editarNombre"]) || !isset($_POST["idListaPrecio"])) {
			return;
		}
		$codigo = trim(preg_replace('/[^a-z0-9_]/i', '_', $_POST["editarCodigo"]));
		if ($codigo === '') {
			echo '<script>swal({ type: "error", title: "Error", text: "Código inválido." });</script>';
			return;
		}
		$valor_descuento = isset($_POST["editarValorDescuento"]) ? floatval($_POST["editarValorDescuento"]) : 0;
		$tipo_descuento = ($_POST["editarTipoDescuento"] ?? 'ninguno') === 'porcentaje' ? 'porcentaje' : 'ninguno';
		if ($tipo_descuento === 'ninguno') {
			$valor_descuento = 0;
		}
		$datos = [
			"id"             => (int) $_POST["idListaPrecio"],
			"codigo"         => $codigo,
			"nombre"         => $_POST["editarNombre"],
			"base_precio"    => $_POST["editarBasePrecio"] ?? 'precio_venta',
			"tipo_descuento" => $tipo_descuento,
			"valor_descuento"=> $valor_descuento,
			"orden"          => (int) ($_POST["editarOrden"] ?? 0),
			"activo"         => isset($_POST["editarActivo"]) ? 1 : 0
		];
		$respuesta = ModeloListasPrecio::mdlEditar($datos);
		if ($respuesta === "ok") {
			echo '<script>swal({ type: "success", title: "Listas de precio", text: "Lista actualizada correctamente." }).then(function(){ window.location = "listas-precio"; });</script>';
		} else {
			$msg = is_array($respuesta) ? ($respuesta[2] ?? 'Error') : 'Error al actualizar';
			echo '<script>swal({ type: "error", title: "Error", text: "' . addslashes($msg) . '" });</script>';
		}
	}

	static public function ctrEliminarListaPrecio() {
		if (!isset($_GET["idListaPrecio"])) {
			return;
		}
		$respuesta = ModeloListasPrecio::mdlEliminar((int) $_GET["idListaPrecio"]);
		if ($respuesta === "ok") {
			echo '<script>swal({ type: "success", title: "Listas de precio", text: "Lista desactivada correctamente." }).then(function(){ window.location = "listas-precio"; });</script>';
		} else {
			echo '<script>swal({ type: "error", title: "Error", text: "No se pudo desactivar." });</script>';
		}
	}
}
