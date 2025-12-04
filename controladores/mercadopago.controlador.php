<?php

class ControladorMercadoPago {

	/*=============================================
	OBTENER CREDENCIALES DE MERCADOPAGO
	=============================================*/
	static public function ctrObtenerCredenciales() {

		// Intentar obtener desde .env usando función env()
		if (function_exists('env')) {
			$publicKey = env('MP_PUBLIC_KEY');
			$accessToken = env('MP_ACCESS_TOKEN');
		} else {
			// Fallback a $_ENV
			$publicKey = isset($_ENV['MP_PUBLIC_KEY']) ? $_ENV['MP_PUBLIC_KEY'] : null;
			$accessToken = isset($_ENV['MP_ACCESS_TOKEN']) ? $_ENV['MP_ACCESS_TOKEN'] : null;
		}

		// Si no están definidas, usar valores por defecto de TEST
		if (!$publicKey || !$accessToken) {
			$publicKey = 'TEST-9e420918-959d-45dc-a85f-33bcda359e78';
			$accessToken = 'TEST-3927436741225472-082909-b379465087e47bff35a8716eb049526a-1188183100';
		}

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
}
