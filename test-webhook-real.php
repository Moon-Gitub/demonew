<?php
/**
 * TEST REAL DEL WEBHOOK
 * Simula una llamada HTTP real al webhook con el JSON de MercadoPago
 */

// El JSON que envía MercadoPago (basado en el que mostraste)
$jsonData = '{
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

echo "=== SIMULANDO LLAMADA AL WEBHOOK ===\n\n";

// Simular variables de entorno
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET = array('data_id' => '123456', 'type' => 'order');
$_POST = array();

// Simular el input
$inputFile = 'php://temp';
file_put_contents($inputFile, $jsonData);

// O mejor, usar curl para hacer una llamada real
$webhookUrl = 'http://localhost/webhook-mercadopago.php'; // Cambiar por tu URL real

echo "Enviando JSON a: $webhookUrl\n";
echo "JSON:\n" . $jsonData . "\n\n";

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "=== RESPUESTA ===\n";
echo "HTTP Code: $httpCode\n";
if ($curlError) {
    echo "Error cURL: $curlError\n";
}
echo "Response: $response\n";

echo "\n=== INSTRUCCIONES PARA PROBAR ===\n";
echo "1. Cambia la URL en este script por tu URL real del webhook\n";
echo "2. Ejecuta: php test-webhook-real.php\n";
echo "3. Revisa los logs en error_log para ver qué pasó\n";
echo "4. Verifica en la BD si se registró el pago en mercadopago_pagos\n";
