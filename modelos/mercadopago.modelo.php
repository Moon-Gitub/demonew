<?php

require_once "conexion.php";

class ModeloMercadoPago {

	/*=============================================
	REGISTRAR INTENTO DE PAGO (PREFERENCIA CREADA)
	=============================================*/
	static public function mdlRegistrarIntentoPago($datos) {

		try {
			$conexion = Conexion::conectarMoon();
			
			if (!$conexion) {
				error_log("ERROR mdlRegistrarIntentoPago: No se pudo conectar a BD Moon");
				return "error";
			}

			// PREVENIR DUPLICADOS: Verificar si ya existe un intento pendiente para este cliente con el mismo preference_id
			if (isset($datos["preference_id"]) && !empty($datos["preference_id"])) {
				$stmtCheck = $conexion->prepare("SELECT id FROM mercadopago_intentos 
					WHERE preference_id = :preference_id 
					AND estado = 'pendiente' 
					LIMIT 1");
				$stmtCheck->bindParam(":preference_id", $datos["preference_id"], PDO::PARAM_STR);
				$stmtCheck->execute();
				$intentoExistente = $stmtCheck->fetch();
				$stmtCheck->closeCursor();
				
				if ($intentoExistente) {
					error_log("⚠️ Intento ya existe para preference_id: " . $datos["preference_id"] . " (ID: " . $intentoExistente['id'] . ")");
					return "ok"; // Retornar ok porque el intento ya existe
				}
			}

			// PREVENIR MÚLTIPLES INTENTOS PENDIENTES: Verificar si hay un intento pendiente reciente (últimos 30 minutos) para el mismo cliente y monto
			// AUMENTADO DE 5 A 30 MINUTOS para evitar duplicados cuando se recarga la página
			if (isset($datos["id_cliente_moon"]) && isset($datos["monto"])) {
				$monto = floatval($datos["monto"]);
				
				// Verificar por cliente y monto (con tolerancia de 0.01 para redondeos)
				$stmtCheckCliente = $conexion->prepare("SELECT id, preference_id, fecha_creacion FROM mercadopago_intentos 
					WHERE id_cliente_moon = :id_cliente 
					AND ABS(monto - :monto) < 0.01
					AND estado = 'pendiente' 
					AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
					ORDER BY fecha_creacion DESC
					LIMIT 1");
				$stmtCheckCliente->bindParam(":id_cliente", $datos["id_cliente_moon"], PDO::PARAM_INT);
				$stmtCheckCliente->bindParam(":monto", $monto, PDO::PARAM_STR);
				$stmtCheckCliente->execute();
				$intentoReciente = $stmtCheckCliente->fetch();
				$stmtCheckCliente->closeCursor();
				
				if ($intentoReciente) {
					error_log("⚠️ Ya existe un intento pendiente reciente para cliente " . $datos["id_cliente_moon"] . " con monto $monto (ID: " . $intentoReciente['id'] . ", creado: " . $intentoReciente['fecha_creacion'] . ")");
					error_log("   NO se creará un nuevo intento para evitar duplicados");
					return "ok"; // Retornar ok para evitar duplicados
				}
			}
			
			// PREVENIR DUPLICADOS POR PREFERENCE_ID (si no tiene preference_id, verificar por cliente+monto+descripcion)
			// Esto evita crear múltiples intentos cuando se recarga la página sin preference_id
			if ((!isset($datos["preference_id"]) || empty($datos["preference_id"])) && isset($datos["id_cliente_moon"]) && isset($datos["monto"]) && isset($datos["descripcion"])) {
				$monto = floatval($datos["monto"]);
				$descripcion = isset($datos["descripcion"]) ? $datos["descripcion"] : '';
				
				$stmtCheckSinPreference = $conexion->prepare("SELECT id FROM mercadopago_intentos 
					WHERE id_cliente_moon = :id_cliente 
					AND ABS(monto - :monto) < 0.01
					AND descripcion = :descripcion
					AND (preference_id IS NULL OR preference_id = '')
					AND estado = 'pendiente' 
					AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
					LIMIT 1");
				$stmtCheckSinPreference->bindParam(":id_cliente", $datos["id_cliente_moon"], PDO::PARAM_INT);
				$stmtCheckSinPreference->bindParam(":monto", $monto, PDO::PARAM_STR);
				$stmtCheckSinPreference->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
				$stmtCheckSinPreference->execute();
				$intentoSinPreference = $stmtCheckSinPreference->fetch();
				$stmtCheckSinPreference->closeCursor();
				
				if ($intentoSinPreference) {
					error_log("⚠️ Ya existe un intento pendiente sin preference_id para cliente " . $datos["id_cliente_moon"] . " con monto $monto y descripción '$descripcion' (ID: " . $intentoSinPreference['id'] . ")");
					return "ok"; // Retornar ok para evitar duplicados
				}
			}

			$stmt = $conexion->prepare("INSERT INTO mercadopago_intentos
				(id_cliente_moon, preference_id, monto, descripcion, fecha_creacion, estado)
				VALUES (:id_cliente, :preference_id, :monto, :descripcion, :fecha_creacion, :estado)");

			$stmt->bindParam(":id_cliente", $datos["id_cliente_moon"], PDO::PARAM_INT);
			$preferenceId = isset($datos["preference_id"]) ? $datos["preference_id"] : null;
			$stmt->bindParam(":preference_id", $preferenceId, PDO::PARAM_STR);
			$monto = isset($datos["monto"]) ? floatval($datos["monto"]) : 0;
			$stmt->bindParam(":monto", $monto, PDO::PARAM_STR);
			$descripcion = isset($datos["descripcion"]) ? $datos["descripcion"] : 'Pago';
			$stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
			$fechaCreacion = isset($datos["fecha_creacion"]) ? $datos["fecha_creacion"] : date('Y-m-d H:i:s');
			$stmt->bindParam(":fecha_creacion", $fechaCreacion, PDO::PARAM_STR);
			$estado = isset($datos["estado"]) ? $datos["estado"] : 'pendiente';
			$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);

			if ($stmt->execute()) {
				$idIntento = $conexion->lastInsertId();
				error_log("✅ Intento de pago registrado. ID: $idIntento, Cliente: " . $datos["id_cliente_moon"] . ", Preference ID: " . ($preferenceId ?: 'N/A'));
				return "ok";
			} else {
				$errorInfo = $stmt->errorInfo();
				error_log("ERROR al ejecutar INSERT en mercadopago_intentos: " . print_r($errorInfo, true));
				return $errorInfo;
			}

		} catch (PDOException $e) {
			error_log("EXCEPCIÓN al registrar intento de pago: " . $e->getMessage());
			error_log("Stack trace: " . $e->getTraceAsString());
			return "error";
		}

		$stmt->closeCursor();
		$stmt = null;
	}

	/*=============================================
	REGISTRAR PAGO CONFIRMADO
	=============================================*/
	static public function mdlRegistrarPagoConfirmado($datos) {

		// Validar datos requeridos
		// NOTA: id_cliente_moon puede ser 0 para pagos QR/ventas POS sin cliente asociado
		if (!isset($datos["id_cliente_moon"]) || $datos["id_cliente_moon"] === null || $datos["id_cliente_moon"] === '') {
			error_log("ERROR mdlRegistrarPagoConfirmado: id_cliente_moon no proporcionado");
			error_log("Datos recibidos: " . print_r($datos, true));
			return array("error" => "id_cliente_moon requerido (puede ser 0 para pagos sin cliente)");
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

			// Usar INSERT IGNORE para evitar duplicados si hay race condition
			// Pero primero verificar explícitamente para mejor logging
			$stmtCheck = $conexion->prepare("SELECT id FROM mercadopago_pagos WHERE payment_id = :payment_id LIMIT 1");
			$stmtCheck->bindParam(":payment_id", $datos["payment_id"], PDO::PARAM_STR);
			$stmtCheck->execute();
			$pagoExistente = $stmtCheck->fetch();
			$stmtCheck->closeCursor();
			
			if ($pagoExistente) {
				error_log("⚠️ ADVERTENCIA: Payment ID " . $datos["payment_id"] . " ya existe en mercadopago_pagos (ID: " . $pagoExistente['id'] . ")");
				error_log("   Esto no debería pasar si se verificó antes. Puede ser race condition o webhook duplicado.");
				// Retornar "ok" en lugar de error para evitar que el webhook se marque como fallido
				// El pago ya está registrado, así que consideramos el proceso exitoso
				return "ok";
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
	OBTENER INTENTO PENDIENTE RECIENTE PARA UN CLIENTE
	=============================================*/
	static public function mdlObtenerIntentoPendienteReciente($idCliente, $monto = null) {
		
		try {
			$conexion = Conexion::conectarMoon();
			
			if (!$conexion) {
				error_log("ERROR mdlObtenerIntentoPendienteReciente: No se pudo conectar a BD Moon");
				return null;
			}

			// Buscar intentos pendientes recientes (últimos 60 minutos) para este cliente
			// AUMENTADO A 60 MINUTOS para evitar crear nuevas preferencias innecesariamente
			$sql = "SELECT id, preference_id, monto, descripcion, fecha_creacion 
				FROM mercadopago_intentos 
				WHERE id_cliente_moon = :id_cliente 
				AND estado = 'pendiente' 
				AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)";
			
			$params = [":id_cliente" => $idCliente];
			
			// Si se especifica monto, filtrar por monto también
			if ($monto !== null) {
				$montoFloat = floatval($monto);
				$sql .= " AND ABS(monto - :monto) < 0.01";
				$params[":monto"] = $montoFloat;
			}
			
			$sql .= " ORDER BY fecha_creacion DESC LIMIT 1";
			
			$stmt = $conexion->prepare($sql);
			foreach ($params as $key => $value) {
				$stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
			}
			$stmt->execute();
			
			$intento = $stmt->fetch(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			
			return $intento ? $intento : null;
			
		} catch (PDOException $e) {
			error_log("EXCEPCIÓN al obtener intento pendiente: " . $e->getMessage());
			return null;
		}
	}

	/*=============================================
	VERIFICAR SI UN PAGO YA FUE PROCESADO
	=============================================*/
	static public function mdlVerificarPagoProcesado($paymentId) {

		if (empty($paymentId)) {
			error_log("ADVERTENCIA mdlVerificarPagoProcesado: payment_id vacío");
			return false;
		}

		try {
			$conexion = Conexion::conectarMoon();
			
			if (!$conexion) {
				error_log("ERROR mdlVerificarPagoProcesado: No se pudo conectar a BD Moon");
				return false; // Si no hay conexión, asumir que no está procesado para intentar procesarlo
			}

			$stmt = $conexion->prepare("SELECT COUNT(*) as total FROM mercadopago_pagos WHERE payment_id = :payment_id");

			$stmt->bindParam(":payment_id", $paymentId, PDO::PARAM_STR);
			$stmt->execute();

			$resultado = $stmt->fetch();
			$total = isset($resultado["total"]) ? intval($resultado["total"]) : 0;
			
			if ($total > 0) {
				error_log("⚠️ Pago $paymentId ya existe en mercadopago_pagos (total: $total)");
				// Obtener detalles del pago existente para logging
				$stmtDetalle = $conexion->prepare("SELECT id, id_cliente_moon, monto, estado, fecha_pago FROM mercadopago_pagos WHERE payment_id = :payment_id LIMIT 1");
				$stmtDetalle->bindParam(":payment_id", $paymentId, PDO::PARAM_STR);
				$stmtDetalle->execute();
				$pagoExistente = $stmtDetalle->fetch();
				if ($pagoExistente) {
					error_log("   Detalles del pago existente: ID=" . $pagoExistente['id'] . ", Cliente=" . $pagoExistente['id_cliente_moon'] . ", Monto=" . $pagoExistente['monto'] . ", Estado=" . $pagoExistente['estado'] . ", Fecha=" . $pagoExistente['fecha_pago']);
				}
				$stmtDetalle->closeCursor();
			}

			return ($total > 0);

		} catch (PDOException $e) {
			error_log("EXCEPCIÓN al verificar pago procesado: " . $e->getMessage());
			error_log("Stack trace: " . $e->getTraceAsString());
			return false; // En caso de error, asumir que no está procesado
		}

		$stmt->closeCursor();
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
	/*=============================================
	ACTUALIZAR ESTADO DE INTENTO
	Puede actualizar por preference_id o por order_id (para modelo atendido)
	=============================================*/
	static public function mdlActualizarEstadoIntento($preferenceId, $estado, $orderId = null) {

		try {
			$conexion = Conexion::conectarMoon();
			
			if (!$conexion) {
				error_log("ERROR mdlActualizarEstadoIntento: No se pudo conectar a BD Moon");
				return "error";
			}

			$fechaActualizacion = date('Y-m-d H:i:s');
			$filasAfectadas = 0;

			// Método 1: Actualizar por preference_id (si existe)
			if ($preferenceId && !empty($preferenceId)) {
				$stmt = $conexion->prepare("UPDATE mercadopago_intentos
					SET estado = :estado, fecha_actualizacion = :fecha_actualizacion
					WHERE preference_id = :preference_id AND estado = 'pendiente'");

				$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
				$stmt->bindParam(":fecha_actualizacion", $fechaActualizacion, PDO::PARAM_STR);
				$stmt->bindParam(":preference_id", $preferenceId, PDO::PARAM_STR);

				if ($stmt->execute()) {
					$filasAfectadas = $stmt->rowCount();
					if ($filasAfectadas > 0) {
						error_log("✅ Estado de intento actualizado por preference_id: $preferenceId -> $estado");
					}
				}
				$stmt->closeCursor();
			}

			// Método 2: Si no se actualizó y hay order_id, buscar intentos pendientes recientes
			// Esto es para el modelo atendido (QR estático) donde no hay preference_id
			// NOTA: Los pagos QR pueden no tener intentos registrados, así que esto es opcional
			if ($filasAfectadas == 0 && $orderId && !empty($orderId)) {
				// Intentar actualizar intentos pendientes recientes (últimos 10 minutos)
				// Esto es un fallback para cuando no hay preference_id
				// Solo actualizamos si hay intentos pendientes recientes
				$stmt = $conexion->prepare("UPDATE mercadopago_intentos
					SET estado = :estado, fecha_actualizacion = :fecha_actualizacion
					WHERE estado = 'pendiente' 
					AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
					ORDER BY fecha_creacion DESC
					LIMIT 1");

				$stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
				$stmt->bindParam(":fecha_actualizacion", $fechaActualizacion, PDO::PARAM_STR);

				if ($stmt->execute()) {
					$filasAfectadas = $stmt->rowCount();
					if ($filasAfectadas > 0) {
						error_log("✅ Estado de intento actualizado por order_id (fallback): $orderId -> $estado");
					} else {
						error_log("ℹ️ No se encontraron intentos pendientes recientes para actualizar con order_id: $orderId (puede ser pago QR sin intento previo)");
					}
				}
				$stmt->closeCursor();
			}

			if ($filasAfectadas > 0) {
				return "ok";
			} else {
				error_log("⚠️ No se encontró intento pendiente para actualizar. Preference ID: " . ($preferenceId ?: 'N/A') . ", Order ID: " . ($orderId ?: 'N/A'));
				return "ok"; // Retornar ok aunque no se actualizó (puede que ya esté actualizado)
			}

		} catch (PDOException $e) {
			error_log("EXCEPCIÓN al actualizar estado de intento: " . $e->getMessage());
			error_log("Stack trace: " . $e->getTraceAsString());
			return "error";
		}
	}
}
