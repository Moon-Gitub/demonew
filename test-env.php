<?php
/**
 * ARCHIVO DE PRUEBA - Verificar que .env funciona
 * Acceder a: https://newmoon.posmoon.com.ar/test-env.php
 */

// Cargar vendor autoload
require_once "extensiones/vendor/autoload.php";

// Cargar configuración
require_once "config.php";

// Cargar .env si existe
if (file_exists(__DIR__ . '/.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "✅ Dotenv está instalado y cargado<br><br>";
} else {
    echo "❌ Dotenv NO está disponible (usando config.php como fallback)<br><br>";
}

// Verificar variables
echo "<h2>📋 Variables de Entorno Cargadas:</h2>";
echo "<pre>";
echo "DB_HOST: " . getenv('DB_HOST') . "\n";
echo "DB_NAME: " . getenv('DB_NAME') . "\n";
echo "DB_USER: " . getenv('DB_USER') . "\n";
echo "DB_PASS: " . (getenv('DB_PASS') ? '***oculta***' : 'NO DEFINIDA') . "\n\n";

echo "MOON_DB_HOST: " . getenv('MOON_DB_HOST') . "\n";
echo "MOON_DB_NAME: " . getenv('MOON_DB_NAME') . "\n";
echo "MOON_DB_USER: " . getenv('MOON_DB_USER') . "\n";
echo "MOON_DB_PASS: " . (getenv('MOON_DB_PASS') ? '***oculta***' : 'NO DEFINIDA') . "\n\n";

echo "MP_PUBLIC_KEY: " . substr(getenv('MP_PUBLIC_KEY'), 0, 20) . "...\n";
echo "MP_ACCESS_TOKEN: " . substr(getenv('MP_ACCESS_TOKEN'), 0, 20) . "...\n\n";

echo "MOON_CLIENTE_ID: " . getenv('MOON_CLIENTE_ID') . "\n";
echo "</pre>";

// Probar conexión a BD Moon
echo "<h2>🔌 Prueba de Conexión BD Moon:</h2>";
require_once "modelos/conexion.php";

try {
    $conexion = Conexion::conectarMoon();
    if ($conexion) {
        echo "✅ Conexión exitosa a BD Moon<br>";

        // Probar query simple
        $stmt = $conexion->query("SELECT COUNT(*) as total FROM clientes");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ BD Moon tiene {$result['total']} clientes registrados<br>";
    } else {
        echo "❌ No se pudo conectar a BD Moon (conexión retornó null)<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>⚠️ IMPORTANTE:</strong> Elimina este archivo después de probar por seguridad:</p>";
echo "<code>rm /home/newmoon/public_html/test-env.php</code>";
?>
