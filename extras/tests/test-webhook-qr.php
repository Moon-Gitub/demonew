<?php
/**
 * SCRIPT DE PRUEBA PARA WEBHOOK QR
 * 
 * Simula el JSON que envía MercadoPago para pagos QR
 * y verifica que el webhook lo procese correctamente
 */

// Cargar autoload
require_once __DIR__ . '/extensiones/vendor/autoload.php';

// Cargar .env
if (file_exists(__DIR__ . '/.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Cargar clases necesarias
require_once __DIR__ . '/controladores/mercadopago.controlador.php';
require_once __DIR__ . '/modelos/mercadopago.modelo.php';
require_once __DIR__ . '/modelos/conexion.php';

echo "=== TEST WEBHOOK QR ===\n\n";

// Simular el JSON que envía MercadoPago (basado en el que mostraste)
$jsonSimulado = '{
    "action": "order.processed",
    "api_version": "v1",
    "application_id": "7101882075144875",
    "data": {
        "external_reference": "14",
        "id": "123456",
        "status": "processed",
        "status_detail": "accredited",
        "total_paid_amount": 100000,
        "transactions": {
            "payments": [
                {
                    "amount": 100000,
                    "id": "PAY01K7S9596QBWZRTY02NF",
                    "paid_amount": 100000,
                    "payment_method": {
                        "id": "visa",
                        "installments": 1,
                        "type": "credit_card"
                    },
                    "reference": {
                        "id": 1234567891
                    },
                    "status": "processed",
                    "status_detail": "accredited"
                }
            ]
        },
        "type": "point",
        "version": 3
    },
    "date_created": "2021-11-01T02:02:02-04:00",
    "live_mode": true,
    "type": "order",
    "user_id": 1188183100
}';

echo "1. JSON Simulado:\n";
echo $jsonSimulado . "\n\n";

// Parsear JSON
$data = json_decode($jsonSimulado, true);

echo "2. Datos extraídos del JSON:\n";
echo "   - Action: " . (isset($data['action']) ? $data['action'] : 'NO') . "\n";
echo "   - Type: " . (isset($data['type']) ? $data['type'] : 'NO') . "\n";
echo "   - Data ID: " . (isset($data['data']['id']) ? $data['data']['id'] : 'NO') . "\n";
echo "   - External Reference: " . (isset($data['data']['external_reference']) ? $data['data']['external_reference'] : 'NO') . "\n";
echo "   - Payments en transactions: " . (isset($data['data']['transactions']['payments']) ? count($data['data']['transactions']['payments']) : 0) . "\n\n";

// Simular lo que hace el webhook
$topic = null;
$id = null;
$action = null;

if (isset($data['action']) && isset($data['data']['id'])) {
    $action = $data['action'];
    if (strpos($action, 'order') !== false) {
        $topic = 'merchant_order';
    } else {
        $topic = isset($data['type']) ? $data['type'] : 'payment';
    }
    $id = $data['data']['id'];
}

if ($topic === 'order') {
    $topic = 'merchant_order';
}

echo "3. Procesamiento del webhook:\n";
echo "   - Topic detectado: $topic\n";
echo "   - ID detectado: $id\n";
echo "   - Action: $action\n\n";

// Verificar payments
if (isset($data['data']['transactions']['payments']) && is_array($data['data']['transactions']['payments'])) {
    echo "4. Payments encontrados en JSON:\n";
    foreach ($data['data']['transactions']['payments'] as $idx => $paymentInfo) {
        echo "   Payment " . ($idx + 1) . ":\n";
        echo "     - ID: " . (isset($paymentInfo['id']) ? $paymentInfo['id'] : 'NO') . "\n";
        echo "     - Amount: " . (isset($paymentInfo['amount']) ? $paymentInfo['amount'] : 'NO') . "\n";
        echo "     - Status: " . (isset($paymentInfo['status']) ? $paymentInfo['status'] : 'NO') . "\n";
    }
    echo "\n";
} else {
    echo "4. ⚠️ NO se encontraron payments en data.transactions.payments\n\n";
}

// Verificar cliente
$externalRef = isset($data['data']['external_reference']) ? $data['data']['external_reference'] : null;
$idClienteMoon = null;

if ($externalRef) {
    if (is_numeric($externalRef)) {
        $idClienteMoon = intval($externalRef);
        echo "5. Cliente detectado desde external_reference: $idClienteMoon\n";
    } else {
        echo "5. ⚠️ External reference no es numérico: $externalRef\n";
        echo "   Buscaría en intentos recientes por monto...\n";
    }
} else {
    echo "5. ⚠️ NO hay external_reference en el JSON\n";
}

echo "\n=== RESUMEN ===\n";
echo "✅ Topic: $topic\n";
echo "✅ ID Orden: $id\n";
echo "✅ Payments encontrados: " . (isset($data['data']['transactions']['payments']) ? count($data['data']['transactions']['payments']) : 0) . "\n";
echo ($idClienteMoon ? "✅ Cliente: $idClienteMoon\n" : "⚠️ Cliente: NO ENCONTRADO (buscaría en intentos)\n");
echo "\n";

// Verificar si hay intentos recientes para este cliente
if ($idClienteMoon) {
    try {
        $conexion = Conexion::conectarMoon();
        if ($conexion) {
            $stmt = $conexion->prepare("SELECT id, preference_id, monto, fecha_creacion FROM mercadopago_intentos 
                WHERE id_cliente_moon = :id_cliente 
                AND estado = 'pendiente' 
                AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)
                ORDER BY fecha_creacion DESC
                LIMIT 5");
            $stmt->bindParam(":id_cliente", $idClienteMoon, PDO::PARAM_INT);
            $stmt->execute();
            $intentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($intentos) {
                echo "6. Intentos pendientes encontrados para cliente $idClienteMoon:\n";
                foreach ($intentos as $intento) {
                    echo "   - ID: " . $intento['id'] . ", Monto: " . $intento['monto'] . ", Fecha: " . $intento['fecha_creacion'] . "\n";
                }
            } else {
                echo "6. ⚠️ NO hay intentos pendientes recientes para cliente $idClienteMoon\n";
            }
        }
    } catch (Exception $e) {
        echo "6. ❌ Error consultando intentos: " . $e->getMessage() . "\n";
    }
}

echo "\n=== FIN TEST ===\n";
