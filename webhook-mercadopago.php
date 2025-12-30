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

    // Registrar el webhook en la base de datos (solo si las clases están disponibles)
    $webhookId = null;
    
    if (class_exists('ControladorMercadoPago')) {
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
        error_log("Webhook registrado con ID: $webhookId");
    } else {
        error_log("ADVERTENCIA: ControladorMercadoPago no disponible, webhook no se registrará en BD");
    }

    // Procesar si es un pago o una orden (modelo atendido)
    if ($topic === 'payment' || $topic === 'merchant_order') {

        error_log("Procesando pago con ID: $id");

        // Verificar si ya fue procesado (solo si las clases están disponibles)
        // IMPORTANTE: Verificar por payment_id, no por order_id
        $paymentIdParaVerificar = $id; // Por defecto usar el ID recibido
        
        if (class_exists('ControladorMercadoPago')) {
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
                    }

                    echo json_encode(['error' => false, 'message' => 'Pago ya procesado']);
                    exit;
                }
            }
        }

        // Obtener credenciales
        if (!class_exists('ControladorMercadoPago')) {
            error_log("ERROR: ControladorMercadoPago no disponible");
            echo json_encode(['error' => true, 'message' => 'Controlador no disponible']);
            exit;
        }
        
        $credenciales = ControladorMercadoPago::ctrObtenerCredenciales();

        // Consultar el pago en la API de MercadoPago
        $url = "https://api.mercadopago.com/v1/payments/$id";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $credenciales['access_token'],
            'Content-Type: application/json'
        ));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        error_log("Respuesta de MP (HTTP $httpCode): " . $response);

        if ($httpCode == 200) {
            $payment = json_decode($response, true);

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
                                if (class_exists('ControladorMercadoPago') && ControladorMercadoPago::ctrVerificarPagoProcesado($paymentId)) {
                                    error_log("⚠️ Payment $paymentId ya fue procesado anteriormente");
                                    
                                    // Marcar webhook como procesado
                                    if ($webhookId) {
                                        ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                                    }
                                    
                                    echo json_encode(['error' => false, 'message' => 'Pago ya procesado']);
                                    exit;
                                }
                            } else {
                                error_log("ERROR: No se pudo obtener el pago de la orden (HTTP $paymentHttpCode)");
                                echo json_encode(['error' => false, 'message' => 'Orden procesada pero no se pudo obtener el pago']);
                                exit;
                            }
                        } else {
                            error_log("ERROR: La orden no tiene pagos asociados");
                            echo json_encode(['error' => false, 'message' => 'Orden sin pagos']);
                            exit;
                        }
                    } else {
                        error_log("Orden con estado: " . (isset($order['status']) ? $order['status'] : 'unknown') . " - No se procesa");
                        echo json_encode(['error' => false, 'message' => 'Orden no cerrada']);
                        exit;
                    }
                } else {
                    error_log("ERROR: No se pudo consultar la orden (HTTP $orderHttpCode)");
                    echo json_encode(['error' => true, 'message' => 'Error al consultar orden']);
                    exit;
                }
            }

            // Solo procesar si el pago está aprobado
            if (isset($payment['status']) && $payment['status'] === 'approved') {

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

                // Método 4: Fallback - buscar en description o title
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

                if ($idClienteMoon && $idClienteMoon > 0) {

                    // Registrar el pago en nuestra base de datos
                    // Manejar fecha_approved: si existe y es válida, usarla; sino, usar fecha actual
                    $fechaPago = date('Y-m-d H:i:s');
                    if (isset($payment['date_approved']) && !empty($payment['date_approved'])) {
                        $fechaAprobada = strtotime($payment['date_approved']);
                        if ($fechaAprobada !== false) {
                            $fechaPago = date('Y-m-d H:i:s', $fechaAprobada);
                        }
                    }
                    
                    $datosPago = array(
                        'id_cliente_moon' => $idClienteMoon,
                        'payment_id' => $payment['id'],
                        'preference_id' => isset($payment['preference_id']) ? $payment['preference_id'] : null,
                        'monto' => $payment['transaction_amount'],
                        'estado' => $payment['status'],
                        'fecha_pago' => $fechaPago,
                        'payment_type' => isset($payment['payment_type_id']) ? $payment['payment_type_id'] : null,
                        'payment_method_id' => isset($payment['payment_method_id']) ? $payment['payment_method_id'] : null,
                        'datos_json' => json_encode($payment)
                    );

                    // PASO 1: Registrar el pago en mercadopago_pagos (TABLA DE COBROS)
                    error_log("═══════════════════════════════════════");
                    error_log("PASO 1: REGISTRANDO PAGO EN mercadopago_pagos");
                    error_log("Payment ID: " . $payment['id']);
                    error_log("Cliente Moon: $idClienteMoon");
                    error_log("Monto: " . $payment['transaction_amount']);
                    error_log("Estado: " . $payment['status']);
                    error_log("═══════════════════════════════════════");
                    
                    $resultadoPago = ControladorMercadoPago::ctrRegistrarPagoConfirmado($datosPago);
                    error_log("Resultado registro en mercadopago_pagos: " . (is_array($resultadoPago) ? json_encode($resultadoPago) : $resultadoPago));

                    // Validar que el registro fue exitoso
                    if ($resultadoPago === "ok") {
                        error_log("✅✅✅ PAGO REGISTRADO CORRECTAMENTE EN mercadopago_pagos ✅✅✅");
                        error_log("   - Payment ID: " . $payment['id']);
                        error_log("   - Cliente: $idClienteMoon");
                        error_log("   - Monto: " . $payment['transaction_amount']);
                        error_log("   - Fecha: " . $datosPago['fecha_pago']);

                        // PASO 2: Registrar el pago en la cuenta corriente del cliente
                        error_log("═══════════════════════════════════════");
                        error_log("PASO 2: REGISTRANDO EN CUENTA CORRIENTE");
                        error_log("═══════════════════════════════════════");
                        
                        $resultadoCtaCte = ControladorSistemaCobro::ctrRegistrarMovimientoCuentaCorriente(
                            $idClienteMoon,
                            $payment['transaction_amount']
                        );
                        error_log("Resultado cuenta corriente: " . (is_array($resultadoCtaCte) ? json_encode($resultadoCtaCte) : $resultadoCtaCte));

                        if ($resultadoCtaCte === "ok") {
                            error_log("✅✅✅ MOVIMIENTO DE CUENTA CORRIENTE REGISTRADO ✅✅✅");
                            error_log("   - Cliente: $idClienteMoon");
                            error_log("   - Monto: " . $payment['transaction_amount']);
                            error_log("   - Tipo: PAGO (1)");

                            // PASO 3: Desbloquear cliente si estaba bloqueado
                            error_log("═══════════════════════════════════════");
                            error_log("PASO 3: VERIFICANDO BLOQUEO DE CLIENTE");
                            error_log("═══════════════════════════════════════");
                            
                            // Obtener estado actual del cliente
                            $clienteActual = ControladorSistemaCobro::ctrMostrarClientesCobro($idClienteMoon);
                            $estadoBloqueoActual = isset($clienteActual['estado_bloqueo']) ? intval($clienteActual['estado_bloqueo']) : 0;
                            
                            if ($estadoBloqueoActual == 1) {
                                $resultadoDesbloqueo = ControladorSistemaCobro::ctrActualizarClientesCobro($idClienteMoon, 0);
                                if ($resultadoDesbloqueo !== false) {
                                    error_log("✅✅✅ CLIENTE DESBLOQUEADO CORRECTAMENTE ✅✅✅");
                                    error_log("   - Cliente: $idClienteMoon");
                                    error_log("   - Estado anterior: BLOQUEADO (1)");
                                    error_log("   - Estado nuevo: DESBLOQUEADO (0)");
                                } else {
                                    error_log("❌ ERROR al desbloquear cliente $idClienteMoon");
                                }
                            } else {
                                error_log("ℹ️ Cliente $idClienteMoon no estaba bloqueado (estado: $estadoBloqueoActual)");
                            }
                            
                            // PASO 4: Actualizar estado del intento si existe
                            if (isset($payment['preference_id']) && !empty($payment['preference_id'])) {
                                error_log("═══════════════════════════════════════");
                                error_log("PASO 4: ACTUALIZANDO ESTADO DE INTENTO");
                                error_log("═══════════════════════════════════════");
                                
                                $resultadoIntento = ModeloMercadoPago::mdlActualizarEstadoIntento($payment['preference_id'], 'aprobado');
                                if ($resultadoIntento === "ok") {
                                    error_log("✅ Estado de intento actualizado a 'aprobado'");
                                } else {
                                    error_log("⚠️ No se pudo actualizar estado de intento (puede que no exista)");
                                }
                            }
                            
                            error_log("═══════════════════════════════════════");
                            error_log("✅✅✅ PROCESO COMPLETO EXITOSO ✅✅✅");
                            error_log("═══════════════════════════════════════");
                            
                        } else {
                            error_log("❌❌❌ ERROR CRÍTICO al registrar en cuenta corriente ❌❌❌");
                            error_log("   - Cliente: $idClienteMoon");
                            error_log("   - Monto: " . $payment['transaction_amount']);
                            error_log("   - Error: " . (is_array($resultadoCtaCte) ? json_encode($resultadoCtaCte) : $resultadoCtaCte));
                            error_log("   ⚠️ El pago SÍ se registró en mercadopago_pagos, pero NO en cuenta corriente");
                        }
                    } else {
                        error_log("❌❌❌ ERROR CRÍTICO al registrar pago en mercadopago_pagos ❌❌❌");
                        error_log("   - Payment ID: " . $payment['id']);
                        error_log("   - Cliente: $idClienteMoon");
                        error_log("   - Error: " . (is_array($resultadoPago) ? json_encode($resultadoPago) : $resultadoPago));
                        error_log("   ⚠️ El pago NO se registró. Revisar conexión a BD Moon o datos del pago");
                        // NO continuar si no se pudo registrar en mercadopago_pagos
                    }

                    // Actualizar estado del intento si existe
                    if (isset($payment['preference_id'])) {
                        ModeloMercadoPago::mdlActualizarEstadoIntento($payment['preference_id'], 'aprobado');
                    }

                    // Marcar webhook como procesado
                    if ($webhookId) {
                        ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                    }

                    error_log("✅ Pago procesado exitosamente");
                    echo json_encode(['error' => false, 'message' => 'Pago procesado exitosamente']);

                } else {
                    error_log("❌ ERROR CRÍTICO: No se pudo obtener ID del cliente Moon");
                    error_log("Payment data: " . json_encode($payment));
                    error_log("External reference: " . (isset($payment['external_reference']) ? $payment['external_reference'] : 'NO DEFINIDO'));
                    error_log("Metadata: " . (isset($payment['metadata']) ? json_encode($payment['metadata']) : 'NO DEFINIDO'));
                    
                    // Intentar registrar el pago sin cliente (para auditoría)
                    // Esto permite ver qué pagos no se pudieron asociar
                    try {
                        $datosPagoSinCliente = array(
                            'id_cliente_moon' => 0, // 0 = cliente desconocido
                            'payment_id' => $payment['id'],
                            'preference_id' => isset($payment['preference_id']) ? $payment['preference_id'] : null,
                            'monto' => $payment['transaction_amount'],
                            'estado' => $payment['status'],
                            'fecha_pago' => date('Y-m-d H:i:s', strtotime($payment['date_approved'])),
                            'payment_type' => $payment['payment_type_id'],
                            'payment_method_id' => $payment['payment_method_id'],
                            'datos_json' => json_encode($payment)
                        );
                        $resultadoAuditoria = ControladorMercadoPago::ctrRegistrarPagoConfirmado($datosPagoSinCliente);
                        error_log("Pago registrado para auditoría (cliente 0): " . (is_array($resultadoAuditoria) ? json_encode($resultadoAuditoria) : $resultadoAuditoria));
                    } catch (Exception $e) {
                        error_log("Error al registrar pago para auditoría: " . $e->getMessage());
                    }
                    
                    echo json_encode(['error' => true, 'message' => 'No se pudo obtener ID del cliente. Pago registrado para auditoría.']);
                }

            } else {
                error_log("Pago con estado: " . $payment['status'] . " - No se procesa");

                // Marcar webhook como procesado
                if ($webhookId) {
                    ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
                }

                echo json_encode(['error' => false, 'message' => 'Pago no aprobado']);
            }

        } else {
            error_log("ERROR: No se pudo consultar el pago en MP");
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
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}

error_log("=== FIN WEBHOOK ===");
