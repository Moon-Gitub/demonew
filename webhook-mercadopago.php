<?php
/**
 * WEBHOOK DE MERCADOPAGO
 *
 * Este archivo recibe las notificaciones automáticas de MercadoPago
 * cuando un pago cambia de estado (aprobado, rechazado, etc)
 *
 * URL a configurar en MercadoPago:
 * https://tu-dominio.com/webhook-mercadopago.php
 */

// CRÍTICO: SIEMPRE responder 200 OK para que MercadoPago no reintente
// Los errores se loguean pero no se muestran al exterior
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Responder OK INMEDIATAMENTE antes de cualquier procesamiento
// Esto previene que MercadoPago reciba error 500 y reintente
http_response_code(200);
header('Content-Type: application/json');

// Función para salir con éxito (siempre 200 OK)
function exitOk($message = 'ok', $error = false) {
    echo json_encode(['error' => $error, 'message' => $message]);
    exit;
}

try {
    // Cargar vendor autoload y configuración
    if (file_exists(__DIR__ . '/extensiones/vendor/autoload.php')) {
        require_once __DIR__ . '/extensiones/vendor/autoload.php';
    }

    // Cargar configuración
    if (file_exists(__DIR__ . '/config.php')) {
        require_once __DIR__ . '/config.php';
    }

    // Cargar variables de entorno desde .env (si existe y si Dotenv está instalado)
    if (file_exists(__DIR__ . '/.env') && class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }

    // Cargar helpers si existe
    if (file_exists(__DIR__ . '/helpers.php')) {
        require_once __DIR__ . '/helpers.php';
    }

    // Cargar dependencias solo si existen
    if (file_exists(__DIR__ . '/controladores/mercadopago.controlador.php')) {
        require_once __DIR__ . '/controladores/mercadopago.controlador.php';
    }
    if (file_exists(__DIR__ . '/controladores/sistema_cobro.controlador.php')) {
        require_once __DIR__ . '/controladores/sistema_cobro.controlador.php';
    }
    if (file_exists(__DIR__ . '/modelos/mercadopago.modelo.php')) {
        require_once __DIR__ . '/modelos/mercadopago.modelo.php';
    }
    if (file_exists(__DIR__ . '/modelos/sistema_cobro.modelo.php')) {
        require_once __DIR__ . '/modelos/sistema_cobro.modelo.php';
    }
    // Cargar conexión para búsquedas directas
    if (file_exists(__DIR__ . '/modelos/conexion.php')) {
        require_once __DIR__ . '/modelos/conexion.php';
    }
} catch (Exception $e) {
    error_log("ERROR CARGANDO DEPENDENCIAS WEBHOOK: " . $e->getMessage());
    // Responder OK de todos modos para que MP no reintente
    exitOk('Dependencias no disponibles, webhook recibido pero no procesado', false);
}

// Configurar zona horaria
date_default_timezone_set('America/Argentina/Mendoza');

// Log para debugging con timestamp
$timestamp = date('Y-m-d H:i:s');
$inputRaw = file_get_contents('php://input');
error_log("==========================================");
error_log("=== WEBHOOK MERCADOPAGO RECIBIDO ===");
error_log("Timestamp: $timestamp");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("GET params: " . json_encode($_GET));
error_log("POST params: " . json_encode($_POST));
error_log("Body raw (primeros 500 chars): " . substr($inputRaw, 0, 500));
error_log("Body completo length: " . strlen($inputRaw));
if ($inputRaw) {
    $inputParsed = json_decode($inputRaw, true);
    if ($inputParsed) {
        error_log("Body parseado correctamente:");
        error_log("   - action: " . (isset($inputParsed['action']) ? $inputParsed['action'] : 'NO'));
        error_log("   - type: " . (isset($inputParsed['type']) ? $inputParsed['type'] : 'NO'));
        error_log("   - data.id: " . (isset($inputParsed['data']['id']) ? $inputParsed['data']['id'] : 'NO'));
        error_log("   - data.external_reference: " . (isset($inputParsed['data']['external_reference']) ? $inputParsed['data']['external_reference'] : 'NO'));
        error_log("   - data.transactions.payments: " . (isset($inputParsed['data']['transactions']['payments']) ? count($inputParsed['data']['transactions']['payments']) : 0));
    } else {
        error_log("⚠️ Body NO se pudo parsear como JSON");
    }
}
error_log("Headers: " . json_encode(getallheaders()));
error_log("==========================================");

// Si es una petición OPTIONS (preflight), responder y salir
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exitOk('OPTIONS request');
}

// Aceptar tanto GET como POST
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("⚠️ Método no permitido: " . $_SERVER['REQUEST_METHOD']);
    exitOk('Method not allowed but ack received', false);
}

// Si es un test de MercadoPago (GET sin parámetros), responder OK
if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($_GET['topic']) && empty($_GET['id'])) {
    error_log("✅ Test de webhook - Respondiendo OK");
    exitOk('Webhook activo y funcionando');
}

