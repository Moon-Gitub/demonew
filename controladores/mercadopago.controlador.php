<?php

class ControladorMercadoPago {

	/*=============================================
	OBTENER CREDENCIALES DE MERCADOPAGO
	=============================================*/
	static public function ctrObtenerCredenciales() {

		// Leer de .env, sino credenciales fijas
		$publicKey = isset($_ENV['MP_PUBLIC_KEY']) ? $_ENV['MP_PUBLIC_KEY'] : (isset($_SERVER['MP_PUBLIC_KEY']) ? $_SERVER['MP_PUBLIC_KEY'] : 'APP_USR-33156d44-12df-4039-8c92-1635d8d3edde');
		$accessToken = isset($_ENV['MP_ACCESS_TOKEN']) ? $_ENV['MP_ACCESS_TOKEN'] : (isset($_SERVER['MP_ACCESS_TOKEN']) ? $_SERVER['MP_ACCESS_TOKEN'] : 'APP_USR-6921807486493458-102300-5f1cec174eb674c42c9782860caf640c-2916747261');

		return array(
			'public_key' => $publicKey,
			'access_token' => $accessToken
		);
	}

	/*=============================================
	CALCULAR MONTO DE COBRO CON RECARGOS
	=============================================*/
	static public function ctrCalcularMontoCobro($clienteMoon, $ctaCteCliente, $subtotalMensuales = null, $subtotalOtros = null) {

		date_default_timezone_set('America/Argentina/Mendoza');
		$diaActual = date('d');
		$mesActual = date('m');
		$añoActual = date('Y');

		// Verificar si este cliente tiene habilitados los recargos
		// Por defecto SÍ aplica recargos si el campo no existe (1 = SÍ, 0 = NO)
		$aplicarRecargos = isset($clienteMoon['aplicar_recargos']) ? intval($clienteMoon['aplicar_recargos']) : 1;

		// El monto a cobrar es el saldo actual de la cuenta corriente del cliente (lo que debe)
		$saldoCuenta = floatval($ctaCteCliente["saldo"]);

		// Si no se proporcionan subtotales separados, usar el saldo total
		$subtotalMensuales = ($subtotalMensuales !== null) ? floatval($subtotalMensuales) : $saldoCuenta;
		$subtotalOtros = ($subtotalOtros !== null) ? floatval($subtotalOtros) : 0;

		$abonoBase = $subtotalMensuales + $subtotalOtros;
		$mensajeCliente = "";
		$montoFinal = 0;
		$tieneRecargo = false;
		$porcentajeRecargo = 0;

		// Lógica de recargos según el día del mes
		// Los recargos se aplican SOLO sobre servicios mensuales
		// Y SOLO si el cliente tiene aplicar_recargos = 1
		if ($diaActual > 4 && $diaActual <= 9) {
			$mensajeCliente = 'Debes abonar $' . number_format($abonoBase, 2, ',', '.') . ' como abono mensual';
			$montoFinal = $abonoBase;

		} else if ($diaActual >= 10 && $diaActual <= 14) {
			// 10% de recargo SOLO sobre servicios mensuales Y si aplica recargos
			if ($aplicarRecargos && $subtotalMensuales > 0) {
				$recargo1 = $subtotalMensuales * 0.10;
				$montoFinal = $abonoBase + $recargo1;
				$mensajeCliente = 'Debes abonar $' . number_format($montoFinal, 2, ',', '.') . ' (Incluye 10% de recargo por mora sobre servicios mensuales)';
				$tieneRecargo = true;
				$porcentajeRecargo = 10;
			} else {
				$montoFinal = $abonoBase;
				$mensajeCliente = $aplicarRecargos
					? 'Debes abonar $' . number_format($abonoBase, 2, ',', '.')
					: 'Debes abonar $' . number_format($abonoBase, 2, ',', '.') . ' (Cliente exento de recargos por mora)';
			}

		} else if ($diaActual >= 15 && $diaActual <= 19) {
			// 15% de recargo SOLO sobre servicios mensuales Y si aplica recargos
			if ($aplicarRecargos && $subtotalMensuales > 0) {
				$recargo2 = $subtotalMensuales * 0.15;
				$montoFinal = $abonoBase + $recargo2;
				$mensajeCliente = 'Debes abonar $' . number_format($montoFinal, 2, ',', '.') . ' (Incluye 15% de recargo por mora sobre servicios mensuales)';
				$tieneRecargo = true;
				$porcentajeRecargo = 15;
			} else {
				$montoFinal = $abonoBase;
				$mensajeCliente = $aplicarRecargos
					? 'Debes abonar $' . number_format($abonoBase, 2, ',', '.')
					: 'Debes abonar $' . number_format($abonoBase, 2, ',', '.') . ' (Cliente exento de recargos por mora)';
			}

		} else if ($diaActual >= 20 && $diaActual <= 24) {
			// 20% de recargo SOLO sobre servicios mensuales Y si aplica recargos
			if ($aplicarRecargos && $subtotalMensuales > 0) {
				$recargo3 = $subtotalMensuales * 0.20;
				$montoFinal = $abonoBase + $recargo3;
				$mensajeCliente = 'Debes abonar $' . number_format($montoFinal, 2, ',', '.') . ' (Incluye 20% de recargo por mora sobre servicios mensuales)';
				$tieneRecargo = true;
				$porcentajeRecargo = 20;
			} else {
				$montoFinal = $abonoBase;
				$mensajeCliente = $aplicarRecargos
					? 'Debes abonar $' . number_format($abonoBase, 2, ',', '.')
					: 'Debes abonar $' . number_format($abonoBase, 2, ',', '.') . ' (Cliente exento de recargos por mora)';
			}

		} else if ($diaActual >= 25) {
			// 30% de recargo SOLO sobre servicios mensuales Y si aplica recargos
			if ($aplicarRecargos && $subtotalMensuales > 0) {
				$recargo4 = $subtotalMensuales * 0.30;
				$montoFinal = $abonoBase + $recargo4;
				$mensajeCliente = 'Debes abonar $' . number_format($montoFinal, 2, ',', '.') . ' (Incluye 30% de recargo por mora sobre servicios mensuales)';
				$tieneRecargo = true;
				$porcentajeRecargo = 30;
			} else {
				$montoFinal = $abonoBase;
				$mensajeCliente = $aplicarRecargos
					? 'Debes abonar $' . number_format($abonoBase, 2, ',', '.')
					: 'Debes abonar $' . number_format($abonoBase, 2, ',', '.') . ' (Cliente exento de recargos por mora)';
			}

		} else {
			// Días 1-4: Sin recargo aún
			$mensajeCliente = 'Tu abono mensual es de $' . number_format($abonoBase, 2, ',', '.') . '. Recuerda abonar antes del día 5 para evitar recargos.';
			$montoFinal = $abonoBase;
		}

		return array(
			'monto' => $montoFinal,
			'abono_base' => $abonoBase,
			'tiene_recargo' => $tieneRecargo,
			'porcentaje_recargo' => $porcentajeRecargo,
			'mensaje' => $mensajeCliente,
			'saldo_actual' => $saldoCuenta,
			'dia_actual' => $diaActual,
			'periodo' => "$mesActual/$añoActual",
			'aplicar_recargos' => $aplicarRecargos,
			'subtotal_mensuales' => $subtotalMensuales,
			'subtotal_otros' => $subtotalOtros
		);
	}

