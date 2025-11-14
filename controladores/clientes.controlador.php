<?php

class ControladorClientes{

	/*=============================================
	CREAR CLIENTES
	=============================================*/
	static public function ctrCrearCliente(){

		if(isset($_POST["nuevoCliente"])){

			// if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoCliente"]) &&
			//    preg_match('/^[0-9]+$/', $_POST["nuevoDocumentoId"])){

		   	$agregarClienteDesde = (isset($_POST["agregarClienteDesde"])) ? $_POST["agregarClienteDesde"] : "clientes";

		    $_POST["nuevaFechaNacimiento"] = ($_POST["nuevaFechaNacimiento"] != "") ? $_POST["nuevaFechaNacimiento"] : null;

		   	$tabla = "clientes";

		   	$datos = array(
		   				"tipo_documento" => $_POST["nuevoTipoDocumento"],
		   				"documento" => $_POST["nuevoDocumentoId"],
		   				"nombre" => $_POST["nuevoCliente"],
		   				"condicion_iva" => $_POST["nuevoCondicionIva"],
						"email" => $_POST["nuevoEmail"],
						"telefono" => $_POST["nuevoTelefono"],
						"direccion" => $_POST["nuevaDireccion"],
						"fecha_nacimiento" => $_POST["nuevaFechaNacimiento"], 
						"observaciones" => $_POST["nuevaObservaciones"]);

		   	$respuesta = ModeloClientes::mdlIngresarCliente($tabla, $datos);

		   	if($respuesta == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "Clientes",
					  text: "El cliente ha sido guardado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "'.$agregarClienteDesde.'";

								}
							})

