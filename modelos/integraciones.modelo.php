<?php

require_once "conexion.php";

class ModeloIntegraciones{

	/*=============================================
	MOSTRAR INTEGRACIONES
	=============================================*/
	static public function mdlMostrarIntegraciones($item, $valor){
		
		if($item != null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM integraciones WHERE $item = :$item ORDER BY fecha_creacion DESC");
			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);
		} else {
			$stmt = Conexion::conectar()->prepare("SELECT * FROM integraciones ORDER BY fecha_creacion DESC");
		}
		
		$stmt -> execute();
		return $stmt -> fetchAll();
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	CREAR INTEGRACIÓN
	=============================================*/
	static public function mdlCrearIntegracion($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (nombre, tipo, webhook_url, api_key, descripcion, activo) VALUES (:nombre, :tipo, :webhook_url, :api_key, :descripcion, :activo)");

		$stmt -> bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt -> bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
		$stmt -> bindParam(":webhook_url", $datos["webhook_url"], PDO::PARAM_STR);
		$stmt -> bindParam(":api_key", $datos["api_key"], PDO::PARAM_STR);
		$stmt -> bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt -> bindParam(":activo", $datos["activo"], PDO::PARAM_INT);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	EDITAR INTEGRACIÓN
	=============================================*/
	static public function mdlEditarIntegracion($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, tipo = :tipo, webhook_url = :webhook_url, api_key = :api_key, descripcion = :descripcion, activo = :activo WHERE id = :id");

		$stmt -> bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt -> bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
		$stmt -> bindParam(":webhook_url", $datos["webhook_url"], PDO::PARAM_STR);
		$stmt -> bindParam(":api_key", $datos["api_key"], PDO::PARAM_STR);
		$stmt -> bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt -> bindParam(":activo", $datos["activo"], PDO::PARAM_INT);
		$stmt -> bindParam(":id", $datos["id"], PDO::PARAM_INT);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	ELIMINAR INTEGRACIÓN
	=============================================*/
	static public function mdlEliminarIntegracion($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}

}