	/*=============================================
	REGISTRAR INTENTO DE PAGO
	=============================================*/
	static public function ctrRegistrarIntentoPago($datos) {

		$respuesta = ModeloMercadoPago::mdlRegistrarIntentoPago($datos);

		return $respuesta;
	}

	/*=============================================
	REGISTRAR PAGO CONFIRMADO
	=============================================*/
	static public function ctrRegistrarPagoConfirmado($datos) {

		$respuesta = ModeloMercadoPago::mdlRegistrarPagoConfirmado($datos);

		return $respuesta;
	}

	/*=============================================
	VERIFICAR SI YA SE PROCESÓ UN PAGO
	=============================================*/
	static public function ctrVerificarPagoProcesado($paymentId) {

		$respuesta = ModeloMercadoPago::mdlVerificarPagoProcesado($paymentId);

		return $respuesta;
	}

	/*=============================================
	OBTENER HISTORIAL DE PAGOS POR CLIENTE
	=============================================*/
	static public function ctrObtenerHistorialPagos($idCliente) {

		$respuesta = ModeloMercadoPago::mdlObtenerHistorialPagos($idCliente);

		return $respuesta;
	}

	/*=============================================
	REGISTRAR WEBHOOK RECIBIDO
	=============================================*/
	static public function ctrRegistrarWebhook($datos) {

		$respuesta = ModeloMercadoPago::mdlRegistrarWebhook($datos);

		return $respuesta;
	}

