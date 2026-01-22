<?php
/**
 * WEBHOOK MERCADOPAGO - VERSIÓN OPTIMIZADA
 * Procesamiento en tiempo real con transacciones y manejo de errores mejorado
 * 
 * URL a configurar en MercadoPago:
 * https://tu-dominio.com/webhook-mercadopago.php
 */
// PRIMERO que todo, loguear ABSOLUTAMENTE TODO
file_put_contents('/tmp/webhook_raw.log', 
    date('Y-m-d H:i:s') . " - " . 
    $_SERVER['REQUEST_METHOD'] . " - " .
    file_get_contents('php://input') . "\n\n",
    FILE_APPEND
);



// CRÍTICO: SIEMPRE responder 200 OK para que MercadoPago no reintente
ini_set('display_errors', 0);
error_reporting(E_ALL);
date_default_timezone_set('America/Argentina/Mendoza');

// Responder OK INMEDIATAMENTE antes de cualquier procesamiento
http_response_code(200);
header('Content-Type: application/json');

// Función de logging estructurado
function logWebhook($nivel, $mensaje, $datos = []) {
    $log = json_encode([
        'timestamp' => date('Y-m-d H:i:s'),
        'nivel' => $nivel,
        'mensaje' => $mensaje,
        'datos' => $datos
    ], JSON_UNESCAPED_UNICODE);
    error_log($log);
}

// Función para salir con éxito (siempre 200 OK)
function exitOk($message = 'ok', $error = false) {
    echo json_encode(['error' => $error, 'message' => $message]);
    exit;
}

// Función para validar firma del webhook (según documentación oficial)
function validarFirmaWebhook($xSignature, $xRequestId, $dataId, $secretKey) {
    if (empty($xSignature) || empty($secretKey)) {
        logWebhook('WARNING', 'Validación de firma omitida - faltan datos', [
            'tiene_signature' => !empty($xSignature),
            'tiene_secret' => !empty($secretKey)
        ]);
        return true; // No fallar si no hay datos (modo permisivo)
    }
    
    // Extraer ts y v1 del header
    $parts = explode(',', $xSignature);
    $ts = null;
    $hash = null;
    
    foreach ($parts as $part) {
        $keyValue = explode('=', trim($part), 2);
        if (count($keyValue) == 2) {
            $key = trim($keyValue[0]);
            $value = trim($keyValue[1]);
            if ($key === 'ts') {
                $ts = $value;
            } elseif ($key === 'v1') {
                $hash = $value;
            }
        }
    }
    
    if (!$ts || !$hash || !$xRequestId || !$dataId) {
        logWebhook('WARNING', 'Validación de firma omitida - datos incompletos');
        return true; // Modo permisivo
    }
    
    // CRÍTICO: data.id debe estar en minúsculas según documentación
    $dataIdLower = strtolower($dataId);
    
    // Generar manifest según documentación oficial
    $manifest = "id:$dataIdLower;request-id:$xRequestId;ts:$ts;";
    
    // Calcular HMAC SHA256
    $calculatedHash = hash_hmac('sha256', $manifest, $secretKey);
    
    $valido = ($calculatedHash === $hash);
    
    if (!$valido) {
        logWebhook('WARNING', 'Firma de webhook no válida', [
            'calculated' => $calculatedHash,
            'received' => $hash,
            'manifest' => $manifest
        ]);
    } else {
        logWebhook('INFO', 'Firma de webhook válida');
    }
    
    return $valido;
}

// Función para consultar API de MercadoPago
function consultarMP($url, $accessToken, $timeout = 30) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        logWebhook('ERROR', 'Error cURL', ['url' => $url, 'error' => $curlError]);
        return null;
    }
    
    if ($httpCode != 200) {
        logWebhook('WARNING', 'HTTP no 200', ['url' => $url, 'code' => $httpCode, 'response' => substr($response, 0, 200)]);
        return null;
    }
    
    $data = json_decode($response, true);
    if (!$data) {
        logWebhook('ERROR', 'JSON inválido', ['url' => $url, 'response' => substr($response, 0, 200)]);
        return null;
    }
    
    return $data;
}

