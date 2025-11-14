<?php

require_once "conexion.php";

class ModeloPresupuestos{

	/*=============================================
	RANGO FECHAS
	=============================================*/	
	static public function mdlRangoFechasPresupuestos($tabla, $fechaInicial, $fechaFinal){

		if($fechaInicial == null){

			$stmt = Conexion::conectar()->prepare("SELECT pre.*, cli.nombre as cliente FROM presupuestos pre INNER JOIN clientes cli ON pre.id_cliente = cli.id ORDER BY pre.id DESC");

		}else if($fechaInicial == $fechaFinal){

			$stmt = Conexion::conectar()->prepare("SELECT pre.*, cli.nombre as cliente FROM presupuestos pre INNER JOIN clientes cli ON pre.id_cliente = cli.id WHERE pre.fecha like '%$fechaFinal%' ORDER BY pre.id DESC");

			$stmt -> bindParam(":fecha", $fechaFinal, PDO::PARAM_STR);

		}else{

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT pre.*, cli.nombre as cliente FROM presupuestos pre INNER JOIN clientes cli ON pre.id_cliente = cli.id WHERE pre.fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno' ORDER BY pre.id DESC");

			}else{


				$stmt = Conexion::conectar()->prepare("SELECT pre.*, cli.nombre as cliente FROM presupuestos pre INNER JOIN clientes cli ON pre.id_cliente = cli.id WHERE pre.fecha BETWEEN '$fechaInicial' AND '$fechaFinal' ORDER BY pre.id DESC");

			}

		}

		$stmt -> execute();

		return $stmt -> fetchAll();

	}

	/*=============================================
	MOSTRAR PRESUPUESTOS
	=============================================*/
	static public function mdlMostrarPresupuestos($tabla, $item, $valor){

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

	/*=============================================
	REGISTRO DE PRESUPUESTO
	=============================================*/
	static public function mdlIngresarPresupuesto($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(fecha, id_cliente, id_vendedor, productos, impuesto, impuesto_detalle, neto, neto_gravado, base_imponible_0, base_imponible_2, base_imponible_5, base_imponible_10, base_imponible_21, base_imponible_27, iva_2, iva_5, iva_10, iva_21, iva_27, total, metodo_pago, estado, observaciones) VALUES (:fecha, :id_cliente, :id_vendedor, :productos, :impuesto, :impuesto_detalle, :neto, :neto_gravado, :base_imponible_0, :base_imponible_2, :base_imponible_5, :base_imponible_10, :base_imponible_21, :base_imponible_27, :iva_2, :iva_5, :iva_10, :iva_21, :iva_27, :total, :metodo_pago, :estado, :observaciones)");

		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
		$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
		$stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
		$stmt->bindParam(":impuesto_detalle", $datos["impuesto_detalle"], PDO::PARAM_STR);
		$stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
	   	$stmt->bindParam(":neto_gravado", $datos["neto_gravado"], PDO::PARAM_STR);
		$stmt->bindParam(":base_imponible_0", $datos["base_imponible_0"], PDO::PARAM_STR);
		$stmt->bindParam(":base_imponible_2", $datos["base_imponible_2"], PDO::PARAM_STR);
		$stmt->bindParam(":base_imponible_5", $datos["base_imponible_5"], PDO::PARAM_STR);
		$stmt->bindParam(":base_imponible_10", $datos["base_imponible_10"], PDO::PARAM_STR);
		$stmt->bindParam(":base_imponible_21", $datos["base_imponible_21"], PDO::PARAM_STR);
		$stmt->bindParam(":base_imponible_27", $datos["base_imponible_27"], PDO::PARAM_STR);
		$stmt->bindParam(":iva_2", $datos["iva_2"], PDO::PARAM_STR);
		$stmt->bindParam(":iva_5", $datos["iva_5"], PDO::PARAM_STR);
		$stmt->bindParam(":iva_10", $datos["iva_10"], PDO::PARAM_STR);
		$stmt->bindParam(":iva_21", $datos["iva_21"], PDO::PARAM_STR);
		$stmt->bindParam(":iva_27", $datos["iva_27"], PDO::PARAM_STR);
		$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
		$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_INT);
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
	ELIMINAR PRESUPUESTO
	=============================================*/
	static public function mdlEliminarPresupuesto($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return $stmt->errorInfo();	

		}

		$stmt -> close();

		$stmt = null;

	}

	//--------------------------------------------------------------------------------------------------------

	/*=============================================
	EDITAR PRESUPUESTO
	=============================================*/
	static public function mdlEditarPresupuesto($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET  id_cliente = :id_cliente, cbte_tipo = :cbte_tipo, id_vendedor = :id_vendedor, productos = :productos, impuesto = :impuesto, neto = :neto, total= :total, metodo_pago = :metodo_pago, pto_vta = :pto_vta, concepto = :concepto, fec_desde = :fec_desde, fec_hasta = :fec_hasta, fec_vencimiento = :fec_vencimiento, observaciones_vta = :observaciones_vta WHERE codigo = :codigo");

		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
		$stmt->bindParam(":cbte_tipo", $datos["cbte_tipo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
		$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
		$stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
		$stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
		$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
		$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
		$stmt->bindParam(":pto_vta", $datos["pto_vta"], PDO::PARAM_INT);
		$stmt->bindParam(":concepto", $datos["concepto"], PDO::PARAM_INT);
		$stmt->bindParam(":fec_desde", $datos["fec_desde"], PDO::PARAM_STR);
		$stmt->bindParam(":fec_hasta", $datos["fec_hasta"], PDO::PARAM_STR);
		$stmt->bindParam(":fec_vencimiento", $datos["fec_vencimiento"], PDO::PARAM_STR);		
		$stmt->bindParam(":observaciones_vta", $datos["observaciones_vta"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt -> errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR PRESUPUESTO
	=============================================*/
	static public function mdlActualizarPresupuesto($tabla, $item1, $valor1, $id){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = $valor1 WHERE id = $id");

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return $stmt -> errorInfo();	

		}

		$stmt -> close();

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
	TOTAL PRESUPUESTOS RANGO FECHAS
	=============================================*/	
	static public function mdlRangoFechasTotalPresupuestos($fechaInicial, $fechaFinal){

		if($fechaInicial == null){

			$stmt = Conexion::conectar()->prepare("SELECT SUM(total) as total FROM ventas");

		}else if($fechaInicial == $fechaFinal){

			$stmt = Conexion::conectar()->prepare("SELECT SUM(total) as total FROM ventas WHERE fecha like '%$fechaFinal%'");

		}else{

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT  SUM(total) as total FROM ventas WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno'");

			}else{

				$stmt = Conexion::conectar()->prepare("SELECT  SUM(total) as total FROM ventas WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal'");

			}

		}

		$stmt -> execute();

		return $stmt -> fetch();

	}

	/*=============================================
	MOSTRAR PRESUPUESTO CON CLIENTE
	=============================================*/
	static public function mdlMostrarPresupuestoConCliente($idPresupuesto){

		$stmt = Conexion::conectar()->prepare("SELECT v.*, c.id as idCliente, c.nombre, c.documento FROM ventas v LEFT JOIN clientes c ON v.id_cliente = c.id WHERE v.id = :id");

		$stmt -> bindParam(":id", $idVenta, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();
		
		$stmt -> close();

		$stmt = null;

	}

}