<?php
// ✅ Seguridad AJAX
require_once "seguridad.ajax.php";

// Para peticiones GET (verificar pago, obtener QR, verificar orden) no requerir CSRF, solo sesión y AJAX
// Para peticiones POST (crear preferencia, crear orden) sí requerir CSRF
if (isset($_GET["verificarPago"]) || isset($_GET["obtenerQREstatico"]) || isset($_GET["verificarPagoPorReference"]) || isset($_GET["verificarOrden"])) {
    SeguridadAjax::inicializar(false); // false = no verificar CSRF para GET
} else {
    SeguridadAjax::inicializar(); // Verificar CSRF para POST
}

require_once "../controladores/mercadopago.controlador.php";

/*=============================================
CREAR PREFERENCIA DE PAGO PARA VENTA
=============================================*/
if(isset($_POST["crearPreferenciaVenta"])){
	$monto = isset($_POST["monto"]) ? floatval($_POST["monto"]) : 0;
	$descripcion = isset($_POST["descripcion"]) ? $_POST["descripcion"] : "Venta POS";
	$externalReference = isset($_POST["external_reference"]) ? $_POST["external_reference"] : null;

	if($monto <= 0){
		http_response_code(400);
		echo json_encode(array("error" => true, "mensaje" => "El monto debe ser mayor a 0"));
		exit;
	}

	$respuesta = ControladorMercadoPago::ctrCrearPreferenciaVenta($monto, $descripcion, $externalReference);
	echo json_encode($respuesta);
	exit;
}

/*=============================================
VERIFICAR ESTADO DE PAGO
=============================================*/
if(isset($_GET["verificarPago"]) || isset($_POST["verificarPago"])){
	$preferenceId = isset($_GET["preference_id"]) ? $_GET["preference_id"] : (isset($_POST["preference_id"]) ? $_POST["preference_id"] : null);

	if(!$preferenceId){
		http_response_code(400);
		echo json_encode(array("error" => true, "mensaje" => "Preference ID requerido"));
		exit;
	}

	$respuesta = ControladorMercadoPago::ctrVerificarEstadoPago($preferenceId);
	echo json_encode($respuesta);
	exit;
}

/*=============================================
OBTENER O CREAR POS ESTÁTICO (QR ESTÁTICO)
=============================================*/
if(isset($_GET["obtenerQREstatico"]) || isset($_POST["obtenerQREstatico"])){
	$respuesta = ControladorMercadoPago::ctrObtenerOcrearPOSEstatico();
	echo json_encode($respuesta);
	exit;
}

/*=============================================
CREAR ORDEN QR DINÁMICO (NUEVO - RECOMENDADO)
=============================================*/
if(isset($_POST["crearOrdenQRDinamico"])){
	$monto = isset($_POST["monto"]) ? floatval($_POST["monto"]) : 0;
	$descripcion = isset($_POST["descripcion"]) ? $_POST["descripcion"] : "Venta POS";
	$externalReference = isset($_POST["external_reference"]) ? $_POST["external_reference"] : null;
	$idCliente = isset($_POST["id_cliente"]) ? intval($_POST["id_cliente"]) : null;

	if($monto <= 0){
		http_response_code(400);
		echo json_encode(array("error" => true, "mensaje" => "El monto debe ser mayor a 0"));
		exit;
	}

	if(!$externalReference){
		// Si hay id_cliente, usarlo como external_reference
		if($idCliente && $idCliente > 0){
			$externalReference = strval($idCliente);
		} else {
			$externalReference = "venta_pos_" . time() . "_" . str_replace('.', '_', $monto);
		}
	}

	$respuesta = ControladorMercadoPago::ctrCrearOrdenQRDinamico($monto, $descripcion, $externalReference, $idCliente);
	echo json_encode($respuesta);
	exit;
}

