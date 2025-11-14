<?php

require_once "conexion.php";

class ModeloClientes{

	/*=============================================
	CREAR CLIENTE
	=============================================*/

	static public function mdlIngresarCliente($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(tipo_documento, documento, nombre, condicion_iva, email, telefono, direccion, fecha_nacimiento, observaciones) VALUES (:tipo_documento, :documento, :nombre, :condicion_iva, :email, :telefono, :direccion, :fecha_nacimiento, :observaciones)");

		$stmt->bindParam(":tipo_documento", $datos["tipo_documento"], PDO::PARAM_INT);
		$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":condicion_iva", $datos["condicion_iva"], PDO::PARAM_INT);
		$stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
		$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
		$stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);
		$stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	MOSTRAR CLIENTES
	=============================================*/
	static public function mdlMostrarClientes($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

		$stmt = null;

	}
	
	/*=============================================
	MOSTRAR CLIENTES
	=============================================*/
	static public function mdlMostrarClientesBeta($tabla, $valor){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE id = '$valor'");
			$stmt -> execute();
			return $stmt -> fetch();
			$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	EDITAR CLIENTE
	=============================================*/
	static public function mdlEditarCliente($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET tipo_documento = :tipo_documento, documento = :documento, nombre = :nombre, condicion_iva = :condicion_iva, email = :email, telefono = :telefono, direccion = :direccion, fecha_nacimiento = :fecha_nacimiento, observaciones = :observaciones WHERE id = :id");

		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_documento", $datos["tipo_documento"], PDO::PARAM_INT);
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":condicion_iva", $datos["condicion_iva"], PDO::PARAM_INT);
		$stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
		$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
		$stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);
		$stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	ELIMINAR CLIENTE
	=============================================*/
	static public function mdlEliminarCliente($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR CLIENTE
	=============================================*/

	static public function mdlActualizarCliente($tabla, $item1, $valor1, $valor){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE id = :id");

		$stmt -> bindParam(":".$item1, $valor1, PDO::PARAM_STR);
		$stmt -> bindParam(":id", $valor, PDO::PARAM_STR);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	CREAR CLIENTE DESDE VENTAS
	=============================================*/
	static public function mdlIngresarClienteVenta($tabla, $datos){

		$dbCon = Conexion::conectar(); 
		$stmt = $dbCon->prepare("INSERT INTO $tabla(tipo_documento, documento, nombre, condicion_iva, email, telefono, direccion, fecha_nacimiento, observaciones) VALUES (:tipo_documento, :documento, :nombre, :condicion_iva, :email, :telefono, :direccion, :fecha_nacimiento, :observaciones)");

		$stmt->bindParam(":tipo_documento", $datos["tipo_documento"], PDO::PARAM_INT);
		$stmt->bindParam(":documento", $datos["documento"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":condicion_iva", $datos["condicion_iva"], PDO::PARAM_INT);
		$stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
		$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
		$stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha_nacimiento", $datos["fecha_nacimiento"], PDO::PARAM_STR);
		$stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

		if($stmt->execute()){

			return $dbCon->lastInsertId();

		}else{

			return $stmt -> errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	CLIENTES FILTRADOS POR AUTOCOMPLETE
	=============================================*/	
	static public function mdlMostrarClientesFiltrados($tabla, $filtro){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE nombre LIKE '%$filtro%' OR documento LIKE '%$filtro%' ORDER BY nombre LIMIT 0,20");
		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;
	}

	/*=============================================
	CLIENTES POR ID
	=============================================*/	
	static public function mdlMostrarClientesPorId($idCliente){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM clientes WHERE id = :id");

		$stmt->bindParam(":id", $idCliente, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;
	}

	/*=============================================
	MOSTRAR ULTIMA FECHA ACTUALIZADA
	=============================================*/
	static public function mdlFechaActualizacion(){
		$stmt = Conexion::conectar()->prepare("SELECT MAX(fecha) as fecha FROM clientes");
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;
	}
    
}