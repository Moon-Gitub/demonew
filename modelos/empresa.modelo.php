<?php

require_once "conexion.php";

class ModeloEmpresa{

	/*=============================================
	MOSTRAR EMPRESA
	=============================================*/
	static public function mdlMostrarEmpresa($tabla, $item, $valor){
		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
		$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);
		$stmt -> execute();
		$resultado = $stmt -> fetch();
		$stmt = null;
		return $resultado;
	}

	/*=============================================
	EDITAR EMPRESA
	=============================================*/
	static public function mdlEditarEmpresa($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET razon_social = :razon_social, titular = :titular, cuit = :cuit, domicilio = :domicilio, localidad = :localidad, codigo_postal = :codigo_postal, mail = :mail, telefono = :telefono, ptos_venta = :ptos_venta, pto_venta_defecto = :pto_venta_defecto, condicion_iva = :condicion_iva, condicion_iibb = :condicion_iibb, numero_iibb = :numero_iibb, inicio_actividades = :inicio_actividades, numero_establecimiento = :numero_establecimiento, cbu = :cbu, cbu_alias = :cbu_alias, concepto_defecto = :concepto_defecto, tipos_cbtes = :tipos_cbtes, entorno_facturacion = :entorno_facturacion, ws_padron = :ws_padron, csr = :csr, passphrase = :passphrase, pem = :pem, logo = :logo, login_fondo = :login_fondo, login_logo = :login_logo, login_fondo_form = :login_fondo_form, login_color_boton = :login_color_boton, login_fuente = :login_fuente, login_color_texto_titulo = :login_color_texto_titulo, mp_public_key = :mp_public_key, mp_access_token = :mp_access_token WHERE id = :id");

		$stmt -> bindParam(":razon_social", $datos["razon_social"], PDO::PARAM_STR);
		$stmt -> bindParam(":titular", $datos["titular"], PDO::PARAM_STR);
		$stmt -> bindParam(":cuit", $datos["cuit"], PDO::PARAM_STR);
		$stmt -> bindParam(":domicilio", $datos["domicilio"], PDO::PARAM_STR);
		$stmt -> bindParam(":localidad", $datos["localidad"], PDO::PARAM_STR);
		$stmt -> bindParam(":codigo_postal", $datos["codigo_postal"], PDO::PARAM_STR);
		$stmt -> bindParam(":mail", $datos["mail"], PDO::PARAM_STR);
		$stmt -> bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
		$stmt -> bindParam(":ptos_venta", $datos["ptos_venta"], PDO::PARAM_STR);
		$stmt -> bindParam(":pto_venta_defecto", $datos["pto_venta_defecto"], PDO::PARAM_STR);
		$stmt -> bindParam(":condicion_iva", $datos["condicion_iva"], PDO::PARAM_STR);
		$stmt -> bindParam(":condicion_iibb", $datos["condicion_iibb"], PDO::PARAM_STR);
		$stmt -> bindParam(":numero_iibb", $datos["numero_iibb"], PDO::PARAM_STR);
		$stmt -> bindParam(":inicio_actividades", $datos["inicio_actividades"], PDO::PARAM_STR);
		$stmt -> bindParam(":numero_establecimiento", $datos["numero_establecimiento"], PDO::PARAM_STR);
		$stmt -> bindParam(":cbu", $datos["cbu"], PDO::PARAM_STR);
		$stmt -> bindParam(":cbu_alias", $datos["cbu_alias"], PDO::PARAM_STR);
		$stmt -> bindParam(":concepto_defecto", $datos["concepto_defecto"], PDO::PARAM_STR);
		$stmt -> bindParam(":tipos_cbtes", $datos["tipos_cbtes"], PDO::PARAM_STR);
		$stmt -> bindParam(":entorno_facturacion", $datos["entorno_facturacion"], PDO::PARAM_STR);
		$stmt -> bindParam(":ws_padron", $datos["ws_padron"], PDO::PARAM_STR);
		$stmt -> bindParam(":csr", $datos["csr"], PDO::PARAM_STR);
		$stmt -> bindParam(":passphrase", $datos["passphrase"], PDO::PARAM_STR);
		$stmt -> bindParam(":pem", $datos["pem"], PDO::PARAM_STR);
		$stmt -> bindParam(":logo", $datos["logo"], PDO::PARAM_STR);
		$stmt -> bindParam(":login_fondo", $datos["login_fondo"], PDO::PARAM_STR);
		$stmt -> bindParam(":login_logo", $datos["login_logo"], PDO::PARAM_STR);
		$stmt -> bindParam(":login_fondo_form", $datos["login_fondo_form"], PDO::PARAM_STR);
		$stmt -> bindParam(":login_color_boton", $datos["login_color_boton"], PDO::PARAM_STR);
		$stmt -> bindParam(":login_fuente", $datos["login_fuente"], PDO::PARAM_STR);
		$stmt -> bindParam(":login_color_texto_titulo", $datos["login_color_texto_titulo"], PDO::PARAM_STR);
		$stmt -> bindParam(":mp_public_key", $datos["mp_public_key"], PDO::PARAM_STR);
		$stmt -> bindParam(":mp_access_token", $datos["mp_access_token"], PDO::PARAM_STR);
		$stmt -> bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if($stmt->execute()){

			return true;

		}else{

			$error = $stmt->errorInfo();
			$stmt = null;
			return $error;

		}

		$stmt = null;

	}

}
