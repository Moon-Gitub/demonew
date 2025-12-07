<?php

require_once "../modelos/integraciones.modelo.php";

class ControladorIntegraciones{

	/*=============================================
	MOSTRAR INTEGRACIONES
	=============================================*/
	static public function ctrMostrarIntegraciones($item, $valor){
		$respuesta = ModeloIntegraciones::mdlMostrarIntegraciones($item, $valor);
		return $respuesta;
	}

	/*=============================================
	CREAR INTEGRACIÓN
	=============================================*/
	public function ctrCrearIntegracion(){

		if(isset($_POST["nuevoNombre"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoNombre"])){

				$datos = array(
					"nombre" => $_POST["nuevoNombre"],
					"tipo" => $_POST["nuevoTipo"],
					"webhook_url" => $_POST["nuevoWebhookUrl"] ?? '',
					"api_key" => $_POST["nuevoApiKey"] ?? '',
					"descripcion" => $_POST["nuevaDescripcion"] ?? '',
					"activo" => isset($_POST["nuevoActivo"]) ? 1 : 0
				);

				$respuesta = ModeloIntegraciones::mdlCrearIntegracion("integraciones", $datos);

				if($respuesta == "ok"){

					echo'<script>

					swal({
						type: "success",
						title: "¡La integración ha sido guardada correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(function(result){
							if (result.value) {
								window.location = "integraciones";
							}
						})

					</script>';

				}

			}else{

				echo'<script>

				swal({
					type: "error",
					title: "¡El nombre no puede ir vacío o llevar caracteres especiales!",
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "integraciones";
						}
					})

				</script>';

			}

		}

	}

	/*=============================================
	EDITAR INTEGRACIÓN
	=============================================*/
	public function ctrEditarIntegracion(){

		if(isset($_POST["editarNombre"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarNombre"])){

				$datos = array(
					"id" => $_POST["idIntegracion"],
					"nombre" => $_POST["editarNombre"],
					"tipo" => $_POST["editarTipo"],
					"webhook_url" => $_POST["editarWebhookUrl"] ?? '',
					"api_key" => $_POST["editarApiKey"] ?? '',
					"descripcion" => $_POST["editarDescripcion"] ?? '',
					"activo" => isset($_POST["editarActivo"]) ? 1 : 0
				);

				$respuesta = ModeloIntegraciones::mdlEditarIntegracion("integraciones", $datos);

				if($respuesta == "ok"){

					echo'<script>

					swal({
						type: "success",
						title: "¡La integración ha sido actualizada correctamente!",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(function(result){
							if (result.value) {
								window.location = "integraciones";
							}
						})

					</script>';

				}

			}else{

				echo'<script>

				swal({
					type: "error",
					title: "¡El nombre no puede ir vacío o llevar caracteres especiales!",
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "integraciones";
						}
					})

				</script>';

			}

		}

	}

	/*=============================================
	ELIMINAR INTEGRACIÓN
	=============================================*/
	public function ctrEliminarIntegracion(){

		if(isset($_GET["idIntegracion"])){

			$datos = $_GET["idIntegracion"];

			$respuesta = ModeloIntegraciones::mdlEliminarIntegracion("integraciones", $datos);

			if($respuesta == "ok"){

				echo'<script>

				swal({
					type: "success",
					title: "¡La integración ha sido borrada correctamente!",
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "integraciones";
						}
					})

				</script>';

			}

		}

	}

}

