<?php

class ControladorProveedores{

	/*=============================================
	CREAR PROVEEDOR
	=============================================*/
	static public function ctrCrearProveedor(){

		if(isset($_POST["nuevoProveedor"])){

		   	$tabla = "proveedores";

		   	$datos = array("nombre"=>$_POST["nuevoProveedor"],
						   "inicio_actividades"=>$_POST["nuevoInicioActividades"],
						   "cuit"=>$_POST["nuevoCuit"],
						   "ingresos_brutos"=>$_POST["nuevoIngresosBrutos"],
				           "localidad"=>$_POST["nuevaLocalidad"],
				           "telefono"=>$_POST["nuevoTelefono"],
				           "direccion"=>$_POST["nuevaDireccion"],
						   "email"=>$_POST["nuevoEmail"],
						   "observaciones" => $_POST["nuevaObservaciones"]);

		   	$respuesta = ModeloProveedores::mdlIngresarProveedor($tabla, $datos);

		   	if($respuesta == "ok"){

				echo'<script>

					localStorage.setItem("msjProveedorCorrecto", true);
					window.location = "proveedores";

				</script>';

			} else {

				$msjError = (isset($respuesta[2])) ? $respuesta[2] : "Error desconocido";
				echo'<script>

				swal({
					  type: "error",
					  title: "Proveedores",
					  text: "'.$msjError.'",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "proveedores";

								}
							})

				</script>';
			}

		}

	}
	
	/*=============================================
	CREAR PROVEEDOR
	=============================================*/
	static public function ctrCrearProveedorCompra(){

		if(isset($_POST["nuevoProveedor"])){

		   	$tabla = "proveedores";

		    $datos = array("nombre"=>$_POST["nuevoNombre"],
						   "inicio_actividades"=>$_POST["nuevoInicioActividades"],
						   "cuit"=>$_POST["nuevoCuit"],
						   "ingresos_brutos"=>$_POST["nuevoIngresosBrutos"],
				           "localidad"=>$_POST["nuevaLocalidad"],
				           "telefono"=>$_POST["nuevoTelefono"],
				           "direccion"=>$_POST["nuevaDireccion"],
						   "email"=>$_POST["nuevoEmail"]);

		   	$respuesta = ModeloProveedores::mdlIngresarProveedor($tabla, $datos);

		   	if($respuesta == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "El Proveedore ha sido guardado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								

								}
							})

				</script>';

			}

		}

	}

	/*=============================================
	MOSTRAR PROVEEDORES
	=============================================*/
	static public function ctrMostrarProveedores($item, $valor){

		$tabla = "proveedores";

		$respuesta = ModeloProveedores::mdlMostrarProveedores($tabla, $item, $valor);

		return $respuesta;
		
	}
	
	/*=============================================
	MOSTRAR PROVEEDORES
	=============================================*/
	static public function ctrMostrarProductosNota($item, $valor){

		$tabla = "notaCredito";

		$respuesta = ModeloProveedores::mdlMostrarProductosNota($tabla, $item, $valor);

		return $respuesta;
		
	}

	/*=============================================
	MOSTRAR PROVEEDORES
	=============================================*/
	static public function ctrMostrarPagosProveedores($valor){

		$tabla = "proveedores_cuenta_corriente";

		$respuesta = ModeloProveedores::mdlMostrarPagosProveedores($tabla, $valor);

		return $respuesta;
		
	}

	/*=============================================
	EDITAR PROVEEDER
	=============================================*/
	static public function ctrEditarProveedor(){

		if(isset($_POST["idProveedor"])){

		   	$tabla = "proveedores";

		   	$datos = array("id"=>$_POST["idProveedor"],
		   				   "nombre"=>$_POST["editarNombre"],
						   "inicio_actividades"=>$_POST["editarInicioActividades"],
						   "cuit"=>$_POST["editarCuit"],
						   "ingresos_brutos"=>$_POST["editarIngresosBrutos"],
				           "localidad"=>$_POST["editarLocalidad"],
				           "telefono"=>$_POST["editarTelefono"],
				           "direccion"=>$_POST["editarDireccion"],
				           "email"=>$_POST["editarEmail"],
				       	   "observaciones" => $_POST["editarObservaciones"]);

		   	$respuesta = ModeloProveedores::mdlEditarProveedor($tabla, $datos);

		   	if($respuesta == "ok"){

				echo'<script>

					localStorage.setItem("msjProveedorCorrecto", true);
					window.location = "proveedores";

				</script>';

			} else {

				$msjError = (isset($respuesta[2])) ? $respuesta[2] : "Error desconocido";
				echo'<script>

				swal({
					  type: "error",
					  title: "Proveedores",
					  text: "'.$msjError.'",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "proveedores";

								}
							})

				</script>';
			}

		}

	}

	/*=============================================
	ELIMINAR PROVEEDER
	=============================================*/
	static public function ctrEliminarProveedor(){

		if(isset($_GET["idProveedor"])){

			$tabla ="proveedores";
			$datos = $_GET["idProveedor"];

			$respuesta = ModeloProveedores::mdlEliminarProveedor($tabla, $datos);

			if($respuesta == "ok"){

				echo'<script>

					localStorage.setItem("msjProveedorCorrecto", true);
					window.location = "proveedores";

				</script>';

			} else {

				$msjError = (isset($respuesta[2])) ? $respuesta[2] : "Error desconocido";
				echo'<script>

				swal({
					  type: "error",
					  title: "Proveedores",
					  text: "'.$msjError.'",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "proveedores";

								}
							})

				</script>';
			}	

		}

	}

	/*=============================================
	LISTAR PROVEEDORES FILTRADOS (AUTOCOMPLETE PROVEEDORES)
	=============================================*/
	static public function ctrMostrarProveedoresFiltrados($filtro){

		$tabla = "proveedores";

		$respuesta = ModeloProveedores::mdlMostrarProveedoresFiltrados($tabla, $filtro);

		return $respuesta;

	}

}