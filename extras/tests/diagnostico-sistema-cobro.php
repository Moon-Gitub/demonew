<?php
/**
 * DIAGNÓSTICO COMPLETO DEL SISTEMA DE COBRO
 * 
 * Este script verifica:
 * 1. Configuración de credenciales de Mercado Pago
 * 2. Estado de la base de datos
 * 3. Últimos pagos registrados
 * 4. Últimos webhooks recibidos
 * 5. Últimos intentos de pago
 * 6. Configuración del webhook
 */

header('Content-Type: text/html; charset=utf-8');

// Cargar dependencias
require_once __DIR__ . '/extensiones/vendor/autoload.php';
if (file_exists(__DIR__ . '/.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/modelos/conexion.php';
require_once __DIR__ . '/controladores/mercadopago.controlador.php';
require_once __DIR__ . '/modelos/mercadopago.modelo.php';
require_once __DIR__ . '/controladores/sistema_cobro.controlador.php';
require_once __DIR__ . '/modelos/sistema_cobro.modelo.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico Sistema de Cobro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #009ee3; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .test { margin: 15px 0; padding: 15px; border-left: 4px solid #ddd; background: #f9f9f9; }
        .test.ok { border-left-color: #28a745; background: #d4edda; }
        .test.error { border-left-color: #dc3545; background: #f8d7da; }
        .test.warning { border-left-color: #ffc107; background: #fff3cd; }
        .test h3 { margin-top: 0; }
        .code { background: #f4f4f4; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table th, table td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        table th { background: #f0f0f0; font-weight: bold; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: black; }
        .badge-info { background: #17a2b8; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico Completo del Sistema de Cobro</h1>
        
        <?php
        // Test 1: Credenciales de Mercado Pago
        echo '<div class="test">';
        echo '<h3>1. Verificando Credenciales de Mercado Pago</h3>';
        $credenciales = ControladorMercadoPago::ctrObtenerCredenciales();
        if (!empty($credenciales['access_token']) && !empty($credenciales['public_key'])) {
            echo '<p>✅ <strong>Access Token:</strong> Configurado (' . substr($credenciales['access_token'], 0, 20) . '...)</p>';
            echo '<p>✅ <strong>Public Key:</strong> Configurado (' . substr($credenciales['public_key'], 0, 20) . '...)</p>';
        } else {
            echo '<p>❌ <strong>ERROR:</strong> Credenciales no configuradas correctamente</p>';
        }
        echo '</div>';
        
        // Test 2: Conexión a Base de Datos Moon
        echo '<div class="test">';
        echo '<h3>2. Verificando Conexión a Base de Datos Moon</h3>';
        try {
            $pdo = Conexion::conectarMoon();
            if ($pdo) {
                echo '<p>✅ Conexión a BD Moon exitosa</p>';
                
                // Verificar tablas
                $tablas = ['mercadopago_intentos', 'mercadopago_pagos', 'mercadopago_webhooks', 'clientes_cuenta_corriente'];
                echo '<p><strong>Tablas verificadas:</strong></p><ul>';
                foreach ($tablas as $tabla) {
                    try {
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabla LIMIT 1");
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo '<li>✅ <code>' . htmlspecialchars($tabla) . '</code> - ' . $result['total'] . ' registros</li>';
                    } catch (Exception $e) {
                        echo '<li>❌ <code>' . htmlspecialchars($tabla) . '</code> - Error: ' . htmlspecialchars($e->getMessage()) . '</li>';
                    }
                }
                echo '</ul>';
            } else {
                echo '<p>❌ ERROR: No se pudo conectar a BD Moon</p>';
            }
        } catch (Exception $e) {
            echo '<p>❌ ERROR: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        echo '</div>';
        
        // Test 3: Últimos Pagos Registrados
        echo '<div class="test">';
        echo '<h3>3. Últimos 10 Pagos Registrados</h3>';
        try {
            $pdo = Conexion::conectarMoon();
            if ($pdo) {
                $stmt = $pdo->query("SELECT id, id_cliente_moon, payment_id, monto, estado, fecha_pago, payment_method_id 
                    FROM mercadopago_pagos 
                    ORDER BY fecha_pago DESC 
                    LIMIT 10");
                $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($pagos) > 0) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Cliente</th><th>Payment ID</th><th>Monto</th><th>Estado</th><th>Método</th><th>Fecha</th></tr>';
                    foreach ($pagos as $pago) {
                        $estadoBadge = $pago['estado'] === 'approved' ? 'badge-success' : 'badge-warning';
                        echo '<tr>';
                        echo '<td>' . $pago['id'] . '</td>';
                        echo '<td>' . $pago['id_cliente_moon'] . '</td>';
                        echo '<td><code>' . htmlspecialchars(substr($pago['payment_id'], 0, 20)) . '...</code></td>';
                        echo '<td>$' . number_format($pago['monto'], 2) . '</td>';
                        echo '<td><span class="badge ' . $estadoBadge . '">' . htmlspecialchars($pago['estado']) . '</span></td>';
                        echo '<td>' . htmlspecialchars($pago['payment_method_id'] ?: 'N/A') . '</td>';
                        echo '<td>' . htmlspecialchars($pago['fecha_pago']) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p>⚠️ No hay pagos registrados en la base de datos</p>';
                }
            }
        } catch (Exception $e) {
            echo '<p>❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        echo '</div>';
        
        // Test 4: Últimos Webhooks Recibidos
        echo '<div class="test">';
        echo '<h3>4. Últimos 10 Webhooks Recibidos</h3>';
        try {
            $pdo = Conexion::conectarMoon();
            if ($pdo) {
                $stmt = $pdo->query("SELECT id, topic, resource_id, fecha_recibido, procesado 
                    FROM mercadopago_webhooks 
                    ORDER BY fecha_recibido DESC 
                    LIMIT 10");
                $webhooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($webhooks) > 0) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Topic</th><th>Resource ID</th><th>Fecha</th><th>Procesado</th></tr>';
                    foreach ($webhooks as $wh) {
                        $procesadoBadge = $wh['procesado'] ? 'badge-success' : 'badge-warning';
                        echo '<tr>';
                        echo '<td>' . $wh['id'] . '</td>';
                        echo '<td><span class="badge badge-info">' . htmlspecialchars($wh['topic']) . '</span></td>';
                        echo '<td><code>' . htmlspecialchars(substr($wh['resource_id'], 0, 20)) . '...</code></td>';
                        echo '<td>' . htmlspecialchars($wh['fecha_recibido']) . '</td>';
                        echo '<td><span class="badge ' . $procesadoBadge . '">' . ($wh['procesado'] ? 'Sí' : 'No') . '</span></td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p>⚠️ No hay webhooks registrados (puede ser que no se estén recibiendo notificaciones)</p>';
                }
            }
        } catch (Exception $e) {
            echo '<p>❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        echo '</div>';
        
        // Test 5: Últimos Intentos de Pago
        echo '<div class="test">';
        echo '<h3>5. Últimos 10 Intentos de Pago</h3>';
        try {
            $pdo = Conexion::conectarMoon();
            if ($pdo) {
                $stmt = $pdo->query("SELECT id, id_cliente_moon, preference_id, monto, estado, fecha_creacion 
                    FROM mercadopago_intentos 
                    ORDER BY fecha_creacion DESC 
                    LIMIT 10");
                $intentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($intentos) > 0) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Cliente</th><th>Preference ID</th><th>Monto</th><th>Estado</th><th>Fecha Creación</th></tr>';
                    foreach ($intentos as $intento) {
                        $estadoBadge = $intento['estado'] === 'pendiente' ? 'badge-warning' : 
                                      ($intento['estado'] === 'aprobado' ? 'badge-success' : 'badge-danger');
                        echo '<tr>';
                        echo '<td>' . $intento['id'] . '</td>';
                        echo '<td>' . $intento['id_cliente_moon'] . '</td>';
                        echo '<td><code>' . htmlspecialchars(substr($intento['preference_id'] ?: 'N/A', 0, 30)) . '</code></td>';
                        echo '<td>$' . number_format($intento['monto'], 2) . '</td>';
                        echo '<td><span class="badge ' . $estadoBadge . '">' . htmlspecialchars($intento['estado']) . '</span></td>';
                        echo '<td>' . htmlspecialchars($intento['fecha_creacion']) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p>⚠️ No hay intentos registrados</p>';
                }
            }
        } catch (Exception $e) {
            echo '<p>❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        echo '</div>';
        
        // Test 6: Verificar Logs del Webhook
        echo '<div class="test">';
        echo '<h3>6. Verificando Logs del Webhook</h3>';
        $logDirs = [
            getenv('MP_WEBHOOK_LOG_DIR') ?: '',
            getenv('HOME') . '/logs',
            '/tmp',
            __DIR__ . '/logs'
        ];
        
        $logEncontrado = false;
        foreach ($logDirs as $logDir) {
            if ($logDir && is_dir($logDir)) {
                $logFile = $logDir . '/webhook.log';
                if (file_exists($logFile)) {
                    $logEncontrado = true;
                    $logSize = filesize($logFile);
                    echo '<p>✅ Archivo de log encontrado: <code>' . htmlspecialchars($logFile) . '</code></p>';
                    echo '<p>Tamaño: <strong>' . number_format($logSize) . ' bytes</strong></p>';
                    
                    if ($logSize > 0) {
                        echo '<p>Últimas 5 líneas del log:</p>';
                        $lines = file($logFile);
                        $lastLines = array_slice($lines, -5);
                        echo '<div class="code">';
                        foreach ($lastLines as $line) {
                            echo htmlspecialchars(trim($line)) . "\n";
                        }
                        echo '</div>';
                    }
                    break;
                }
            }
        }
        
        if (!$logEncontrado) {
            echo '<p>⚠️ No se encontró archivo de log del webhook</p>';
            echo '<p>Directorios verificados:</p><ul>';
            foreach ($logDirs as $dir) {
                echo '<li><code>' . htmlspecialchars($dir ?: '(vacío)') . '</code></li>';
            }
            echo '</ul>';
        }
        echo '</div>';
        
        // Test 7: Verificar URL del Webhook
        echo '<div class="test">';
        echo '<h3>7. Verificando URL del Webhook</h3>';
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $webhookUrl = $protocol . '://' . $host . '/webhook-mercadopago.php';
        echo '<p>URL del webhook: <code>' . htmlspecialchars($webhookUrl) . '</code></p>';
        
        // Test de accesibilidad
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            echo '<p>✅ Webhook accesible (HTTP 200)</p>';
            $responseData = json_decode($response, true);
            if ($responseData && isset($responseData['ok'])) {
                echo '<p>Respuesta: <code>' . htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT)) . '</code></p>';
            }
        } else {
            echo '<p>⚠️ Webhook responde con código HTTP ' . $httpCode . '</p>';
        }
        echo '</div>';
        
        // Resumen
        echo '<div class="test" style="background: #e7f3ff; border-left-color: #009ee3;">';
        echo '<h3>📋 Resumen del Diagnóstico</h3>';
        echo '<p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>';
        echo '<p><strong>URL del Webhook:</strong> <code>' . htmlspecialchars($webhookUrl) . '</code></p>';
        echo '<p><strong>Próximos pasos:</strong></p>';
        echo '<ol>';
        echo '<li>Verifica que la URL del webhook esté configurada correctamente en Mercado Pago</li>';
        echo '<li>Revisa los logs del webhook para ver si se están recibiendo notificaciones</li>';
        echo '<li>Verifica que los pagos se estén registrando en <code>mercadopago_pagos</code></li>';
        echo '<li>Si no hay webhooks, usa el botón "Simular notificación" en el panel de Mercado Pago</li>';
        echo '</ol>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
