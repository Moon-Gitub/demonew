<?php

require_once "conexion.php";

class ModeloCompras{

	//MOSTRAR COMPRAS
	static public function mdlMostrarCompras($tabla, $item, $valor){
		if($item != null){ 
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id ASC");
			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);
			$stmt -> execute();
			return $stmt -> fetch();

		}else{
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id ASC");
			$stmt -> execute();
			return $stmt -> fetchAll();
		}
		
		$stmt -> close();
		$stmt = null;

	}

	//REGISTRO DE COMPRA
	static public function mdlCargarNota($tabla, $idCompra, $productos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_compra, productos) VALUES (:id_compra, :productos)");
	
		$stmt->bindParam(":id_compra", $idCompra, PDO::PARAM_INT);
		$stmt->bindParam(":productos", $productos, PDO::PARAM_STR);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}
	
	/*=============================================
	REGISTRO DE COMPRA NOTA DEBITO
	=============================================*/

	static public function mdlCargarNotaDebito($tabla, $idCompra, $productos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_compra, productos) VALUES (:id_compra, :productos)");
	
		$stmt->bindParam(":id_compra", $idCompra, PDO::PARAM_INT);
		$stmt->bindParam(":productos", $productos, PDO::PARAM_STR);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}
	
	/*=============================================
	REGISTRO DE COMPRA
	=============================================*/
	static public function mdlIngresarCompra($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(codigo, id_proveedor, usuarioPedido, usuarioConfirma, fechaEntrega, fechaPago, productos, estado, fecha, total) VALUES (:codigo, :id_proveedor, :usuarioPedido, :usuarioConfirma, :fechaEntrega, :fechaPago, :productos, :estado, :fecha, :total)");

		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":usuarioPedido", $datos["usuarioPedido"], PDO::PARAM_STR);
		$stmt->bindParam(":usuarioConfirma", $datos["usuarioConfirma"], PDO::PARAM_STR);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
		$stmt->bindParam(":fechaEntrega", $datos["fechaEntrega"], PDO::PARAM_STR);
		$stmt->bindParam(":fechaPago", $datos["fechaPago"], PDO::PARAM_STR);
		$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
		$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt -> errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	EDITAR INGRESO
	=============================================*/

	static public function mdlEditarIngreso($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET usuarioPedido = :usuarioPedido, id_proveedor= :id_proveedor, usuarioConfirma = :usuarioConfirma, remitoNumero = :remitoNumero, numeroFactura = :numeroFactura, fechaEmision = :fechaEmision, observacionFactura = :observacionFactura, estado = :estado, descuento = :descuento, totalNeto= :totalNeto, tipo = :tipo, iva= :iva, precepcionesIngresosBrutos= :precepcionesIngresosBrutos,  precepcionesIva= :precepcionesIva, precepcionesGanancias= :precepcionesGanancias, impuestoInterno= :impuestoInterno, fechaIngreso = :fechaIngreso, productos = :productos, total= :total WHERE id = :id");

		$stmt->bindParam(":usuarioPedido", $datos["usuarioPedido"], PDO::PARAM_STR);
		$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_STR);
		$stmt->bindParam(":usuarioConfirma", $datos["usuarioConfirma"], PDO::PARAM_STR);
		$stmt->bindParam(":remitoNumero", $datos["remitoNumero"], PDO::PARAM_STR);
		$stmt->bindParam(":numeroFactura", $datos["numeroFactura"], PDO::PARAM_STR);
		$stmt->bindParam(":fechaEmision", $datos["fechaEmision"], PDO::PARAM_STR);
		$stmt->bindParam(":observacionFactura", $datos["observacionFactura"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$stmt->bindParam(":totalNeto", $datos["totalNeto"], PDO::PARAM_STR);
		$stmt->bindParam(":descuento", $datos["descuento"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
		$stmt->bindParam(":iva", $datos["iva"], PDO::PARAM_STR);
		$stmt->bindParam(":precepcionesIngresosBrutos", $datos["precepcionesIngresosBrutos"], PDO::PARAM_STR);
		$stmt->bindParam(":precepcionesIva", $datos["precepcionesIva"], PDO::PARAM_STR);
		$stmt->bindParam(":precepcionesGanancias", $datos["precepcionesGanancias"], PDO::PARAM_STR);
		$stmt->bindParam(":impuestoInterno", $datos["impuestoInterno"], PDO::PARAM_STR);
		$stmt->bindParam(":fechaIngreso", $datos["fechaIngreso"], PDO::PARAM_STR);		
		$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
		$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}
	
	/*=============================================
	ELIMINAR COMPRA
	=============================================*/

	static public function mdlEliminarCompra($tabla, $datos){

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
	static public function mdlRangoFechasCompras($tabla, $fechaInicial, $fechaFinal){

		if($fechaInicial == null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 0 ORDER BY id DESC");
			$stmt -> execute();
			return $stmt -> fetchAll();	

		}else if($fechaInicial == $fechaFinal){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha like '%$fechaFinal%' ORDER BY codigo DESC");
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
	RANGO FECHAS
	=============================================*/	
	static public function mdlRangoFechasComprasIngresadas($tabla, $fechaInicial, $fechaFinal){

		if($fechaInicial == null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 1 ORDER BY id DESC");
			$stmt -> execute();
			return $stmt -> fetchAll();	

		} else if($fechaInicial == $fechaFinal){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 1 AND fecha like '%$fechaFinal%' ORDER BY codigo DESC");

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

				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 1 AND fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno'");

			}else{

				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 1 AND fecha BETWEEN '$fechaInicial' AND '$fechaFinal'");

			}
		
			$stmt -> execute();

			return $stmt -> fetchAll();

		}

	}

	/*=============================================
	RANGO FECHAS
	=============================================*/	

	static public function mdlRangoFechasComprasValidadas($tabla, $fechaInicial, $fechaFinal){

		if($fechaInicial == null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 2 ORDER BY id DESC");
			$stmt -> execute();
			return $stmt -> fetchAll();	

		}else if($fechaInicial == $fechaFinal){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 2 AND fecha like '%$fechaFinal%' ORDER BY codigo DESC");
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

				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 2 AND fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno'");

			}else{

				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 2 AND fecha BETWEEN '$fechaInicial' AND '$fechaFinal'");

			}
		
			$stmt -> execute();
			return $stmt -> fetchAll();
		}
	}
	
	/*=============================================
	SUMAR EL TOTAL DE COMPRAS
	=============================================*/
	public function mdlSumaTotalCompras($tabla){	
		$stmt = Conexion::conectar()->prepare("SELECT SUM(neto) as total FROM $tabla");
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;

	}

	/*=============================================
	RANGO FECHAS
	=============================================*/	
	static public function mdlMostrarProveedoresInforme($tabla, $fechaInicial, $fechaFinal){
		if($fechaInicial == null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");
			$stmt -> execute();
			return $stmt -> fetchAll();	

		}else{
			$stmt = Conexion::conectar()->prepare("SELECT id_proveedor, sum(total) as total, count(id) as compras FROM $tabla WHERE fecha>='$fechaInicial' AND fecha<='$fechaFinal' group by id_proveedor order by total DESC" );
	
		}
		$stmt -> execute();
		return $stmt -> fetchAll();

	}

	/*=============================================
	ULTIMO ID / CODIGO COMPRAS
	=============================================*/
	static public function mdlUltimoIdCodigoCompras($item){	
		$stmt = Conexion::conectar()->prepare("SELECT MAX($item) as ultimo FROM compras");
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;
	}

}