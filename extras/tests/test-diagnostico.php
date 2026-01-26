<?php
/**
 * TEST MANUAL DEL DIAGNÓSTICO
 * Ejecuta este archivo desde el navegador o línea de comandos
 * para verificar que el script de diagnóstico funciona correctamente
 */

require_once __DIR__ . '/diagnostico-webhook.php';

// Simular un webhook de prueba
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'Test Script';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Simular JSON de webhook QR
$jsonTest = [
    'action' => 'order.processed',
    'type' => 'order',
    'data' => [
        'id' => '123456789',
        'external_reference' => '14',
        'status' => 'processed',
        'total_paid_amount' => 100000,
        'transactions' => [
            'payments' => [
                [
                    'id' => 'PAY01TEST',
                    'amount' => 100000,
                    'status' => 'approved'
                ]
            ]
        ]
    ]
];

// Simular php://input
file_put_contents('php://temp', json_encode($jsonTest));

echo "✅ Test ejecutado. Revisa el archivo diagnostico_webhook.log\n";
echo "Ubicación: " . __DIR__ . "/diagnostico_webhook.log\n";
