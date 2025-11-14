<?php

require_once "conexion.php";

class ModeloUsuarios{

	/*=============================================
	MOSTRAR USUARIOS
	=============================================*/
	static public function mdlMostrarUsuarios($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

			$stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetch();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");

			$stmt->execute();

			return $stmt->fetchAll();

		}
		

		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	REGISTRO DE USUARIO
	=============================================*/
	static public function mdlIngresarUsuario($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, usuario, password, perfil, sucursal, puntos_venta, listas_precio, foto) VALUES (:nombre, :usuario, :password, :perfil, :sucursal, :puntos_venta, :listas_precio, :foto)");

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

		$stmt->close();
		
		$stmt = null;

	}

	/*=============================================
	EDITAR USUARIO
	=============================================*/
	static public function mdlEditarUsuario($tabla, $datos){
	
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, password = :password, perfil = :perfil, sucursal = :sucursal, puntos_venta = :puntos_venta, listas_precio = :listas_precio, foto = :foto WHERE usuario = :usuario");

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

		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR USUARIO
	=============================================*/
	static public function mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE $item2 = :$item2");

		$stmt->bindParam(":".$item1, $valor1, PDO::PARAM_STR);
		$stmt->bindParam(":".$item2, $valor2, PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt->close();

		$stmt = null;

	}

	/*=============================================
	BORRAR USUARIO
	=============================================*/
	static public function mdlBorrarUsuario($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt->bindParam(":id", $datos, PDO::PARAM_INT);

		if($stmt->execute()){

			return "ok";
		
		}else{

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