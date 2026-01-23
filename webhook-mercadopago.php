<?php
/**
 * Webhook Mercado Pago (cPanel/PHP 7.4+)
 * - Logging robusto sin /tmp
 * - Soporte payment / merchant_order / order / point_integration / wallet_connect
 * - Responde 200 OK rapido y procesa luego
 */

declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);
date_default_timezone_set('America/Argentina/Mendoza');

// ----------------------
// Configuracion
// ----------------------
$CFG = [
    'log_dir' => getenv('MP_WEBHOOK_LOG_DIR') ?: '',
    'debug_enabled' => (getenv('MP_WEBHOOK_DEBUG') === '1'),
    'webhook_secret' => getenv('MP_WEBHOOK_SECRET') ?: '',
    'ignore_test_ids' => (getenv('MP_IGNORE_TEST_IDS') === '1'),
    'max_body_bytes' => (int)(getenv('MP_MAX_BODY_BYTES') ?: 1048576),
    'rate_limit_per_min' => (int)(getenv('MP_RATE_LIMIT_PER_MIN') ?: 0),
];

// ----------------------
// Utilidades
// ----------------------
function get_headers_compat(): array {
    if (function_exists('getallheaders')) {
        return getallheaders();
    }
    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
            $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
            $headers[$header] = $value;
        }
    }
    return $headers;
}

function resolve_log_dir(string $preferred): string {
    $candidates = [];
    if ($preferred !== '') {
        $candidates[] = $preferred;
    }
    $home = getenv('HOME');
    if ($home) {
        $candidates[] = rtrim($home, '/').'/logs/mercadopago';
    }
    $candidates[] = __DIR__.'/logs';

    foreach ($candidates as $dir) {
        if (@is_dir($dir) || @mkdir($dir, 0750, true)) {
            if (@is_writable($dir)) {
                // Si es un dir dentro de public_html, protegemos con .htaccess
                if (strpos($dir, __DIR__) === 0) {
                    $htaccess = rtrim($dir, '/').'/.htaccess';
                    if (!file_exists($htaccess)) {
                        @file_put_contents($htaccess, "Deny from all\n", LOCK_EX);
                    }
                }
                return rtrim($dir, '/');
            }
        }
    }
    return '';
}

function redact_headers(array $headers): array {
    $out = [];
    foreach ($headers as $k => $v) {
        $lk = strtolower($k);
        if ($lk === 'authorization' || $lk === 'x-access-token') {
            $out[$k] = '[REDACTED]';
        } else {
            $out[$k] = $v;
        }
    }
    return $out;
}

function redact_payload($data) {
    if (is_array($data)) {
        $redacted = [];
        foreach ($data as $k => $v) {
            $lk = strtolower((string)$k);
            if (in_array($lk, ['access_token', 'token', 'authorization', 'card_number', 'security_code', 'cvv'], true)) {
                $redacted[$k] = '[REDACTED]';
            } else {
                $redacted[$k] = redact_payload($v);
            }
        }
        return $redacted;
    }
    return $data;
}

function safe_json($data): string {
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function log_event(string $level, string $message, array $context = []): void {
    global $LOG_FILE;
    $line = safe_json([
        'ts' => date('Y-m-d H:i:s'),
        'level' => $level,
        'message' => $message,
        'context' => $context,
    ])."\n";
    if ($LOG_FILE) {
        @file_put_contents($LOG_FILE, $line, FILE_APPEND | LOCK_EX);
    } else {
        error_log($line);
    }
}

function next_request_id(string $logDir, string $fallback): string {
    $seqFile = $logDir ? ($logDir.'/request_id.seq') : '';
    if ($seqFile && is_writable(dirname($seqFile))) {
        $fp = @fopen($seqFile, 'c+');
        if ($fp) {
            if (flock($fp, LOCK_EX)) {
                $current = (int)trim((string)stream_get_contents($fp));
                $current++;
                ftruncate($fp, 0);
                rewind($fp);
                fwrite($fp, (string)$current);
                fflush($fp);
                flock($fp, LOCK_UN);
                fclose($fp);
                return 'req-'.$current.'-'.$fallback;
            }
            fclose($fp);
        }
    }
    return 'req-'.$fallback;
}

function send_ok_once(array $payload): void {
    static $sent = false;
    if ($sent) {
        return;
    }
    http_response_code(200);
    header('Content-Type: application/json');
    echo safe_json($payload);
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } else {
        @ob_flush();
        @flush();
    }
    $sent = true;
}

