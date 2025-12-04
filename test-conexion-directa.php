<?php
/**
 * TEST DE CONEXIÓN DIRECTA
 * Prueba la conexión sin pasar por el sistema
 */

echo "<h1>Test de Conexión Directa</h1><pre>";

// Test 1: Conexión directa con credenciales hardcodeadas
echo "═══════════════════════════════════════\n";
echo "TEST 1: Conexión directa\n";
echo "═══════════════════════════════════════\n\n";

$host = 'localhost';
$db = 'newmoon_newmoon_db';
$user = 'newmoon_newmoon_user';
$pass = '61t;t62h5P$}.sXT';

echo "Host: $host\n";
echo "DB: $db\n";
echo "User: $user\n";
echo "Pass: " . substr($pass, 0, 3) . "***\n\n";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->exec("set names utf8");
    echo "✅ CONEXIÓN EXITOSA!\n\n";
    
    // Probar query
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "Total usuarios: " . $result['total'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
}

// Test 2: Usar la clase Conexion
echo "\n═══════════════════════════════════════\n";
echo "TEST 2: Usando clase Conexion\n";
echo "═══════════════════════════════════════\n\n";

require_once "modelos/conexion.php";

try {
    $conn2 = Conexion::conectar();
    echo "✅ Conexion::conectar() EXITOSA!\n\n";
    
    $stmt2 = $conn2->query("SELECT COUNT(*) as total FROM usuarios");
    $result2 = $stmt2->fetch();
    echo "Total usuarios: " . $result2['total'] . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n═══════════════════════════════════════\n";
echo "</pre>";

