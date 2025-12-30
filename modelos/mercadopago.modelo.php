<?php

require_once "conexion.php";

class ModeloMercadoPago {

	/*=============================================
	REGISTRAR INTENTO DE PAGO (PREFERENCIA CREADA)
	=============================================*/
	static public function mdlRegistrarIntentoPago($datos) {

		try {
			$stmt = Conexion::conectarMoon()->prepare("INSERT INTO mercadopago_intentos
				(id_cliente_moon, preference_id, monto, descripcion, fecha_creacion, estado)
				VALUES (:id_cliente, :preference_id, :monto, :descripcion, :fecha_creacion, :estado)");

			$stmt->bindParam(":id_cliente", $datos["id_cliente_moon"], PDO::PARAM_INT);
			$stmt->bindParam(":preference_id", $datos["preference_id"], PDO::PARAM_STR);
			$stmt->bindParam(":monto", $datos["monto"], PDO::PARAM_STR);
			$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
			$stmt->bindParam(":fecha_creacion", $datos["fecha_creacion"], PDO::PARAM_STR);
			$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

			if ($stmt->execute()) {
				return "ok";
			} else {
				return $stmt->errorInfo();
			}

		} catch (PDOException $e) {
			error_log("Error al registrar intento de pago: " . $e->getMessage());
			return "error";
		}

		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	REGISTRAR PAGO CONFIRMADO
	=============================================*/
	static public function mdlRegistrarPagoConfirmado($datos) {

		// Validar datos requeridos
		if (!isset($datos["id_cliente_moon"]) || empty($datos["id_cliente_moon"])) {
			error_log("ERROR mdlRegistrarPagoConfirmado: id_cliente_moon no proporcionado");
			error_log("Datos recibidos: " . print_r($datos, true));
			return array("error" => "id_cliente_moon requerido");
		}

		if (!isset($datos["payment_id"]) || empty($datos["payment_id"])) {
			error_log("ERROR mdlRegistrarPagoConfirmado: payment_id no proporcionado");
			return array("error" => "payment_id requerido");
		}

		try {
			$conexion = Conexion::conectarMoon();
			
			if (!$conexion) {
				error_log("ERROR CRÍTICO mdlRegistrarPagoConfirmado: No se pudo conectar a BD Moon");
				return array("error" => "Error de conexión a BD Moon");
			}

			$stmt = $conexion->prepare("INSERT INTO mercadopago_pagos
				(id_cliente_moon, payment_id, preference_id, monto, estado, fecha_pago, payment_type, payment_method_id, datos_json)
				VALUES (:id_cliente, :payment_id, :preference_id, :monto, :estado, :fecha_pago, :payment_type, :payment_method_id, :datos_json)");

			$stmt->bindParam(":id_cliente", $datos["id_cliente_moon"], PDO::PARAM_INT);
			$stmt->bindParam(":payment_id", $datos["payment_id"], PDO::PARAM_STR);
			$preferenceId = isset($datos["preference_id"]) ? $datos["preference_id"] : null;
			$stmt->bindParam(":preference_id", $preferenceId, PDO::PARAM_STR);
			$monto = isset($datos["monto"]) ? floatval($datos["monto"]) : 0;
			$stmt->bindParam(":monto", $monto, PDO::PARAM_STR);
			$estado = isset($datos["estado"]) ? $datos["estado"] : 'approved';
			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
			$fechaPago = isset($datos["fecha_pago"]) ? $datos["fecha_pago"] : date('Y-m-d H:i:s');
			$stmt->bindParam(":fecha_pago", $fechaPago, PDO::PARAM_STR);
			$paymentType = isset($datos["payment_type"]) ? $datos["payment_type"] : null;
			$stmt->bindParam(":payment_type", $paymentType, PDO::PARAM_STR);
			$paymentMethodId = isset($datos["payment_method_id"]) ? $datos["payment_method_id"] : null;
			$stmt->bindParam(":payment_method_id", $paymentMethodId, PDO::PARAM_STR);
			$datosJson = isset($datos["datos_json"]) ? $datos["datos_json"] : null;
			$stmt->bindParam(":datos_json", $datosJson, PDO::PARAM_STR);

			if ($stmt->execute()) {
				$idPago = $conexion->lastInsertId();
				error_log("✅ Pago registrado exitosamente en mercadopago_pagos. ID: $idPago, Cliente: " . $datos["id_cliente_moon"] . ", Payment ID: " . $datos["payment_id"] . ", Monto: $monto");
				return "ok";
			} else {
				$errorInfo = $stmt->errorInfo();
				error_log("ERROR al ejecutar INSERT en mercadopago_pagos: " . print_r($errorInfo, true));
				error_log("Datos intentados: " . print_r($datos, true));
				return $errorInfo;
			}

		} catch (PDOException $e) {
			error_log("EXCEPCIÓN al registrar pago confirmado: " . $e->getMessage());
			error_log("Stack trace: " . $e->getTraceAsString());
			error_log("Datos: " . print_r($datos, true));
			return array("error" => $e->getMessage());
		}

		$stmt->closeCursor();
		$stmt = null;
	}

	/*=============================================
	VERIFICAR SI UN PAGO YA FUE PROCESADO
	=============================================*/
	static public function mdlVerificarPagoProcesado($paymentId) {

		try {
			$stmt = Conexion::conectarMoon()->prepare("SELECT COUNT(*) as total FROM mercadopago_pagos WHERE payment_id = :payment_id");

			$stmt->bindParam(":payment_id", $paymentId, PDO::PARAM_STR);
			$stmt->execute();

			$resultado = $stmt->fetch();

			return ($resultado["total"] > 0);

		} catch (PDOException $e) {
			error_log("Error al verificar pago procesado: " . $e->getMessage());
			return false;
		}

		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	OBTENER HISTORIAL DE PAGOS DE UN CLIENTE
	=============================================*/
	static public function mdlObtenerHistorialPagos($idCliente) {

		try {
			$stmt = Conexion::conectarMoon()->prepare("SELECT * FROM mercadopago_pagos
				WHERE id_cliente_moon = :id_cliente
				ORDER BY fecha_pago DESC");

			$stmt->bindParam(":id_cliente", $idCliente, PDO::PARAM_INT);
			$stmt->execute();

			return $stmt->fetchAll();

		} catch (PDOException $e) {
			error_log("Error al obtener historial de pagos: " . $e->getMessage());
			return array();
		}

		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	REGISTRAR WEBHOOK RECIBIDO
	=============================================*/
	static public function mdlRegistrarWebhook($datos) {

		try {
			$conexion = Conexion::conectarMoon();
			
			if (!$conexion) {
				error_log("Error: No se pudo conectar a BD Moon en mdlRegistrarWebhook");
				return false;
			}
			
			$stmt = $conexion->prepare("INSERT INTO mercadopago_webhooks
				(topic, resource_id, datos_json, fecha_recibido, procesado)
				VALUES (:topic, :resource_id, :datos_json, :fecha_recibido, :procesado)");

			$stmt->bindParam(":topic", $datos["topic"], PDO::PARAM_STR);
			$stmt->bindParam(":resource_id", $datos["resource_id"], PDO::PARAM_STR);
			$stmt->bindParam(":datos_json", $datos["datos_json"], PDO::PARAM_STR);
			$stmt->bindParam(":fecha_recibido", $datos["fecha_recibido"], PDO::PARAM_STR);
			$stmt->bindParam(":procesado", $datos["procesado"], PDO::PARAM_INT);

			if ($stmt->execute()) {
				return $conexion->lastInsertId(); // ✅ CORRECTO: usar $conexion, no $stmt
			} else {
				return false;
			}

		} catch (PDOException $e) {
			error_log("Error al registrar webhook: " . $e->getMessage());
			return false;
		}

		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	MARCAR WEBHOOK COMO PROCESADO
	=============================================*/
	static public function mdlMarcarWebhookProcesado($webhookId) {

		try {
			$stmt = Conexion::conectarMoon()->prepare("UPDATE mercadopago_webhooks
				SET procesado = 1, fecha_procesado = :fecha_procesado
				WHERE id = :id");

			$fechaProcesado = date('Y-m-d H:i:s');
			$stmt->bindParam(":fecha_procesado", $fechaProcesado, PDO::PARAM_STR);
			$stmt->bindParam(":id", $webhookId, PDO::PARAM_INT);

			if ($stmt->execute()) {
				return "ok";
			} else {
				return "error";
			}

		} catch (PDOException $e) {
			error_log("Error al marcar webhook procesado: " . $e->getMessage());
			return "error";
		}

		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	ACTUALIZAR ESTADO DE INTENTO DE PAGO
	=============================================*/
	static public function mdlActualizarEstadoIntento($preferenceId, $estado) {

		try {
			$stmt = Conexion::conectarMoon()->prepare("UPDATE mercadopago_intentos
				SET estado = :estado, fecha_actualizacion = :fecha_actualizacion
				WHERE preference_id = :preference_id");

			$fechaActualizacion = date('Y-m-d H:i:s');
			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
			$stmt->bindParam(":fecha_actualizacion", $fechaActualizacion, PDO::PARAM_STR);
			$stmt->bindParam(":preference_id", $preferenceId, PDO::PARAM_STR);

			if ($stmt->execute()) {
				return "ok";
			} else {
				return "error";
			}

		} catch (PDOException $e) {
			error_log("Error al actualizar estado de intento: " . $e->getMessage());
			return "error";
		}

		$stmt->close();
		$stmt = null;
	}
}
