<?php

class ControladorEmpresa{

	/*=============================================
	MOSTRAR empresa
	=============================================*/
	static public function ctrMostrarempresa($item, $valor){
		$respuesta = ModeloEmpresa::mdlMostrarEmpresa('empresa', $item, $valor);
		return $respuesta;
	}

	/*=============================================
	EDITAR Empresa
	=============================================*/
	static public function ctrEditarEmpresa(){

		if(isset($_POST["empRazonSocial"])){

			if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["empRazonSocial"])){

				$csr_subido = "";
				$csr_bd = $_POST["hayCSR"];

				$frasepass = $_POST["hayPhrase"];

				$pem_subido = "";
				$pem_db = $_POST["hayPEM"];

				$dir_subida = "controladores/facturacion/keys/";
				$dir_bd = "keys/";

				if (!$_FILES['empCSR']['error']) { 
					if(isset($_FILES["empCSR"]["tmp_name"])){

						$frasepass = $_FILES["empCSR"]["name"];

						$csr_subido = $dir_subida . basename($_FILES['empCSR']['name']);

						if (move_uploaded_file($_FILES['empCSR']['tmp_name'], $csr_subido)) {
							$csr_bd = $dir_bd . basename($_FILES['empCSR']['name']);
						} else {
							exit("Error al subir archivo CSR");
						}
					}
				} 

				if (!$_FILES['empPEM']['error']) { 
					if(isset($_FILES["empPEM"]["tmp_name"])){

						$pem_subido = $dir_subida . basename($_FILES['empPEM']['name']);

						if (move_uploaded_file($_FILES['empPEM']['tmp_name'], $pem_subido)) {
							$pem_db = $dir_bd . basename($_FILES['empPEM']['name']);
						} else {
							exit("Error al subir archivo PEM");
						}

					}
				}

				$tabla = "empresa";

				$datos = array(
					"id"=>$_POST["idEmpresa"],
					"razon_social" => $_POST["empRazonSocial"],
					"titular" => $_POST["empTitular"],
					"cuit" => $_POST["empCuit"],
					"domicilio" => $_POST["empDomicilio"],
					"localidad" => $_POST["empLocalidad"],
					"codigo_postal" => $_POST["empCodPostal"],
					"mail" => $_POST["empMail"],
					"telefono" => $_POST["empTelefono"],
					"ptos_venta" => $_POST["empPtosVta"],
					"pto_venta_defecto" => $_POST["empPtoVtaDefecto"],
					"condicion_iva" => $_POST["empCondicionIva"],
					"condicion_iibb" => $_POST["empCondicionIIBB"],
					"numero_iibb" => $_POST["empNumeroIIBB"],
					"inicio_actividades" => $_POST["empInicioActividades"],
					"numero_establecimiento" => $_POST["empNumeroEstablecimiento"],
					"cbu" => $_POST["empNumeroCBU"],
					"cbu_alias" => $_POST["empNumeroCBUAlias"],
					"concepto_defecto" => $_POST["empConceptoDefecto"],
					"tipos_cbtes" => $_POST["empTipoCbtes"],
					"entorno_facturacion" => ($_POST["entornoFacturacion"] == "NULL") ? null : $_POST["entornoFacturacion"],
					"ws_padron" => ($_POST["empTipoPadron"] == "NULL") ? null : $_POST["empTipoPadron"],
					"csr" => $csr_bd, 
					"passphrase" => $frasepass,
					"pem" => $pem_db,
					"logo" => ''
				);

				$respuesta = ModeloEmpresa::mdlEditarEmpresa($tabla, $datos);

				if($respuesta == true){

					echo'<script>

					swal({
						type: "success",
						title: "La empresa ha sido cambiada correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
						}).then(function(result){
							if (result.value) {
								window.location = "empresa";
							}
							})
							</script>';
				}

			}else{

				echo'<script>

				swal({
					type: "error",
					title: "¡Ha ocurrido un error al editar la empresa!",
					showConfirmButton: true,
					confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {

							window.location = "empresa";

						}
					})
				</script>';

			}
		}

	}

}