<?php
//DEMO
require_once "conexion.php";

class ModeloCajaCierres{

	/*=============================================
	CREAR CIERRE CAJA
	=============================================*/
	static public function mdlIngresarCierreCaja($datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO caja_cierres(fecha_hora, punto_venta_cobro, ultimo_id_caja, total_ingresos, total_egresos, detalle_ingresos, detalle_egresos, apertura_siguiente_monto, id_usuario_cierre, detalle, detalle_ingresos_manual, detalle_egresos_manual, diferencias) VALUES (:fecha_hora, :punto_venta_cobro, :ultimo_id_caja, :total_ingresos, :total_egresos, :detalle_ingresos, :detalle_egresos, :apertura_siguiente_monto, :id_usuario_cierre, :detalle, :detalle_ingresos_manual, :detalle_egresos_manual, :diferencias)");

		$stmt->bindParam(":fecha_hora", $datos["fecha_hora"], PDO::PARAM_STR);
		$stmt->bindParam(":ultimo_id_caja", $datos["ultimo_id_caja"], PDO::PARAM_INT);
		$stmt->bindParam(":punto_venta_cobro", $datos["punto_venta_cobro"], PDO::PARAM_INT);
		$stmt->bindParam(":total_ingresos", $datos["total_ingresos"], PDO::PARAM_STR);
		$stmt->bindParam(":total_egresos", $datos["total_egresos"], PDO::PARAM_STR);
		$stmt->bindParam(":detalle_ingresos", $datos["detalle_ingresos"], PDO::PARAM_STR);
		$stmt->bindParam(":detalle_egresos", $datos["detalle_egresos"], PDO::PARAM_STR);
		$stmt->bindParam(":apertura_siguiente_monto", $datos["apertura_siguiente_monto"], PDO::PARAM_STR);
		$stmt->bindParam(":id_usuario_cierre", $datos["id_usuario_cierre"], PDO::PARAM_INT);
		$stmt->bindParam(":detalle", $datos["detalle"], PDO::PARAM_STR);
		$stmt->bindParam(":detalle_ingresos_manual", $datos["detalle_ingresos_manual"], PDO::PARAM_STR);
		$stmt->bindParam(":detalle_egresos_manual", $datos["detalle_egresos_manual"], PDO::PARAM_STR);
		$stmt->bindParam(":diferencias", $datos["diferencias"], PDO::PARAM_STR);

		if($stmt->execute()){
			return "ok";

		}else{
			return $stmt -> errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}
	
	/*=============================================
	RANGO FECHAS
	=============================================*/	
	static public function mdlRangoFechasCajaCierres($fechaInicial, $fechaFinal){

		if($fechaInicial == null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM caja_cierres ORDER BY fecha_hora ASC");

		}else if($fechaInicial == $fechaFinal){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM caja_cierres WHERE fecha_hora LIKE '%$fechaFinal%' ORDER BY fecha_hora ASC");

		}else{
			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d H:i");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d H:i");
			if($fechaFinalMasUno == $fechaActualMasUno){
				$stmt = Conexion::conectar()->prepare("SELECT * FROM caja_cierres WHERE fecha_hora BETWEEN '$fechaInicial' AND '$fechaFinalMasUno' ORDER BY fecha_hora ASC");

			}else{
				$stmt = Conexion::conectar()->prepare("SELECT * FROM caja_cierres WHERE fecha_hora BETWEEN '$fechaInicial' AND '$fechaFinal' ORDER BY fecha_hora ASC");
			
			}
		}
		$stmt -> execute();
		return $stmt -> fetchAll();
	}

	/*=============================================
	MOSTRAR CIERRES CAJA
	=============================================*/
	static public function mdlMostrarCierresCaja($idCierre){
		if($idCierre != null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM caja_cierres WHERE id = :idCierre");
			$stmt -> bindParam(":idCierre", $idCierre, PDO::PARAM_INT);
			$stmt -> execute();
			return $stmt -> fetch();
		}else{
			$stmt = Conexion::conectar()->prepare("SELECT * FROM caja_cierres");
			$stmt -> execute();
			return $stmt -> fetchAll();
		}
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	OBTENER ULTIMO CIERRE DE CAJA PARA LISTAR MOVIMIENTOS DE CAJA
	=============================================*/	
	static public function mdlUltimoCierreCaja($numCaja){
		$stmt = Conexion::conectar()->prepare("SELECT * FROM caja_cierres WHERE id = ( SELECT MAX( id ) FROM caja_cierres WHERE punto_venta_cobro = :punto_venta_cobro ) ");
		//$stmt = Conexion::conectar()->prepare("SELECT MAX( id ) as ultCierre FROM caja_cierres ");
		$stmt -> bindParam(":punto_venta_cobro", $numCaja, PDO::PARAM_INT);
		$stmt -> execute();
		return $stmt -> fetch();
	}

	/*=============================================
	OBTENER EL CIERRE DE CAJA ANTERIOR AL ULTIMO 
	=============================================
	static public function mdlAnteriorUltimoCierreCaja($numCaja){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM caja_cierres WHERE punto_venta_cobro = :punto_venta_cobro AND id = (SELECT MAX(id) FROM caja_cierres WHERE punto_venta_cobro =:punto_venta_cobro AND id < (SELECT MAX(id) FROM caja_cierres WHERE punto_venta_cobro = :punto_venta_cobro))");

		$stmt -> bindParam(":punto_venta_cobro", $numCaja, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();

	}*/	

	/*=============================================
	OBTENER EL CIERRE DE CAJA ANTERIOR AL SELECCIONADO
	=============================================*/	
	static public function mdlAnteriorSeleccionadoCierreCaja($numCaja, $idCierre){
		$stmt = Conexion::conectar()->prepare("SELECT * FROM caja_cierres WHERE punto_venta_cobro = :punto_venta_cobro AND id = (SELECT MAX(id) FROM caja_cierres WHERE punto_venta_cobro = :punto_venta_cobro AND id < :id_cierre)");
		$stmt -> bindParam(":punto_venta_cobro", $numCaja, PDO::PARAM_INT);
		$stmt -> bindParam(":id_cierre", $idCierre, PDO::PARAM_INT);
		$stmt -> execute();
		return $stmt -> fetch();
	}

}