function rate_limit_hit(string $logDir, string $ip, int $limitPerMin): bool {
    if ($limitPerMin <= 0 || $logDir === '') {
        return false;
    }
    $file = $logDir.'/rate_limit.json';
    $nowBucket = date('Y-m-d H:i');
    $data = [];
    $fp = @fopen($file, 'c+');
    if (!$fp) {
        return false;
    }
    if (flock($fp, LOCK_EX)) {
        $raw = stream_get_contents($fp);
        if ($raw) {
            $data = json_decode($raw, true) ?: [];
        }
        if (!isset($data[$nowBucket])) {
            $data = [$nowBucket => []];
        }
        $data[$nowBucket][$ip] = ($data[$nowBucket][$ip] ?? 0) + 1;
        $count = $data[$nowBucket][$ip];
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, safe_json($data));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return $count > $limitPerMin;
    }
    fclose($fp);
    return false;
}

function validate_signature(array $headers, ?string $dataId, string $secret): bool {
    if (!$secret) {
        return true;
    }
    $xSignature = $headers['X-Signature'] ?? $headers['x-signature'] ?? '';
    $xRequestId = $headers['X-Request-Id'] ?? $headers['x-request-id'] ?? '';
    if (!$xSignature || !$xRequestId || !$dataId) {
        return true;
    }
    $parts = explode(',', $xSignature);
    $ts = '';
    $hash = '';
    foreach ($parts as $part) {
        $kv = explode('=', trim($part), 2);
        if (count($kv) === 2) {
            if ($kv[0] === 'ts') {
                $ts = $kv[1];
            } elseif ($kv[0] === 'v1') {
                $hash = $kv[1];
            }
        }
    }
    if ($ts === '' || $hash === '') {
        return true;
    }
    $manifest = 'id:'.strtolower($dataId).';request-id:'.$xRequestId.';ts:'.$ts.';';
    $calc = hash_hmac('sha256', $manifest, $secret);
    return hash_equals($calc, $hash);
}

function consultar_mp(string $url, string $accessToken, int $timeout = 30): ?array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer '.$accessToken,
            'Content-Type: application/json',
        ],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        log_event('ERROR', 'cURL error', ['url' => $url, 'error' => $err]);
        return null;
    }
    if ($httpCode !== 200) {
        log_event('WARNING', 'HTTP no 200', ['url' => $url, 'code' => $httpCode, 'response' => substr((string)$response, 0, 200)]);
        return null;
    }
    $data = json_decode((string)$response, true);
    if (!$data) {
        log_event('ERROR', 'JSON invalido en respuesta MP', ['url' => $url]);
        return null;
    }
    return $data;
}

function find_client_id(array $payment, ?array $eventData, ?array $orderData): int {
    // 1) external_reference directo
    if (isset($payment['external_reference']) && is_numeric($payment['external_reference'])) {
        return (int)$payment['external_reference'];
    }
    // 2) external_reference con prefijo
    if (isset($payment['external_reference']) && preg_match('/^(\d+)/', (string)$payment['external_reference'], $m)) {
        return (int)$m[1];
    }
    // 3) metadata
    if (isset($payment['metadata']['id_cliente_moon'])) {
        return (int)$payment['metadata']['id_cliente_moon'];
    }
    // 4) external_reference en order
    if ($orderData && isset($orderData['external_reference']) && is_numeric($orderData['external_reference'])) {
        return (int)$orderData['external_reference'];
    }
    // 5) external_reference en payload de webhook
    if ($eventData && isset($eventData['data']['external_reference'])) {
        $ext = $eventData['data']['external_reference'];
        if (is_numeric($ext)) {
            return (int)$ext;
        }
        if (preg_match('/^(\d+)/', (string)$ext, $m)) {
            return (int)$m[1];
        }
    }
    return 0;
}

