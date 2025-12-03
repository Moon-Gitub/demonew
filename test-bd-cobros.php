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

        // 4. Verificar tabla clientes
        echo "<h2>4️⃣ Clientes registrados:</h2>";
        $stmt = $conexion->query("SELECT id, nombre, email, estado_bloqueo FROM clientes ORDER BY id DESC LIMIT 10");
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($clientes) > 0) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Estado Bloqueo</th></tr>";
            foreach ($clientes as $cliente) {
                $color = $cliente['estado_bloqueo'] == 1 ? 'background: #ffcccc;' : '';
                echo "<tr style='$color'>";
                echo "<td>{$cliente['id']}</td>";
                echo "<td>{$cliente['nombre']}</td>";
                echo "<td>{$cliente['email']}</td>";
                echo "<td>" . ($cliente['estado_bloqueo'] == 1 ? '🔴 BLOQUEADO' : '✅ Activo') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "⚠️ No hay clientes registrados";
        }

        // 5. Verificar cliente específico
        echo "<h2>5️⃣ Cliente ID " . getenv('MOON_CLIENTE_ID') . " (configurado en .env):</h2>";
        $idCliente = intval(getenv('MOON_CLIENTE_ID') ?: 7);

        $clienteMoon = ControladorSistemaCobro::ctrMostrarClientesCobro($idCliente);
        if ($clienteMoon) {
            echo "<pre>";
            echo "ID: {$clienteMoon['id']}\n";
            echo "Nombre: {$clienteMoon['nombre']}\n";
            echo "Email: {$clienteMoon['email']}\n";
            echo "Estado Bloqueo: " . ($clienteMoon['estado_bloqueo'] == 1 ? '🔴 BLOQUEADO' : '✅ Activo') . "\n";
            echo "</pre>";
        } else {
            echo "❌ No se encontró el cliente con ID $idCliente";
        }

        // 6. Verificar cuenta corriente
        echo "<h2>6️⃣ Cuenta Corriente del Cliente:</h2>";
        $ctaCte = ControladorSistemaCobro::ctrMostrarSaldoCuentaCorriente($idCliente);
        if ($ctaCte) {
            echo "<pre>";
            echo "Saldo: $" . number_format($ctaCte['saldo'], 2) . "\n";
            if ($ctaCte['saldo'] > 0) {
                echo "Estado: ⚠️ TIENE DEUDA\n";
            } else {
                echo "Estado: ✅ AL DÍA\n";
            }
            echo "</pre>";
        } else {
            echo "⚠️ No se encontró cuenta corriente para este cliente";
        }

        // 7. Verificar tablas MercadoPago
        echo "<h2>7️⃣ Tablas de MercadoPago:</h2>";

        // Intentos
        $stmt = $conexion->query("SELECT COUNT(*) as total FROM mercadopago_intentos");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "• mercadopago_intentos: {$result['total']} registros<br>";

        // Pagos
        $stmt = $conexion->query("SELECT COUNT(*) as total FROM mercadopago_pagos");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "• mercadopago_pagos: {$result['total']} registros<br>";

        // Webhooks
        $stmt = $conexion->query("SELECT COUNT(*) as total FROM mercadopago_webhooks");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "• mercadopago_webhooks: {$result['total']} registros<br>";

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
