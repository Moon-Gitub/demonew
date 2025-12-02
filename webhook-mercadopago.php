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

// Cargar vendor autoload y configuración
if (file_exists(__DIR__ . '/extensiones/vendor/autoload.php')) {
    require_once __DIR__ . '/extensiones/vendor/autoload.php';
}

// Cargar configuración
require_once __DIR__ . '/config.php';

// Cargar variables de entorno desde .env (si existe y si Dotenv está instalado)
if (file_exists(__DIR__ . '/.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Cargar dependencias
require_once "controladores/mercadopago.controlador.php";
require_once "controladores/sistema_cobro.controlador.php";
require_once "modelos/mercadopago.modelo.php";
require_once "modelos/sistema_cobro.modelo.php";

// Configurar zona horaria
date_default_timezone_set('America/Argentina/Mendoza');

// Log para debugging
error_log("=== WEBHOOK MERCADOPAGO RECIBIDO ===");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("GET params: " . json_encode($_GET));
error_log("POST params: " . json_encode($_POST));
error_log("Headers: " . json_encode(getallheaders()));

// Responder siempre 200 OK para que MP no reintente
http_response_code(200);

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

    // Registrar el webhook en la base de datos
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

    // Solo procesar si es un pago
    if ($topic === 'payment') {

        error_log("Procesando pago con ID: $id");

        // Verificar si ya fue procesado
        if (ControladorMercadoPago::ctrVerificarPagoProcesado($id)) {
            error_log("Pago $id ya fue procesado anteriormente");

            // Marcar webhook como procesado
            if ($webhookId) {
                ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
            }

            echo json_encode(['error' => false, 'message' => 'Pago ya procesado']);
            exit;
        }

        // Obtener credenciales
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

            // Solo procesar si el pago está aprobado
            if ($payment['status'] === 'approved') {

                error_log("Pago aprobado, procesando...");

                // Obtener ID del cliente desde los metadatos o external_reference
                $idClienteMoon = null;

                if (isset($payment['external_reference']) && is_numeric($payment['external_reference'])) {
                    $idClienteMoon = intval($payment['external_reference']);
                } elseif (isset($payment['metadata']['id_cliente_moon'])) {
                    $idClienteMoon = intval($payment['metadata']['id_cliente_moon']);
                }

                error_log("ID Cliente Moon: $idClienteMoon");

                if ($idClienteMoon) {

                    // Registrar el pago en nuestra base de datos
                    $datosPago = array(
                        'id_cliente_moon' => $idClienteMoon,
                        'payment_id' => $payment['id'],
                        'preference_id' => isset($payment['preference_id']) ? $payment['preference_id'] : null,
                        'monto' => $payment['transaction_amount'],
                        'estado' => $payment['status'],
                        'fecha_pago' => date('Y-m-d H:i:s', strtotime($payment['date_approved'])),
                        'payment_type' => $payment['payment_type_id'],
                        'payment_method_id' => $payment['payment_method_id'],
                        'datos_json' => json_encode($payment)
                    );

                    $resultadoPago = ControladorMercadoPago::ctrRegistrarPagoConfirmado($datosPago);
                    error_log("Resultado registro pago: $resultadoPago");

                    // Registrar el pago en la cuenta corriente del cliente
                    $resultadoCtaCte = ControladorSistemaCobro::ctrRegistrarMovimientoCuentaCorriente(
                        $idClienteMoon,
                        $payment['transaction_amount']
                    );
                    error_log("Resultado cuenta corriente: $resultadoCtaCte");

                    // Desbloquear cliente si estaba bloqueado
                    ControladorSistemaCobro::ctrActualizarClientesCobro($idClienteMoon, 0);
                    error_log("Cliente desbloqueado");

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
                    error_log("ERROR: No se pudo obtener ID del cliente");
                    echo json_encode(['error' => true, 'message' => 'No se pudo obtener ID del cliente']);
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