try {
    // Obtener parámetros del webhook
    $topic = isset($_GET['topic']) ? $_GET['topic'] : (isset($_POST['topic']) ? $_POST['topic'] : null);
    $id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : null);
    $action = null; // Para notificaciones QR con formato nuevo
    
    // CRÍTICO: Verificar también GET con data_id (formato QR de MP)
    if (!$id && isset($_GET['data_id'])) {
        $id = $_GET['data_id'];
        $topic = isset($_GET['type']) ? $_GET['type'] : null;
        error_log("✅ Detectado formato GET con data_id: $id, type: $topic");
    }

    // Si viene por POST o en input, intentar parsear el body
    $input = file_get_contents('php://input');
    if ($input) {
        $data = json_decode($input, true);

        if ($data) {
            // NUEVO FORMATO QR: {"action": "order.processed", "type": "order", "data": {"id": "123456789", ...}}
            if (isset($data['action']) && isset($data['data']['id'])) {
                $action = $data['action'];
                // Si action es "order.processed", es una merchant_order
                if (strpos($action, 'order') !== false) {
                    $topic = 'merchant_order';
                } else {
                    $topic = isset($data['type']) ? $data['type'] : 'payment';
                }
                $id = $data['data']['id'];
                error_log("✅✅✅ WEBHOOK QR DETECTADO - Formato nuevo en input ✅✅✅");
                error_log("   Action: $action");
                error_log("   Topic detectado: $topic");
                error_log("   Order/Payment ID: $id");
                
                // Si viene con payments en data.transactions.payments, guardar para referencia
                if (isset($data['data']['transactions']['payments']) && is_array($data['data']['transactions']['payments'])) {
                    error_log("   Payments en data.transactions.payments: " . count($data['data']['transactions']['payments']));
                }
            } 
            // FORMATO TRADICIONAL
            else {
                if (!$topic) {
                    $topic = isset($data['topic']) ? $data['topic'] : (isset($data['type']) ? $data['type'] : null);
                }
                if (!$id) {
                    $id = isset($data['id']) ? $data['id'] : (isset($data['data']['id']) ? $data['data']['id'] : null);
                }
            }
        }
    }
    
    // Si topic es "order" del formato QR, convertirlo a merchant_order
    if ($topic === 'order') {
        $topic = 'merchant_order';
        error_log("✅ Topic 'order' convertido a 'merchant_order' para procesamiento QR");
    }

    error_log("Topic FINAL: $topic");
    error_log("ID FINAL: $id");
    if ($action) {
        error_log("Action: $action");
    }

    // Validar que tengamos los datos necesarios
    if (!$topic || !$id) {
        error_log("ERROR: Faltan parámetros topic o id");
        echo json_encode(['error' => false, 'message' => 'Parámetros recibidos']);
        exit;
    }
    
    // CRÍTICO: Solo ignorar IDs de prueba para payments, NO para merchant_orders
    // Los merchant_orders pueden tener IDs cortos (como 123456 en pruebas)
    // PERO los payment_ids reales son números largos (9+ dígitos)
    if ($topic === 'payment' && ($id === '123456' || ($id !== null && strlen($id) < 9 && !preg_match('/^[0-9]{9,}$/', $id)))) {
        error_log("⚠️ Webhook ignorado: ID de prueba o inválido detectado (ID: $id, Topic: $topic)");
        error_log("   Los payment_ids reales de MercadoPago son números largos (mínimo 9 dígitos)");
        
        // Registrar webhook pero marcarlo como procesado para evitar reintentos
        if (class_exists('ModeloMercadoPago')) {
            $datosWebhookTest = array(
                'topic' => $topic,
                'resource_id' => $id,
                'datos_json' => json_encode(['ignored' => true, 'reason' => 'test_id']),
                'fecha_recibido' => date('Y-m-d H:i:s'),
                'procesado' => 1 // Marcar como procesado inmediatamente
            );
            $webhookId = ControladorMercadoPago::ctrRegistrarWebhook($datosWebhookTest);
        }
        
        exitOk('Webhook de prueba ignorado (ID: 123456)', false);
    }
    
    // IMPORTANTE: NO ignorar merchant_orders aunque tengan ID corto
    // Los merchant_orders pueden tener IDs cortos y aún así ser válidos
    // El ID real del payment vendrá dentro de data.transactions.payments
    
    // Log detallado para payment_ids reales
    if ($topic === 'payment' && preg_match('/^[0-9]{9,}$/', $id)) {
        error_log("✅✅✅ WEBHOOK RECIBIDO - PAYMENT_ID REAL DETECTADO ✅✅✅");
        error_log("   Payment ID: $id");
        error_log("   Topic: $topic");
        error_log("   Este es un pago REAL de MercadoPago, se procesará");
    }

    // Registrar el webhook en la base de datos (solo si las clases están disponibles)
    $webhookId = null;
    
    // Verificar que las clases estén disponibles ANTES de procesar
    if (!class_exists('ControladorMercadoPago')) {
        error_log("❌ ERROR CRÍTICO: ControladorMercadoPago no está disponible");
        error_log("   Verificar que el archivo existe: " . __DIR__ . '/controladores/mercadopago.controlador.php');
        exitOk('Controlador no disponible - webhook recibido pero no procesado', false);
    }
    
    if (!class_exists('ModeloMercadoPago')) {
        error_log("❌ ERROR CRÍTICO: ModeloMercadoPago no está disponible");
        error_log("   Verificar que el archivo existe: " . __DIR__ . '/modelos/mercadopago.modelo.php');
        exitOk('Modelo no disponible - webhook recibido pero no procesado', false);
    }
    
    if (!class_exists('ControladorSistemaCobro')) {
        error_log("❌ ERROR CRÍTICO: ControladorSistemaCobro no está disponible");
        error_log("   Verificar que el archivo existe: " . __DIR__ . '/controladores/sistema_cobro.controlador.php');
        exitOk('ControladorSistemaCobro no disponible - webhook recibido pero no procesado', false);
    }
    
    error_log("✅ Todas las clases están disponibles, procediendo a registrar webhook");
    
    $datosWebhook = array(
        'topic' => $topic,
        'resource_id' => $id,
        'datos_json' => json_encode(array(
            'get' => $_GET,
            'post' => $_POST,
            'input' => file_get_contents('php://input')
        )),
        'fecha_recibido' => date('Y-m-d H:i:s'),
        'procesado' => 0
    );

    $webhookId = ControladorMercadoPago::ctrRegistrarWebhook($datosWebhook);
    error_log("✅ Webhook registrado con ID: $webhookId");

    // Procesar si es un pago o una orden (modelo atendido)
    if ($topic === 'payment' || $topic === 'merchant_order') {

        error_log("Procesando pago con ID: $id");

        // Verificar si ya fue procesado
        // IMPORTANTE: Verificar por payment_id, no por order_id
        $paymentIdParaVerificar = $id; // Por defecto usar el ID recibido
        
        // Si es merchant_order, necesitamos obtener el payment_id primero
        if ($topic === 'merchant_order') {
            // La verificación se hará después de obtener el payment_id de la orden
        } else {
            // Para payment, verificar directamente
            if (ControladorMercadoPago::ctrVerificarPagoProcesado($id)) {
                error_log("⚠️ Pago $id ya fue procesado anteriormente");

                // Marcar webhook como procesado
                if ($webhookId) {
                    ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                    error_log("✅ Webhook marcado como procesado (pago duplicado)");
                }

                echo json_encode(['error' => false, 'message' => 'Pago ya procesado']);
                exit;
            }
        }

        // Obtener credenciales (ya verificamos que las clases existen arriba)
        $credenciales = ControladorMercadoPago::ctrObtenerCredenciales();
        
        if (empty($credenciales['access_token'])) {
            error_log("❌ ERROR CRÍTICO: No se pudo obtener access_token de MercadoPago");
            if ($webhookId) {
                ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
            }
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Credenciales no disponibles']);
            exit;
        }

        // CRÍTICO: Si es merchant_order (especialmente del nuevo formato QR), procesar payments
        if ($topic === 'merchant_order') {
            error_log("═══════════════════════════════════════");
            error_log("PROCESANDO MERCHANT_ORDER (QR)");
            error_log("Order ID: $id");
            if ($action) {
                error_log("Action: $action");
            }
            error_log("═══════════════════════════════════════");
            
            $payment = null;
            $order = null;
            $paymentsParaProcesar = [];
            
            // MÉTODO 1: Intentar extraer payments directamente del JSON del input (más rápido)
            if ($input) {
                $inputData = json_decode($input, true);
                error_log("DEBUG: Input parseado: " . ($inputData ? "SÍ" : "NO"));
                if ($inputData) {
                    error_log("DEBUG: Tiene data: " . (isset($inputData['data']) ? "SÍ" : "NO"));
                    if (isset($inputData['data'])) {
                        error_log("DEBUG: Tiene transactions: " . (isset($inputData['data']['transactions']) ? "SÍ" : "NO"));
                        if (isset($inputData['data']['transactions'])) {
                            error_log("DEBUG: Tiene payments: " . (isset($inputData['data']['transactions']['payments']) ? "SÍ" : "NO"));
                            if (isset($inputData['data']['transactions']['payments'])) {
                                error_log("DEBUG: Payments es array: " . (is_array($inputData['data']['transactions']['payments']) ? "SÍ" : "NO"));
                                if (is_array($inputData['data']['transactions']['payments'])) {
                                    error_log("DEBUG: Cantidad de payments: " . count($inputData['data']['transactions']['payments']));
                                }
                            }
                        }
                    }
                }
                
                if ($inputData && isset($inputData['data']['transactions']['payments']) && is_array($inputData['data']['transactions']['payments']) && count($inputData['data']['transactions']['payments']) > 0) {
                    error_log("✅✅✅ Payments encontrados directamente en JSON del input ✅✅✅");
                    $paymentsParaProcesar = $inputData['data']['transactions']['payments'];
                    error_log("   Cantidad de payments: " . count($paymentsParaProcesar));
                    // Guardar external_reference y otros datos de la orden del JSON
                    if (isset($inputData['data']['external_reference'])) {
                        $externalRefFromJson = $inputData['data']['external_reference'];
                        error_log("   External Reference desde JSON: $externalRefFromJson");
                    }
                } else {
                    error_log("⚠️ NO se encontraron payments en data.transactions.payments del JSON");
                }
            } else {
                error_log("⚠️ Input está vacío");
            }
            
            // MÉTODO 2: Si no hay payments en el JSON, consultar la orden en MP
            if (empty($paymentsParaProcesar)) {
                error_log("⚠️ No se encontraron payments en JSON, consultando orden en MP...");
                
                $orderUrl = "https://api.mercadopago.com/merchant_orders/$id";
                $ch = curl_init($orderUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization: Bearer ' . $credenciales['access_token'],
                    'Content-Type: application/json'
                ));
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                
                $orderResponse = curl_exec($ch);
                $orderHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                if ($curlError) {
                    error_log("❌ ERROR cURL al consultar orden: $curlError");
                }
                
                if ($orderHttpCode == 200) {
                    $order = json_decode($orderResponse, true);
                    
                    if ($order && is_array($order) && isset($order['payments']) && is_array($order['payments']) && count($order['payments']) > 0) {
                        error_log("✅ Orden obtenida de MP con " . count($order['payments']) . " payment(s)");
                        // Convertir payments de la orden a formato similar
                        foreach ($order['payments'] as $paymentInfo) {
                            $paymentsParaProcesar[] = array(
                                'id' => isset($paymentInfo['id']) ? $paymentInfo['id'] : (is_numeric($paymentInfo) ? $paymentInfo : null),
                                'amount' => isset($paymentInfo['transaction_amount']) ? $paymentInfo['transaction_amount'] : null,
                                'status' => isset($paymentInfo['status']) ? $paymentInfo['status'] : null
                            );
                        }
                    }
                } else {
                    error_log("❌ ERROR: No se pudo consultar la orden (HTTP $orderHttpCode)");
                }
            }
            
            // Procesar cada payment encontrado
            if (!empty($paymentsParaProcesar)) {
                error_log("═══════════════════════════════════════");
                error_log("PROCESANDO " . count($paymentsParaProcesar) . " PAYMENT(S)");
                error_log("═══════════════════════════════════════");
                
                foreach ($paymentsParaProcesar as $idx => $paymentInfo) {
                    error_log("--- Payment " . ($idx + 1) . " de " . count($paymentsParaProcesar) . " ---");
                    error_log("Datos del payment: " . json_encode($paymentInfo));
                    
                    $paymentId = isset($paymentInfo['id']) ? $paymentInfo['id'] : null;
                    
                    if (!$paymentId) {
                        error_log("⚠️ Payment sin ID válido, saltando...");
                        continue;
                    }
                    
                    error_log("✅ Payment ID válido: $paymentId");
                    
                    // Verificar si este payment_id ya fue procesado
                    if (ControladorMercadoPago::ctrVerificarPagoProcesado($paymentId)) {
                        error_log("⚠️ Payment $paymentId ya fue procesado anteriormente, saltando...");
                        continue;
                    }
                    
                    error_log("Consultando payment completo desde MP API...");
                    // Consultar el pago completo desde MP para obtener todos los datos
                    $paymentUrl = "https://api.mercadopago.com/v1/payments/$paymentId";
                    $ch = curl_init($paymentUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Authorization: Bearer ' . $credenciales['access_token'],
                        'Content-Type: application/json'
                    ));
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                    
                    $paymentResponse = curl_exec($ch);
                    $paymentHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError = curl_error($ch);
                    curl_close($ch);
                    
                    if ($curlError) {
                        error_log("❌ ERROR cURL: $curlError");
                    }
                    
                    error_log("Respuesta HTTP: $paymentHttpCode");
                    
                    if ($paymentHttpCode == 200) {
                        $payment = json_decode($paymentResponse, true);
                        
                        if ($payment && is_array($payment)) {
                            error_log("✅✅✅ Payment obtenido correctamente desde MP ✅✅✅");
                            error_log("   Payment ID: " . (isset($payment['id']) ? $payment['id'] : 'N/A'));
                            error_log("   Status: " . (isset($payment['status']) ? $payment['status'] : 'N/A'));
                            error_log("   Transaction Amount: " . (isset($payment['transaction_amount']) ? $payment['transaction_amount'] : 'N/A'));
                            
                            // Si la orden tiene external_reference, agregarlo al payment si no lo tiene
                            if ($order && isset($order['external_reference']) && !isset($payment['external_reference'])) {
                                $payment['external_reference'] = $order['external_reference'];
                                error_log("   External Reference agregado desde orden: " . $order['external_reference']);
                            }
                            // Si viene del JSON, usar el external_reference del JSON
                            if ($input && isset($inputData['data']['external_reference']) && !isset($payment['external_reference'])) {
                                $payment['external_reference'] = $inputData['data']['external_reference'];
                                error_log("   External Reference agregado desde JSON: " . $inputData['data']['external_reference']);
                            }
                            
                            error_log("   External Reference FINAL: " . (isset($payment['external_reference']) ? $payment['external_reference'] : 'NO'));
                            break; // Procesar solo el primer payment aprobado
                        } else {
                            error_log("❌ Payment decodificado pero no es array válido");
                        }
                    } else {
                        error_log("❌ No se pudo obtener payment $paymentId (HTTP $paymentHttpCode)");
                        error_log("   Respuesta: " . substr($paymentResponse, 0, 200));
                    }
                }
            }
            
            // Si no se encontró ningún payment válido para procesar
            if (!isset($payment) || !is_array($payment)) {
                error_log("⚠️ No se encontró ningún payment válido para procesar");
                if ($webhookId) {
                    ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                }
                exitOk('Orden sin payments válidos para procesar', false);
            }
        } else {
            // Si es payment directo, consultar el pago normalmente
            error_log("═══════════════════════════════════════");
            error_log("CONSULTANDO PAGO DIRECTO EN API DE MERCADOPAGO");
            error_log("Payment ID: $id");
            error_log("═══════════════════════════════════════");
            
            $url = "https://api.mercadopago.com/v1/payments/$id";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $credenciales['access_token'],
                'Content-Type: application/json'
            ));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                error_log("❌ ERROR cURL al consultar pago: $curlError");
            }
            
            error_log("Respuesta de MP (HTTP $httpCode): " . substr($response, 0, 500) . (strlen($response) > 500 ? '...' : ''));
            
            if ($httpCode == 200) {
                $payment = json_decode($response, true);
                
                if (!$payment || !is_array($payment)) {
                    error_log("❌ ERROR: No se pudo decodificar la respuesta de MercadoPago");
                    if ($webhookId) {
                        ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                    }
                    exitOk('Error al decodificar respuesta de MercadoPago', false);
                }
                
                error_log("✅ Pago obtenido correctamente de MercadoPago");
                error_log("   - Payment ID: " . (isset($payment['id']) ? $payment['id'] : 'N/A'));
                error_log("   - Status: " . (isset($payment['status']) ? $payment['status'] : 'N/A'));
                error_log("   - External Reference: " . (isset($payment['external_reference']) ? $payment['external_reference'] : 'N/A'));
            } else {
                error_log("❌ ERROR: No se pudo consultar el pago en MP (HTTP $httpCode)");
                error_log("Respuesta: " . substr($response, 0, 500));
                if ($webhookId) {
                    ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                }
                exitOk('Error al consultar pago en MercadoPago', false);
            }
        }
        
        // Continuar con el procesamiento del payment (código común para ambos casos)
        if (isset($payment) && is_array($payment)) {

            // Procesar TODOS los estados de pago (approved, pending, rejected, cancelled, refunded, etc.)
            // Esto permite tener un registro completo de todos los pagos
            $estadoPago = isset($payment['status']) ? $payment['status'] : 'unknown';
            error_log("Pago recibido con estado: $estadoPago");
            
            // Solo procesar pagos aprobados para cuenta corriente y desbloqueo
            // Pero registrar TODOS los estados en mercadopago_pagos
            if ($estadoPago === 'approved') {

                error_log("Pago aprobado, procesando...");
                error_log("Datos completos del pago: " . json_encode($payment));

                // Obtener ID del cliente desde los metadatos o external_reference
                $idClienteMoon = null;

                // Método 1: external_reference (puede ser numérico o string con formato "ID-otro")
                if (isset($payment['external_reference']) && !empty($payment['external_reference'])) {
                    $externalRef = $payment['external_reference'];
                    // Si es numérico directo
                    if (is_numeric($externalRef)) {
                        $idClienteMoon = intval($externalRef);
                    } 
                    // Si tiene formato "ID-otro", extraer el ID
                    elseif (preg_match('/^(\d+)/', $externalRef, $matches)) {
                        $idClienteMoon = intval($matches[1]);
                    }
                    error_log("ID Cliente desde external_reference: $idClienteMoon (original: $externalRef)");
                }
                
                // Método 2: metadata
                if (!$idClienteMoon && isset($payment['metadata']['id_cliente_moon'])) {
                    $idClienteMoon = intval($payment['metadata']['id_cliente_moon']);
                    error_log("ID Cliente desde metadata: $idClienteMoon");
                }
                
                // Método 3: Si es merchant_order, buscar en la orden
                if (!$idClienteMoon && $topic === 'merchant_order' && isset($order)) {
                    if (isset($order['external_reference']) && is_numeric($order['external_reference'])) {
                        $idClienteMoon = intval($order['external_reference']);
                        error_log("ID Cliente desde merchant_order external_reference: $idClienteMoon");
                    }
                }

                // Método 4: Para pagos con QR (formato venta_pos_TIMESTAMP_MONTO), intentar buscar en la orden
                // Los pagos con QR pueden tener el external_reference en la orden, no en el payment
                if (!$idClienteMoon && isset($payment['order']) && isset($payment['order']['id'])) {
                    $orderIdFromPayment = $payment['order']['id'];
                    error_log("Intentando obtener orden desde payment.order.id: $orderIdFromPayment");
                    
                    $orderUrl = "https://api.mercadopago.com/merchant_orders/$orderIdFromPayment";
                    $chOrder = curl_init($orderUrl);
                    curl_setopt($chOrder, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($chOrder, CURLOPT_HTTPHEADER, array(
                        'Authorization: Bearer ' . $credenciales['access_token'],
                        'Content-Type: application/json'
                    ));
                    $orderResponse = curl_exec($chOrder);
                    $orderHttpCode = curl_getinfo($chOrder, CURLINFO_HTTP_CODE);
                    curl_close($chOrder);
                    
                    if ($orderHttpCode == 200) {
                        $orderFromPayment = json_decode($orderResponse, true);
                        if (isset($orderFromPayment['external_reference']) && is_numeric($orderFromPayment['external_reference'])) {
                            $idClienteMoon = intval($orderFromPayment['external_reference']);
                            error_log("ID Cliente desde orden obtenida desde payment.order.id: $idClienteMoon");
                        }
                    }
                }

                // Método 5: Para pagos QR con formato venta_pos_TIMESTAMP_MONTO, buscar en intentos pendientes recientes
                // Si el external_reference tiene formato venta_pos_*, buscar intentos recientes con el mismo monto
                if (!$idClienteMoon && isset($payment['external_reference']) && strpos($payment['external_reference'], 'venta_pos_') === 0) {
                    // Extraer monto del external_reference si es posible
                    $montoDelPago = isset($payment['transaction_amount']) ? floatval($payment['transaction_amount']) : 0;
                    
                    if ($montoDelPago > 0) {
                        error_log("🔍🔍🔍 Buscando cliente en intentos recientes para pago QR con monto: $montoDelPago 🔍🔍🔍");
                        
                        try {
                            $conexion = Conexion::conectarMoon();
                            if ($conexion) {
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
                                
                                if ($intentoEncontrado && isset($intentoEncontrado['id_cliente_moon']) && $intentoEncontrado['id_cliente_moon'] > 0) {
                                    $idClienteMoon = intval($intentoEncontrado['id_cliente_moon']);
                                    error_log("✅✅✅ ID Cliente encontrado desde intento reciente: $idClienteMoon (monto: $montoDelPago) ✅✅✅");
                                } else {
                                    error_log("⚠️ No se encontró intento reciente con monto $montoDelPago");
                                }
                            }
                        } catch (Exception $e) {
                            error_log("ERROR al buscar cliente en intentos: " . $e->getMessage());
                        }
                    }
                }
                
                // Método 6: Fallback - buscar en description o title
                if (!$idClienteMoon) {
                    // Intentar extraer ID de description si tiene formato conocido
                    if (isset($payment['description'])) {
                        if (preg_match('/cliente[_\s]*(\d+)/i', $payment['description'], $matches)) {
                            $idClienteMoon = intval($matches[1]);
                            error_log("ID Cliente desde description: $idClienteMoon");
                        }
                    }
                }

                error_log("ID Cliente Moon FINAL: " . ($idClienteMoon ?: 'NO ENCONTRADO'));
                
                // CRÍTICO: Si no encontramos el cliente pero hay un external_reference con formato venta_pos_,
                // intentar buscar en intentos pendientes recientes con el mismo monto
                // Esto es para pagos QR desde el sistema de cobro donde el external_reference no tiene el ID
                if (!$idClienteMoon && isset($payment['external_reference']) && strpos($payment['external_reference'], 'venta_pos_') === 0) {
                    $montoDelPago = isset($payment['transaction_amount']) ? floatval($payment['transaction_amount']) : 0;
                    
                    if ($montoDelPago > 0) {
                        error_log("⚠️ CRÍTICO: Pago QR sin cliente encontrado. Buscando en intentos recientes con monto: $montoDelPago");
                        
                        try {
                            if (class_exists('Conexion')) {
                                $conexion = Conexion::conectarMoon();
                                if ($conexion) {
                                    // Buscar intentos pendientes recientes (últimos 30 minutos) con el mismo monto
                                    $stmtBuscarIntento = $conexion->prepare("SELECT id_cliente_moon FROM mercadopago_intentos 
                                        WHERE ABS(monto - :monto) < 0.01
                                        AND estado = 'pendiente' 
                                        AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                                        ORDER BY fecha_creacion DESC
                                        LIMIT 1");
                                    $stmtBuscarIntento->bindParam(":monto", $montoDelPago, PDO::PARAM_STR);
                                    $stmtBuscarIntento->execute();
                                    $intentoEncontrado = $stmtBuscarIntento->fetch();
                                    $stmtBuscarIntento->closeCursor();
                                    
                                    if ($intentoEncontrado && isset($intentoEncontrado['id_cliente_moon']) && $intentoEncontrado['id_cliente_moon'] > 0) {
                                        $idClienteMoon = intval($intentoEncontrado['id_cliente_moon']);
                                        error_log("✅✅✅ ID Cliente encontrado desde intento reciente: $idClienteMoon (monto: $montoDelPago) ✅✅✅");
                                    } else {
                                        error_log("⚠️ No se encontró intento reciente con monto $montoDelPago");
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            error_log("ERROR al buscar cliente en intentos: " . $e->getMessage());
                        }
                    }
                }
                
                error_log("ID Cliente Moon DESPUÉS DE BÚSQUEDA EN INTENTOS: " . ($idClienteMoon ?: 'NO ENCONTRADO (será 0 para pagos QR/ventas POS sin cliente)'));

                // IMPORTANTE: Procesar TODOS los pagos aprobados, incluso si no tienen id_cliente_moon
                // Los pagos con QR o ventas POS pueden no tener cliente asociado (id_cliente_moon = 0)
                $idClienteMoonFinal = ($idClienteMoon && $idClienteMoon > 0) ? $idClienteMoon : 0;
                
                if (true) { // Procesar siempre, incluso sin cliente

                    // Registrar el pago en nuestra base de datos
                    // Manejar fecha_approved: si existe y es válida, usarla; sino, usar fecha actual
                    $fechaPago = date('Y-m-d H:i:s');
                    if (isset($payment['date_approved']) && !empty($payment['date_approved'])) {
                        $fechaAprobada = strtotime($payment['date_approved']);
                        if ($fechaAprobada !== false) {
                            $fechaPago = date('Y-m-d H:i:s', $fechaAprobada);
                        }
                    }
                    
                    // Capturar información completa del tipo y método de pago
                    $paymentType = isset($payment['payment_type_id']) ? $payment['payment_type_id'] : null;
                    $paymentMethodId = isset($payment['payment_method_id']) ? $payment['payment_method_id'] : null;
                    
                    // Log detallado del tipo de pago para debugging
                    error_log("Tipo de pago detectado: payment_type_id=$paymentType, payment_method_id=$paymentMethodId");
                    error_log("Tipos de pago soportados: credit_card, debit_card, ticket, account_money, bank_transfer, atm, etc.");
                    
                    $datosPago = array(
                        'id_cliente_moon' => $idClienteMoonFinal,
                        'payment_id' => $payment['id'],
                        'preference_id' => isset($payment['preference_id']) ? $payment['preference_id'] : null,
                        'monto' => isset($payment['transaction_amount']) ? $payment['transaction_amount'] : 0,
                        'estado' => $payment['status'],
                        'fecha_pago' => $fechaPago,
                        'payment_type' => $paymentType,
                        'payment_method_id' => $paymentMethodId ?: 'desconocido',
                        'datos_json' => json_encode($payment)
                    );

                    // PASO 1: Registrar el pago en mercadopago_pagos (TABLA DE COBROS)
                    error_log("═══════════════════════════════════════");
                    error_log("PASO 1: REGISTRANDO PAGO EN mercadopago_pagos");
                    error_log("Payment ID: " . $payment['id']);
                    error_log("Cliente Moon: $idClienteMoonFinal" . ($idClienteMoonFinal == 0 ? " (pago QR/venta POS sin cliente)" : ""));
                    error_log("Monto: " . (isset($payment['transaction_amount']) ? $payment['transaction_amount'] : 'N/A'));
                    error_log("Estado: " . $payment['status']);
                    error_log("Tipo de pago: " . ($paymentType ?: 'N/A'));
                    error_log("Método de pago: " . ($paymentMethodId ?: 'N/A'));
                    error_log("External Reference: " . (isset($payment['external_reference']) ? $payment['external_reference'] : 'N/A'));
                    error_log("═══════════════════════════════════════");
                    
                    $resultadoPago = ControladorMercadoPago::ctrRegistrarPagoConfirmado($datosPago);
                    error_log("Resultado registro en mercadopago_pagos: " . (is_array($resultadoPago) ? json_encode($resultadoPago) : $resultadoPago));

                    // Validar que el registro fue exitoso
                    if ($resultadoPago === "ok") {
                        error_log("✅✅✅ PAGO REGISTRADO CORRECTAMENTE EN mercadopago_pagos ✅✅✅");
                        error_log("   - Payment ID: " . $payment['id']);
                        error_log("   - Cliente: $idClienteMoonFinal" . ($idClienteMoonFinal == 0 ? " (pago QR/venta POS)" : ""));
                        error_log("   - Monto: " . $payment['transaction_amount']);
                        error_log("   - Fecha: " . $datosPago['fecha_pago']);

                        // PASO 2: Registrar el pago en la cuenta corriente del cliente (SOLO si tiene cliente válido)
                        if ($idClienteMoonFinal > 0) {
                            error_log("═══════════════════════════════════════");
                            error_log("PASO 2: REGISTRANDO EN CUENTA CORRIENTE");
                            error_log("═══════════════════════════════════════");
                            
                            $resultadoCtaCte = ControladorSistemaCobro::ctrRegistrarMovimientoCuentaCorriente(
                                $idClienteMoonFinal,
                                $payment['transaction_amount']
                            );
                            error_log("Resultado cuenta corriente: " . (is_array($resultadoCtaCte) ? json_encode($resultadoCtaCte) : $resultadoCtaCte));

                            if ($resultadoCtaCte === "ok") {
                                error_log("✅✅✅ MOVIMIENTO DE CUENTA CORRIENTE REGISTRADO ✅✅✅");
                                error_log("   - Cliente: $idClienteMoonFinal");
                                error_log("   - Monto: " . $payment['transaction_amount']);
                                error_log("   - Tipo: PAGO (1)");

                                // PASO 3: Desbloquear cliente si estaba bloqueado
                                error_log("═══════════════════════════════════════");
                                error_log("PASO 3: VERIFICANDO BLOQUEO DE CLIENTE");
                                error_log("═══════════════════════════════════════");
                                
                                // Obtener estado actual del cliente
                                $clienteActual = ControladorSistemaCobro::ctrMostrarClientesCobro($idClienteMoonFinal);
                                $estadoBloqueoActual = isset($clienteActual['estado_bloqueo']) ? intval($clienteActual['estado_bloqueo']) : 0;
                                
                                if ($estadoBloqueoActual == 1) {
                                    $resultadoDesbloqueo = ControladorSistemaCobro::ctrActualizarClientesCobro($idClienteMoonFinal, 0);
                                    if ($resultadoDesbloqueo !== false) {
                                        error_log("✅✅✅ CLIENTE DESBLOQUEADO CORRECTAMENTE ✅✅✅");
                                        error_log("   - Cliente: $idClienteMoonFinal");
                                        error_log("   - Estado anterior: BLOQUEADO (1)");
                                        error_log("   - Estado nuevo: DESBLOQUEADO (0)");
                                    } else {
                                        error_log("❌ ERROR al desbloquear cliente $idClienteMoonFinal");
                                    }
                                } else {
                                    error_log("ℹ️ Cliente $idClienteMoonFinal no estaba bloqueado (estado: $estadoBloqueoActual)");
                                }
                            } else {
                                error_log("❌❌❌ ERROR CRÍTICO al registrar en cuenta corriente ❌❌❌");
                                error_log("   - Cliente: $idClienteMoonFinal");
                                error_log("   - Monto: " . $payment['transaction_amount']);
                                error_log("   - Error: " . (is_array($resultadoCtaCte) ? json_encode($resultadoCtaCte) : $resultadoCtaCte));
                                error_log("   ⚠️ El pago SÍ se registró en mercadopago_pagos, pero NO en cuenta corriente");
                            }
                        } else {
                            error_log("ℹ️ Pago sin cliente asociado (QR/venta POS) - No se registra en cuenta corriente");
                        }
                        
                        // PASO 4: Actualizar estado del intento si existe (por preference_id o order_id)
                        error_log("═══════════════════════════════════════");
                        error_log("PASO 4: ACTUALIZANDO ESTADO DE INTENTO");
                        error_log("═══════════════════════════════════════");
                        
                        $preferenceIdParaIntento = isset($payment['preference_id']) && !empty($payment['preference_id']) ? $payment['preference_id'] : null;
                        $orderIdParaIntento = null;
                        
                        // Para pagos con QR (modelo atendido), obtener order_id
                        if (!$preferenceIdParaIntento) {
                            if (isset($payment['order']) && isset($payment['order']['id'])) {
                                $orderIdParaIntento = $payment['order']['id'];
                            } elseif ($topic === 'merchant_order' && isset($order) && isset($order['id'])) {
                                $orderIdParaIntento = $order['id'];
                            }
                        }
                        
                        if ($preferenceIdParaIntento || $orderIdParaIntento) {
                            $resultadoIntento = ModeloMercadoPago::mdlActualizarEstadoIntento($preferenceIdParaIntento, 'aprobado', $orderIdParaIntento);
                            if ($resultadoIntento === "ok") {
                                error_log("✅ Estado de intento actualizado a 'aprobado' (preference_id: " . ($preferenceIdParaIntento ?: 'N/A') . ", order_id: " . ($orderIdParaIntento ?: 'N/A') . ")");
                            } else {
                                error_log("⚠️ No se pudo actualizar estado de intento (puede que no exista)");
                            }
                        } else {
                            error_log("ℹ️ No hay preference_id ni order_id para actualizar intento");
                        }
                        
                        error_log("═══════════════════════════════════════");
                        error_log("✅✅✅ PROCESO COMPLETO EXITOSO ✅✅✅");
                        error_log("═══════════════════════════════════════");
                        
                    } else {
                        error_log("❌❌❌ ERROR CRÍTICO al registrar pago en mercadopago_pagos ❌❌❌");
                        error_log("   - Payment ID: " . $payment['id']);
                        error_log("   - Cliente: $idClienteMoonFinal");
                        error_log("   - Error: " . (is_array($resultadoPago) ? json_encode($resultadoPago) : $resultadoPago));
                        error_log("   ⚠️ El pago NO se registró. Revisar conexión a BD Moon o datos del pago");
                        // NO continuar si no se pudo registrar en mercadopago_pagos
                        // PERO marcar el webhook como procesado para evitar reintentos infinitos
                    }

                    // Marcar webhook como procesado SIEMPRE (incluso si hubo errores)
                    // Esto evita que MercadoPago siga reenviando el mismo webhook
                    if ($webhookId) {
                        ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                        error_log("✅ Webhook marcado como procesado (ID: $webhookId)");
                    }

                    if ($resultadoPago === "ok") {
                        error_log("✅ Pago procesado exitosamente");
                        echo json_encode(['error' => false, 'message' => 'Pago procesado exitosamente']);
                    } else {
                        error_log("⚠️ Pago procesado con errores, pero webhook marcado como procesado");
                        echo json_encode(['error' => true, 'message' => 'Error al registrar pago, pero webhook procesado']);
                    }

                } 
                // NOTA: Este bloque ya no es necesario porque ahora procesamos todos los pagos arriba
                // Se mantiene solo por compatibilidad, pero nunca debería ejecutarse

            } else {
                // Registrar pagos con otros estados (pending, rejected, cancelled, etc.)
                // Esto permite tener un registro completo de todos los intentos de pago
                error_log("═══════════════════════════════════════");
                error_log("PAGO CON ESTADO: $estadoPago (NO APROBADO)");
                error_log("Registrando en BD sin procesar cuenta corriente");
                error_log("═══════════════════════════════════════");
                
                // Obtener ID del cliente (mismo proceso que para approved)
                $idClienteMoon = null;
                
                if (isset($payment['external_reference']) && !empty($payment['external_reference'])) {
                    $externalRef = $payment['external_reference'];
                    if (is_numeric($externalRef)) {
                        $idClienteMoon = intval($externalRef);
                    } elseif (preg_match('/^(\d+)/', $externalRef, $matches)) {
                        $idClienteMoon = intval($matches[1]);
                    }
                }
                
                if (!$idClienteMoon && isset($payment['metadata']['id_cliente_moon'])) {
                    $idClienteMoon = intval($payment['metadata']['id_cliente_moon']);
                }
                
                // Para pagos QR con formato venta_pos_TIMESTAMP_MONTO, buscar en intentos recientes
                if (!$idClienteMoon && isset($payment['external_reference']) && strpos($payment['external_reference'], 'venta_pos_') === 0) {
                    $montoDelPago = isset($payment['transaction_amount']) ? floatval($payment['transaction_amount']) : 0;
                    
                    if ($montoDelPago > 0) {
                        error_log("Buscando cliente en intentos recientes para pago QR (estado: $estadoPago) con monto: $montoDelPago");
                        
                        try {
                            if (class_exists('Conexion')) {
                                $conexion = Conexion::conectarMoon();
                                if ($conexion) {
                                    $stmtBuscarIntento = $conexion->prepare("SELECT id_cliente_moon FROM mercadopago_intentos 
                                        WHERE ABS(monto - :monto) < 0.01
                                        AND estado = 'pendiente' 
                                        AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                                        ORDER BY fecha_creacion DESC
                                        LIMIT 1");
                                    $stmtBuscarIntento->bindParam(":monto", $montoDelPago, PDO::PARAM_STR);
                                    $stmtBuscarIntento->execute();
                                    $intentoEncontrado = $stmtBuscarIntento->fetch();
                                    $stmtBuscarIntento->closeCursor();
                                    
                                    if ($intentoEncontrado && isset($intentoEncontrado['id_cliente_moon']) && $intentoEncontrado['id_cliente_moon'] > 0) {
                                        $idClienteMoon = intval($intentoEncontrado['id_cliente_moon']);
                                        error_log("✅ ID Cliente encontrado desde intento reciente (estado: $estadoPago): $idClienteMoon");
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            error_log("ERROR al buscar cliente en intentos (estado: $estadoPago): " . $e->getMessage());
                        }
                    }
                }
                
                $idClienteMoonFinal = ($idClienteMoon && $idClienteMoon > 0) ? $idClienteMoon : 0;
                
                // Registrar el pago con su estado actual (aunque no esté aprobado)
                $fechaPago = date('Y-m-d H:i:s');
                if (isset($payment['date_approved']) && !empty($payment['date_approved'])) {
                    $fechaAprobada = strtotime($payment['date_approved']);
                    if ($fechaAprobada !== false) {
                        $fechaPago = date('Y-m-d H:i:s', $fechaAprobada);
                    }
                } elseif (isset($payment['date_created']) && !empty($payment['date_created'])) {
                    $fechaCreada = strtotime($payment['date_created']);
                    if ($fechaCreada !== false) {
                        $fechaPago = date('Y-m-d H:i:s', $fechaCreada);
                    }
                }
                
                $datosPago = array(
                    'id_cliente_moon' => $idClienteMoonFinal,
                    'payment_id' => $payment['id'],
                    'preference_id' => isset($payment['preference_id']) ? $payment['preference_id'] : null,
                    'monto' => isset($payment['transaction_amount']) ? $payment['transaction_amount'] : 0,
                    'estado' => $estadoPago,
                    'fecha_pago' => $fechaPago,
                    'payment_type' => isset($payment['payment_type_id']) ? $payment['payment_type_id'] : null,
                    'payment_method_id' => isset($payment['payment_method_id']) ? $payment['payment_method_id'] : null,
                    'datos_json' => json_encode($payment)
                );
                
                $resultadoPago = ControladorMercadoPago::ctrRegistrarPagoConfirmado($datosPago);
                if ($resultadoPago === "ok") {
                    error_log("✅ Pago registrado con estado '$estadoPago' (no aprobado, solo registro)");
                    
                    // Actualizar estado del intento si existe
                    $preferenceIdParaIntento = isset($payment['preference_id']) && !empty($payment['preference_id']) ? $payment['preference_id'] : null;
                    $orderIdParaIntento = null;
                    
                    if (!$preferenceIdParaIntento && isset($payment['order']) && isset($payment['order']['id'])) {
                        $orderIdParaIntento = $payment['order']['id'];
                    } elseif ($topic === 'merchant_order' && isset($order) && isset($order['id'])) {
                        $orderIdParaIntento = $order['id'];
                    }
                    
                    if ($preferenceIdParaIntento || $orderIdParaIntento) {
                        $estadoIntento = ($estadoPago === 'approved') ? 'aprobado' : (($estadoPago === 'rejected' || $estadoPago === 'cancelled') ? 'rechazado' : 'pendiente');
                        ModeloMercadoPago::mdlActualizarEstadoIntento($preferenceIdParaIntento, $estadoIntento, $orderIdParaIntento);
                    }
                }

                // Marcar webhook como procesado
                if ($webhookId) {
                    ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                }

                echo json_encode(['error' => false, 'message' => "Pago registrado con estado: $estadoPago"]);
            }

        } else {
            error_log("ERROR: No se pudo consultar el pago en MP (HTTP $httpCode)");
            error_log("Respuesta: " . substr($response, 0, 500));
            
            // Marcar webhook como procesado incluso si hay error (para evitar reintentos infinitos)
            if ($webhookId) {
                ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                error_log("✅ Webhook marcado como procesado a pesar del error (ID: $webhookId)");
            }
            
            echo json_encode(['error' => true, 'message' => 'Error al consultar pago']);
        }

    } else {
        error_log("Topic no es payment, se ignora");

        // Marcar webhook como procesado
        if ($webhookId) {
            ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
        }

        echo json_encode(['error' => false, 'message' => 'Topic no procesado']);
    }

} catch (Exception $e) {
    error_log("EXCEPCIÓN en webhook: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Marcar webhook como procesado incluso si hay excepción (para evitar reintentos infinitos)
    if (isset($webhookId) && $webhookId) {
        try {
            ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
            error_log("✅ Webhook marcado como procesado a pesar de excepción (ID: $webhookId)");
        } catch (Exception $e2) {
            error_log("ERROR al marcar webhook como procesado: " . $e2->getMessage());
        }
    }
    
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}

error_log("=== FIN WEBHOOK ===");
