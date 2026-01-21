<?php
/**
 * DIAGNÓSTICO DE WEBHOOK
 * 
 * 1. Sube este archivo a tu servidor
 * 2. Configura este archivo como webhook TEMPORAL en MercadoPago
 * 3. Haz un pago QR de prueba
 * 4. Descarga el archivo 'diagnostico_webhook.log' y revísalo
 */

// Configuración
$logFile = __DIR__ . '/diagnostico_webhook.log';
$maxLogSize = 5 * 1024 * 1024; // 5MB

// Limpiar log si es muy grande
if (file_exists($logFile) && filesize($logFile) > $maxLogSize) {
    unlink($logFile);
}

// Función de logging
function log_diagnostico($tipo, $mensaje, $datos = null) {
    global $logFile;
    
    $timestamp = date('Y-m-d H:i:s');
    $separador = str_repeat('=', 80);
    
    $log = "\n$separador\n";
    $log .= "[$timestamp] [$tipo]\n";
    $log .= "$separador\n";
    $log .= "$mensaje\n";
    
    if ($datos !== null) {
        $log .= "\nDATOS:\n";
        $log .= print_r($datos, true);
    }
    
    $log .= "\n$separador\n\n";
    
    file_put_contents($logFile, $log, FILE_APPEND);
}

// Iniciar diagnóstico
log_diagnostico('INICIO', 'Webhook recibido');

// 1. INFORMACIÓN DEL REQUEST
$requestInfo = [
    'Método' => $_SERVER['REQUEST_METHOD'],
    'Protocolo' => $_SERVER['SERVER_PROTOCOL'] ?? 'N/A',
    'IP Remota' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
    'User Agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
    'Content-Type' => $_SERVER['CONTENT_TYPE'] ?? 'N/A',
    'Request URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'Timestamp' => date('Y-m-d H:i:s'),
];

log_diagnostico('REQUEST INFO', 'Información del request', $requestInfo);

// 2. HEADERS
$headers = function_exists('getallheaders') ? getallheaders() : [];
log_diagnostico('HEADERS', 'Headers recibidos', $headers);

// 3. PARÁMETROS GET
if (!empty($_GET)) {
    log_diagnostico('GET PARAMS', 'Parámetros GET', $_GET);
} else {
    log_diagnostico('GET PARAMS', 'No hay parámetros GET');
}

// 4. PARÁMETROS POST
if (!empty($_POST)) {
    log_diagnostico('POST PARAMS', 'Parámetros POST', $_POST);
} else {
    log_diagnostico('POST PARAMS', 'No hay parámetros POST');
}

// 5. RAW INPUT
$input = file_get_contents('php://input');
if (!empty($input)) {
    log_diagnostico('RAW INPUT', 'Contenido RAW (primeros 2000 chars)', substr($input, 0, 2000));
    
    // Intentar decodificar JSON
    $json = json_decode($input, true);
    if ($json !== null) {
        log_diagnostico('JSON DECODIFICADO', 'JSON parseado correctamente', $json);
        
        // Extraer campos importantes
        $camposImportantes = [
            'action' => $json['action'] ?? 'N/A',
            'type' => $json['type'] ?? 'N/A',
            'data.id' => $json['data']['id'] ?? 'N/A',
            'data.external_reference' => $json['data']['external_reference'] ?? 'N/A',
            'data.transactions.payments' => isset($json['data']['transactions']['payments']) ? count($json['data']['transactions']['payments']) : 0,
            'live_mode' => $json['live_mode'] ?? 'N/A',
        ];
        
        log_diagnostico('CAMPOS CLAVE', 'Campos importantes del webhook', $camposImportantes);
        
        // Detectar tipo de webhook
        $tipo = 'DESCONOCIDO';
        if (isset($json['action']) && strpos($json['action'], 'order') !== false) {
            $tipo = '✅ PAGO QR (order.processed)';
        } elseif (isset($json['type'])) {
            if ($json['type'] === 'payment') {
                $tipo = '✅ PAGO DIRECTO (Botón/Link)';
            } elseif ($json['type'] === 'merchant_order' || $json['type'] === 'order') {
                $tipo = '✅ PAGO QR (Merchant Order)';
            }
        }
        
        log_diagnostico('TIPO DETECTADO', $tipo);
        
        // Si es QR, extraer información del payment
        if ($tipo === '✅ PAGO QR (order.processed)' || $tipo === '✅ PAGO QR (Merchant Order)') {
            if (isset($json['data']['transactions']['payments'][0])) {
                $paymentInfo = $json['data']['transactions']['payments'][0];
                log_diagnostico('PAYMENT INFO', 'Información del payment en JSON', [
                    'payment_id' => $paymentInfo['id'] ?? 'N/A',
                    'amount' => $paymentInfo['amount'] ?? 'N/A',
                    'status' => $paymentInfo['status'] ?? 'N/A',
                ]);
            }
        }
        
    } else {
        log_diagnostico('JSON ERROR', 'No se pudo decodificar el JSON. Error: ' . json_last_error_msg());
    }
} else {
    log_diagnostico('RAW INPUT', 'No hay contenido en php://input (puede ser un GET de prueba)');
}

// 6. VARIABLES DE ENTORNO
$envInfo = [
    'PHP Version' => phpversion(),
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    'Script Filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'N/A',
    'HTTPS' => isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'NO',
];

