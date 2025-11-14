<?php

require_once "conexion.php";

class ModeloCajas{

	/*=============================================
	CREAR CAJA
	=============================================*/
	static public function mdlIngresarCaja($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(fecha, id_usuario, punto_venta, tipo, descripcion, monto, medio_pago, codigo_venta, id_venta, id_cliente_proveedor,  observaciones) VALUES (:fecha, :id_usuario, :punto_venta, :tipo, :descripcion, :monto, :medio_pago, :codigo_venta, :id_venta, :id_cliente_proveedor, :observaciones)");

		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_INT);
		$stmt->bindParam(":punto_venta", $datos["punto_venta"], PDO::PARAM_INT);
		$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_INT);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);
		$stmt->bindParam(":medio_pago", $datos["medio_pago"], PDO::PARAM_STR);
		$stmt->bindParam(":codigo_venta", $datos["codigo_venta"], PDO::PARAM_STR);
		$stmt->bindParam(":id_venta", $datos["id_venta"], PDO::PARAM_INT);
		$stmt->bindParam(":id_cliente_proveedor", $datos["id_cliente_proveedor"], PDO::PARAM_INT);
		$stmt->bindParam(":observaciones", $datos["observaciones"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		} else {

			return $stmt -> errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	EDITAR CLIENTE
	=============================================*/

	// static public function mdlEditarCaja($tabla, $datos){

	// 	$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET tipo = :tipo, descripcion = :descripcion, monto = :monto WHERE id = :id");

	// 	$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
	// 	$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_INT);
	// 	$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
	// 	$stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);

	// 	if($stmt->execute()){

	// 		return "ok";

	// 	}else{

	// 		return "error";
		
	// 	}

	// 	$stmt->close();
	// 	$stmt = null;

	// }


	/*=============================================
	MOSTRAR CAJA
	=============================================*/
	static public function mdlMostrarCajas($tabla, $item, $valor){

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
	RANGO FECHAS
	=============================================*/	
	static public function mdlRangoFechasCajas($tabla, $fechaInicial, $fechaFinal, $numCaja){

		$clausulaCaja = ($numCaja <> 0) ? 'c.punto_venta = ' . $numCaja . ' AND ': '';
		if($fechaInicial == null){

			$stmt = Conexion::conectar()->prepare("SELECT c.*, u.nombre FROM cajas c INNER JOIN usuarios u ON c.id_usuario = u.id ORDER BY c.fecha ASC");

		} else if($fechaInicial == $fechaFinal) {

			$stmt = Conexion::conectar()->prepare("SELECT c.*, u.nombre FROM cajas c INNER JOIN usuarios u ON c.id_usuario = u.id WHERE $clausulaCaja c.fecha LIKE '$fechaFinal%' ORDER BY c.fecha ASC");

		} else {

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d H:i");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d H:i");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT c.*, u.nombre FROM cajas c INNER JOIN usuarios u ON c.id_usuario = u.id WHERE $clausulaCaja c.fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno' ORDER BY c.fecha ASC");

			} else {

				$stmt = Conexion::conectar()->prepare("SELECT c.*, u.nombre FROM cajas c INNER JOIN usuarios u ON c.id_usuario = u.id WHERE $clausulaCaja c.fecha BETWEEN '$fechaInicial%' AND '$fechaFinal%' ORDER BY c.fecha ASC");

			}

		}

		$stmt -> execute();
	
		return $stmt -> fetchAll();	

		$stmt -> close();

		$stmt = null;		

	}
	
	/*=============================================
	RANGO IDS
	=============================================*/	
	static public function mdlRangoIdsCajas($tabla, $idInicial, $idFinal, $numCaja){

        $tabla = ($tabla) ? $tabla : 'cajas';
		$clausulaCaja = ($numCaja <> 0) ? 'c.punto_venta = ' . $numCaja . ' AND ': '';
		$stmt = Conexion::conectar()->prepare("SELECT c.*, u.nombre FROM cajas c INNER JOIN usuarios u ON c.id_usuario = u.id 
		                                        WHERE $clausulaCaja c.id BETWEEN $idInicial AND $idFinal ORDER BY c.fecha ASC");

		$stmt -> execute();
		return $stmt -> fetchAll();	
		$stmt -> close();
		$stmt = null;		

	}

	/*=============================================
	 ELIMINAR CAJA
	 =============================================*/

	// static public function mdlEliminarCaja($tabla, $datos){

	// 	$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

	// 	$stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

	// 	if($stmt -> execute()){

	// 		return "ok";
		
	// 	}else{

	// 		return "error";	

	// 	}

	// 	$stmt -> close();

	// 	$stmt = null;

	// }

	/*=============================================
	SUMAR EL TOTAL DE CAJA
	=============================================*/

	static public function mdlSumaTotalCajas($tabla){	

		$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as ingresos FROM $tabla WHERE tipo = 1");

		$stmt -> execute();

		$totalIng = $stmt -> fetch();

		$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as egresos FROM $tabla WHERE tipo = 0");

		$stmt -> execute();

		$totalEgr = $stmt -> fetch();

		return $totalIng['ingresos'] - $totalEgr['egresos'];

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	MOSTRAR SALDO A XX FECHA 
	=============================================*/ 

	static public function mdlSaldoCajaAl($fecha, $numCaja){

		$clausulaCaja = ($numCaja <> 0) ? 'punto_venta = ' . $numCaja . ' AND ': '';

		$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) AS ingresos FROM cajas WHERE $clausulaCaja tipo = 1 AND fecha < '$fecha%'");
		$stmt -> execute();
		$totalIng = $stmt -> fetch();

		$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) AS egresos FROM cajas WHERE $clausulaCaja tipo = 0 AND fecha < '$fecha%'");
		$stmt -> execute();
		$totalEgr = $stmt -> fetch();

		return $totalIng['ingresos'] - $totalEgr['egresos'];
		$stmt -> close();
		$stmt = null;

	}	

	/*=============================================
	NISTRAR DESCRIPCION
	=============================================*/
	static public function mdlMostrarDescripcion($txt){

		$stmt = Conexion::conectar()->prepare("SELECT DISTINCT descripcion FROM cajas WHERE descripcion NOT LIKE  'Ingreso por venta - N°%' AND descripcion NOT LIKE  'Egreso por devolucion - N°%' AND descripcion <> '' AND descripcion LIKE '%$txt%' ORDER BY descripcion");
		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	TOTAL GASTOS RANGO FECHAS
	=============================================*/	
	static public function mdlRangoTotalesGastos($fechaInicial, $fechaFinal){

		if($fechaInicial == null){

			$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as gastos FROM cajas WHERE descripcion NOT LIKE 'Ingresos por venta - Nº%' AND tipo = 0 AND descripcion <> '' AND descripcion LIKE 'GASTO%'");

		} else if($fechaInicial == $fechaFinal) {

			$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as gastos FROM cajas WHERE fecha LIKE '$fechaFinal%' AND descripcion NOT LIKE 'Ingresos por venta - Nº%' AND tipo = 0 AND descripcion <> '' AND descripcion LIKE 'GASTO%'");

		} else {

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d H:i");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d H:i");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as gastos FROM cajas WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno' AND descripcion NOT LIKE 'Ingresos por venta - Nº%' AND tipo = 0 AND descripcion <> '' AND descripcion LIKE 'GASTO%'");

			} else {

				$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as gastos FROM cajas WHERE fecha BETWEEN '$fechaInicial%' AND '$fechaFinal%' AND descripcion NOT LIKE 'Ingresos por venta - Nº%' AND tipo = 0 AND descripcion <> '' AND descripcion LIKE 'GASTO%'");

			}

		}

		$stmt -> execute();
	
		return $stmt -> fetch();	

		$stmt -> close();

		$stmt = null;		

	}

	/*=============================================
	TOTAL GASTOS RANGO FECHAS
	=============================================*/	
	static public function mdlRangoTotalesRetirosMM($fechaInicial, $fechaFinal){

		if($fechaInicial == null){

			$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as retiros FROM cajas WHERE descripcion NOT LIKE 'Ingresos por venta - Nº%' AND tipo = 0 AND descripcion <> '' AND descripcion LIKE 'RETIRO MM%'");

		} else if($fechaInicial == $fechaFinal) {

			$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as retiros FROM cajas WHERE fecha LIKE '$fechaFinal%' AND descripcion NOT LIKE 'Ingresos por venta - Nº%' AND tipo = 0 AND descripcion <> '' AND descripcion LIKE 'RETIRO MM%'");

		} else {

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d H:i");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d H:i");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as retiros FROM cajas WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno' AND descripcion NOT LIKE 'Ingresos por venta - Nº%' AND tipo = 0 AND descripcion <> '' AND descripcion LIKE 'RETIRO MM%'");

			} else {

				$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as retiros FROM cajas WHERE fecha BETWEEN '$fechaInicial%' AND '$fechaFinal%' AND descripcion NOT LIKE 'Ingresos por venta - Nº%' AND tipo = 0 AND descripcion <> '' AND descripcion LIKE 'RETIRO MM%'");

			}

		}

		$stmt -> execute();
	
		return $stmt -> fetch();	

		$stmt -> close();

		$stmt = null;		

	}

	/*=============================================
	TOTAL GASTOS RANGO FECHAS
	=============================================*/	
	static public function mdlRangoTotalesConsumicionesMM($fechaInicial, $fechaFinal){

		if($fechaInicial == null){

			$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as consumiciones FROM cajas WHERE descripcion NOT LIKE 'Ingresos por venta - Nº%' AND tipo = 0 AND descripcion <> '' AND descripcion LIKE 'CONSUMICIONES MM%'");

		} else if($fechaInicial == $fechaFinal) {

			$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as consumiciones FROM cajas WHERE fecha LIKE '$fechaFinal%' AND descripcion NOT LIKE 'Ingresos por venta - Nº%' AND tipo = 0 AND descripcion <> '' AND descripcion LIKE 'CONSUMICIONES MM%'");

		} else {

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d H:i");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d H:i");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as consumiciones FROM cajas WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno' AND descripcion NOT LIKE 'Ingresos por venta - Nº%' AND tipo = 0 AND descripcion <> '' AND descripcion LIKE 'CONSUMICIONES MM%'");

			} else {

				$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as consumiciones FROM cajas WHERE fecha BETWEEN '$fechaInicial%' AND '$fechaFinal%' AND descripcion NOT LIKE 'Ingresos por venta - Nº%' AND tipo = 0 AND descripcion <> '' AND descripcion LIKE 'CONSUMICIONES MM%'");

			}

		}

		$stmt -> execute();
	
		return $stmt -> fetch();	

		$stmt -> close();

		$stmt = null;		

	}

	/*=============================================
	MEDIOS DE PAGO USADOS
	=============================================*/	
	static public function mdlMediosPagosUsados(){
		$stmt = Conexion::conectar()->prepare("SELECT DISTINCT(medio_pago) FROM cajas");

		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;
	}

	/*=============================================
	MEDIOS DE PAGO USADOS
	=============================================*/	
	static public function mdlSumatoriaMedios($tipo, $medioPago, $fechaInicial, $fechaFinal, $numCaja){
		
		$clausulaCaja = ($numCaja <> 0) ? ' punto_venta = ' . $numCaja . ' AND ': '';

		if($fechaInicial == null){

			$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as total FROM cajas WHERE $clausulaCaja tipo = :tipo AND medio_pago LIKE CONCAT(:medioPago, '%')");


		}else if($fechaInicial == $fechaFinal){

			$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as total FROM cajas WHERE $clausulaCaja tipo = :tipo AND medio_pago LIKE CONCAT(:medioPago, '%') AND fecha LIKE '%$fechaFinal%'");
			
		}else{

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d H:i");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d H:i");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as total FROM cajas WHERE $clausulaCaja tipo = :tipo AND medio_pago LIKE CONCAT(:medioPago, '%') AND fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno'");

			}else{

				$stmt = Conexion::conectar()->prepare("SELECT SUM(monto) as total FROM cajas WHERE $clausulaCaja tipo = :tipo AND medio_pago LIKE CONCAT(:medioPago, '%') AND fecha BETWEEN '$fechaInicial' AND '$fechaFinal'");

			}

		}

		$stmt -> bindParam(":medioPago", $medioPago, PDO::PARAM_STR);
		$stmt -> bindParam(":tipo", $tipo, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;
	}

	/*=============================================
	RANGO FECHAS
	=============================================*/	
	static public function mldRangoFechasCajasUltimoCierre($ultimoIdCaja, $numCaja){

		$clausulaCaja1 = ($numCaja <> 0) ? ' WHERE c.punto_venta = ' . $numCaja : '';
		$clausulaCaja = ($numCaja <> 0) ? ' c.punto_venta = ' . $numCaja . ' AND ': '';

		if($ultimoIdCaja == null){

			$stmt = Conexion::conectar()->prepare("SELECT c.*, u.nombre FROM cajas c INNER JOIN usuarios u ON c.id_usuario = u.id $clausulaCaja1 ORDER BY c.fecha ASC");

		} else {

			$stmt = Conexion::conectar()->prepare("SELECT c.*, u.nombre FROM cajas c INNER JOIN usuarios u ON c.id_usuario = u.id WHERE $clausulaCaja c.id > $ultimoIdCaja ORDER BY c.fecha ASC");

		}

		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	LISTADO MOVIMIENTOS CAJA DESDE ULTIMO CIERRE
	=============================================*/	
	static public function mdlMovimientosCajaDesdeUltimoCierre($idCaja){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM cajas WHERE id > :idCaja ORDER BY id ASC");

		$stmt -> bindParam(":idCaja", $idCaja, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	LISTADO MOVIMIENTOS CAJA SEGUN CIERRE CAJA (INFORME DE CIERRE DE CAJA)
	=============================================*/	
	static public function mdlMovimientosCajaSegunCierre($numCaja, $desdeId, $hastaId){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM cajas WHERE punto_venta = :punto_venta AND id > :desde AND id <= :hasta ORDER BY id ASC");

		$stmt -> bindParam(":punto_venta", $numCaja, PDO::PARAM_INT);
		$stmt -> bindParam(":desde", $desdeId, PDO::PARAM_INT);
		$stmt -> bindParam(":hasta", $hastaId, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;

	}
}