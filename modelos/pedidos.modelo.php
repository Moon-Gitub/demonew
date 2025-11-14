<?php

require_once "conexion.php";

class ModeloPedidos{

	/*=============================================
	MOSTRAR PEDIDOS
	=============================================*/

	static public function mdlMostrarPedidos($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id DESC");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 0 ORDER BY id DESC");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	MOSTRAR PEDIDOS
	=============================================*/

	static public function mdlMostrarPedidosValidados($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id DESC");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 1 ORDER BY id DESC");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	REGISTRO DE PEDIDO
	=============================================*/

	static public function mdlIngresarPedido($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(codigo, id_vendedor, productos, origen, destino, estado, usuarioConfirma) VALUES (:codigo, :id_vendedor, :productos, :origen, :destino, :estado, :usuarioConfirma)");

		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_STR);
		$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
		$stmt->bindParam(":origen", $datos["origen"], PDO::PARAM_STR);
		$stmt->bindParam(":destino", $datos["destino"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$stmt->bindParam(":usuarioConfirma", $datos["usuarioConfirma"], PDO::PARAM_STR);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	EDITAR VENTA
	=============================================*/

	static public function mdlEditarPedido($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET  id_vendedor = :id_vendedor, productos = :productos, origen = :origen, destino = :destino, estado = :estado, usuarioConfirma = :usuarioConfirma WHERE id = :id");

		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_STR);
		$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
		$stmt->bindParam(":origen", $datos["origen"], PDO::PARAM_STR);
		$stmt->bindParam(":destino", $datos["destino"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_INT);
		$stmt->bindParam(":usuarioConfirma", $datos["usuarioConfirma"], PDO::PARAM_STR);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	ELIMINAR PEDIDO
	=============================================*/

	static public function mdlEliminarPedido($tabla, $datos){

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
	RANGO FECHAS
	=============================================*/	

	static public function mdlRangoFechasPedidos($tabla, $fechaInicial, $fechaFinal){

		if($fechaInicial == null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");

			$stmt -> execute();

			return $stmt -> fetchAll();	


		}else if($fechaInicial == $fechaFinal){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha like '%$fechaFinal%'");

			$stmt -> bindParam(":fecha", $fechaFinal, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetchAll();

		}else{

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno'");

			}else{


				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal'");

			}
		
			$stmt -> execute();

			return $stmt -> fetchAll();

		}

	}

	/*=============================================
	MOSTRAR ULTIMO CODIGO VENTA
	=============================================*/ 

	static public function mdlMostrarUltimoCodigo($tabla){

		$stmt = Conexion::conectar()->prepare("SELECT MAX(codigo) as ultimocodigo FROM $tabla");

		//$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}
	
}