log_diagnostico('ENTORNO', 'Información del servidor', $envInfo);

// 7. VERIFICAR ACCESO A ARCHIVOS CRÍTICOS
$archivosVerificar = [
    __DIR__ . '/config.php',
    __DIR__ . '/controladores/mercadopago.controlador.php',
    __DIR__ . '/modelos/mercadopago.modelo.php',
    __DIR__ . '/modelos/conexion.php',
];

$archivosStatus = [];
foreach ($archivosVerificar as $archivo) {
    $archivosStatus[basename($archivo)] = file_exists($archivo) ? '✅ EXISTE' : '❌ NO EXISTE';
}

log_diagnostico('ARCHIVOS', 'Verificación de archivos necesarios', $archivosStatus);

// 8. TEST DE CONEXIÓN A BD (si existe)
if (file_exists(__DIR__ . '/modelos/conexion.php')) {
    try {
        require_once __DIR__ . '/modelos/conexion.php';
        
        if (class_exists('Conexion')) {
            $pdo = Conexion::conectarMoon();
            if ($pdo) {
                log_diagnostico('BASE DE DATOS', '✅ Conexión exitosa');
            } else {
                log_diagnostico('BASE DE DATOS', '❌ No se pudo conectar');
            }
        }
    } catch (Exception $e) {
        log_diagnostico('BASE DE DATOS', '❌ Error: ' . $e->getMessage());
    }
}

// 9. RESUMEN Y RECOMENDACIONES
$resumen = "\n";
$resumen .= "DIAGNÓSTICO COMPLETADO\n";
$resumen .= "======================\n\n";

// Verificar si es un webhook válido de MP
$esWebhookValido = false;
$tipoWebhook = 'DESCONOCIDO';

if (!empty($input)) {
    $json = json_decode($input, true);
    if ($json) {
        // Verificar formato order.processed
        if (isset($json['action']) && isset($json['data']['id'])) {
            $esWebhookValido = true;
            $tipoWebhook = 'order.processed (QR)';
        }
        // Verificar formato tradicional
        elseif (isset($json['type']) && isset($json['data']['id'])) {
            $esWebhookValido = true;
            $tipoWebhook = $json['type'];
        }
        // Verificar formato GET
        elseif (!empty($_GET['topic']) && !empty($_GET['id'])) {
            $esWebhookValido = true;
            $tipoWebhook = $_GET['topic'];
        }
    }
}

if ($esWebhookValido) {
    $resumen .= "✅ ES UN WEBHOOK VÁLIDO DE MERCADOPAGO\n";
    $resumen .= "   Tipo: $tipoWebhook\n\n";
    
    if ($tipoWebhook === 'merchant_order' || $tipoWebhook === 'order' || $tipoWebhook === 'order.processed (QR)') {
        $resumen .= "✅ ES UN PAGO CON QR (merchant_order)\n";
        $resumen .= "   Esto significa que la configuración de eventos está correcta.\n\n";
        
        // Verificar external_reference
        if (!empty($input)) {
            $json = json_decode($input, true);
            if ($json && isset($json['data']['external_reference'])) {
                $extRef = $json['data']['external_reference'];
                if (is_numeric($extRef)) {
                    $resumen .= "✅ External Reference contiene ID de cliente: $extRef\n";
                } else {
                    $resumen .= "⚠️ External Reference NO es numérico: $extRef\n";
                    $resumen .= "   El webhook buscará el cliente por monto en intentos recientes\n";
                }
            } else {
                $resumen .= "⚠️ NO hay external_reference en el JSON\n";
                $resumen .= "   El webhook buscará el cliente por monto en intentos recientes\n";
            }
        }
    } else {
        $resumen .= "ℹ️ Es un pago tipo: $tipoWebhook\n\n";
    }
    
    $resumen .= "\nSIGUIENTE PASO:\n";
    $resumen .= "1. Revisa que tu webhook-mercadopago.php procese este tipo de evento\n";
    $resumen .= "2. Verifica que registre el pago en la base de datos\n";
    $resumen .= "3. Si todo funciona, restaura el webhook original\n";
    
} else {
    if (empty($input) && empty($_GET)) {
        $resumen .= "ℹ️ WEBHOOK DE PRUEBA (Sin contenido)\n";
        $resumen .= "   Esto es normal cuando MP verifica que la URL funcione.\n\n";
    } else {
        $resumen .= "⚠️ NO PARECE SER UN WEBHOOK VÁLIDO\n";
        $resumen .= "   El formato no coincide con los webhooks de MercadoPago.\n\n";
    }
    
    $resumen .= "\nSIGUIENTE PASO:\n";
    $resumen .= "1. Haz un pago QR de prueba\n";
    $resumen .= "2. Espera 1-2 minutos\n";
    $resumen .= "3. Descarga y revisa este archivo de log\n";
}

log_diagnostico('RESUMEN', $resumen);

// 10. RESPONDER SIEMPRE 200 OK
http_response_code(200);
header('Content-Type: application/json');

echo json_encode([
    'status' => 'ok',
    'message' => 'Webhook de diagnóstico - Log guardado',
    'timestamp' => date('Y-m-d H:i:s'),
    'log_file' => basename($logFile),
    'webhook_valido' => $esWebhookValido,
    'tipo' => $tipoWebhook
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Logging final
log_diagnostico('FIN', 'Diagnóstico completado - Respuesta 200 OK enviada');