// Función para buscar cliente desde payment/order
function buscarCliente($payment, $topic, $data = null, $order = null) {
    $idCliente = null;
    
    // Método 1: external_reference directo (numérico)
    if (isset($payment['external_reference']) && is_numeric($payment['external_reference'])) {
        $idCliente = intval($payment['external_reference']);
        logWebhook('INFO', 'Cliente desde external_reference', ['id_cliente' => $idCliente]);
        return $idCliente;
    }
    
    // Método 2: external_reference con formato "ID-*"
    if (isset($payment['external_reference']) && preg_match('/^(\d+)/', $payment['external_reference'], $m)) {
        $idCliente = intval($m[1]);
        logWebhook('INFO', 'Cliente desde external_reference (formato)', ['id_cliente' => $idCliente]);
        return $idCliente;
    }
    
    // Método 3: metadata
    if (isset($payment['metadata']['id_cliente_moon'])) {
        $idCliente = intval($payment['metadata']['id_cliente_moon']);
        logWebhook('INFO', 'Cliente desde metadata', ['id_cliente' => $idCliente]);
        return $idCliente;
    }
    
    // Método 4: external_reference desde orden (para QR)
    if ($order && isset($order['external_reference']) && is_numeric($order['external_reference'])) {
        $idCliente = intval($order['external_reference']);
        logWebhook('INFO', 'Cliente desde orden external_reference', ['id_cliente' => $idCliente]);
        return $idCliente;
    }
    
    // Método 5: external_reference desde JSON del webhook (para QR con formato order.processed)
    // CRÍTICO: El JSON del webhook QR tiene la estructura: {"action":"order.processed","data":{"external_reference":"14",...}}
    if ($data && isset($data['data']['external_reference'])) {
        $externalRef = $data['data']['external_reference'];
        // Si es numérico directo
        if (is_numeric($externalRef)) {
            $idCliente = intval($externalRef);
            logWebhook('INFO', 'Cliente desde JSON webhook (numérico)', ['id_cliente' => $idCliente, 'external_ref' => $externalRef]);
            return $idCliente;
        }
        // Si tiene formato "ID-*", extraer el ID
        if (preg_match('/^(\d+)/', $externalRef, $m)) {
            $idCliente = intval($m[1]);
            logWebhook('INFO', 'Cliente desde JSON webhook (formato)', ['id_cliente' => $idCliente, 'external_ref' => $externalRef]);
            return $idCliente;
        }
    }
    
    // Método 6: Buscar en intentos pendientes por monto (para QR sin external_reference)
    if (isset($payment['transaction_amount'])) {
        $monto = floatval($payment['transaction_amount']);
        if ($monto > 0) {
            try {
                $conexion = Conexion::conectarMoon();
                if ($conexion) {
                    $stmt = $conexion->prepare("SELECT id_cliente_moon FROM mercadopago_intentos 
                        WHERE ABS(monto - :monto) < 0.01
                        AND estado = 'pendiente' 
                        AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)
                        ORDER BY fecha_creacion DESC
                        LIMIT 1");
                    $stmt->bindParam(":monto", $monto, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                    
                    if ($result && isset($result['id_cliente_moon']) && $result['id_cliente_moon'] > 0) {
                        $idCliente = intval($result['id_cliente_moon']);
                        logWebhook('INFO', 'Cliente desde intentos por monto', ['id_cliente' => $idCliente, 'monto' => $monto]);
                        return $idCliente;
                    }
                }
            } catch (Exception $e) {
                logWebhook('ERROR', 'Error buscando cliente en intentos', ['error' => $e->getMessage()]);
            }
        }
    }
    
    // Sin cliente (pago QR genérico o venta POS)
    logWebhook('WARNING', 'Cliente no encontrado', ['payment_id' => isset($payment['id']) ? $payment['id'] : 'N/A']);
    return 0;
}

// Cargar dependencias
try {
    if (file_exists(__DIR__ . '/extensiones/vendor/autoload.php')) {
        require_once __DIR__ . '/extensiones/vendor/autoload.php';
    }
    if (file_exists(__DIR__ . '/config.php')) {
        require_once __DIR__ . '/config.php';
    }
    if (file_exists(__DIR__ . '/.env') && class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }
    if (file_exists(__DIR__ . '/helpers.php')) {
        require_once __DIR__ . '/helpers.php';
    }
    
    require_once __DIR__ . '/controladores/mercadopago.controlador.php';
    require_once __DIR__ . '/controladores/sistema_cobro.controlador.php';
    require_once __DIR__ . '/modelos/mercadopago.modelo.php';
    require_once __DIR__ . '/modelos/sistema_cobro.modelo.php';
    require_once __DIR__ . '/modelos/conexion.php';
} catch (Exception $e) {
    logWebhook('ERROR', 'Error cargando dependencias', ['error' => $e->getMessage()]);
    exitOk('Dependencias no disponibles', false);
}

// Manejar OPTIONS (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exitOk('OPTIONS request');
}

