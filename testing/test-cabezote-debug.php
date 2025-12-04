<?php
/**
 * TEST - Simular exactamente lo que hace cabezote.php
 */

session_start();

require_once __DIR__ . '/../extensiones/vendor/autoload.php';

// Cargar .env
if (file_exists(__DIR__ . '/../.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controladores/sistema_cobro.controlador.php';
require_once __DIR__ . '/../modelos/sistema_cobro.modelo.php';
require_once __DIR__ . '/../modelos/conexion.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Cabezote Debug</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #667eea; }
        .section { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background: #f4f4f4; padding: 15px; overflow: auto; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug Completo del Cabezote</h1>
        <p>Simulando exactamente lo que hace cobro/cabezote.php</p>

        <?php
        // PASO 1: Obtener ID del cliente (igual que cabezote.php)
        echo '<div class="section">';
        echo '<h3>PASO 1: Determinar ID del Cliente</h3>';
        
        $idCliente = isset($_ENV['MOON_CLIENTE_ID']) ? intval($_ENV['MOON_CLIENTE_ID']) : (isset($_SERVER['MOON_CLIENTE_ID']) ? intval($_SERVER['MOON_CLIENTE_ID']) : 14);
        
        echo '<p><strong>ID Cliente detectado:</strong> <span class="ok">' . $idCliente . '</span></p>';
        echo '</div>';

        // PASO 2: Consultar datos del cliente
        echo '<div class="section">';
        echo '<h3>PASO 2: Consultar Datos del Cliente</h3>';
        
        $clienteMoon = ControladorSistemaCobro::ctrMostrarClientesCobro($idCliente);
        
        echo '<p><strong>Resultado de ctrMostrarClientesCobro(' . $idCliente . '):</strong> ';
        if ($clienteMoon) {
            echo '<span class="ok">✅ SUCCESS</span></p>';
            echo '<pre>' . print_r($clienteMoon, true) . '</pre>';
        } else {
            echo '<span class="error">❌ FALSE/NULL</span></p>';
            echo '<p class="error">PROBLEMA: La consulta no devolvió datos. Verificar conexión a BD Moon.</p>';
        }
        echo '</div>';

        // PASO 3: Consultar cuenta corriente
        echo '<div class="section">';
        echo '<h3>PASO 3: Consultar Cuenta Corriente</h3>';
        
        $ctaCteCliente = ControladorSistemaCobro::ctrMostrarSaldoCuentaCorriente($idCliente);
        
        echo '<p><strong>Resultado de ctrMostrarSaldoCuentaCorriente(' . $idCliente . '):</strong> ';
        if ($ctaCteCliente) {
            echo '<span class="ok">✅ SUCCESS</span></p>';
            echo '<pre>' . print_r($ctaCteCliente, true) . '</pre>';
            
            $saldo = floatval($ctaCteCliente["saldo"]);
            echo '<p><strong>Saldo extraído:</strong> $' . number_format($saldo, 2) . '</p>';
            
            if ($saldo <= 0) {
                echo '<p class="ok">✅ Condición: saldo <= 0 → Mostrará "CUENTA AL DÍA"</p>';
            } else {
                echo '<p class="warning">⚠️ Condición: saldo > 0 → DEBE MOSTRAR MODAL DE PAGO</p>';
            }
        } else {
            echo '<span class="error">❌ FALSE/NULL</span></p>';
            echo '<p class="error">PROBLEMA: La consulta no devolvió datos.</p>';
            echo '<p class="error">Cuando cabezote.php hace: if($ctaCteCliente["saldo"] <= 0)</p>';
            echo '<p class="error">Como $ctaCteCliente es FALSE, $ctaCteCliente["saldo"] es NULL</p>';
            echo '<p class="error">Y NULL <= 0 es TRUE, por eso muestra "al día"</p>';
        }
        echo '</div>';

        // PASO 4: Consultar último movimiento
        echo '<div class="section">';
        echo '<h3>PASO 4: Consultar Último Movimiento</h3>';
        
        $ctaCteMov = ControladorSistemaCobro::ctrMostrarMovimientoCuentaCorriente($idCliente);
        
        if ($ctaCteMov) {
            echo '<span class="ok">✅ SUCCESS</span>';
            echo '<pre>' . print_r($ctaCteMov, true) . '</pre>';
        } else {
            echo '<span class="error">❌ FALSE/NULL</span>';
        }
        echo '</div>';

        // PASO 5: Verificar conexión a BD Moon
        echo '<div class="section">';
        echo '<h3>PASO 5: Verificar Conexión BD Moon</h3>';
        
        try {
            $connMoon = Conexion::conectarMoon();
            if ($connMoon) {
                echo '<p class="ok">✅ Conexión a BD Moon exitosa</p>';
                
                // Query directa
                $stmt = $connMoon->prepare("SELECT * FROM clientes WHERE id = :id");
                $stmt->bindParam(':id', $idCliente, PDO::PARAM_INT);
                $stmt->execute();
                $cliente = $stmt->fetch();
                
                if ($cliente) {
                    echo '<p class="ok">✅ Cliente encontrado con query directa</p>';
                    echo '<pre>' . print_r($cliente, true) . '</pre>';
                } else {
                    echo '<p class="error">❌ Cliente NO encontrado con query directa</p>';
                }
            } else {
                echo '<p class="error">❌ Conexión a BD Moon FALLÓ (retornó NULL)</p>';
                echo '<p>Esto explica por qué las consultas devuelven FALSE</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ ERROR: ' . $e->getMessage() . '</p>';
        }
        echo '</div>';

        // DIAGNÓSTICO FINAL
        echo '<div class="section" style="background: #fff3cd; border-left: 5px solid #ffc107;">';
        echo '<h3>🎯 DIAGNÓSTICO FINAL</h3>';
        
        if (!$clienteMoon || !$ctaCteCliente) {
            echo '<p class="error"><strong>PROBLEMA IDENTIFICADO:</strong></p>';
            echo '<p>Las consultas a BD Moon están fallando (devuelven FALSE/NULL)</p>';
            echo '<p><strong>Causa probable:</strong> La conexión a BD Moon no funciona en el contexto del cabezote.php</p>';
            echo '<p><strong>Solución:</strong> Verificar que conexion.php::conectarMoon() funciona correctamente</p>';
        } elseif ($saldo > 0) {
            echo '<p class="ok"><strong>TODO FUNCIONA CORRECTAMENTE</strong></p>';
            echo '<p>Cliente ID 14 tiene deuda de $' . number_format($saldo, 2) . '</p>';
            echo '<p>El modal DEBE mostrarse en el sistema real</p>';
            echo '<p><strong>Si no se muestra en el sistema real, el problema es otro (caché, sesión, etc.)</strong></p>';
        } else {
            echo '<p class="warning">Cliente al día según la consulta</p>';
        }
        echo '</div>';
        ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.html" style="display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 50px;">← Volver</a>
        </div>
    </div>
</body>
</html>


