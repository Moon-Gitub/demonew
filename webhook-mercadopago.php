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

// Manejo de errores para que no se muestren al exterior
ini_set('display_errors', 0);
error_reporting(E_ALL);

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
    // Continuar de todos modos, responder OK
}

// Configurar zona horaria
date_default_timezone_set('America/Argentina/Mendoza');

// Log para debugging con timestamp
$timestamp = date('Y-m-d H:i:s');
error_log("==========================================");
error_log("=== WEBHOOK MERCADOPAGO RECIBIDO ===");
error_log("Timestamp: $timestamp");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("GET params: " . json_encode($_GET));
error_log("POST params: " . json_encode($_POST));
error_log("Body raw: " . file_get_contents('php://input'));
error_log("Headers: " . json_encode(getallheaders()));
error_log("==========================================");

// Responder OK inmediatamente
header('HTTP/1.1 200 OK');
header('Content-Type: application/json');

// Si es una petición OPTIONS (preflight), responder y salir
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit;
}

// Aceptar tanto GET como POST
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use GET or POST']);
    exit;
}

// Si es un test de MercadoPago (GET sin parámetros), responder OK
if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($_GET['topic']) && empty($_GET['id'])) {
    http_response_code(200);
    echo json_encode(['status' => 'ok', 'message' => 'Webhook activo']);
    exit;
}

