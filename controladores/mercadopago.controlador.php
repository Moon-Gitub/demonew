<?php

class ControladorMercadoPago {

	/*=============================================
	OBTENER CREDENCIALES DE MERCADOPAGO
	=============================================*/
	static public function ctrObtenerCredenciales() {

		// Intentar obtener desde .env primero
		$publicKey = getenv('MP_PUBLIC_KEY');
		$accessToken = getenv('MP_ACCESS_TOKEN');

		// Si no están en .env, usar valores por defecto (compatibilidad)
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
	static public function ctrCalcularMontoCobro($clienteMoon, $ctaCteCliente) {

		date_default_timezone_set('America/Argentina/Mendoza');
		$diaActual = date('d');
		$mesActual = date('m');
		$añoActual = date('Y');

		$saldoCuenta = floatval($ctaCteCliente["saldo"]);
		// Usar abono_mensual si existe, sino usar el saldo como monto base
		$abonoMensual = isset($clienteMoon["abono_mensual"]) ? floatval($clienteMoon["abono_mensual"]) : $saldoCuenta;
		$mensajeCliente = "";
		$montoFinal = 0;
		$tieneRecargo = false;
		$porcentajeRecargo = 0;

		// Lógica de recargos según el día del mes
		if ($diaActual > 4 && $diaActual <= 9) {
			$mensajeCliente = 'Debes abonar $' . number_format($abonoMensual, 2, ',', '.') . ' como abono mensual';
			$montoFinal = $abonoMensual;

		} else if ($diaActual >= 10 && $diaActual <= 14) {
			$recargo1 = $abonoMensual * 0.10; // 10% de recargo
			$montoFinal = $abonoMensual + $recargo1;
			$mensajeCliente = 'Debes abonar $' . number_format($montoFinal, 2, ',', '.') . ' (Incluye 10% de recargo por mora)';
			$tieneRecargo = true;
			$porcentajeRecargo = 10;

		} else if ($diaActual >= 15 && $diaActual <= 19) {
			$recargo2 = $abonoMensual * 0.15; // 15% de recargo
			$montoFinal = $abonoMensual + $recargo2;
			$mensajeCliente = 'Debes abonar $' . number_format($montoFinal, 2, ',', '.') . ' (Incluye 15% de recargo por mora)';
			$tieneRecargo = true;
			$porcentajeRecargo = 15;

		} else if ($diaActual >= 20 && $diaActual <= 24) {
			$recargo3 = $abonoMensual * 0.20; // 20% de recargo
			$montoFinal = $abonoMensual + $recargo3;
			$mensajeCliente = 'Debes abonar $' . number_format($montoFinal, 2, ',', '.') . ' (Incluye 20% de recargo por mora)';
			$tieneRecargo = true;
			$porcentajeRecargo = 20;

		} else if ($diaActual >= 25) {
			$recargo4 = $abonoMensual * 0.30; // 30% de recargo
			$montoFinal = $abonoMensual + $recargo4;
			$mensajeCliente = 'Debes abonar $' . number_format($montoFinal, 2, ',', '.') . ' (Incluye 30% de recargo por mora)';
			$tieneRecargo = true;
			$porcentajeRecargo = 30;

		} else {
			// Días 1-4: Sin recargo aún
			$mensajeCliente = 'Tu abono mensual es de $' . number_format($abonoMensual, 2, ',', '.') . '. Recuerda abonar antes del día 5 para evitar recargos.';
			$montoFinal = $abonoMensual;
		}

		return array(
			'monto' => $montoFinal,
			'abono_base' => $abonoMensual,
			'tiene_recargo' => $tieneRecargo,
			'porcentaje_recargo' => $porcentajeRecargo,
			'mensaje' => $mensajeCliente,
			'saldo_actual' => $saldoCuenta,
			'dia_actual' => $diaActual,
			'periodo' => "$mesActual/$añoActual"
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
