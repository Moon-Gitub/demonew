<?php

class ControladorMediosPago{

	/*=============================================
	CREAR MEDIO DE PAGO
	=============================================*/
	static public function ctrCrearMedioPago(){
		if(isset($_POST["nuevoCodigo"])){
			// Verificar que el código no exista
			$existe = ModeloMediosPago::mdlVerificarCodigo($_POST["nuevoCodigo"]);
			if($existe){
				echo'<script>
					swal({
						type: "error",
						title: "¡Error!",
						text: "El código ya existe. Por favor, use otro código.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "medios-pago";
						}
					})
				</script>';
				return;
			}

			$tabla = "medios_pago";
			$datos = array(
				"codigo" => strtoupper($_POST["nuevoCodigo"]),
				"nombre" => $_POST["nuevoNombre"],
				"descripcion" => $_POST["nuevaDescripcion"] ?? "",
				"activo" => isset($_POST["nuevoActivo"]) ? 1 : 0,
				"requiere_codigo" => isset($_POST["nuevoRequiereCodigo"]) ? 1 : 0,
				"requiere_banco" => isset($_POST["nuevoRequiereBanco"]) ? 1 : 0,
				"requiere_numero" => isset($_POST["nuevoRequiereNumero"]) ? 1 : 0,
				"requiere_fecha" => isset($_POST["nuevoRequiereFecha"]) ? 1 : 0,
				"orden" => $_POST["nuevoOrden"] ?? 0
			);

			$respuesta = ModeloMediosPago::mdlIngresarMedioPago($tabla, $datos);

			if($respuesta == "ok"){
				echo'<script>
					swal({
						type: "success",
						title: "Medios de Pago",
						text: "El medio de pago ha sido guardado correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "medios-pago";
						}
					})
				</script>';
			}
		}
	}

	/*=============================================
	MOSTRAR MEDIOS DE PAGO
	=============================================*/
	static public function ctrMostrarMediosPago($item, $valor){
		$tabla = "medios_pago";
		$respuesta = ModeloMediosPago::mdlMostrarMediosPago($tabla, $item, $valor);
		return $respuesta;
	}

	/*=============================================
	EDITAR MEDIO DE PAGO
	=============================================*/
	static public function ctrEditarMedioPago(){
		if(isset($_POST["editarCodigo"])){
			// Verificar que el código no exista en otro registro
			$existe = ModeloMediosPago::mdlVerificarCodigo($_POST["editarCodigo"], $_POST["idMedioPago"]);
			if($existe){
				echo'<script>
					swal({
						type: "error",
						title: "¡Error!",
						text: "El código ya existe en otro medio de pago. Por favor, use otro código.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "medios-pago";
						}
					})
				</script>';
				return;
			}

			$tabla = "medios_pago";
			$datos = array(
				"id" => $_POST["idMedioPago"],
				"codigo" => strtoupper($_POST["editarCodigo"]),
				"nombre" => $_POST["editarNombre"],
				"descripcion" => $_POST["editarDescripcion"] ?? "",
				"activo" => isset($_POST["editarActivo"]) ? 1 : 0,
				"requiere_codigo" => isset($_POST["editarRequiereCodigo"]) ? 1 : 0,
				"requiere_banco" => isset($_POST["editarRequiereBanco"]) ? 1 : 0,
				"requiere_numero" => isset($_POST["editarRequiereNumero"]) ? 1 : 0,
				"requiere_fecha" => isset($_POST["editarRequiereFecha"]) ? 1 : 0,
				"orden" => $_POST["editarOrden"] ?? 0
			);

			$respuesta = ModeloMediosPago::mdlEditarMedioPago($tabla, $datos);

			if($respuesta == "ok"){
				echo'<script>
					swal({
						type: "success",
						title: "Medios de Pago",
						text: "El medio de pago ha sido actualizado correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "medios-pago";
						}
					})
				</script>';
			}
		}
	}

	/*=============================================
	BORRAR MEDIO DE PAGO
	=============================================*/
	static public function ctrBorrarMedioPago(){
		if(isset($_GET["idMedioPago"])){
			$tabla = "medios_pago";
			$datos = $_GET["idMedioPago"];

			$respuesta = ModeloMediosPago::mdlBorrarMedioPago($tabla, $datos);

			if($respuesta == "ok"){
				echo'<script>
					swal({
						type: "success",
						title: "Medios de Pago",
						text: "El medio de pago ha sido eliminado correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "medios-pago";
						}
					})
				</script>';
			}
		}
	}
}