// Test de webhook (GET sin parámetros)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($_GET['topic']) && empty($_GET['id']) && empty($_GET['data_id'])) {
    logWebhook('INFO', 'Test de webhook');
    exitOk('Webhook activo y funcionando');
}

try {
    // 1. CAPTURAR DATOS DEL WEBHOOK
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $topic = $_GET['topic'] ?? $_GET['type'] ?? $data['type'] ?? null;
    $id = $_GET['id'] ?? $_GET['data_id'] ?? $data['data']['id'] ?? null;
    $action = null;
    
    // Detectar formato QR nuevo: {"action": "order.processed", "type": "order", "data": {"id": "123456"}}
    if (isset($data['action']) && isset($data['data']['id'])) {
        $action = $data['action'];
        if (strpos($action, 'order') !== false) {
            $topic = 'merchant_order';
        } else {
            $topic = isset($data['type']) ? $data['type'] : 'payment';
        }
        $id = $data['data']['id'];
        logWebhook('INFO', 'Formato QR detectado', ['action' => $action, 'topic' => $topic, 'id' => $id]);
    }
    
    // Si topic es "order", convertir a merchant_order
    if ($topic === 'order') {
        $topic = 'merchant_order';
    }
    
    logWebhook('INFO', 'Webhook recibido', [
        'topic' => $topic,
        'id' => $id,
        'action' => $action,
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
    
    // Validar firma del webhook (opcional pero recomendado)
    // La clave secreta se obtiene de la configuración de la aplicación en Mercado Pago
    $xSignature = $_SERVER['HTTP_X_SIGNATURE'] ?? null;
    $xRequestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? null;
    $dataId = isset($_GET['data.id']) ? $_GET['data.id'] : (isset($data['data']['id']) ? $data['data']['id'] : $id);
    
    // Intentar obtener clave secreta desde .env o configuración
    $webhookSecret = isset($_ENV['MP_WEBHOOK_SECRET']) ? $_ENV['MP_WEBHOOK_SECRET'] : null;
    
    if ($xSignature && $webhookSecret && $xRequestId && $dataId) {
        $firmaValida = validarFirmaWebhook($xSignature, $xRequestId, $dataId, $webhookSecret);
        if (!$firmaValida) {
            logWebhook('WARNING', 'Firma de webhook inválida - procesando de todas formas (modo permisivo)');
            // No bloquear el procesamiento, solo loguear (modo permisivo)
        }
    } else {
        logWebhook('INFO', 'Validación de firma omitida - no hay datos suficientes', [
            'tiene_signature' => !empty($xSignature),
            'tiene_secret' => !empty($webhookSecret),
            'tiene_request_id' => !empty($xRequestId),
            'tiene_data_id' => !empty($dataId)
        ]);
    }
    
    // Validar datos mínimos
    if (!$topic || !$id) {
        logWebhook('WARNING', 'Parámetros inválidos', ['topic' => $topic, 'id' => $id]);
        exitOk('Parámetros inválidos', false);
    }
    
    // Ignorar IDs de prueba SOLO para payments (NO para merchant_orders)
    if ($topic === 'payment' && ($id === '123456' || (strlen($id) < 9 && !preg_match('/^[0-9]{9,}$/', $id)))) {
        logWebhook('INFO', 'ID de prueba ignorado', ['id' => $id, 'topic' => $topic]);
        if (class_exists('ModeloMercadoPago')) {
            $datosWebhookTest = [
                'topic' => $topic,
                'resource_id' => $id,
                'datos_json' => json_encode(['ignored' => true, 'reason' => 'test_id']),
                'fecha_recibido' => date('Y-m-d H:i:s'),
                'procesado' => 1
            ];
            ControladorMercadoPago::ctrRegistrarWebhook($datosWebhookTest);
        }
        exitOk('Test ID ignorado', false);
    }
    
    // 2. LOCK PARA EVITAR PROCESAMIENTO DUPLICADO
    $lockKey = "mp_webhook_{$topic}_{$id}";
    $lockFile = sys_get_temp_dir() . "/{$lockKey}.lock";
    $fp = @fopen($lockFile, 'w+');
    
    if ($fp && !flock($fp, LOCK_EX | LOCK_NB)) {
        logWebhook('WARNING', 'Webhook ya en proceso', ['id' => $id]);
        exitOk('Ya procesando', false);
    }
    
    try {
        // 3. REGISTRAR WEBHOOK EN BD
        $webhookId = null;
        if (class_exists('ControladorMercadoPago')) {
            $webhookId = ControladorMercadoPago::ctrRegistrarWebhook([
                'topic' => $topic,
                'resource_id' => $id,
                'datos_json' => $input ?: json_encode(['get' => $_GET, 'post' => $_POST]),
                'fecha_recibido' => date('Y-m-d H:i:s'),
                'procesado' => 0
            ]);
            logWebhook('INFO', 'Webhook registrado en BD', ['webhook_id' => $webhookId]);
        }
        
        // 4. VERIFICAR SI YA FUE PROCESADO (solo para payments directos)
        if ($topic === 'payment' && ControladorMercadoPago::ctrVerificarPagoProcesado($id)) {
            logWebhook('INFO', 'Pago ya procesado', ['payment_id' => $id]);
            if ($webhookId) {
                ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
            }
            exitOk('Ya procesado', false);
        }
        
        // 5. PROCESAR SOLO PAYMENTS Y MERCHANT_ORDERS
        if ($topic !== 'payment' && $topic !== 'merchant_order') {
            logWebhook('INFO', 'Topic ignorado', ['topic' => $topic]);
            if ($webhookId) {
                ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
            }
            exitOk('Topic ignorado', false);
        }
        
        // 6. OBTENER CREDENCIALES
        $credenciales = ControladorMercadoPago::ctrObtenerCredenciales();
        if (empty($credenciales['access_token'])) {
            throw new Exception('Credenciales no disponibles');
        }
        
        // 7. OBTENER PAYMENT DESDE MP
        $payment = null;
        $order = null;
        
        if ($topic === 'merchant_order' || ($action && strpos($action, 'order') !== false)) {
            logWebhook('INFO', 'Procesando order (QR)', ['order_id' => $id, 'action' => $action]);
            
            // Verificar el estado de la order según el action
            $orderStatus = null;
            if ($action) {
                if ($action === 'order.processed') {
                    $orderStatus = 'processed';
                } elseif ($action === 'order.canceled') {
                    $orderStatus = 'canceled';
                    logWebhook('INFO', 'Order cancelada', ['order_id' => $id]);
                    if ($webhookId) {
                        ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                    }
                    exitOk('Order cancelada', false);
                } elseif ($action === 'order.refunded') {
                    $orderStatus = 'refunded';
                    logWebhook('INFO', 'Order reembolsada', ['order_id' => $id]);
                    // TODO: Procesar reembolso si es necesario
                    if ($webhookId) {
                        ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                    }
                    exitOk('Order reembolsada', false);
                } elseif ($action === 'order.expired') {
                    $orderStatus = 'expired';
                    logWebhook('INFO', 'Order expirada', ['order_id' => $id]);
                    if ($webhookId) {
                        ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                    }
                    exitOk('Order expirada', false);
                }
            }
            
            // MÉTODO 1: Intentar extraer payments del JSON directamente (formato nuevo)
            $paymentsParaProcesar = [];
            if ($data && isset($data['data']['transactions']['payments']) && is_array($data['data']['transactions']['payments'])) {
                logWebhook('INFO', 'Payments encontrados en JSON (formato nuevo)', ['cantidad' => count($data['data']['transactions']['payments'])]);
                $paymentsParaProcesar = $data['data']['transactions']['payments'];
            } 
            // MÉTODO 2: Consultar order usando API moderna /v1/orders
            if (empty($paymentsParaProcesar)) {
                $orderUrl = "https://api.mercadopago.com/v1/orders/$id";
                $order = consultarMP($orderUrl, $credenciales['access_token']);
                
                if ($order && isset($order['transactions']['payments']) && is_array($order['transactions']['payments'])) {
                    logWebhook('INFO', 'Payments encontrados en order (API /v1/orders)', ['cantidad' => count($order['transactions']['payments'])]);
                    $paymentsParaProcesar = $order['transactions']['payments'];
                }
                // Fallback: API antigua merchant_orders
                elseif ($order && isset($order['payments']) && is_array($order['payments']) && count($order['payments']) > 0) {
                    logWebhook('INFO', 'Payments encontrados en orden (API antigua)', ['cantidad' => count($order['payments'])]);
                    foreach ($order['payments'] as $paymentInfo) {
                        $paymentsParaProcesar[] = [
                            'id' => is_array($paymentInfo) ? ($paymentInfo['id'] ?? $paymentInfo) : $paymentInfo,
                            'amount' => $paymentInfo['transaction_amount'] ?? null,
                            'status' => $paymentInfo['status'] ?? null
                        ];
                    }
                }
            }
            
            if (empty($paymentsParaProcesar)) {
                logWebhook('WARNING', 'No se encontraron payments en la orden', ['order_id' => $id, 'action' => $action]);
                if ($webhookId) {
                    ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                }
                exitOk('Orden sin payments', false);
            }
            
            // Procesar el primer payment aprobado
            foreach ($paymentsParaProcesar as $paymentInfo) {
                $paymentId = is_array($paymentInfo) ? ($paymentInfo['id'] ?? null) : $paymentInfo;
                
                if (!$paymentId) {
                    continue;
                }
                
                // Verificar si ya fue procesado
                if (ControladorMercadoPago::ctrVerificarPagoProcesado($paymentId)) {
                    logWebhook('INFO', 'Payment ya procesado', ['payment_id' => $paymentId]);
                    continue;
                }
                
                // Consultar payment completo
                $paymentUrl = "https://api.mercadopago.com/v1/payments/$paymentId";
                $payment = consultarMP($paymentUrl, $credenciales['access_token']);
                
                if ($payment && isset($payment['status'])) {
                    // CRÍTICO: Agregar external_reference si no lo tiene
                    // Prioridad: 1) JSON del webhook, 2) Orden consultada, 3) Payment original
                    if (!isset($payment['external_reference']) || empty($payment['external_reference'])) {
                        if ($data && isset($data['data']['external_reference'])) {
                            $payment['external_reference'] = $data['data']['external_reference'];
                            logWebhook('INFO', 'External reference agregado desde JSON', ['external_ref' => $data['data']['external_reference']]);
                        } elseif ($order && isset($order['external_reference'])) {
                            $payment['external_reference'] = $order['external_reference'];
                            logWebhook('INFO', 'External reference agregado desde orden', ['external_ref' => $order['external_reference']]);
                        }
                    } else {
                        logWebhook('INFO', 'Payment ya tiene external_reference', ['external_ref' => $payment['external_reference']]);
                    }
                    break; // Procesar solo el primero
                }
            }
        } else {
            // Payment directo
            $paymentUrl = "https://api.mercadopago.com/v1/payments/$id";
            $payment = consultarMP($paymentUrl, $credenciales['access_token']);
        }
        
        if (!$payment || !isset($payment['status'])) {
            logWebhook('ERROR', 'No se pudo obtener payment', ['id' => $id, 'topic' => $topic]);
            if ($webhookId) {
                ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
            }
            exitOk('No se pudo obtener payment', false);
        }
        
        logWebhook('INFO', 'Payment obtenido', [
            'payment_id' => $payment['id'],
            'status' => $payment['status'],
            'amount' => $payment['transaction_amount'] ?? 0
        ]);
        
        // 8. PROCESAR SOLO PAGOS APROBADOS
        if ($payment['status'] !== 'approved') {
            logWebhook('INFO', 'Payment no aprobado', ['status' => $payment['status']]);
            // Registrar pero no procesar cuenta corriente
            $datosPago = [
                'id_cliente_moon' => 0,
                'payment_id' => $payment['id'],
                'preference_id' => $payment['preference_id'] ?? null,
                'monto' => $payment['transaction_amount'] ?? 0,
                'estado' => $payment['status'],
                'fecha_pago' => date('Y-m-d H:i:s'),
                'payment_type' => $payment['payment_type_id'] ?? null,
                'payment_method_id' => $payment['payment_method_id'] ?? 'desconocido',
                'datos_json' => json_encode($payment)
            ];
            ControladorMercadoPago::ctrRegistrarPagoConfirmado($datosPago);
            if ($webhookId) {
                ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
            }
            exitOk('Estado: ' . $payment['status'], false);
        }
        
        // 9. BUSCAR CLIENTE
        $idCliente = buscarCliente($payment, $topic, $data, $order);
        logWebhook('INFO', 'Cliente identificado', ['id_cliente' => $idCliente]);
        
        // 10. PROCESAR CON TRANSACCIÓN
        $pdo = Conexion::conectarMoon();
        if (!$pdo) {
            throw new Exception('No se pudo conectar a BD Moon');
        }
        
        $pdo->beginTransaction();
        
        try {
            // Preparar datos del pago
            $fechaPago = date('Y-m-d H:i:s');
            if (isset($payment['date_approved']) && !empty($payment['date_approved'])) {
                $fechaAprobada = strtotime($payment['date_approved']);
                if ($fechaAprobada !== false) {
                    $fechaPago = date('Y-m-d H:i:s', $fechaAprobada);
                }
            }
            
            $datosPago = [
                'id_cliente_moon' => $idCliente,
                'payment_id' => $payment['id'],
                'preference_id' => $payment['preference_id'] ?? null,
                'monto' => $payment['transaction_amount'] ?? 0,
                'estado' => 'approved',
                'fecha_pago' => $fechaPago,
                'payment_type' => $payment['payment_type_id'] ?? null,
                'payment_method_id' => $payment['payment_method_id'] ?? 'desconocido',
                'datos_json' => json_encode($payment)
            ];
            
            // 1. Registrar pago
            logWebhook('INFO', 'Registrando pago en mercadopago_pagos', ['payment_id' => $payment['id']]);
            $resultadoPago = ControladorMercadoPago::ctrRegistrarPagoConfirmado($datosPago);
            if ($resultadoPago !== "ok") {
                throw new Exception('Error registrando pago: ' . (is_array($resultadoPago) ? json_encode($resultadoPago) : $resultadoPago));
            }
            logWebhook('SUCCESS', 'Pago registrado en mercadopago_pagos', ['payment_id' => $payment['id']]);
            
            // 2. Procesar cuenta corriente (solo si hay cliente)
            if ($idCliente > 0) {
                logWebhook('INFO', 'Registrando en cuenta corriente', ['id_cliente' => $idCliente, 'monto' => $datosPago['monto']]);
                $resultadoCtaCte = ControladorSistemaCobro::ctrRegistrarMovimientoCuentaCorriente(
                    $idCliente,
                    $datosPago['monto']
                );
                
                if ($resultadoCtaCte !== "ok") {
                    throw new Exception('Error en cuenta corriente: ' . (is_array($resultadoCtaCte) ? json_encode($resultadoCtaCte) : $resultadoCtaCte));
                }
                logWebhook('SUCCESS', 'Cuenta corriente actualizada', ['id_cliente' => $idCliente]);
                
                // 3. Desbloquear cliente si está bloqueado
                $cliente = ControladorSistemaCobro::ctrMostrarClientesCobro($idCliente);
                if ($cliente && isset($cliente['estado_bloqueo']) && $cliente['estado_bloqueo'] == 1) {
                    $desbloqueo = ControladorSistemaCobro::ctrActualizarClientesCobro($idCliente, 0);
                    if ($desbloqueo === false) {
                        throw new Exception('Error desbloqueando cliente');
                    }
                    logWebhook('SUCCESS', 'Cliente desbloqueado', ['id_cliente' => $idCliente]);
                }
            } else {
                logWebhook('INFO', 'Pago sin cliente asociado (QR/venta POS)', ['payment_id' => $payment['id']]);
            }
            
            // 4. Actualizar intento
            $preferenceId = $payment['preference_id'] ?? null;
            $orderId = ($topic === 'merchant_order') ? $id : ($payment['order']['id'] ?? null);
            if ($preferenceId || $orderId) {
                ModeloMercadoPago::mdlActualizarEstadoIntento($preferenceId, 'aprobado', $orderId);
                logWebhook('INFO', 'Intento actualizado', ['preference_id' => $preferenceId, 'order_id' => $orderId]);
            }
            
            // 5. Marcar webhook procesado
            if ($webhookId) {
                ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
            }
            
            // COMMIT - Todo OK
            $pdo->commit();
            
            logWebhook('SUCCESS', 'Pago procesado exitosamente', [
                'payment_id' => $payment['id'],
                'cliente' => $idCliente,
                'monto' => $datosPago['monto']
            ]);
            
            echo json_encode([
                'error' => false,
                'message' => 'Pago procesado',
                'payment_id' => $payment['id']
            ]);
            
        } catch (Exception $e) {
            // ROLLBACK en caso de error
            $pdo->rollBack();
            throw $e;
        }
        
    } finally {
        // Liberar lock
        if ($fp) {
            flock($fp, LOCK_UN);
            fclose($fp);
            @unlink($lockFile);
        }
    }
    
} catch (Exception $e) {
    logWebhook('ERROR', 'Error procesando webhook', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // IMPORTANTE: Devolver 200 OK para que MP no reintente infinitamente
    // Los errores se loguean pero no se muestran al exterior
    exitOk('Error procesando: ' . $e->getMessage(), false);
}
