<?php
/**
 * TEST - Verificar Saldo del Cliente
 * 
 * Verifica qué cliente se está consultando y cuál es su saldo real
 */

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Saldo Cliente</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 2rem; margin-bottom: 10px; }
        .content { padding: 40px; }
        .section {
            background: #f8f9fa;
            border-left: 5px solid #667eea;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .section h3 { color: #333; margin-bottom: 15px; }
        .item {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            background: white;
            margin-bottom: 8px;
            border-radius: 5px;
        }
        .item label { font-weight: 600; color: #555; }
        .item .value { font-weight: bold; color: #667eea; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        .alert.success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .alert.error { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }
        .alert.warning { background: #fff3cd; color: #856404; border-left: 5px solid #ffc107; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="font-size: 3rem; margin-bottom: 10px;">💰</div>
            <h1>Test de Saldo del Cliente</h1>
            <p>Verificación de cuenta corriente</p>
        </div>
        
        <div class="content">
            <?php
            // Obtener ID del cliente
            $idCliente = isset($_ENV['MOON_CLIENTE_ID']) ? intval($_ENV['MOON_CLIENTE_ID']) : (isset($_SERVER['MOON_CLIENTE_ID']) ? intval($_SERVER['MOON_CLIENTE_ID']) : 14);
            
            echo '<div class="section">';
            echo '<h3>🆔 Cliente Configurado</h3>';
            echo '<div class="item"><label>ID del Cliente:</label><span class="value">' . $idCliente . '</span></div>';
            echo '</div>';
            
            try {
                // Obtener datos del cliente
                $clienteMoon = ControladorSistemaCobro::ctrMostrarClientesCobro($idCliente);
                
                if ($clienteMoon) {
                    echo '<div class="section">';
                    echo '<h3>👤 Datos del Cliente</h3>';
                    echo '<div class="item"><label>ID:</label><span class="value">' . $clienteMoon['id'] . '</span></div>';
                    echo '<div class="item"><label>Nombre:</label><span class="value">' . $clienteMoon['nombre'] . '</span></div>';
                    echo '<div class="item"><label>Email:</label><span class="value">' . ($clienteMoon['email'] ?? 'N/A') . '</span></div>';
                    echo '<div class="item"><label>Mensual:</label><span class="value">$' . number_format($clienteMoon['mensual'], 2) . '</span></div>';
                    echo '<div class="item"><label>Estado Bloqueo:</label><span class="value">' . ($clienteMoon['estado_bloqueo'] == 1 ? '🔴 BLOQUEADO' : '✅ ACTIVO') . '</span></div>';
                    echo '</div>';
                } else {
                    echo '<div class="alert error">❌ No se encontró el cliente con ID ' . $idCliente . '</div>';
                }
                
                // Obtener saldo de cuenta corriente
                $ctaCteCliente = ControladorSistemaCobro::ctrMostrarSaldoCuentaCorriente($idCliente);
                
                if ($ctaCteCliente) {
                    $saldo = floatval($ctaCteCliente['saldo']);
                    
                    echo '<div class="section">';
                    echo '<h3>💵 Cuenta Corriente</h3>';
                    echo '<div class="item"><label>Total Ventas/Cargos:</label><span class="value">$' . number_format($ctaCteCliente['ventas'], 2) . '</span></div>';
                    echo '<div class="item"><label>Total Pagos:</label><span class="value">$' . number_format($ctaCteCliente['pagos'], 2) . '</span></div>';
                    echo '<div class="item"><label>Saldo (Ventas - Pagos):</label><span class="value" style="font-size: 1.3rem;">$' . number_format($saldo, 2) . '</span></div>';
                    echo '</div>';
                    
                    // Determinar estado
                    if ($saldo <= 0) {
                        echo '<div class="alert success">';
                        echo '<h3>✅ CUENTA AL DÍA</h3>';
                        echo '<p>El cliente no tiene deuda pendiente.</p>';
                        echo '<p>Saldo: $' . number_format($saldo, 2) . '</p>';
                        echo '</div>';
                    } else {
                        echo '<div class="alert warning">';
                        echo '<h3>⚠️ CLIENTE CON DEUDA</h3>';
                        echo '<p><strong>Saldo pendiente: $' . number_format($saldo, 2) . '</strong></p>';
                        echo '<p>El sistema DEBE mostrar el modal de pago.</p>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="alert error">❌ No se pudo consultar la cuenta corriente</div>';
                }
                
                // Mostrar últimos movimientos
                echo '<div class="section">';
                echo '<h3>📋 Último Movimiento</h3>';
                $ultimoMov = ControladorSistemaCobro::ctrMostrarMovimientoCuentaCorriente($idCliente);
                if ($ultimoMov) {
                    echo '<div class="item"><label>Fecha:</label><span class="value">' . $ultimoMov['fecha'] . '</span></div>';
                    echo '<div class="item"><label>Tipo:</label><span class="value">' . ($ultimoMov['tipo'] == 0 ? 'CARGO' : 'PAGO') . '</span></div>';
                    echo '<div class="item"><label>Descripción:</label><span class="value">' . $ultimoMov['descripcion'] . '</span></div>';
                    echo '<div class="item"><label>Importe:</label><span class="value">$' . number_format($ultimoMov['importe'], 2) . '</span></div>';
                } else {
                    echo '<p>No hay movimientos registrados</p>';
                }
                echo '</div>';
                
                // Query directa para ver todos los movimientos
                echo '<div class="section">';
                echo '<h3>📊 Últimos 10 Movimientos (Query Directa)</h3>';
                $conn = Conexion::conectarMoon();
                if ($conn) {
                    $stmt = $conn->prepare("SELECT * FROM clientes_cuenta_corriente WHERE id_cliente = :id ORDER BY id DESC LIMIT 10");
                    $stmt->bindParam(':id', $idCliente, PDO::PARAM_INT);
                    $stmt->execute();
                    $movimientos = $stmt->fetchAll();
                    
                    if (count($movimientos) > 0) {
                        echo '<table style="width: 100%; border-collapse: collapse;">';
                        echo '<tr style="background: #f8f9fa;"><th style="padding: 8px;">Fecha</th><th>Tipo</th><th>Descripción</th><th>Importe</th></tr>';
                        foreach ($movimientos as $mov) {
                            $tipo = $mov['tipo'] == 0 ? '<span style="color: red;">CARGO</span>' : '<span style="color: green;">PAGO</span>';
                            echo '<tr>';
                            echo '<td style="padding: 8px; border-bottom: 1px solid #ddd;">' . $mov['fecha'] . '</td>';
                            echo '<td style="padding: 8px; border-bottom: 1px solid #ddd;">' . $tipo . '</td>';
                            echo '<td style="padding: 8px; border-bottom: 1px solid #ddd;">' . $mov['descripcion'] . '</td>';
                            echo '<td style="padding: 8px; border-bottom: 1px solid #ddd;">$' . number_format($mov['importe'], 2) . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    } else {
                        echo '<p>No hay movimientos para este cliente</p>';
                    }
                } else {
                    echo '<p style="color: red;">No se pudo conectar a la BD Moon</p>';
                }
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<div class="alert error">';
                echo '<h3>❌ Error</h3>';
                echo '<p>' . $e->getMessage() . '</p>';
                echo '</div>';
            }
            ?>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.html" style="display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 50px; font-weight: 600;">← Volver al Menú</a>
            </div>
        </div>
    </div>
</body>
</html>