// ----------------------
// Inicio request
// ----------------------
$LOG_DIR = resolve_log_dir($CFG['log_dir']);
$LOG_FILE = $LOG_DIR ? ($LOG_DIR.'/webhook.log') : '';
$LAST_EVENT_FILE = $LOG_DIR ? ($LOG_DIR.'/last_event.json') : '';
$LOCK_DIR = $LOG_DIR ? ($LOG_DIR.'/locks') : '';
if ($LOCK_DIR && (!is_dir($LOCK_DIR))) {
    @mkdir($LOCK_DIR, 0750, true);
}

$headers = get_headers_compat();
$remoteIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$queryString = $_SERVER['QUERY_STRING'] ?? '';

$requestIdHeader = $headers['X-Request-Id'] ?? $headers['x-request-id'] ?? '';
$reqIdBase = $requestIdHeader ?: substr(uniqid('', true), 0, 12);
$requestId = next_request_id($LOG_DIR, $reqIdBase);

// Healthcheck
if ($method === 'GET' && empty($_GET)) {
    log_event('INFO', 'Healthcheck', ['request_id' => $requestId, 'ip' => $remoteIp]);
    send_ok_once(['ok' => true, 'status' => 'healthcheck', 'request_id' => $requestId]);
    exit;
}

// Debug endpoint
if ($method === 'GET' && isset($_GET['debug'])) {
    if (!$CFG['debug_enabled']) {
        send_ok_once(['ok' => false, 'error' => 'debug_disabled']);
        exit;
    }
    $last = [];
    if ($LAST_EVENT_FILE && file_exists($LAST_EVENT_FILE)) {
        $raw = @file_get_contents($LAST_EVENT_FILE);
        $last = $raw ? json_decode($raw, true) : [];
    }
    send_ok_once(['ok' => true, 'last_event' => $last]);
    exit;
}

// Leer body una sola vez
$rawInput = file_get_contents('php://input');
if ($CFG['max_body_bytes'] > 0 && strlen((string)$rawInput) > $CFG['max_body_bytes']) {
    log_event('WARNING', 'Body demasiado grande', ['request_id' => $requestId, 'size' => strlen((string)$rawInput)]);
    send_ok_once(['ok' => true, 'request_id' => $requestId]);
    exit;
}

$contentType = $headers['Content-Type'] ?? $headers['content-type'] ?? '';
if ($contentType && stripos($contentType, 'application/json') === false) {
    log_event('WARNING', 'Content-Type no JSON', ['request_id' => $requestId, 'content_type' => $contentType]);
}

$payload = $rawInput ? json_decode($rawInput, true) : null;
if ($rawInput && $payload === null && json_last_error() !== JSON_ERROR_NONE) {
    log_event('WARNING', 'JSON invalido en request', ['request_id' => $requestId, 'json_error' => json_last_error_msg()]);
}

// Log inicial de request
$logContext = [
    'request_id' => $requestId,
    'method' => $method,
    'uri' => $requestUri,
    'querystring' => $queryString,
    'ip' => $remoteIp,
    'headers' => redact_headers($headers),
    'body' => redact_payload($payload ?? ['raw' => substr((string)$rawInput, 0, 2000)]),
];
log_event('INFO', 'Webhook recibido', $logContext);
if ($LAST_EVENT_FILE) {
    @file_put_contents($LAST_EVENT_FILE, safe_json($logContext), LOCK_EX);
}

// Rate limit basico
if (rate_limit_hit($LOG_DIR, $remoteIp, $CFG['rate_limit_per_min'])) {
    log_event('WARNING', 'Rate limit excedido', ['request_id' => $requestId, 'ip' => $remoteIp]);
    send_ok_once(['ok' => true, 'request_id' => $requestId]);
    exit;
}

