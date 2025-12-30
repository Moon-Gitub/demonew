<?php 

require_once "conexion.php";

class ModeloSistemaCobro{

	/*=============================================
	MOSTRAR CLIENTES
	=============================================*/
	static public function mdlMostrarClientesCobro($idCliente){

		if($idCliente != null){

			$stmt = Conexion::conectarMoon()->prepare("SELECT * FROM clientes WHERE id = :id");
			$stmt -> bindParam(":id", $idCliente, PDO::PARAM_INT);
			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectarMoon()->prepare("SELECT * FROM clientes");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}
		

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	SALDO EN CUENTA CORRIENTE
	=============================================*/
	static public function mdlMostrarSaldoCuentaCorriente($idCliente){	

		//Solo traigo donde ventas - compras es mayor a 0
		$stmt = Conexion::conectarMoon()->prepare("SELECT SUM(IF (cc.tipo = 0, cc.importe, 0)) AS ventas, SUM(IF (cc.tipo = 1, cc.importe, 0)) AS pagos, (SUM(IF (cc.tipo = 0, cc.importe, 0)) - SUM(IF (cc.tipo = 1, cc.importe, 0))) as saldo FROM clientes_cuenta_corriente cc WHERE cc.id_cliente = :id_cliente");
		$stmt -> bindParam(":id_cliente", $idCliente, PDO::PARAM_INT);
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	ACTUALIZAR ESTADO CLIENTE
	=============================================*/
	static public function mdlActualizarClientesCobro($idCliente, $estado){

		if($idCliente == null) {
			error_log("ERROR: idCliente es null en mdlActualizarClientesCobro");
			return false;
		}

		try {
			$conexion = Conexion::conectarMoon();
			
			if (!$conexion) {
				error_log("ERROR: No se pudo conectar a BD Moon en mdlActualizarClientesCobro");
				return false;
			}
			
			$stmt = $conexion->prepare("UPDATE clientes SET estado_bloqueo = :estado WHERE id = :id");
			$stmt->bindParam(":estado", $estado, PDO::PARAM_INT);
			$stmt->bindParam(":id", $idCliente, PDO::PARAM_INT);

			if($stmt->execute()) {
				error_log("✅ Estado de bloqueo actualizado. Cliente: $idCliente, Estado: " . ($estado == 1 ? 'BLOQUEADO' : 'DESBLOQUEADO'));
				return $estado;
			} else {
				error_log("ERROR al ejecutar UPDATE estado_bloqueo: " . print_r($stmt->errorInfo(), true));
				return false;
			}

		} catch (PDOException $e) {
			error_log("ERROR en mdlActualizarClientesCobro: " . $e->getMessage());
			return false;
		}

		$stmt->closeCursor();
		$stmt = null;

	}

	/*=============================================
	consulto el ultimo registro del cliente (para sacar la descripcion de lo que está debiendo)
	=============================================*/
	static public function mdlMostrarMovimientoCuentaCorriente($idCliente){	

		//Solo traigo donde ventas - compras es mayor a 0
		// ✅ FIX: Usar parámetros separados para el WHERE y el subquery
		$stmt = Conexion::conectarMoon()->prepare("SELECT * FROM clientes_cuenta_corriente WHERE id_cliente = :id_cliente AND id = (SELECT MAX(id) FROM clientes_cuenta_corriente WHERE id_cliente = :id_cliente2 AND tipo = 0)");

		$stmt -> bindParam(":id_cliente", $idCliente, PDO::PARAM_INT);
		$stmt -> bindParam(":id_cliente2", $idCliente, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	REGISTRAR PAGO CLIENTE
	=============================================*/
	static public function mdlRegistrarMovimientoCuentaCorriente($idCliente, $abonoMensual){

		// Validar parámetros
		if (!$idCliente || $idCliente <= 0) {
			error_log("ERROR mdlRegistrarMovimientoCuentaCorriente: ID cliente inválido: $idCliente");
			return array("error" => "ID cliente inválido");
		}

		if (!$abonoMensual || floatval($abonoMensual) <= 0) {
			error_log("ERROR mdlRegistrarMovimientoCuentaCorriente: Monto inválido: $abonoMensual");
			return array("error" => "Monto inválido");
		}

		try {
			$conexion = Conexion::conectarMoon();
			
			if (!$conexion) {
				error_log("ERROR CRÍTICO mdlRegistrarMovimientoCuentaCorriente: No se pudo conectar a BD Moon");
				return array("error" => "Error de conexión a BD Moon");
			}

			$stmt = $conexion->prepare("INSERT INTO clientes_cuenta_corriente (fecha, id_cliente, tipo, descripcion, importe) VALUES(:fecha, :id_cliente, 1, 'PAGO CTA CTE DESDE MERCADO PAGO', :importe)");
			$fecha = date('Y-m-d H:i:s');
			$stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
			$stmt->bindParam(":id_cliente", $idCliente, PDO::PARAM_INT);
			$monto = floatval($abonoMensual);
			$stmt->bindParam(":importe", $monto, PDO::PARAM_STR);

			if($stmt->execute()) {
				$idMovimiento = $conexion->lastInsertId();
				error_log("✅ Movimiento de cuenta corriente registrado. ID: $idMovimiento, Cliente: $idCliente, Monto: $monto");
				return "ok";
			} else {
				$errorInfo = $stmt->errorInfo();
				error_log("ERROR al ejecutar INSERT en clientes_cuenta_corriente: " . print_r($errorInfo, true));
				return $errorInfo;
			}

		} catch (PDOException $e) {
			error_log("EXCEPCIÓN al registrar movimiento cuenta corriente: " . $e->getMessage());
			error_log("Stack trace: " . $e->getTraceAsString());
			return array("error" => $e->getMessage());
		}

		$stmt->closeCursor();
		$stmt = null;

	}
	
	/*=============================================
	REGISTRAR INTERES EN CTA CTE CLIENTE
	=============================================*/
	static public function mdlRegistrarInteresCuentaCorriente($idCliente, $interes){

		// Validar parámetros
		if (!$idCliente || $idCliente <= 0) {
			error_log("ERROR mdlRegistrarInteresCuentaCorriente: ID cliente inválido: $idCliente");
			return array("error" => "ID cliente inválido");
		}

		if (!$interes || floatval($interes) <= 0) {
			error_log("ERROR mdlRegistrarInteresCuentaCorriente: Interés inválido: $interes");
			return array("error" => "Interés inválido");
		}

		try {
			$conexion = Conexion::conectarMoon();
			
			if (!$conexion) {
				error_log("ERROR CRÍTICO mdlRegistrarInteresCuentaCorriente: No se pudo conectar a BD Moon");
				return array("error" => "Error de conexión a BD Moon");
			}

			$stmt = $conexion->prepare("INSERT INTO clientes_cuenta_corriente (fecha, id_cliente, tipo, descripcion, importe) VALUES(:fecha, :id_cliente, 0, 'INTERES POR MORA', :importe)");
			$fecha = date('Y-m-d H:i:s');
			$stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
			$stmt->bindParam(":id_cliente", $idCliente, PDO::PARAM_INT);
			$montoInteres = floatval($interes);
			$stmt->bindParam(":importe", $montoInteres, PDO::PARAM_STR);

			if($stmt->execute()) {
				$idMovimiento = $conexion->lastInsertId();
				error_log("✅ Interés por mora registrado. ID: $idMovimiento, Cliente: $idCliente, Monto: $montoInteres");
				return "ok";
			} else {
				$errorInfo = $stmt->errorInfo();
				error_log("ERROR al ejecutar INSERT de interés: " . print_r($errorInfo, true));
				return $errorInfo;
			}

		} catch (PDOException $e) {
			error_log("EXCEPCIÓN al registrar interés: " . $e->getMessage());
			error_log("Stack trace: " . $e->getTraceAsString());
			return array("error" => $e->getMessage());
		}

		$stmt->closeCursor();
		$stmt = null;

	}

}
