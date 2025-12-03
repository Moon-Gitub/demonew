<?php
/**
 * PRUEBA: Verificar BD de Cobros Moon
 * Acceder a: https://newmoon.posmoon.com.ar/test-bd-cobros.php
 */

// Cargar dependencias
require_once "extensiones/vendor/autoload.php";
require_once "config.php";

// Cargar .env
if (file_exists(__DIR__ . '/.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

require_once "modelos/conexion.php";
require_once "modelos/sistema_cobro.modelo.php";
require_once "controladores/sistema_cobro.controlador.php";

echo "<h1>🧪 Prueba de BD de Cobros Moon</h1>";
echo "<hr>";

// 1. Verificar credenciales
echo "<h2>1️⃣ Credenciales configuradas:</h2>";
echo "<pre>";
echo "Host: " . getenv('MOON_DB_HOST') . "\n";
echo "Database: " . getenv('MOON_DB_NAME') . "\n";
echo "User: " . getenv('MOON_DB_USER') . "\n";
echo "Password: " . (getenv('MOON_DB_PASS') ? '***configurada***' : '❌ NO DEFINIDA') . "\n";
echo "Cliente ID: " . getenv('MOON_CLIENTE_ID') . "\n";
echo "</pre>";

// 2. Probar conexión
echo "<h2>2️⃣ Prueba de conexión:</h2>";
try {
    $conexion = Conexion::conectarMoon();
    if ($conexion) {
        echo "✅ Conexión exitosa a BD Moon<br><br>";

        // 3. Listar tablas
        echo "<h2>3️⃣ Tablas en la BD Moon:</h2>";
        $stmt = $conexion->query("SHOW TABLES");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<ul>";
        foreach ($tablas as $tabla) {
            echo "<li>$tabla";
            if (strpos($tabla, 'mercadopago') !== false) {
                echo " <strong style='color: green;'>← MercadoPago</strong>";
            }
            echo "</li>";
        }
        echo "</ul>";

        // 4. Mostrar estructura de tabla clientes
        echo "<h2>4️⃣ Estructura de tabla <code>clientes</code>:</h2>";
        $stmt = $conexion->query("DESCRIBE clientes");
        $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>Columna</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
        foreach ($estructura as $col) {
            echo "<tr>";
            echo "<td><strong>{$col['Field']}</strong></td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>" . ($col['Default'] ?: 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";

        // 5. Verificar tabla clientes
        echo "<h2>5️⃣ TODOS los clientes (Tabla: <code>clientes</code>):</h2>";
        $stmt = $conexion->query("SELECT * FROM clientes ORDER BY id ASC");
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($clientes) > 0) {
            echo "<p><strong>Total de clientes:</strong> " . count($clientes) . "</p>";

            // Obtener columnas dinámicamente
            $columnas = array_keys($clientes[0]);

            echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%; font-size: 12px;'>";
            echo "<tr style='background: #f0f0f0;'>";
            foreach ($columnas as $col) {
                echo "<th>$col</th>";
            }
            echo "</tr>";

            foreach ($clientes as $cliente) {
                // Color según estado_bloqueo si existe
                $colorBloqueo = isset($cliente['estado_bloqueo']) && $cliente['estado_bloqueo'] == 1 ? '#ffcccc' : '#ccffcc';
                echo "<tr style='background: $colorBloqueo;'>";
                foreach ($columnas as $col) {
                    $valor = $cliente[$col];
                    // Formatear valores especiales
                    if ($col == 'estado_bloqueo') {
                        $valor = $valor == 1 ? '🔴 BLOQ' : '✅ OK';
                    }
                    echo "<td>$valor</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            echo "<p><em>Nota: Fondo rojo = bloqueado, verde = desbloqueado</em></p>";
        } else {
            echo "⚠️ No hay clientes registrados";
        }

        // 6. Verificar cliente específico
        echo "<h2>6️⃣ Cliente configurado en .env (Tabla: <code>clientes</code>):</h2>";
        $idCliente = intval(getenv('MOON_CLIENTE_ID') ?: 7);
        echo "<p><strong>ID del cliente:</strong> $idCliente</p>";

        $clienteMoon = ControladorSistemaCobro::ctrMostrarClientesCobro($idCliente);
        if ($clienteMoon) {
            echo "<pre>";
            print_r($clienteMoon);
            echo "</pre>";
        } else {
            echo "❌ No se encontró el cliente con ID $idCliente";
        }

        // 7. Verificar cuenta corriente
        echo "<h2>7️⃣ Cuenta Corriente (Tabla: <code>clientes_cuenta_corriente</code>):</h2>";
        $ctaCte = ControladorSistemaCobro::ctrMostrarSaldoCuentaCorriente($idCliente);
        if ($ctaCte) {
            echo "<pre>";
            echo "Cliente ID: $idCliente\n";
            echo "Saldo: $" . number_format($ctaCte['saldo'], 2) . "\n";
            if ($ctaCte['saldo'] > 0) {
                echo "Estado: ⚠️ TIENE DEUDA DE $" . number_format($ctaCte['saldo'], 2) . "\n";
            } else {
                echo "Estado: ✅ AL DÍA (Sin deuda)\n";
            }
            echo "</pre>";
        } else {
            echo "⚠️ No se encontró cuenta corriente para este cliente";
        }

        // 8. Verificar tablas MercadoPago
        echo "<h2>8️⃣ Tablas de MercadoPago:</h2>";

        // Intentos
        $stmt = $conexion->query("SELECT COUNT(*) as total FROM mercadopago_intentos");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "• <strong>Tabla:</strong> <code>mercadopago_intentos</code> → {$result['total']} registros<br>";

        // Pagos
        $stmt = $conexion->query("SELECT COUNT(*) as total FROM mercadopago_pagos");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "• <strong>Tabla:</strong> <code>mercadopago_pagos</code> → {$result['total']} registros<br>";

        // Webhooks
        $stmt = $conexion->query("SELECT COUNT(*) as total FROM mercadopago_webhooks");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "• <strong>Tabla:</strong> <code>mercadopago_webhooks</code> → {$result['total']} registros<br>";

    } else {
        echo "❌ La conexión retornó null<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>✅ Conclusión:</h2>";
echo "<p>Si ves todos los datos arriba, significa que está consultando correctamente la BD de cobros Moon.</p>";
echo "<p><strong>⚠️ IMPORTANTE:</strong> Elimina este archivo después de probar:</p>";
echo "<code>rm /home/newmoon/public_html/test-bd-cobros.php</code>";
?>