	/*=============================================
	PROCESAR PAGO DESDE WEBHOOK
	=============================================*/
	static public function ctrProcesarPagoWebhook($paymentId) {

		try {
			// Obtener credenciales
			$credenciales = self::ctrObtenerCredenciales();

			// Verificar que no esté procesado ya
			if (self::ctrVerificarPagoProcesado($paymentId)) {
				return array(
					'error' => false,
					'mensaje' => 'Pago ya procesado anteriormente'
				);
			}

			// Aquí irá la lógica de consultar a la API de MercadoPago
			// y actualizar la cuenta corriente del cliente

			return array(
				'error' => false,
				'mensaje' => 'Pago procesado exitosamente'
			);

		} catch (Exception $e) {
			error_log("Error procesando pago webhook: " . $e->getMessage());
			return array(
				'error' => true,
				'mensaje' => $e->getMessage()
			);
		}
	}

	/*=============================================
	CREAR PREFERENCIA DE PAGO PARA VENTA (QR ESTÁTICO)
	=============================================*/
	static public function ctrCrearPreferenciaVenta($monto, $descripcion, $externalReference = null) {
		try {
			require_once __DIR__ . '/../extensiones/vendor/autoload.php';
			
			$credenciales = self::ctrObtenerCredenciales();
			\MercadoPago\MercadoPagoConfig::setAccessToken($credenciales['access_token']);

			$client = new \MercadoPago\Client\Preference\PreferenceClient();
			
			$preference = $client->create([
				"items" => [
					[
						"title" => $descripcion ?: "Venta POS",
						"quantity" => 1,
						"unit_price" => floatval($monto)
					]
				],
				"external_reference" => $externalReference ?: "venta_" . time(),
				"binary_mode" => true,
				"expires" => false // No expira
			]);

			if ($preference && isset($preference->id)) {
				return array(
					'error' => false,
					'preference_id' => $preference->id,
					'qr_code' => $preference->qr_code ?: null,
					'init_point' => $preference->init_point ?: null,
					'sandbox_init_point' => $preference->sandbox_init_point ?: null
				);
			} else {
				return array(
					'error' => true,
					'mensaje' => 'Error al crear preferencia de pago'
				);
			}

		} catch (Exception $e) {
			error_log("Error creando preferencia de venta: " . $e->getMessage());
			return array(
				'error' => true,
				'mensaje' => $e->getMessage()
			);
		}
	}

	/*=============================================
	VERIFICAR ESTADO DE PAGO POR PREFERENCE_ID
	=============================================*/
	static public function ctrVerificarEstadoPago($preferenceId) {
		try {
			require_once __DIR__ . '/../extensiones/vendor/autoload.php';
			
			$credenciales = self::ctrObtenerCredenciales();
			\MercadoPago\MercadoPagoConfig::setAccessToken($credenciales['access_token']);

			// Buscar pagos asociados a esta preferencia
			$client = new \MercadoPago\Client\Payment\PaymentClient();
			
			$filters = array(
				"preference_id" => $preferenceId,
				"status" => "approved"
			);

			$searchRequest = new \MercadoPago\Net\MPSearchRequest();
			$searchRequest->setLimit(1);
			$searchRequest->setOffset(0);
			
			foreach ($filters as $key => $value) {
				$searchRequest->addFilter($key, "=", $value);
			}

			$searchResult = $client->search($searchRequest);

			if ($searchResult && isset($searchResult->results) && count($searchResult->results) > 0) {
				$payment = $searchResult->results[0];
				return array(
					'error' => false,
					'aprobado' => true,
					'payment_id' => $payment->id,
					'status' => $payment->status,
					'transaction_amount' => $payment->transaction_amount
				);
			} else {
				// Verificar si hay pagos pendientes
				$filtersPending = array(
					"preference_id" => $preferenceId,
					"status" => "pending"
				);

				$searchRequestPending = new \MercadoPago\Net\MPSearchRequest();
				$searchRequestPending->setLimit(1);
				$searchRequestPending->setOffset(0);
				
				foreach ($filtersPending as $key => $value) {
					$searchRequestPending->addFilter($key, "=", $value);
				}

				$searchResultPending = $client->search($searchRequestPending);

				if ($searchResultPending && isset($searchResultPending->results) && count($searchResultPending->results) > 0) {
					return array(
						'error' => false,
						'aprobado' => false,
						'status' => 'pending',
						'mensaje' => 'Pago pendiente'
					);
				}

				return array(
					'error' => false,
					'aprobado' => false,
					'status' => 'no_payment',
					'mensaje' => 'Aún no se ha realizado el pago'
				);
			}

		} catch (Exception $e) {
			error_log("Error verificando estado de pago: " . $e->getMessage());
			return array(
				'error' => true,
				'mensaje' => $e->getMessage()
			);
		}
	}
}