// Responder OK rapido
send_ok_once(['ok' => true, 'request_id' => $requestId]);

// ----------------------
// Parseo de evento
// ----------------------
$topic = $_GET['topic'] ?? $_GET['type'] ?? ($payload['topic'] ?? ($payload['type'] ?? ''));
$action = $payload['action'] ?? '';
$dataId = $_GET['id'] ?? $_GET['data_id'] ?? $_GET['data.id'] ?? ($payload['data']['id'] ?? ($payload['data_id'] ?? ''));
if (!$dataId && !empty($_GET['resource'])) {
    if (preg_match('/(\\d+)/', (string)$_GET['resource'], $m)) {
        $dataId = $m[1];
    }
}

if ($action && stripos($action, 'order.') === 0) {
    $topic = 'order';
}
if ($topic === 'order') {
    $topic = 'merchant_order';
}

$normalizedTopic = $topic;
if (in_array($topic, ['point_integration', 'wallet_connect'], true)) {
    $normalizedTopic = 'payment';
}

log_event('INFO', 'Evento parseado', [
    'request_id' => $requestId,
    'topic' => $topic,
    'normalized_topic' => $normalizedTopic,
    'action' => $action,
    'data_id' => $dataId,
]);

if (!$dataId || !$normalizedTopic) {
    log_event('WARNING', 'Datos insuficientes', ['request_id' => $requestId]);
    exit(0);
}

// Validacion de firma (permisiva)
$signatureOk = validate_signature($headers, (string)$dataId, $CFG['webhook_secret']);
if (!$signatureOk) {
    log_event('WARNING', 'Firma no valida (permisivo)', ['request_id' => $requestId]);
}

// Ignorar IDs de prueba solo si se pide explicitamente
if ($CFG['ignore_test_ids'] && $normalizedTopic === 'payment' && (string)$dataId === '123456') {
    log_event('INFO', 'ID de prueba ignorado', ['request_id' => $requestId, 'data_id' => $dataId]);
    exit;
}

// Lock anti-duplicados
$lockPath = $LOCK_DIR ? ($LOCK_DIR.'/'.sha1($normalizedTopic.'|'.$dataId).'.lock') : '';
$lockFp = null;
if ($lockPath) {
    $lockFp = @fopen($lockPath, 'c+');
    if ($lockFp && !flock($lockFp, LOCK_EX | LOCK_NB)) {
        log_event('WARNING', 'Duplicado en proceso', ['request_id' => $requestId, 'data_id' => $dataId]);
        fclose($lockFp);
        exit;
    }
}

