<?php
/**
 * SCRIPT PARA VERIFICAR QUE MERCADO PAGO PUEDE ACCEDER AL WEBHOOK
 * 
 * Este script verifica:
 * 1. Que el webhook sea accesible desde internet
 * 2. Que responda correctamente a peticiones POST
 * 3. Que el certificado SSL sea válido
 * 4. Que la URL esté configurada correctamente
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verificación Webhook Mercado Pago</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #009ee3; padding-bottom: 10px; }
        .test { margin: 15px 0; padding: 15px; border-left: 4px solid #ddd; background: #f9f9f9; }
        .test.ok { border-left-color: #28a745; background: #d4edda; }
        .test.error { border-left-color: #dc3545; background: #f8d7da; }
        .test.warning { border-left-color: #ffc107; background: #fff3cd; }
        .test h3 { margin-top: 0; }
        .code { background: #f4f4f4; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px; overflow-x: auto; }
        .url { color: #009ee3; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación de Webhook Mercado Pago</h1>
        
        <?php
        $webhookUrl = 'https://newmoon.posmoon.com.ar/webhook-mercadopago.php';
        $tests = [];
        
        // Test 1: Verificar que el archivo existe
        echo '<div class="test">';
        echo '<h3>1. Verificando que el archivo existe...</h3>';
        if (file_exists(__DIR__ . '/webhook-mercadopago.php')) {
            echo '<p>✅ Archivo <code>webhook-mercadopago.php</code> existe</p>';
            $tests[] = ['name' => 'Archivo existe', 'status' => 'ok'];
        } else {
            echo '<p>❌ ERROR: Archivo <code>webhook-mercadopago.php</code> NO existe</p>';
            $tests[] = ['name' => 'Archivo existe', 'status' => 'error'];
        }
        echo '</div>';
        
        // Test 2: Verificar sintaxis PHP
        echo '<div class="test">';
        echo '<h3>2. Verificando sintaxis PHP...</h3>';
        $output = [];
        $returnVar = 0;
        exec("php -l " . escapeshellarg(__DIR__ . '/webhook-mercadopago.php') . " 2>&1", $output, $returnVar);
        if ($returnVar === 0) {
            echo '<p>✅ Sintaxis PHP correcta</p>';
            $tests[] = ['name' => 'Sintaxis PHP', 'status' => 'ok'];
        } else {
            echo '<p>❌ ERROR de sintaxis PHP:</p>';
            echo '<div class="code">' . htmlspecialchars(implode("\n", $output)) . '</div>';
            $tests[] = ['name' => 'Sintaxis PHP', 'status' => 'error'];
        }
        echo '</div>';
        
        // Test 3: Verificar accesibilidad desde internet (GET)
        echo '<div class="test">';
        echo '<h3>3. Verificando accesibilidad desde internet (GET)...</h3>';
        echo '<p>URL: <span class="url">' . htmlspecialchars($webhookUrl) . '</span></p>';
        
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $sslInfo = curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT);
        curl_close($ch);
        
        if ($curlError) {
            echo '<p>❌ ERROR de conexión: <strong>' . htmlspecialchars($curlError) . '</strong></p>';
            $tests[] = ['name' => 'Accesibilidad GET', 'status' => 'error'];
        } else {
            echo '<p>✅ Conexión exitosa</p>';
            echo '<p>HTTP Code: <strong>' . $httpCode . '</strong> ' . ($httpCode == 200 ? '✅' : '⚠️') . '</p>';
            if ($httpCode == 200) {
                $responseData = json_decode($response, true);
                if ($responseData && isset($responseData['message'])) {
                    echo '<p>Respuesta: <code>' . htmlspecialchars($responseData['message']) . '</code></p>';
                }
                $tests[] = ['name' => 'Accesibilidad GET', 'status' => 'ok'];
            } else {
                $tests[] = ['name' => 'Accesibilidad GET', 'status' => 'warning'];
            }
        }
        echo '</div>';
        
        // Test 4: Verificar certificado SSL
        echo '<div class="test">';
        echo '<h3>4. Verificando certificado SSL...</h3>';
        if ($sslInfo === 0) {
            echo '<p>✅ Certificado SSL válido</p>';
            $tests[] = ['name' => 'Certificado SSL', 'status' => 'ok'];
        } else {
            echo '<p>⚠️ ADVERTENCIA: Problema con certificado SSL (código: ' . $sslInfo . ')</p>';
            echo '<p>Mercado Pago requiere HTTPS válido para enviar notificaciones</p>';
            $tests[] = ['name' => 'Certificado SSL', 'status' => 'warning'];
        }
        echo '</div>';
        
        // Test 5: Simular notificación POST
        echo '<div class="test">';
        echo '<h3>5. Simulando notificación de Mercado Pago (POST)...</h3>';
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            echo '<p>❌ ERROR de conexión: <strong>' . htmlspecialchars($curlError) . '</strong></p>';
            $tests[] = ['name' => 'Notificación POST', 'status' => 'error'];
        } else {
            echo '<p>✅ Conexión exitosa</p>';
            echo '<p>HTTP Code: <strong>' . $httpCode . '</strong> ' . ($httpCode == 200 ? '✅' : '⚠️') . '</p>';
            if ($httpCode == 200) {
                $responseData = json_decode($response, true);
                if ($responseData) {
                    echo '<p>Respuesta:</p>';
                    echo '<div class="code">' . htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</div>';
                }
                $tests[] = ['name' => 'Notificación POST', 'status' => 'ok'];
            } else {
                $tests[] = ['name' => 'Notificación POST', 'status' => 'warning'];
            }
        }
        echo '</div>';
        
        // Test 6: Verificar logs
        echo '<div class="test">';
        echo '<h3>6. Verificando logs...</h3>';
        $logFile = '/tmp/webhook_raw.log';
        if (file_exists($logFile)) {
            $logSize = filesize($logFile);
            echo '<p>✅ Archivo de log existe: <code>' . htmlspecialchars($logFile) . '</code></p>';
            echo '<p>Tamaño: <strong>' . number_format($logSize) . ' bytes</strong></p>';
            if ($logSize > 0) {
                echo '<p>Últimas 3 líneas del log:</p>';
                $lines = file($logFile);
                $lastLines = array_slice($lines, -3);
                echo '<div class="code">';
                foreach ($lastLines as $line) {
                    echo htmlspecialchars(trim($line)) . "\n";
                }
                echo '</div>';
            } else {
                echo '<p>⚠️ El log está vacío (no se han recibido notificaciones reales de Mercado Pago)</p>';
            }
            $tests[] = ['name' => 'Logs', 'status' => 'ok'];
        } else {
            echo '<p>⚠️ Archivo de log no existe aún: <code>' . htmlspecialchars($logFile) . '</code></p>';
            echo '<p>(Esto es normal si no se han recibido notificaciones)</p>';
            $tests[] = ['name' => 'Logs', 'status' => 'warning'];
        }
        echo '</div>';
        
        // Resumen
        echo '<div class="test" style="background: #e7f3ff; border-left-color: #009ee3;">';
        echo '<h3>📋 Resumen</h3>';
        $okCount = count(array_filter($tests, function($t) { return $t['status'] === 'ok'; }));
        $errorCount = count(array_filter($tests, function($t) { return $t['status'] === 'error'; }));
        $warningCount = count(array_filter($tests, function($t) { return $t['status'] === 'warning'; }));
        
        echo '<p><strong>Total de tests:</strong> ' . count($tests) . '</p>';
        echo '<p>✅ Exitosos: <strong>' . $okCount . '</strong></p>';
        echo '<p>⚠️ Advertencias: <strong>' . $warningCount . '</strong></p>';
        echo '<p>❌ Errores: <strong>' . $errorCount . '</strong></p>';
        
        if ($errorCount == 0 && $okCount == count($tests)) {
            echo '<p style="color: #28a745; font-weight: bold;">✅ Todos los tests pasaron. El webhook debería funcionar correctamente.</p>';
            echo '<p><strong>Si Mercado Pago aún muestra "0% Notificaciones entregadas":</strong></p>';
            echo '<ol>';
            echo '<li>Verifica que la URL en Mercado Pago sea exactamente: <code>' . htmlspecialchars($webhookUrl) . '</code></li>';
            echo '<li>Usa el botón <strong>"Simular notificación"</strong> en el panel de Mercado Pago</li>';
            echo '<li>Realiza un pago de prueba y verifica los logs</li>';
            echo '<li>Contacta con el soporte de Mercado Pago si el problema persiste</li>';
            echo '</ol>';
        } elseif ($errorCount > 0) {
            echo '<p style="color: #dc3545; font-weight: bold;">❌ Hay errores que deben corregirse antes de que el webhook funcione.</p>';
        } else {
            echo '<p style="color: #ffc107; font-weight: bold;">⚠️ Hay advertencias. Revisa los detalles arriba.</p>';
        }
        echo '</div>';
        ?>
        
        <div style="margin-top: 30px; padding: 15px; background: #f0f0f0; border-radius: 4px;">
            <h3>🔧 Próximos pasos</h3>
            <ol>
                <li>Verifica la configuración en Mercado Pago:
                    <ul>
                        <li>URL: <code><?php echo htmlspecialchars($webhookUrl); ?></code></li>
                        <li>Eventos activados: <strong>Pagos</strong> y <strong>Order (Mercado Pago)</strong></li>
                    </ul>
                </li>
                <li>Usa el botón <strong>"Simular notificación"</strong> en el panel de Mercado Pago</li>
                <li>Revisa los logs en: <code>/tmp/webhook_raw.log</code></li>
                <li>Realiza un pago de prueba y verifica que llegue la notificación</li>
            </ol>
        </div>
    </div>
</body>
</html>