/*=============================================
CREAR ORDEN PARA MODELO ATENDIDO (DEPRECADO - Mantener por compatibilidad)
=============================================*/
if(isset($_POST["crearOrdenAtendido"])){
	$monto = isset($_POST["monto"]) ? floatval($_POST["monto"]) : 0;
	$descripcion = isset($_POST["descripcion"]) ? $_POST["descripcion"] : "Venta POS";
	$externalReference = isset($_POST["external_reference"]) ? $_POST["external_reference"] : null;

	if($monto <= 0){
		http_response_code(400);
		echo json_encode(array("error" => true, "mensaje" => "El monto debe ser mayor a 0"));
		exit;
	}

	if(!$externalReference){
		$externalReference = "venta_pos_" . time() . "_" . str_replace('.', '_', $monto);
	}

	// Usar el nuevo método QR dinámico en lugar del antiguo
	$idCliente = isset($_POST["id_cliente"]) ? intval($_POST["id_cliente"]) : null;
	$respuesta = ControladorMercadoPago::ctrCrearOrdenQRDinamico($monto, $descripcion, $externalReference, $idCliente);
	echo json_encode($respuesta);
	exit;
}

/*=============================================
VERIFICAR ESTADO DE ORDEN (MODELO ATENDIDO)
=============================================*/
if(isset($_GET["verificarOrden"]) || isset($_POST["verificarOrden"])){
	$orderId = isset($_GET["order_id"]) ? $_GET["order_id"] : (isset($_POST["order_id"]) ? $_POST["order_id"] : null);

	if(!$orderId){
		http_response_code(400);
		echo json_encode(array("error" => true, "mensaje" => "Order ID requerido"));
		exit;
	}

	$respuesta = ControladorMercadoPago::ctrVerificarEstadoOrden($orderId);
	echo json_encode($respuesta);
	exit;
}

/*=============================================
VERIFICAR PAGO POR EXTERNAL REFERENCE (QR ESTÁTICO)
=============================================*/
if(isset($_GET["verificarPagoPorReference"]) || isset($_POST["verificarPagoPorReference"])){
	$externalReference = isset($_GET["external_reference"]) ? $_GET["external_reference"] : (isset($_POST["external_reference"]) ? $_POST["external_reference"] : null);

	if(!$externalReference){
		http_response_code(400);
		echo json_encode(array("error" => true, "mensaje" => "External Reference requerido"));
		exit;
	}

	$respuesta = ControladorMercadoPago::ctrVerificarPagoPorExternalReference($externalReference);
	echo json_encode($respuesta);
	exit;
}

