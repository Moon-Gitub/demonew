<?php
/**
 * VERIFICAR CONFIGURACIÓN DEL WEBHOOK
 * 
 * Este script verifica:
 * 1. Si el webhook está accesible
 * 2. Si está recibiendo notificaciones
 * 3. Si está procesando correctamente
 */

require_once __DIR__ . '/extensiones/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

require_once __DIR__ . '/controladores/mercadopago.controlador.php';
require_once __DIR__ . '/modelos/mercadopago.modelo.php';
require_once __DIR__ . '/modelos/conexion.php';

echo "=== VERIFICACIÓN DE WEBHOOK ===\n\n";

// 1. Verificar credenciales
echo "1. Credenciales MercadoPago:\n";
$credenciales = ControladorMercadoPago::ctrObtenerCredenciales();
if ($credenciales && isset($credenciales['access_token'])) {
    echo "   ✅ Access Token: " . substr($credenciales['access_token'], 0, 20) . "...\n";
} else {
    echo "   ❌ NO hay access_token configurado\n";
}
echo "\n";

// 2. Verificar webhooks recibidos
echo "2. Webhooks recibidos (últimos 10):\n";
try {
    $conexion = Conexion::conectarMoon();
    if ($conexion) {
        $stmt = $conexion->prepare("SELECT id, topic, resource_id, fecha_recibido, procesado 
            FROM mercadopago_webhooks 
            ORDER BY fecha_recibido DESC 
            LIMIT 10");
        $stmt->execute();
        $webhooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($webhooks) {
            foreach ($webhooks as $wh) {
                $procesado = $wh['procesado'] == 1 ? '✅' : '❌';
                echo "   $procesado ID: {$wh['id']}, Topic: {$wh['topic']}, Resource: {$wh['resource_id']}, Fecha: {$wh['fecha_recibido']}\n";
            }
        } else {
            echo "   ⚠️ NO hay webhooks registrados en la BD\n";
        }
    } else {
        echo "   ❌ No se pudo conectar a BD Moon\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// 3. Verificar pagos registrados
echo "3. Pagos registrados (últimos 5):\n";
try {
    if ($conexion) {
        $stmt = $conexion->prepare("SELECT id, payment_id, id_cliente_moon, monto, estado, fecha_pago 
            FROM mercadopago_pagos 
            ORDER BY fecha_pago DESC 
            LIMIT 5");
        $stmt->execute();
        $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($pagos) {
            foreach ($pagos as $pago) {
                echo "   ✅ Payment ID: {$pago['payment_id']}, Cliente: {$pago['id_cliente_moon']}, Monto: {$pago['monto']}, Estado: {$pago['estado']}\n";
            }
        } else {
            echo "   ⚠️ NO hay pagos registrados en mercadopago_pagos\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Verificar intentos pendientes
echo "4. Intentos pendientes (últimos 5):\n";
try {
    if ($conexion) {
        $stmt = $conexion->prepare("SELECT id, id_cliente_moon, preference_id, monto, estado, fecha_creacion 
            FROM mercadopago_intentos 
            WHERE estado = 'pendiente'
            ORDER BY fecha_creacion DESC 
            LIMIT 5");
        $stmt->execute();
        $intentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($intentos) {
            foreach ($intentos as $intento) {
                echo "   ⚠️ Cliente: {$intento['id_cliente_moon']}, Monto: {$intento['monto']}, Preference: {$intento['preference_id']}, Fecha: {$intento['fecha_creacion']}\n";
            }
        } else {
            echo "   ✅ NO hay intentos pendientes\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. URL del webhook
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$webhookUrl = "$protocol://$host/webhook-mercadopago.php";

echo "5. URL del webhook:\n";
echo "   $webhookUrl\n";
echo "   ⚠️ Esta URL debe estar configurada en MercadoPago\n";
echo "   ⚠️ Ve a: https://www.mercadopago.com.ar/developers/panel/app\n";
echo "   ⚠️ Configura el webhook con esta URL\n";
echo "\n";

echo "=== FIN VERIFICACIÓN ===\n";
