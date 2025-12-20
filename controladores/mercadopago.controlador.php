<?php

class ControladorMercadoPago {

	/*=============================================
	OBTENER CREDENCIALES DE MERCADOPAGO
	=============================================*/
	static public function ctrObtenerCredenciales() {

		// Intentar leer desde la configuración de empresa (BD)
		$empresa = null;
		try {
			if (class_exists('ControladorEmpresa')) {
				$empresa = ControladorEmpresa::ctrMostrarempresa('id', 1);
			}
		} catch (Exception $e) {
			error_log("Error obteniendo empresa para credenciales MP: " . $e->getMessage());
		}

		// Si hay credenciales en la BD de empresa, usarlas
		if ($empresa && isset($empresa['mp_public_key']) && !empty($empresa['mp_public_key']) && 
		    isset($empresa['mp_access_token']) && !empty($empresa['mp_access_token'])) {
			return array(
				'public_key' => $empresa['mp_public_key'],
				'access_token' => $empresa['mp_access_token']
			);
		}

		// Si no hay en BD, intentar leer de .env
		$publicKey = isset($_ENV['MP_PUBLIC_KEY']) ? $_ENV['MP_PUBLIC_KEY'] : (isset($_SERVER['MP_PUBLIC_KEY']) ? $_SERVER['MP_PUBLIC_KEY'] : null);
		$accessToken = isset($_ENV['MP_ACCESS_TOKEN']) ? $_ENV['MP_ACCESS_TOKEN'] : (isset($_SERVER['MP_ACCESS_TOKEN']) ? $_SERVER['MP_ACCESS_TOKEN'] : null);

		// Si tampoco hay en .env, usar credenciales por defecto (solo para desarrollo/testing)
		if (empty($publicKey)) {
			$publicKey = 'APP_USR-33156d44-12df-4039-8c92-1635d8d3edde';
		}
		if (empty($accessToken)) {
			$accessToken = 'APP_USR-6921807486493458-102300-5f1cec174eb674c42c9782860caf640c-2916747261';
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
				// Obtener QR code de la respuesta (puede estar en diferentes lugares según la versión del SDK)
				$qrCode = null;
				if (isset($preference->qr_code)) {
					$qrCode = $preference->qr_code;
				} elseif (isset($preference->qr_code_base64)) {
					$qrCode = $preference->qr_code_base64;
				} elseif (isset($preference->point_of_interaction) && isset($preference->point_of_interaction->transaction_data)) {
					$qrCode = isset($preference->point_of_interaction->transaction_data->qr_code) 
						? $preference->point_of_interaction->transaction_data->qr_code 
						: null;
				}
				
				return array(
					'error' => false,
					'preference_id' => $preference->id,
					'qr_code' => $qrCode,
					'init_point' => isset($preference->init_point) ? $preference->init_point : null,
					'sandbox_init_point' => isset($preference->sandbox_init_point) ? $preference->sandbox_init_point : null
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
	VERIFICAR ESTADO DE PAGO POR PREFERENCE_ID (DEPRECADO - Usar external_reference)
	NOTA: Este método se mantiene por compatibilidad pero ya no se usa para QR estático
	=============================================*/
	static public function ctrVerificarEstadoPago($preferenceId) {
		// La API de Mercado Pago no permite buscar por preference_id directamente
		// Este método está deprecado. Usar ctrVerificarPagoPorExternalReference en su lugar
		error_log("ADVERTENCIA: ctrVerificarEstadoPago con preference_id está deprecado. Usar external_reference.");
		return array(
			'error' => true,
			'mensaje' => 'Método deprecado. Use verificación por external_reference para QR estático.'
		);
	}

	/*=============================================
	OBTENER O CREAR POS ESTÁTICO (QR ESTÁTICO)
	=============================================*/
	static public function ctrObtenerOcrearPOSEstatico() {
		try {
			$credenciales = self::ctrObtenerCredenciales();
			
			// Primero intentar obtener POS existente desde la BD de empresa
			$empresa = null;
			try {
				if (class_exists('ControladorEmpresa')) {
					$empresa = ControladorEmpresa::ctrMostrarempresa('id', 1);
					if ($empresa && isset($empresa['mp_pos_id']) && !empty($empresa['mp_pos_id'])) {
						// Ya existe un POS, obtenerlo de Mercado Pago
						$posId = $empresa['mp_pos_id'];
						$url = "https://api.mercadopago.com/pos/$posId";
						
						$ch = curl_init($url);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array(
							'Authorization: Bearer ' . $credenciales['access_token'],
							'Content-Type: application/json'
						));
						
						$response = curl_exec($ch);
						$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
						curl_close($ch);
						
						if ($httpCode == 200) {
							$pos = json_decode($response, true);
							// El QR puede estar en diferentes formatos según la API
							$qrImage = null;
							$qrData = null;
							
							if (isset($pos['qr']['image'])) {
								$qrImage = $pos['qr']['image'];
							}
							if (isset($pos['qr']['data'])) {
								$qrData = $pos['qr']['data'];
							} elseif (isset($pos['qr']['qr_code_base64'])) {
								$qrData = $pos['qr']['qr_code_base64'];
							}
							
							return array(
								'error' => false,
								'pos_id' => $pos['id'],
								'qr_code' => $qrImage,
								'qr_data' => $qrData,
								'name' => isset($pos['name']) ? $pos['name'] : 'POS Estático'
							);
						} else {
							error_log("Error obteniendo POS $posId: HTTP $httpCode - $response");
							// Si el POS no existe o hay error, crear uno nuevo
						}
					}
				}
			} catch (Exception $e) {
				error_log("Error obteniendo POS desde BD: " . $e->getMessage());
			}
			
			// Primero crear o obtener una tienda (store) - es obligatorio
			$storeId = null;
			$externalStoreId = "tiendapos" . time(); // ID externo único para la tienda
			
			// Obtener user_id para crear la tienda
			$userId = null;
			$tokenParts = explode('-', $credenciales['access_token']);
			if (count($tokenParts) >= 5) {
				$userId = $tokenParts[count($tokenParts) - 1];
			}
			
			if ($userId) {
				// Intentar obtener tiendas existentes primero
				$listUrl = "https://api.mercadopago.com/users/$userId/stores";
				$ch = curl_init($listUrl);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Authorization: Bearer ' . $credenciales['access_token'],
					'Content-Type: application/json'
				));
				
				$listResponse = curl_exec($ch);
				$listHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if ($listHttpCode == 200) {
					$stores = json_decode($listResponse, true);
					if (isset($stores['results']) && count($stores['results']) > 0) {
						$storeId = $stores['results'][0]['id'];
						$externalStoreId = isset($stores['results'][0]['external_id']) ? $stores['results'][0]['external_id'] : $externalStoreId;
						error_log("Usando tienda existente: $storeId");
					}
				}
				
				// Si no hay tienda existente, crear una nueva
				if (!$storeId) {
					$storeUrl = "https://api.mercadopago.com/users/$userId/stores";
					$storeData = array(
						"name" => "Tienda Principal",
						"external_id" => $externalStoreId,
						"location" => array(
							"street_number" => "0",
							"street_name" => "Sin dirección",
							"city_name" => "Buenos Aires",
							"state_name" => "Capital Federal", // Debe ser una provincia válida de Argentina
							"latitude" => -34.603722, // Buenos Aires por defecto
							"longitude" => -58.381592
						)
					);
					
					$ch = curl_init($storeUrl);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($storeData));
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						'Authorization: Bearer ' . $credenciales['access_token'],
						'Content-Type: application/json'
					));
					
					$storeResponse = curl_exec($ch);
					$storeHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
					curl_close($ch);
					
					if ($storeHttpCode == 201 || $storeHttpCode == 200) {
						$store = json_decode($storeResponse, true);
						$storeId = isset($store['id']) ? $store['id'] : null;
						error_log("Tienda creada exitosamente: " . json_encode($store));
					} else {
						error_log("Error creando tienda: HTTP $storeHttpCode - $storeResponse");
					}
				}
			}
			
			// Crear el POS con store_id o external_store_id
			$posData = array(
				"name" => "POS Estático",
				"fixed_amount" => false, // Permite monto dinámico
				"external_id" => "posestatico" . time() // Solo alfanumérico (sin guiones bajos)
			);
			
			if ($storeId) {
				$posData["store_id"] = $storeId;
			} else if ($externalStoreId) {
				$posData["external_store_id"] = $externalStoreId;
			} else {
				return array(
					'error' => true,
					'mensaje' => 'No se pudo crear ni obtener una tienda. Verifique las credenciales de Mercado Pago.'
				);
			}
			
			// Crear el POS
			$url = "https://api.mercadopago.com/pos";
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($posData));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Authorization: Bearer ' . $credenciales['access_token'],
				'Content-Type: application/json'
			));
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if ($httpCode == 201 || $httpCode == 200) {
				$pos = json_decode($response, true);
				
				// El QR puede estar en diferentes formatos según la API
				$qrImage = null;
				$qrData = null;
				
				if (isset($pos['qr']['image'])) {
					$qrImage = $pos['qr']['image'];
				}
				if (isset($pos['qr']['data'])) {
					$qrData = $pos['qr']['data'];
				} elseif (isset($pos['qr']['qr_code_base64'])) {
					$qrData = $pos['qr']['qr_code_base64'];
				}
				
				// Guardar POS ID en empresa
				try {
					if (class_exists('ModeloEmpresa')) {
						require_once __DIR__ . '/../modelos/empresa.modelo.php';
						$stmt = \Conexion::conectar()->prepare("UPDATE empresa SET mp_pos_id = :pos_id WHERE id = 1");
						$stmt->bindParam(":pos_id", $pos['id'], \PDO::PARAM_STR);
						$stmt->execute();
						$stmt = null;
					}
				} catch (Exception $e) {
					error_log("Error guardando POS ID en empresa: " . $e->getMessage());
				}
				
				return array(
					'error' => false,
					'pos_id' => $pos['id'],
					'qr_code' => $qrImage,
					'qr_data' => $qrData,
					'name' => isset($pos['name']) ? $pos['name'] : 'POS Estático'
				);
			} else {
				error_log("Error creando POS: HTTP $httpCode - $response");
				return array(
					'error' => true,
					'mensaje' => 'Error al crear POS estático: ' . $response
				);
			}
			
		} catch (Exception $e) {
			error_log("Error obteniendo/creando POS estático: " . $e->getMessage());
			return array(
				'error' => true,
				'mensaje' => $e->getMessage()
			);
		}
	}

	/*=============================================
	CREAR ORDEN PARA MODELO ATENDIDO (QR ESTÁTICO)
	En el modelo atendido, se crea una orden con el monto específico y se asigna al POS.
	Cuando el cliente escanea el QR estático, ve la orden con el monto.
	=============================================*/
	static public function ctrCrearOrdenAtendido($monto, $descripcion, $externalReference) {
		try {
			$credenciales = self::ctrObtenerCredenciales();
			
			// Obtener POS ID desde la empresa
			$posId = null;
			try {
				if (class_exists('ControladorEmpresa')) {
					$empresa = ControladorEmpresa::ctrMostrarempresa('id', 1);
					if ($empresa && isset($empresa['mp_pos_id']) && !empty($empresa['mp_pos_id'])) {
						$posId = $empresa['mp_pos_id'];
					}
				}
			} catch (Exception $e) {
				error_log("Error obteniendo POS ID: " . $e->getMessage());
			}
			
			if (!$posId) {
				// Si no hay POS, crear uno primero
				$posResult = self::ctrObtenerOcrearPOSEstatico();
				if ($posResult['error']) {
					return $posResult;
				}
				$posId = $posResult['pos_id'];
			}
			
			// Obtener user_id (collector_id) desde el access_token
			// El access_token tiene formato: APP_USR-XXXX-XXXX-XXXX-XXXX-USER_ID
			$userId = null;
			$tokenParts = explode('-', $credenciales['access_token']);
			if (count($tokenParts) >= 5) {
				$userId = $tokenParts[count($tokenParts) - 1];
			}
			
			if (!$userId) {
				// Intentar obtener desde la API
				$url = "https://api.mercadopago.com/users/me";
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Authorization: Bearer ' . $credenciales['access_token'],
					'Content-Type: application/json'
				));
				
				$response = curl_exec($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if ($httpCode == 200) {
					$userData = json_decode($response, true);
					$userId = isset($userData['id']) ? $userData['id'] : null;
				}
			}
			
			if (!$userId) {
				return array(
					'error' => true,
					'mensaje' => 'No se pudo obtener el user_id de Mercado Pago'
				);
			}
			
			// Crear la orden y asignarla al POS
			// Endpoint: PUT /instore/orders/qr/seller/collectors/{user_id}/pos/{external_pos_id}/qrs
			$url = "https://api.mercadopago.com/instore/orders/qr/seller/collectors/$userId/pos/$posId/qrs";
			
			// Construir URL de notificación
			$notificationUrl = "";
			if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
				$notificationUrl = "https://";
			} else {
				$notificationUrl = "http://";
			}
			$notificationUrl .= $_SERVER['HTTP_HOST'] . "/webhook-mercadopago.php";
			
			$data = array(
				"external_reference" => $externalReference,
				"title" => $descripcion,
				"description" => $descripcion,
				"notification_url" => $notificationUrl,
				"total_amount" => floatval($monto),
				"items" => array(
					array(
						"title" => $descripcion,
						"description" => $descripcion,
						"quantity" => 1,
						"unit_price" => floatval($monto)
					)
				)
			);
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Authorization: Bearer ' . $credenciales['access_token'],
				'Content-Type: application/json'
			));
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if ($httpCode == 200 || $httpCode == 201) {
				$order = json_decode($response, true);
				error_log("Orden creada exitosamente: " . json_encode($order));
				return array(
					'error' => false,
					'order_id' => isset($order['id']) ? $order['id'] : null,
					'qr_code' => isset($order['qr_code']) ? $order['qr_code'] : null,
					'status' => isset($order['status']) ? $order['status'] : 'pending',
					'external_reference' => $externalReference
				);
			} else {
				error_log("Error creando orden: HTTP $httpCode - $response");
				return array(
					'error' => true,
					'mensaje' => 'Error al crear orden: ' . $response
				);
			}
			
		} catch (Exception $e) {
			error_log("Error creando orden atendido: " . $e->getMessage());
			return array(
				'error' => true,
				'mensaje' => $e->getMessage()
			);
		}
	}

	/*=============================================
	VERIFICAR ESTADO DE ORDEN (MODELO ATENDIDO)
	=============================================*/
	static public function ctrVerificarEstadoOrden($orderId) {
		try {
			$credenciales = self::ctrObtenerCredenciales();
			
			// Obtener user_id
			$userId = null;
			$tokenParts = explode('-', $credenciales['access_token']);
			if (count($tokenParts) >= 5) {
				$userId = $tokenParts[count($tokenParts) - 1];
			}
			
			if (!$userId) {
				return array(
					'error' => true,
					'mensaje' => 'No se pudo obtener el user_id'
				);
			}
			
			// Consultar estado de la orden
			$url = "https://api.mercadopago.com/merchant_orders/$orderId";
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Authorization: Bearer ' . $credenciales['access_token'],
				'Content-Type: application/json'
			));
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if ($httpCode == 200) {
				$order = json_decode($response, true);
				$status = isset($order['status']) ? $order['status'] : 'unknown';
				$closed = ($status === 'closed');
				
				return array(
					'error' => false,
					'aprobado' => $closed,
					'status' => $status,
					'order_id' => $orderId,
					'payment_id' => isset($order['payments']) && count($order['payments']) > 0 ? $order['payments'][0]['id'] : null
				);
			} else {
				error_log("Error consultando orden $orderId: HTTP $httpCode - $response");
				return array(
					'error' => true,
					'mensaje' => 'Error al consultar estado de la orden'
				);
			}
			
		} catch (Exception $e) {
			error_log("Error verificando orden: " . $e->getMessage());
			return array(
				'error' => true,
				'mensaje' => $e->getMessage()
			);
		}
	}

	/*=============================================
	VERIFICAR PAGO POR POS ID Y MONTO (PARA QR ESTÁTICO - MODELO ATENDIDO)
	En el modelo atendido, el cliente escanea el QR estático e ingresa el monto manualmente.
	Mercado Pago NO incluye external_reference automáticamente, así que buscamos por:
	- POS ID (point_of_interaction.transaction_data.qr_code)
	- Monto (transaction_amount)
	- Rango de tiempo (últimos 5 minutos)
	=============================================*/
	static public function ctrVerificarPagoPorExternalReference($externalReference) {
		try {
			$credenciales = self::ctrObtenerCredenciales();
			
			// Extraer información del external_reference
			// Formato: venta_pos_TIMESTAMP_MONTO
			$partes = explode('_', $externalReference);
			$montoEsperado = 0;
			$timestampInicio = 0;
			
			if (count($partes) >= 4 && $partes[0] === 'venta' && $partes[1] === 'pos') {
				$timestampInicio = isset($partes[2]) ? intval($partes[2]) : 0;
				$montoEsperado = isset($partes[3]) ? floatval(str_replace('_', '.', $partes[3])) : 0;
			}
			
			// Obtener POS ID desde la empresa
			$posId = null;
			try {
				if (class_exists('ControladorEmpresa')) {
					$empresa = ControladorEmpresa::ctrMostrarempresa('id', 1);
					if ($empresa && isset($empresa['mp_pos_id']) && !empty($empresa['mp_pos_id'])) {
						$posId = $empresa['mp_pos_id'];
					}
				}
			} catch (Exception $e) {
				error_log("Error obteniendo POS ID: " . $e->getMessage());
			}
			
			// Buscar pagos recientes (últimos 5 minutos) por monto
			// En el modelo atendido, buscamos pagos que coincidan con el monto y tiempo
			$fechaDesde = date('Y-m-d\TH:i:s.000\Z', $timestampInicio - 300); // 5 minutos antes
			$fechaHasta = date('Y-m-d\TH:i:s.000\Z', time() + 60); // 1 minuto después (margen)
			
			// Construir URL de búsqueda
			// Buscar por rango de fecha y monto aproximado
			$url = "https://api.mercadopago.com/v1/payments/search";
			$params = array(
				'sort' => 'date_created',
				'criteria' => 'desc',
				'limit' => 50, // Buscar más pagos para encontrar el correcto
				'range' => 'date_created',
				'begin_date' => $fechaDesde,
				'end_date' => $fechaHasta
			);
			
			$url .= '?' . http_build_query($params);
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Authorization: Bearer ' . $credenciales['access_token'],
				'Content-Type: application/json'
			));
			
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if ($httpCode != 200) {
				error_log("Error consultando pagos: HTTP $httpCode - $response");
				return array(
					'error' => true,
					'mensaje' => 'Error al consultar estado del pago'
				);
			}
			
			$data = json_decode($response, true);
			
			if (!isset($data['results']) || !is_array($data['results']) || count($data['results']) == 0) {
				return array(
					'error' => false,
					'aprobado' => false,
					'status' => 'no_payment',
					'mensaje' => 'Aún no se ha realizado el pago'
				);
			}
			
			// Buscar pago que coincida con:
			// 1. Monto (con tolerancia de 0.01 para redondeos)
			// 2. POS ID si está disponible (point_of_interaction)
			// 3. Estado aprobado
			$toleranciaMonto = 0.01;
			foreach ($data['results'] as $payment) {
				$montoPago = isset($payment['transaction_amount']) ? floatval($payment['transaction_amount']) : 0;
				$paymentPosId = null;
				
				// Intentar obtener POS ID del pago
				if (isset($payment['point_of_interaction']) && 
				    isset($payment['point_of_interaction']['transaction_data']) &&
				    isset($payment['point_of_interaction']['transaction_data']['qr_code'])) {
					// El POS ID puede estar en el QR code o en metadata
					$paymentPosId = isset($payment['point_of_interaction']['transaction_data']['qr_code']) 
						? $payment['point_of_interaction']['transaction_data']['qr_code'] 
						: null;
				}
				
				// Verificar si coincide el monto (con tolerancia)
				$montoCoincide = abs($montoPago - $montoEsperado) <= $toleranciaMonto;
				
				// Si tenemos POS ID, verificar que coincida
				$posCoincide = true;
				if ($posId && $paymentPosId) {
					// El POS ID puede estar en diferentes formatos, comparar si es posible
					$posCoincide = (strpos($paymentPosId, $posId) !== false || $posId === $paymentPosId);
				}
				
				// Si el monto coincide y el estado es aprobado
				if ($montoCoincide && isset($payment['status']) && $payment['status'] === 'approved') {
					error_log("Pago aprobado encontrado: Payment ID " . $payment['id'] . ", Monto: $montoPago, Esperado: $montoEsperado");
					return array(
						'error' => false,
						'aprobado' => true,
						'payment_id' => $payment['id'],
						'status' => $payment['status'],
						'transaction_amount' => $montoPago,
						'date_approved' => isset($payment['date_approved']) ? $payment['date_approved'] : null
					);
				}
				
				// Si el monto coincide y está pendiente
				if ($montoCoincide && isset($payment['status']) && $payment['status'] === 'pending') {
					return array(
						'error' => false,
						'aprobado' => false,
						'status' => 'pending',
						'mensaje' => 'Pago pendiente de confirmación',
						'payment_id' => $payment['id']
					);
				}
			}
			
			// No se encontró pago que coincida
			return array(
				'error' => false,
				'aprobado' => false,
				'status' => 'no_payment',
				'mensaje' => 'Aún no se ha realizado el pago con el monto esperado'
			);

		} catch (Exception $e) {
			error_log("Error verificando pago por external_reference: " . $e->getMessage());
			return array(
				'error' => true,
				'mensaje' => $e->getMessage()
			);
		}
	}
}