try {
    // Obtener parámetros del webhook
    $topic = isset($_GET['topic']) ? $_GET['topic'] : (isset($_POST['topic']) ? $_POST['topic'] : null);
    $id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : null);

    // Si viene por POST, intentar parsear el body
    if (!$topic && !$id) {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if ($data) {
            $topic = isset($data['topic']) ? $data['topic'] : (isset($data['type']) ? $data['type'] : null);
            $id = isset($data['id']) ? $data['id'] : (isset($data['data']['id']) ? $data['data']['id'] : null);
        }
    }

    error_log("Topic: $topic");
    error_log("ID: $id");

    // Validar que tengamos los datos necesarios
    if (!$topic || !$id) {
        error_log("ERROR: Faltan parámetros topic o id");
        echo json_encode(['error' => false, 'message' => 'Parámetros recibidos']);
        exit;
    }
    
    // CRÍTICO: Ignorar IDs de prueba o inválidos (como 123456 que es usado en simulaciones)
    // Los payment_ids reales de MercadoPago tienen un formato específico (números largos)
    if ($topic === 'payment' && (strlen($id) < 8 || $id === '123456' || !is_numeric($id))) {
        error_log("⚠️ Webhook ignorado: ID de prueba o inválido detectado (ID: $id, Topic: $topic)");
        error_log("   Los payment_ids reales de MercadoPago son números largos (mínimo 8 dígitos)");
        
        // Registrar webhook pero marcarlo como procesado para evitar reintentos
        if (class_exists('ModeloMercadoPago')) {
            $webhookId = ModeloMercadoPago::mdlRegistrarWebhook($topic, $id, json_encode(['ignored' => true, 'reason' => 'test_id']));
            if ($webhookId) {
                ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
            }
        }
        
        http_response_code(200);
        echo json_encode(['error' => false, 'message' => 'Webhook de prueba ignorado']);
        exit;
    }

    // Registrar el webhook en la base de datos (solo si las clases están disponibles)
    $webhookId = null;
    
    // Verificar que las clases estén disponibles ANTES de procesar
    if (!class_exists('ControladorMercadoPago')) {
        error_log("❌ ERROR CRÍTICO: ControladorMercadoPago no está disponible");
        error_log("   Verificar que el archivo existe: " . __DIR__ . '/controladores/mercadopago.controlador.php');
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Controlador no disponible']);
        exit;
    }
    
    if (!class_exists('ModeloMercadoPago')) {
        error_log("❌ ERROR CRÍTICO: ModeloMercadoPago no está disponible");
        error_log("   Verificar que el archivo existe: " . __DIR__ . '/modelos/mercadopago.modelo.php');
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Modelo no disponible']);
        exit;
    }
    
    if (!class_exists('ControladorSistemaCobro')) {
        error_log("❌ ERROR CRÍTICO: ControladorSistemaCobro no está disponible");
        error_log("   Verificar que el archivo existe: " . __DIR__ . '/controladores/sistema_cobro.controlador.php');
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'ControladorSistemaCobro no disponible']);
        exit;
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

        // Consultar el pago en la API de MercadoPago
        $url = "https://api.mercadopago.com/v1/payments/$id";
        
        error_log("═══════════════════════════════════════");
        error_log("CONSULTANDO PAGO EN API DE MERCADOPAGO");
        error_log("URL: $url");
        error_log("Payment ID: $id");
        error_log("Topic: $topic");
        error_log("═══════════════════════════════════════");

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $credenciales['access_token'],
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout de 30 segundos
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Timeout de conexión de 10 segundos

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
                http_response_code(500);
                echo json_encode(['error' => true, 'message' => 'Error al decodificar respuesta de MercadoPago']);
                exit;
            }
            
            error_log("✅ Pago obtenido correctamente de MercadoPago");
            error_log("   - Payment ID: " . (isset($payment['id']) ? $payment['id'] : 'N/A'));
            error_log("   - Status: " . (isset($payment['status']) ? $payment['status'] : 'N/A'));
            error_log("   - External Reference: " . (isset($payment['external_reference']) ? $payment['external_reference'] : 'N/A'));

            // Si es merchant_order, obtener el payment de la orden
            if ($topic === 'merchant_order') {
                error_log("Procesando merchant_order con ID: $id");
                
                // Consultar la orden
                $orderUrl = "https://api.mercadopago.com/merchant_orders/$id";
                $ch = curl_init($orderUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization: Bearer ' . $credenciales['access_token'],
                    'Content-Type: application/json'
                ));
                
                $orderResponse = curl_exec($ch);
                $orderHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($orderHttpCode == 200) {
                    $order = json_decode($orderResponse, true);
                    error_log("Orden obtenida: " . json_encode($order));
                    
                    // Verificar si la orden está cerrada (pagada)
                    if (isset($order['status']) && $order['status'] === 'closed') {
                        // Obtener el payment_id de la orden
                        if (isset($order['payments']) && count($order['payments']) > 0) {
                            $paymentId = $order['payments'][0]['id'];
                            error_log("Orden cerrada, obteniendo pago con ID: $paymentId");
                            
                            // Consultar el pago
                            $paymentUrl = "https://api.mercadopago.com/v1/payments/$paymentId";
                            $ch = curl_init($paymentUrl);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'Authorization: Bearer ' . $credenciales['access_token'],
                                'Content-Type: application/json'
                            ));
                            
                            $paymentResponse = curl_exec($ch);
                            $paymentHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);
                            
                            if ($paymentHttpCode == 200) {
                                $payment = json_decode($paymentResponse, true);
                                error_log("Pago obtenido de la orden: " . json_encode($payment));
                                
                                // Verificar si este payment_id ya fue procesado
                                if (ControladorMercadoPago::ctrVerificarPagoProcesado($paymentId)) {
                                    error_log("⚠️ Payment $paymentId ya fue procesado anteriormente");
                                    
                                    // Marcar webhook como procesado
                                    if ($webhookId) {
                                        ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                                        error_log("✅ Webhook marcado como procesado (pago duplicado desde merchant_order)");
                                    }
                                    
                                    echo json_encode(['error' => false, 'message' => 'Pago ya procesado']);
                                    exit;
                                }
                            } else {
                                error_log("ERROR: No se pudo obtener el pago de la orden (HTTP $paymentHttpCode)");
                                
                                // Marcar webhook como procesado
                                if ($webhookId) {
                                    ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                                }
                                
                                echo json_encode(['error' => false, 'message' => 'Orden procesada pero no se pudo obtener el pago']);
                                exit;
                            }
                        } else {
                            error_log("ERROR: La orden no tiene pagos asociados");
                            
                            // Marcar webhook como procesado
                            if ($webhookId) {
                                ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                            }
                            
                            echo json_encode(['error' => false, 'message' => 'Orden sin pagos']);
                            exit;
                        }
                    } else {
                        error_log("Orden con estado: " . (isset($order['status']) ? $order['status'] : 'unknown') . " - No se procesa");
                        
                        // Marcar webhook como procesado
                        if ($webhookId) {
                            ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                        }
                        
                        echo json_encode(['error' => false, 'message' => 'Orden no cerrada']);
                        exit;
                    }
                } else {
                    error_log("ERROR: No se pudo consultar la orden (HTTP $orderHttpCode)");
                    
                    // Marcar webhook como procesado
                    if ($webhookId) {
                        ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                    }
                    
                    echo json_encode(['error' => true, 'message' => 'Error al consultar orden']);
                    exit;
                }
            }

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
                        error_log("Buscando cliente en intentos recientes para pago QR con monto: $montoDelPago");
                        
                        try {
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
                                    error_log("✅ ID Cliente encontrado desde intento reciente: $idClienteMoon (monto: $montoDelPago)");
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
