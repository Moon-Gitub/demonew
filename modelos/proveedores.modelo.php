<?php

require_once "conexion.php";

class ModeloProveedores{

	/*=============================================
	CREAR PROVEEDOR
	=============================================*/
	static public function mdlIngresarProveedor($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, inicio_actividades, tipo_documento, cuit, ingresos_brutos, localidad, telefono, direccion, email, observaciones) VALUES (:nombre, :inicio_actividades, :tipo_documento, :cuit, :ingresos_brutos, :localidad, :telefono, :direccion, :email, :observaciones)");

		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":inicio_actividades", $datos["inicio_actividades"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_documento", $datos["tipo_documento"], PDO::PARAM_INT);
		$stmt->bindParam(":cuit", $datos["cuit"], PDO::PARAM_STR);
		$stmt->bindParam(":ingresos_brutos", $datos["ingresos_brutos"], PDO::PARAM_STR);
		$stmt->bindParam(":localidad", $datos["localidad"], PDO::PARAM_STR);
		$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
		$stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
		$stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
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
	CREAR PROVEEDOR USADO EN IMPORTAR EXCEL
	=============================================*/
	static public function mdlIngresarProveedorExcel($nomProveedor){

        $pdo = Conexion::conectar();
		$stmt = $pdo->prepare("INSERT INTO proveedores(nombre, tipo_documento, cuit) VALUES (:nombre, 99, '0')");

		$stmt->bindParam(":nombre", $nomProveedor, PDO::PARAM_STR);

		if($stmt->execute()){

			return $pdo->lastInsertId();

		} else {

			return $stmt->errorInfo()[2];

		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	MOSTRAR PROVEEDORES
	=============================================*/
	static public function mdlMostrarProveedores($tabla, $item, $valor){

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
	MOSTRAR PROVEEDORES POR ID
	=============================================*/
	static public function mdlMostrarProveedoresPorId($idProv){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM proveedores WHERE id = :id");

		$stmt -> bindParam(":id", $idProv, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}
	
	/*=============================================
	MOSTRAR PROVEEDORES
	=============================================*/
	static public function mdlMostrarProductosNota($tabla, $item, $valor){

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
	MOSTRAR PROVEEDORES
	=============================================*/
	static public function mdlMostrarPagosProveedores($tabla, $valor){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha_movimiento = '$valor' AND tipo = 1 AND estado = 1");

			//$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetchAll();

		
		$stmt -> close();

		$stmt = null;

	}
	/*=============================================
	EDITAR PROVEEDOR
	=============================================*/
	static public function mdlEditarProveedor($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, inicio_actividades = :inicio_actividades, tipo_documento = :tipo_documento, cuit = :cuit, ingresos_brutos = :ingresos_brutos, localidad = :localidad, direccion = :direccion, telefono = :telefono, email = :email, observaciones = :observaciones WHERE id = :id");

		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":inicio_actividades", $datos["inicio_actividades"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_documento", $datos["tipo_documento"], PDO::PARAM_INT);
		$stmt->bindParam(":cuit", $datos["cuit"], PDO::PARAM_STR);
		$stmt->bindParam(":ingresos_brutos", $datos["ingresos_brutos"], PDO::PARAM_STR);
		$stmt->bindParam(":localidad", $datos["localidad"], PDO::PARAM_STR);
		$stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
		$stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
		$stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
		$stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt -> errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	ELIMINAR PROVEEDOR
	=============================================*/

	static public function mdlEliminarProveedor($tabla, $datos){

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
	ACTUALIZAR PROVEEDOR
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
	PROVEEDORES FILTRADOS POR AUTOCOMPLETE
	=============================================*/	
	static public function mdlMostrarProveedoresFiltrados($tabla, $filtro){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE nombre LIKE '%$filtro%' OR cuit LIKE '%$filtro%' ORDER BY nombre LIMIT 0,20");
		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;
	}
}