<?php

require_once "conexion.php";

class ModeloCategorias{

	/*=============================================
	CREAR CATEGORIA
	=============================================*/
	static public function mdlIngresarCategoria($tabla, $datos){
		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(categoria) VALUES (:categoria)");
		$stmt->bindParam(":categoria", $datos["categoria"], PDO::PARAM_STR);
		if($stmt->execute()){
			return "ok";
		}else{
			return "error";
		}
		$stmt->close();
		$stmt = null;
	}
	
	/*=============================================
	DEVOLVER ULTIMO ID
	=============================================*/
	static public function mdlUltimoId($tabla){	
		$stmt = Conexion::conectar()->prepare("SELECT MAX(id) as ultimo FROM $tabla");
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;
	}
	
	/*=============================================
	CREAR CATEGORIA (USADO EN IMPORTAR EXCEL)
	=============================================*/
	static public function mdlIngresarCategoriaExcel($nomCategoria){
		$pdo = Conexion::conectar();
		$stmt = $pdo->prepare("INSERT INTO categorias(categoria) VALUES (:categoria)");
		$stmt->bindParam(":categoria", $nomCategoria, PDO::PARAM_STR);
		if($stmt->execute()){
			return $pdo->lastInsertId();
		} else {
			return $stmt->errorInfo()[2];
		}
		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	MOSTRAR CATEGORIAS
	=============================================*/
	static public function mdlMostrarCategorias($tabla, $item, $valor){
		if($item != null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM categorias WHERE $item = :$item");
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
	EDITAR CATEGORIA
	=============================================*/

	static public function mdlEditarCategoria($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET categoria = :categoria WHERE id = :id");

		$stmt -> bindParam(":categoria", $datos["categoria"], PDO::PARAM_STR);
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
	BORRAR CATEGORIA
	=============================================*/

	static public function mdlBorrarCategoria($tabla, $datos){

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


}