<?php
/**
 * SCRIPT DE DIAGNÓSTICO: Verificar accesibilidad del webhook
 * 
 * Este script verifica:
 * 1. Si el webhook es accesible desde internet
 * 2. Si responde correctamente
 * 3. Si hay errores de sintaxis
 */

// Obtener la URL base
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$webhookUrl = $protocol . '://' . $host . '/webhook-mercadopago.php';

echo "=== DIAGNÓSTICO DE ACCESIBILIDAD DEL WEBHOOK ===\n\n";
echo "URL del webhook: $webhookUrl\n\n";

// Test 1: Verificar que el archivo existe
echo "1. Verificando que el archivo existe...\n";
if (file_exists(__DIR__ . '/webhook-mercadopago.php')) {
    echo "   ✅ Archivo webhook-mercadopago.php existe\n";
} else {
    echo "   ❌ ERROR: Archivo webhook-mercadopago.php NO existe\n";
    exit(1);
}

// Test 2: Verificar sintaxis PHP
echo "\n2. Verificando sintaxis PHP...\n";
$output = [];
$returnVar = 0;
exec("php -l " . escapeshellarg(__DIR__ . '/webhook-mercadopago.php') . " 2>&1", $output, $returnVar);
if ($returnVar === 0) {
    echo "   ✅ Sintaxis PHP correcta\n";
} else {
    echo "   ❌ ERROR de sintaxis PHP:\n";
    foreach ($output as $line) {
        echo "      $line\n";
    }
    exit(1);
}

// Test 3: Hacer petición GET al webhook (test básico)
echo "\n3. Haciendo petición GET al webhook (test básico)...\n";
$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Solo para test
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo "   ❌ ERROR de conexión: $curlError\n";
} else {
    echo "   ✅ Conexión exitosa\n";
    echo "   HTTP Code: $httpCode\n";
    if ($httpCode == 200) {
        echo "   ✅ Webhook responde 200 OK\n";
        $responseData = json_decode($response, true);
        if ($responseData && isset($responseData['message'])) {
            echo "   Respuesta: " . $responseData['message'] . "\n";
        }
    } else {
        echo "   ⚠️ ADVERTENCIA: Webhook responde con código $httpCode (debería ser 200)\n";
        echo "   Respuesta: " . substr($response, 0, 200) . "\n";
    }
}

// Test 4: Simular notificación de Mercado Pago (POST)
echo "\n4. Simulando notificación de Mercado Pago (POST)...\n";
$testData = json_encode([
    'type' => 'payment',
    'data' => [
        'id' => '123456789'
    ]
]);

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($testData)
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Solo para test
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo "   ❌ ERROR de conexión: $curlError\n";
} else {
    echo "   ✅ Conexión exitosa\n";
    echo "   HTTP Code: $httpCode\n";
    if ($httpCode == 200) {
        echo "   ✅ Webhook responde 200 OK a notificaciones\n";
    } else {
        echo "   ⚠️ ADVERTENCIA: Webhook responde con código $httpCode (debería ser 200)\n";
    }
    $responseData = json_decode($response, true);
    if ($responseData) {
        echo "   Respuesta: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    }
}

// Test 5: Verificar logs
echo "\n5. Verificando logs...\n";
$logFile = '/tmp/webhook_raw.log';
if (file_exists($logFile)) {
    $logSize = filesize($logFile);
    echo "   ✅ Archivo de log existe: $logFile\n";
    echo "   Tamaño: " . number_format($logSize) . " bytes\n";
    if ($logSize > 0) {
        echo "   Últimas 5 líneas del log:\n";
        $lines = file($logFile);
        $lastLines = array_slice($lines, -5);
        foreach ($lastLines as $line) {
            echo "      " . trim($line) . "\n";
        }
    } else {
        echo "   ⚠️ El log está vacío (no se han recibido notificaciones)\n";
    }
} else {
    echo "   ⚠️ Archivo de log no existe aún: $logFile\n";
    echo "   (Esto es normal si no se han recibido notificaciones)\n";
}

// Test 6: Verificar permisos de escritura
echo "\n6. Verificando permisos de escritura...\n";
$testFile = '/tmp/webhook_test_' . time() . '.txt';
if (@file_put_contents($testFile, 'test')) {
    echo "   ✅ Permisos de escritura OK\n";
    @unlink($testFile);
} else {
    echo "   ❌ ERROR: No se pueden escribir archivos en /tmp\n";
}

echo "\n=== RESUMEN ===\n";
echo "Si todos los tests pasan pero Mercado Pago muestra '0% Notificaciones entregadas':\n";
echo "1. Verifica que la URL en Mercado Pago sea exactamente: $webhookUrl\n";
echo "2. Verifica que el servidor sea accesible desde internet (no localhost)\n";
echo "3. Verifica que no haya firewall bloqueando las peticiones de Mercado Pago\n";
echo "4. Usa el botón 'Simular notificación' en el panel de Mercado Pago\n";
echo "5. Revisa los logs en: $logFile\n";
echo "\n";
