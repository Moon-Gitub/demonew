<?php

require_once "conexion.php";

class ModeloMediosPago{

	/*=============================================
	CREAR MEDIO DE PAGO
	=============================================*/
	static public function mdlIngresarMedioPago($tabla, $datos){
		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(codigo, nombre, descripcion, activo, requiere_codigo, requiere_banco, requiere_numero, requiere_fecha, orden) VALUES (:codigo, :nombre, :descripcion, :activo, :requiere_codigo, :requiere_banco, :requiere_numero, :requiere_fecha, :orden)");
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":activo", $datos["activo"], PDO::PARAM_INT);
		$stmt->bindParam(":requiere_codigo", $datos["requiere_codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":requiere_banco", $datos["requiere_banco"], PDO::PARAM_INT);
		$stmt->bindParam(":requiere_numero", $datos["requiere_numero"], PDO::PARAM_INT);
		$stmt->bindParam(":requiere_fecha", $datos["requiere_fecha"], PDO::PARAM_INT);
		$stmt->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
		if($stmt->execute()){
			return "ok";
		}else{
			return "error";
		}
		$stmt = null;
	}
	
	/*=============================================
	MOSTRAR MEDIOS DE PAGO
	=============================================*/
	static public function mdlMostrarMediosPago($tabla, $item, $valor){
		if($item != null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);
			$stmt -> execute();
			return $stmt -> fetch();
		}else{
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY orden ASC, nombre ASC");
			$stmt -> execute();
			return $stmt -> fetchAll();
		}
		$stmt = null;
	}

	/*=============================================
	MOSTRAR MEDIOS DE PAGO ACTIVOS
	=============================================*/
	static public function mdlMostrarMediosPagoActivos(){
		try {
			$stmt = Conexion::conectar()->prepare("SELECT * FROM medios_pago WHERE activo = 1 ORDER BY orden ASC, nombre ASC");
			$stmt -> execute();
			$resultado = $stmt -> fetchAll();
			$stmt = null;
			return $resultado;
		} catch (Exception $e) {
			error_log("Error en mdlMostrarMediosPagoActivos: " . $e->getMessage());
			return false;
		}
	}

	/*=============================================
	EDITAR MEDIO DE PAGO
	=============================================*/
	static public function mdlEditarMedioPago($tabla, $datos){
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET codigo = :codigo, nombre = :nombre, descripcion = :descripcion, activo = :activo, requiere_codigo = :requiere_codigo, requiere_banco = :requiere_banco, requiere_numero = :requiere_numero, requiere_fecha = :requiere_fecha, orden = :orden WHERE id = :id");
		$stmt -> bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt -> bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt -> bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt -> bindParam(":activo", $datos["activo"], PDO::PARAM_INT);
		$stmt -> bindParam(":requiere_codigo", $datos["requiere_codigo"], PDO::PARAM_INT);
		$stmt -> bindParam(":requiere_banco", $datos["requiere_banco"], PDO::PARAM_INT);
		$stmt -> bindParam(":requiere_numero", $datos["requiere_numero"], PDO::PARAM_INT);
		$stmt -> bindParam(":requiere_fecha", $datos["requiere_fecha"], PDO::PARAM_INT);
		$stmt -> bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
		$stmt -> bindParam(":id", $datos["id"], PDO::PARAM_INT);
		if($stmt->execute()){
			return "ok";
		}else{
			return "error";
		}
		$stmt = null;
	}

	/*=============================================
	BORRAR MEDIO DE PAGO
	=============================================*/
	static public function mdlBorrarMedioPago($tabla, $datos){
		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
		$stmt -> bindParam(":id", $datos, PDO::PARAM_INT);
		if($stmt -> execute()){
			return "ok";
		}else{
			return "error";	
		}
		$stmt = null;
	}
}