/*=============================================
REGISTRAR PAGO CONFIRMADO DESDE FRONTEND
=============================================*/
if(isset($_POST["registrarPagoDesdeFrontend"]) || isset($_GET["registrarPagoDesdeFrontend"])){
	$paymentId = isset($_POST["payment_id"]) ? $_POST["payment_id"] : (isset($_GET["payment_id"]) ? $_GET["payment_id"] : null);
	$orderId = isset($_POST["order_id"]) ? $_POST["order_id"] : (isset($_GET["order_id"]) ? $_GET["order_id"] : null);
	
	if(!$paymentId || !$orderId){
		http_response_code(400);
		echo json_encode(array("error" => true, "mensaje" => "Payment ID y Order ID requeridos"));
		exit;
	}
	
	// Obtener datos del pago desde Mercado Pago
	$credenciales = ControladorMercadoPago::ctrObtenerCredenciales();
	$url = "https://api.mercadopago.com/v1/payments/$paymentId";
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: Bearer ' . $credenciales['access_token'],
		'Content-Type: application/json'
	));
	
	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	
	if($httpCode == 200){
		$payment = json_decode($response, true);
		
		// Obtener id_cliente_moon desde external_reference
		// NOTA: Para ventas POS, el external_reference puede no contener el ID del cliente
		// En ese caso, intentamos obtenerlo desde la orden o usamos 0 (venta sin cliente del sistema de cobro)
		$idClienteMoon = null;
		
		// Método 1: Desde external_reference (puede ser numérico o formato "ID-otro")
		if(isset($payment['external_reference']) && !empty($payment['external_reference'])){
			$externalRef = $payment['external_reference'];
			if(is_numeric($externalRef)){
				$idClienteMoon = intval($externalRef);
			} elseif(preg_match('/^(\d+)/', $externalRef, $matches)){
				$idClienteMoon = intval($matches[1]);
			} elseif(preg_match('/cliente[_\s]*(\d+)/i', $externalRef, $matches)){
				$idClienteMoon = intval($matches[1]);
			}
		}
		
		// Método 2: Desde metadata
		if(!$idClienteMoon && isset($payment['metadata']['id_cliente_moon'])){
			$idClienteMoon = intval($payment['metadata']['id_cliente_moon']);
		}
		
		// Método 3: Intentar desde la orden (merchant_order)
		if(!$idClienteMoon && $orderId){
			$orderUrl = "https://api.mercadopago.com/merchant_orders/$orderId";
			$chOrder = curl_init($orderUrl);
			curl_setopt($chOrder, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($chOrder, CURLOPT_HTTPHEADER, array(
				'Authorization: Bearer ' . $credenciales['access_token'],
				'Content-Type: application/json'
			));
			$orderResponse = curl_exec($chOrder);
			$orderHttpCode = curl_getinfo($chOrder, CURLINFO_HTTP_CODE);
			curl_close($chOrder);
			
			if($orderHttpCode == 200){
				$order = json_decode($orderResponse, true);
				if(isset($order['external_reference']) && is_numeric($order['external_reference'])){
					$idClienteMoon = intval($order['external_reference']);
				}
			}
		}
		
		// Método 4: Para pagos QR con formato venta_pos_TIMESTAMP_MONTO, buscar en intentos recientes
		// Si el external_reference tiene formato venta_pos_*, buscar intentos pendientes recientes con el mismo monto
		if(!$idClienteMoon && isset($payment['external_reference']) && strpos($payment['external_reference'], 'venta_pos_') === 0){
			$montoDelPago = isset($payment['transaction_amount']) ? floatval($payment['transaction_amount']) : 0;
			
			if($montoDelPago > 0){
				error_log("🔍 Buscando cliente en intentos recientes para pago QR desde frontend con monto: $montoDelPago");
				
				try {
					require_once __DIR__ . '/../modelos/conexion.php';
					$conexion = Conexion::conectarMoon();
					if($conexion){
						// Buscar intentos pendientes recientes (últimos 60 minutos) con el mismo monto
						// AUMENTADO A 60 MINUTOS para capturar más intentos
						$stmtBuscarIntento = $conexion->prepare("SELECT id_cliente_moon FROM mercadopago_intentos 
							WHERE ABS(monto - :monto) < 0.01
							AND estado = 'pendiente' 
							AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)
							ORDER BY fecha_creacion DESC
							LIMIT 1");
						$stmtBuscarIntento->bindParam(":monto", $montoDelPago, PDO::PARAM_STR);
						$stmtBuscarIntento->execute();
						$intentoEncontrado = $stmtBuscarIntento->fetch();
						$stmtBuscarIntento->closeCursor();
						
						if($intentoEncontrado && isset($intentoEncontrado['id_cliente_moon']) && $intentoEncontrado['id_cliente_moon'] > 0){
							$idClienteMoon = intval($intentoEncontrado['id_cliente_moon']);
							error_log("✅✅✅ ID Cliente encontrado desde intento reciente (frontend): $idClienteMoon (monto: $montoDelPago) ✅✅✅");
						} else {
							error_log("⚠️ No se encontró intento reciente con monto $montoDelPago (frontend)");
						}
					}
				} catch(Exception $e){
					error_log("ERROR al buscar cliente en intentos desde frontend: " . $e->getMessage());
				}
			}
		}
		
		// Método 5: Si aún no se encontró y hay orderId, buscar en la orden de MercadoPago
		if(!$idClienteMoon && $orderId){
			error_log("🔍 Buscando cliente desde orden de MercadoPago (order_id: $orderId)");
			
			try {
				$orderUrl = "https://api.mercadopago.com/merchant_orders/$orderId";
				$chOrder = curl_init($orderUrl);
				curl_setopt($chOrder, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($chOrder, CURLOPT_HTTPHEADER, array(
					'Authorization: Bearer ' . $credenciales['access_token'],
					'Content-Type: application/json'
				));
				$orderResponse = curl_exec($chOrder);
				$orderHttpCode = curl_getinfo($chOrder, CURLINFO_HTTP_CODE);
				curl_close($chOrder);
				
				if($orderHttpCode == 200){
					$order = json_decode($orderResponse, true);
					
					// Intentar desde external_reference de la orden
					if(isset($order['external_reference']) && !empty($order['external_reference'])){
						$externalRef = $order['external_reference'];
						if(is_numeric($externalRef)){
							$idClienteMoon = intval($externalRef);
							error_log("✅ ID Cliente encontrado desde order.external_reference: $idClienteMoon");
						} elseif(preg_match('/^(\d+)/', $externalRef, $matches)){
							$idClienteMoon = intval($matches[1]);
							error_log("✅ ID Cliente extraído desde order.external_reference: $idClienteMoon");
						}
					}
					
					// Si aún no se encontró, buscar en intentos por monto de la orden
					if(!$idClienteMoon && isset($order['total_amount'])){
						$montoOrden = floatval($order['total_amount']);
						error_log("🔍 Buscando cliente en intentos por monto de orden: $montoOrden");
						
						require_once __DIR__ . '/../modelos/conexion.php';
						$conexion = Conexion::conectarMoon();
						if($conexion){
							$stmtBuscarIntento = $conexion->prepare("SELECT id_cliente_moon FROM mercadopago_intentos 
								WHERE ABS(monto - :monto) < 0.01
								AND estado = 'pendiente' 
								AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)
								ORDER BY fecha_creacion DESC
								LIMIT 1");
							$stmtBuscarIntento->bindParam(":monto", $montoOrden, PDO::PARAM_STR);
							$stmtBuscarIntento->execute();
							$intentoEncontrado = $stmtBuscarIntento->fetch();
							$stmtBuscarIntento->closeCursor();
							
							if($intentoEncontrado && isset($intentoEncontrado['id_cliente_moon']) && $intentoEncontrado['id_cliente_moon'] > 0){
								$idClienteMoon = intval($intentoEncontrado['id_cliente_moon']);
								error_log("✅✅✅ ID Cliente encontrado desde intento por monto de orden: $idClienteMoon ✅✅✅");
							}
						}
					}
				}
			} catch(Exception $e){
				error_log("ERROR al buscar cliente desde orden: " . $e->getMessage());
			}
		}
		
		// Si aún no se encuentra, usar 0 para auditoría
		// Esto es válido para ventas POS que no están asociadas al sistema de cobro
		if(!$idClienteMoon){
			$idClienteMoon = 0;
			error_log("ℹ️ No se encontró id_cliente_moon para payment $paymentId (puede ser venta POS sin cliente del sistema de cobro). Registrando con id=0");
		}
		
		// Preparar datos del pago
		$fechaPago = date('Y-m-d H:i:s');
		if(isset($payment['date_approved']) && !empty($payment['date_approved'])){
			$fechaAprobada = strtotime($payment['date_approved']);
			if($fechaAprobada !== false){
				$fechaPago = date('Y-m-d H:i:s', $fechaAprobada);
			}
		}
		
		$datosPago = array(
			'id_cliente_moon' => $idClienteMoon,
			'payment_id' => $paymentId,
			'preference_id' => isset($payment['preference_id']) ? $payment['preference_id'] : null,
			'monto' => isset($payment['transaction_amount']) ? $payment['transaction_amount'] : 0,
			'estado' => isset($payment['status']) ? $payment['status'] : 'approved',
			'fecha_pago' => $fechaPago,
			'payment_type' => isset($payment['payment_type_id']) ? $payment['payment_type_id'] : null,
			'payment_method_id' => isset($payment['payment_method_id']) ? $payment['payment_method_id'] : null,
			'datos_json' => json_encode($payment)
		);
		
		// CRÍTICO: Registrar en mercadopago_pagos
		error_log("═══════════════════════════════════════");
		error_log("REGISTRANDO PAGO DESDE FRONTEND");
		error_log("Payment ID: $paymentId");
		error_log("Order ID: $orderId");
		error_log("Cliente Moon: " . ($idClienteMoon ?: 'NO ENCONTRADO (0)'));
		error_log("Monto: " . (isset($payment['transaction_amount']) ? $payment['transaction_amount'] : 'N/A'));
		error_log("Estado: " . (isset($payment['status']) ? $payment['status'] : 'N/A'));
		error_log("═══════════════════════════════════════");
		
		$resultadoPago = ControladorMercadoPago::ctrRegistrarPagoConfirmado($datosPago);
		error_log("Resultado registro en mercadopago_pagos: " . (is_array($resultadoPago) ? json_encode($resultadoPago) : $resultadoPago));
		
		if($resultadoPago === "ok"){
			error_log("✅✅✅ PAGO REGISTRADO EN mercadopago_pagos ✅✅✅");
			
			// Actualizar estado del intento (puede ser por preference_id o order_id)
			$preferenceId = isset($datosPago['preference_id']) && !empty($datosPago['preference_id']) ? $datosPago['preference_id'] : null;
			$resultadoIntento = ModeloMercadoPago::mdlActualizarEstadoIntento($preferenceId, 'aprobado', $orderId);
			error_log("Resultado actualización intento: " . ($resultadoIntento === "ok" ? "✅ OK" : "⚠️ " . $resultadoIntento));
			
			// CRÍTICO: Registrar en cuenta corriente si el pago está aprobado y hay cliente válido
			if($idClienteMoon > 0 && isset($payment['status']) && $payment['status'] === 'approved'){
				$monto = floatval($payment['transaction_amount']);
				
				error_log("═══════════════════════════════════════");
				error_log("REGISTRANDO EN CUENTA CORRIENTE (FRONTEND)");
				error_log("Cliente: $idClienteMoon");
				error_log("Monto: $monto");
				error_log("═══════════════════════════════════════");
				
				$resultadoCtaCte = ControladorSistemaCobro::ctrRegistrarMovimientoCuentaCorriente($idClienteMoon, $monto);
				error_log("Resultado cuenta corriente: " . (is_array($resultadoCtaCte) ? json_encode($resultadoCtaCte) : $resultadoCtaCte));
				
				if($resultadoCtaCte === "ok"){
					error_log("✅✅✅ MOVIMIENTO DE CUENTA CORRIENTE REGISTRADO ✅✅✅");
				} else {
					error_log("❌❌❌ ERROR AL REGISTRAR EN CUENTA CORRIENTE ❌❌❌");
				}
				
				// Desbloquear cliente
				$resultadoDesbloqueo = ControladorSistemaCobro::ctrActualizarClientesCobro($idClienteMoon, 0);
				error_log("Resultado desbloqueo: " . ($resultadoDesbloqueo !== false ? "✅ Cliente desbloqueado" : "⚠️ No se pudo desbloquear"));
			} else {
				error_log("⚠️ No se registra en cuenta corriente: Cliente=" . ($idClienteMoon ?: '0') . ", Status=" . (isset($payment['status']) ? $payment['status'] : 'N/A'));
			}
			
			echo json_encode(array(
				"error" => false,
				"mensaje" => "Pago registrado correctamente en sistema de cobro",
				"id_cliente_moon" => $idClienteMoon,
				"payment_id" => $paymentId,
				"registrado_en_pagos" => true,
				"registrado_en_cuenta_corriente" => ($idClienteMoon > 0 && isset($payment['status']) && $payment['status'] === 'approved')
			));
		} else {
			error_log("❌❌❌ ERROR AL REGISTRAR PAGO EN mercadopago_pagos ❌❌❌");
			http_response_code(500);
			echo json_encode(array(
				"error" => true,
				"mensaje" => "Error al registrar pago: " . (is_array($resultadoPago) ? json_encode($resultadoPago) : $resultadoPago)
			));
		}
	} else {
		http_response_code($httpCode);
		echo json_encode(array(
			"error" => true,
			"mensaje" => "Error al obtener datos del pago desde Mercado Pago"
		));
	}
	
	exit;
}