				</script>';

			} else {

				$msj = (isset($respuesta[2])) ? $respuesta[2] : "Error desconocido";

				echo'<script>

				swal({
					  type: "error",
					  title: "Clientes",
					  text: "'.$msj.'",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "'.$agregarClienteDesde.'";

								}
							})

				</script>';

			}

		}

	}

	/*=============================================
	MOSTRAR CLIENTES
	=============================================*/
	static public function ctrMostrarClientes($item, $valor){

		$tabla = "clientes";

		$respuesta = ModeloClientes::mdlMostrarClientes($tabla, $item, $valor);

		return $respuesta;

	}

	/*=============================================
	EDITAR CLIENTE
	=============================================*/
	static public function ctrEditarCliente(){

		if(isset($_POST["editarCliente"])){

			// if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarCliente"]) &&
			//    preg_match('/^[0-9]+$/', $_POST["editarDocumentoId"])){

			    $_POST["editarFechaNacimiento"] = ($_POST["editarFechaNacimiento"] != "") ? $_POST["editarFechaNacimiento"] : null;

			   	$tabla = "clientes";

			   	$datos = array(
							"id"=>$_POST["idCliente"],
							"tipo_documento" => $_POST["editarTipoDocumento"],
							"documento"=>$_POST["editarDocumentoId"],
							"nombre"=>$_POST["editarCliente"],
							"condicion_iva" => $_POST["editarCondicionIva"],
							"email"=>$_POST["editarEmail"],
							"telefono"=>$_POST["editarTelefono"],
							"direccion"=>$_POST["editarDireccion"],
							"fecha_nacimiento"=>$_POST["editarFechaNacimiento"], 
							"observaciones" => $_POST["editarObservaciones"]);

			   	$respuesta = ModeloClientes::mdlEditarCliente($tabla, $datos);

			   	if($respuesta == "ok"){

					echo'<script>

					swal({
						  type: "success",
						  title: "Clientes",
						  text: "El cliente ha sido cambiado correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
									if (result.value) {

									window.location = "clientes";

									}
								})

					</script>';

				} else {

				$msj = (isset($respuesta[2])) ? $respuesta[2] : "Error desconocido";

				echo'<script>

				swal({
					  type: "error",
					  title: "Clientes",
					  text: "'.$msj.'",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "'.$agregarClienteDesde.'";

								}
							})

				</script>';

			}

		}

	}

	/*=============================================
	ELIMINAR CLIENTE
	=============================================*/

	static public function ctrEliminarCliente(){

		if(isset($_GET["idCliente"])){

			$tabla ="clientes";
			$datos = $_GET["idCliente"];

			$respuesta = ModeloClientes::mdlEliminarCliente($tabla, $datos);

			if($respuesta == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "Clientes",
					  text: "El cliente ha sido borrado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar",
					  closeOnConfirm: false
					  }).then(function(result){
								if (result.value) {

								window.location = "clientes";

								}
							})

				</script>';

			}

		}

	}

    /*=============================================
	CREAR CLIENTES
	=============================================*/
	static public function ctrCrearClienteVenta($postClienteVenta){

	   	$tabla = "clientes";

	   	$datos = array("nombre"=>$postClienteVenta["nuevoCliente"],
			           "tipo_documento"=>$postClienteVenta["nuevoTipoDocumento"],
			           "documento"=>$postClienteVenta["nuevoDocumentoId"],
			           "condicion_iva"=>$postClienteVenta["nuevoCondicionIva"],
					   "email"=>$postClienteVenta["nuevoEmail"],
			           "telefono"=>$postClienteVenta["nuevoTelefono"],
					   "direccion"=>$postClienteVenta["nuevaDireccion"],
			           "fecha_nacimiento"=>$postClienteVenta["nuevaFechaNacimiento"],
			       	   "observaciones" => $postClienteVenta["observaciones"]);

	   	$respuesta = ModeloClientes::mdlIngresarClienteVenta($tabla, $datos);

	   	return $respuesta;

	}


	/*=============================================
	LISTAR CLIENTES FILTRADOS (AUTOCOMPLETE CLIENTES)
	=============================================*/
	static public function ctrMostrarClientesFiltrados($filtro){

		$tabla = "clientes";

		$respuesta = ModeloClientes::mdlMostrarClientesFiltrados($tabla, $filtro);

		return $respuesta;

	}

	/*=============================================
	PADRON AFIP
	=============================================*/
	static public function ctrPadronAfip($idPersona){

		$respuesta = ModeloEmpresa::mdlMostrarEmpresa('empresa', 'id', 1);

       	$wsaaP = new WSAA_P($respuesta);

	    if (!$wsaaP) {
	        return "Error en wsaaP";
	    }       

	    //Comparar que la fecha/hora de expiracion del ultimo TAP 
	    //sea mayor que ahora. Si es menor genero nuevo TAP
	    if (date('Y-m-d H:i:s', strtotime($wsaaP->get_expiration())) < date('Y-m-d H:i:s')) {

            if (!$wsaaP->generar_TAP()) {
                return "Error al intentar generar Ticket de Acceso";
            }

	    } 

	    $WSPadron = new PADRON($respuesta);

	    if (!$WSPadron) {
	        return "Error en WSPadron";
	    }

	    //Abrir ticket de acceso para obtener el token y el sign        
	    if(!$WSPadron->openTAP()){
	        return "Error en OpenTAP";
	    }
	    
	    if($respuesta["ws_padron"] == 'ws_sr_padron_a100'){
	        return $WSPadron->getPersona100($idPersona);
	    } elseif ($respuesta["ws_padron"] == 'ws_sr_constancia_inscripcion') {
	    	return $WSPadron->getPersona2($idPersona);
		} else {
	        return $WSPadron->getPersona($idPersona);    
	    }

	}

	/*=============================================
	PADRON AFIP LISTA
	=============================================*/
	static public function ctrPadronAfipLista($arrCUIT){

		$respuesta = ModeloEmpresa::mdlMostrarEmpresa('empresa', 'id', 1);
       	$wsaaP = new WSAA_P($respuesta);
	    if (!$wsaaP) {
	        return "Error en wsaaP";
	    }       
	    //Comparar que la fecha/hora de expiracion del ultimo TAP 
	    //sea mayor que ahora. Si es menor genero nuevo TAP
	    if (date('Y-m-d H:i:s', strtotime($wsaaP->get_expiration())) < date('Y-m-d H:i:s')) {
            if (!$wsaaP->generar_TAP()) {
                return "Error al intentar generar Ticket de Acceso";
            }
	    } 
	    $WSPadron = new PADRON($respuesta);
	    if (!$WSPadron) {
	        return "Error en WSPadron";
	    }

	    //Abrir ticket de acceso para obtener el token y el sign        
	    if(!$WSPadron->openTAP()){
	        return "Error en OpenTAP";
	    }
	    
	    return $WSPadron->getPersonaList($arrCUIT);
	}

	/*=============================================
	MOSTRAR ULTIMA FECHA ACTUALIZACION
	=============================================*/
	static public function ctrFechaActualizacion(){
		$respuesta = ModeloClientes::mdlFechaActualizacion();
		return $respuesta;
	}
    
}