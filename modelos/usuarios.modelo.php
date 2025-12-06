<?php

require_once "conexion.php";
require_once "validador-sql.modelo.php";

class ModeloUsuarios{

	/*=============================================
	MOSTRAR USUARIOS - VERSIÓN SEGURA
	=============================================*/
	static public function mdlMostrarUsuarios($tabla, $item, $valor){

		try {
			// ✅ Validar tabla
			$tabla = ModeloValidadorSQL::validarTabla($tabla);
			
			$pdo = Conexion::conectar();
			
			if($item != null){
				// ✅ Validar columna
				$item = ModeloValidadorSQL::validarColumna($tabla, $item);
				
				// ✅ Usar prepared statement correctamente con backticks
				$stmt = $pdo->prepare("SELECT * FROM `$tabla` WHERE `$item` = :valor");
				$stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
				$stmt->execute();
				
				return $stmt->fetch();
				
			}else{
				$stmt = $pdo->prepare("SELECT * FROM `$tabla`");
				$stmt->execute();
				
				return $stmt->fetchAll();
			}
			
		} catch (Exception $e) {
			error_log("Error en mdlMostrarUsuarios: " . $e->getMessage());
			return false;
		}

		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	REGISTRO DE USUARIO - VERSIÓN SEGURA
	=============================================*/
	static public function mdlIngresarUsuario($tabla, $datos){

		try {
			// ✅ Validar tabla
			$tabla = ModeloValidadorSQL::validarTabla($tabla);
			$pdo = Conexion::conectar();
			
			$stmt = $pdo->prepare("INSERT INTO `$tabla`(nombre, usuario, password, perfil, sucursal, puntos_venta, listas_precio, foto) VALUES (:nombre, :usuario, :password, :perfil, :sucursal, :puntos_venta, :listas_precio, :foto)");

			$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
			$stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
			$stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
			$stmt->bindParam(":perfil", $datos["perfil"], PDO::PARAM_STR);
			$stmt->bindParam(":sucursal", $datos["sucursal"], PDO::PARAM_STR);
			$stmt->bindParam(":puntos_venta", $datos["puntos_venta"], PDO::PARAM_STR);
			$stmt->bindParam(":listas_precio", $datos["listas_precio"], PDO::PARAM_STR);
			$stmt->bindParam(":foto", $datos["foto"], PDO::PARAM_STR);

			if($stmt->execute()){
				return "ok";	
			}else{
				return $stmt->errorInfo();
			}
			
		} catch (Exception $e) {
			error_log("Error en mdlIngresarUsuario: " . $e->getMessage());
			return ["error" => $e->getMessage()];
		}

		$stmt->close();
		
		$stmt = null;

	}

	/*=============================================
	EDITAR USUARIO - VERSIÓN SEGURA
	=============================================*/
	static public function mdlEditarUsuario($tabla, $datos){
	
		try {
			// ✅ Validar tabla
			$tabla = ModeloValidadorSQL::validarTabla($tabla);
			$pdo = Conexion::conectar();
			
			$stmt = $pdo->prepare("UPDATE `$tabla` SET nombre = :nombre, password = :password, perfil = :perfil, sucursal = :sucursal, puntos_venta = :puntos_venta, listas_precio = :listas_precio, foto = :foto WHERE usuario = :usuario");

			$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
			$stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
			$stmt->bindParam(":perfil", $datos["perfil"], PDO::PARAM_STR);
			$stmt->bindParam(":sucursal", $datos["sucursal"], PDO::PARAM_STR);
			$stmt->bindParam(":puntos_venta", $datos["puntos_venta"], PDO::PARAM_STR);
			$stmt->bindParam(":listas_precio", $datos["listas_precio"], PDO::PARAM_STR);
			$stmt->bindParam(":foto", $datos["foto"], PDO::PARAM_STR);
			$stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);

			if($stmt->execute()){
				return "ok";
			}else{
				return $stmt->errorInfo();
			}
			
		} catch (Exception $e) {
			error_log("Error en mdlEditarUsuario: " . $e->getMessage());
			return ["error" => $e->getMessage()];
		}

		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR USUARIO - VERSIÓN SEGURA
	=============================================*/
	static public function mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2){

		try {
			// ✅ Validar tabla y columnas
			$tabla = ModeloValidadorSQL::validarTabla($tabla);
			$item1 = ModeloValidadorSQL::validarColumna($tabla, $item1);
			$item2 = ModeloValidadorSQL::validarColumna($tabla, $item2);
			
			$pdo = Conexion::conectar();
			
			// ✅ Usar placeholders con nombres únicos
			$stmt = $pdo->prepare("UPDATE `$tabla` SET `$item1` = :valor1 WHERE `$item2` = :valor2");
			
			$stmt->bindParam(":valor1", $valor1, PDO::PARAM_STR);
			$stmt->bindParam(":valor2", $valor2, PDO::PARAM_STR);

			if($stmt->execute()){
				return "ok";
			}else{
				return "error";	
			}
			
		} catch (Exception $e) {
			error_log("Error en mdlActualizarUsuario: " . $e->getMessage());
			return "error";
		}

		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	BORRAR USUARIO - VERSIÓN SEGURA
	=============================================*/
	static public function mdlBorrarUsuario($tabla, $datos){

		try {
			// ✅ Validar tabla
			$tabla = ModeloValidadorSQL::validarTabla($tabla);
			$pdo = Conexion::conectar();
			
			$stmt = $pdo->prepare("DELETE FROM `$tabla` WHERE id = :id");

			$stmt->bindParam(":id", $datos, PDO::PARAM_INT);

			if($stmt->execute()){
				return "ok";
			}else{
				return "error";	
			}
			
		} catch (Exception $e) {
			error_log("Error en mdlBorrarUsuario: " . $e->getMessage());
			return "error";
		}

		$stmt->close();

		$stmt = null;


	}

	/*=============================================
	MOSTRAR USUARIOS POR ID
	=============================================*/
	static public function mdlMostrarUsuariosPorId($idUsuario){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM usuarios WHERE id = :id");

		$stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);

		$stmt->execute();

		return $stmt->fetch();

		$stmt->close();

		$stmt = null;

	}
}