try {
    // Cargar dependencias (no fatal si faltan)
    try {
        if (file_exists(__DIR__.'/extensiones/vendor/autoload.php')) {
            require_once __DIR__.'/extensiones/vendor/autoload.php';
        }
        if (file_exists(__DIR__.'/config.php')) {
            require_once __DIR__.'/config.php';
        }
        if (file_exists(__DIR__.'/.env') && class_exists('Dotenv\\Dotenv')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
            $dotenv->load();
        }
        if (file_exists(__DIR__.'/helpers.php')) {
            require_once __DIR__.'/helpers.php';
        }
        if (file_exists(__DIR__.'/controladores/mercadopago.controlador.php')) {
            require_once __DIR__.'/controladores/mercadopago.controlador.php';
        }
        if (file_exists(__DIR__.'/controladores/sistema_cobro.controlador.php')) {
            require_once __DIR__.'/controladores/sistema_cobro.controlador.php';
        }
        if (file_exists(__DIR__.'/modelos/mercadopago.modelo.php')) {
            require_once __DIR__.'/modelos/mercadopago.modelo.php';
        }
        if (file_exists(__DIR__.'/modelos/sistema_cobro.modelo.php')) {
            require_once __DIR__.'/modelos/sistema_cobro.modelo.php';
        }
        if (file_exists(__DIR__.'/modelos/conexion.php')) {
            require_once __DIR__.'/modelos/conexion.php';
        }
    } catch (Exception $e) {
        log_event('ERROR', 'Error cargando dependencias', ['request_id' => $requestId, 'error' => $e->getMessage()]);
    }

    // Registrar webhook en BD si existe
    $webhookId = null;
    if (class_exists('ControladorMercadoPago')) {
        $webhookId = ControladorMercadoPago::ctrRegistrarWebhook([
            'topic' => $normalizedTopic,
            'resource_id' => $dataId,
            'datos_json' => $rawInput ?: safe_json(['get' => $_GET, 'post' => $_POST]),
            'fecha_recibido' => date('Y-m-d H:i:s'),
            'procesado' => 0,
        ]);
    }

    // Obtener credenciales
    $credenciales = class_exists('ControladorMercadoPago') ? ControladorMercadoPago::ctrObtenerCredenciales() : [];
    $accessToken = $credenciales['access_token'] ?? '';
    if (!$accessToken) {
        log_event('WARNING', 'Access token no disponible', ['request_id' => $requestId]);
        return;
    }

    $payment = null;
    $order = null;

    if ($normalizedTopic === 'merchant_order') {
        // Intentar /v1/orders
        $order = consultar_mp('https://api.mercadopago.com/v1/orders/'.$dataId, $accessToken);
        if (!$order) {
            $order = consultar_mp('https://api.mercadopago.com/merchant_orders/'.$dataId, $accessToken);
        }

        $payments = [];
        if ($payload && isset($payload['data']['transactions']['payments']) && is_array($payload['data']['transactions']['payments'])) {
            $payments = $payload['data']['transactions']['payments'];
        } elseif ($order && isset($order['transactions']['payments']) && is_array($order['transactions']['payments'])) {
            $payments = $order['transactions']['payments'];
        } elseif ($order && isset($order['payments']) && is_array($order['payments'])) {
            $payments = $order['payments'];
        }

        if (empty($payments)) {
            log_event('WARNING', 'Order sin payments', ['request_id' => $requestId, 'order_id' => $dataId]);
            if ($webhookId && class_exists('ModeloMercadoPago')) {
                ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
            }
            exit(0);
        }

        foreach ($payments as $p) {
            $pid = is_array($p) ? ($p['id'] ?? null) : $p;
            if (!$pid) {
                continue;
            }
            if (class_exists('ControladorMercadoPago') && ControladorMercadoPago::ctrVerificarPagoProcesado($pid)) {
                continue;
            }
            $payment = consultar_mp('https://api.mercadopago.com/v1/payments/'.$pid, $accessToken);
            if ($payment && isset($payment['status'])) {
                if (empty($payment['external_reference'])) {
                    if ($payload && isset($payload['data']['external_reference'])) {
                        $payment['external_reference'] = $payload['data']['external_reference'];
                    } elseif ($order && isset($order['external_reference'])) {
                        $payment['external_reference'] = $order['external_reference'];
                    }
                }
                break;
            }
        }
    } else {
        $payment = consultar_mp('https://api.mercadopago.com/v1/payments/'.$dataId, $accessToken);
    }

    if (!$payment || !isset($payment['status'])) {
        log_event('WARNING', 'Payment no disponible', ['request_id' => $requestId, 'data_id' => $dataId, 'topic' => $normalizedTopic]);
        if ($webhookId && class_exists('ModeloMercadoPago')) {
            ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
        }
        exit(0);
    }

    log_event('INFO', 'Payment obtenido', [
        'request_id' => $requestId,
        'payment_id' => $payment['id'] ?? null,
        'status' => $payment['status'] ?? null,
        'amount' => $payment['transaction_amount'] ?? null,
    ]);

    // Registrar pagos no aprobados
    if (($payment['status'] ?? '') !== 'approved') {
        if (class_exists('ControladorMercadoPago')) {
            $datosPago = [
                'id_cliente_moon' => 0,
                'payment_id' => $payment['id'],
                'preference_id' => $payment['preference_id'] ?? null,
                'monto' => $payment['transaction_amount'] ?? 0,
                'estado' => $payment['status'],
                'fecha_pago' => date('Y-m-d H:i:s'),
                'payment_type' => $payment['payment_type_id'] ?? null,
                'payment_method_id' => $payment['payment_method_id'] ?? 'desconocido',
                'datos_json' => safe_json($payment),
            ];
            ControladorMercadoPago::ctrRegistrarPagoConfirmado($datosPago);
        }
        if ($webhookId && class_exists('ModeloMercadoPago')) {
            ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
        }
        exit(0);
    }

    // Buscar cliente
    $idCliente = find_client_id($payment, $payload, $order);
    log_event('INFO', 'Cliente identificado', ['request_id' => $requestId, 'id_cliente' => $idCliente]);

    // Procesar BD si existe
    if (!class_exists('Conexion')) {
        log_event('WARNING', 'Conexion DB no disponible', ['request_id' => $requestId]);
        if ($webhookId && class_exists('ModeloMercadoPago')) {
            ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
        }
        exit(0);
    }

    $pdo = Conexion::conectarMoon();
    if (!$pdo) {
        log_event('WARNING', 'No se pudo conectar a BD', ['request_id' => $requestId]);
        if ($webhookId && class_exists('ModeloMercadoPago')) {
            ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
        }
        exit(0);
    }

    $pdo->beginTransaction();

    try {
        $fechaPago = date('Y-m-d H:i:s');
        if (!empty($payment['date_approved'])) {
            $ts = strtotime($payment['date_approved']);
            if ($ts !== false) {
                $fechaPago = date('Y-m-d H:i:s', $ts);
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
            'datos_json' => safe_json($payment),
        ];

        if (class_exists('ControladorMercadoPago')) {
            $res = ControladorMercadoPago::ctrRegistrarPagoConfirmado($datosPago);
            if ($res !== 'ok') {
                throw new Exception('Error registrando pago');
            }
        }

        if ($idCliente > 0 && class_exists('ControladorSistemaCobro')) {
            $resCta = ControladorSistemaCobro::ctrRegistrarMovimientoCuentaCorriente($idCliente, $datosPago['monto']);
            if ($resCta !== 'ok') {
                throw new Exception('Error cuenta corriente');
            }
            $cliente = ControladorSistemaCobro::ctrMostrarClientesCobro($idCliente);
            if ($cliente && isset($cliente['estado_bloqueo']) && (int)$cliente['estado_bloqueo'] === 1) {
                ControladorSistemaCobro::ctrActualizarClientesCobro($idCliente, 0);
            }
        }

        if (class_exists('ModeloMercadoPago')) {
            $preferenceId = $payment['preference_id'] ?? null;
            $orderId = ($normalizedTopic === 'merchant_order') ? $dataId : ($payment['order']['id'] ?? null);
            if ($preferenceId || $orderId) {
                ModeloMercadoPago::mdlActualizarEstadoIntento($preferenceId, 'aprobado', $orderId);
            }
            if ($webhookId) {
                ModeloMercadoPago::mdlMarcarWebhookProcesado($webhookId);
            }
        }

        $pdo->commit();
        log_event('SUCCESS', 'Pago procesado exitosamente', [
            'request_id' => $requestId,
            'payment_id' => $payment['id'] ?? null,
            'id_cliente' => $idCliente,
            'monto' => $datosPago['monto'] ?? 0
        ]);
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        log_event('ERROR', 'Error procesando BD', [
            'request_id' => $requestId, 
            'error' => $e->getMessage(),
            'payment_id' => $payment['id'] ?? null,
            'trace' => $e->getTraceAsString()
        ]);
    }
} finally {
    if ($lockFp) {
        @flock($lockFp, LOCK_UN);
        @fclose($lockFp);
        if ($lockPath) {
            @unlink($lockPath);
        }
    }
}
