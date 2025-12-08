<?php
/**
 * Script de prueba para verificar integraciones N8N para el chat
 * Acceder desde: testing/test-integraciones-chat.php
 */

// Cargar configuración
require_once "../extensiones/vendor/autoload.php";
if (file_exists(__DIR__ . '/../.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}
require_once "../helpers.php";
require_once "../config.php";
require_once "../modelos/conexion.php";
require_once "../modelos/integraciones.modelo.php";
require_once "../controladores/integraciones.controlador.php";

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 Diagnóstico de Integraciones N8N para Chat</h1>";
echo "<hr>";

try {
    // Probar conexión
    $conexion = Conexion::conectar();
    echo "<p>✅ <strong>Conexión a BD:</strong> Exitosa</p>";
    
    // Verificar si existe la tabla
    $stmt = $conexion->query("SHOW TABLES LIKE 'integraciones'");
    $tablaExiste = $stmt->fetch();
    
    if(!$tablaExiste){
        echo "<p>❌ <strong>Error:</strong> La tabla integraciones NO existe. Ejecuta el SQL primero.</p>";
        exit;
    }
    
    echo "<p>✅ <strong>Tabla integraciones:</strong> Existe</p>";
    echo "<hr>";
    
    // Buscar TODAS las integraciones
    echo "<h2>📋 Todas las integraciones en la BD:</h2>";
    $todas = ControladorIntegraciones::ctrMostrarIntegraciones(null, null);
    echo "<pre>";
    print_r($todas);
    echo "</pre>";
    echo "<hr>";
    
    // Buscar por tipo "n8n" (minúsculas)
    echo "<h2>🔍 Buscando por tipo 'n8n' (minúsculas):</h2>";
    $item = "tipo";
    $valor = "n8n";
    $integraciones = ControladorIntegraciones::ctrMostrarIntegraciones($item, $valor);
    echo "<pre>";
    print_r($integraciones);
    echo "</pre>";
    
    if($integraciones && is_array($integraciones) && count($integraciones) > 0){
        echo "<h3>✅ Se encontraron " . count($integraciones) . " integración(es) de tipo 'n8n'</h3>";
        
        foreach($integraciones as $idx => $integracion){
            echo "<h4>Integración #" . ($idx + 1) . ":</h4>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> " . ($integracion["id"] ?? 'NULL') . "</li>";
            echo "<li><strong>Nombre:</strong> " . ($integracion["nombre"] ?? 'NULL') . "</li>";
            echo "<li><strong>Tipo:</strong> " . ($integracion["tipo"] ?? 'NULL') . "</li>";
            echo "<li><strong>Activo (raw):</strong> " . var_export($integracion["activo"] ?? null, true) . "</li>";
            echo "<li><strong>Activo (int):</strong> " . (isset($integracion["activo"]) ? (int)$integracion["activo"] : 'NULL') . "</li>";
            echo "<li><strong>Webhook URL:</strong> " . ($integracion["webhook_url"] ?? 'NULL') . "</li>";
            echo "<li><strong>Webhook vacío?:</strong> " . (empty($integracion["webhook_url"]) ? 'SÍ ❌' : 'NO ✅') . "</li>";
            
            $activo = isset($integracion["activo"]) ? (int)$integracion["activo"] : 0;
            $tieneWebhook = !empty($integracion["webhook_url"]);
            $esValida = $activo == 1 && $tieneWebhook;
            
            echo "<li><strong>¿Es válida para chat?:</strong> " . ($esValida ? '✅ SÍ' : '❌ NO') . "</li>";
            echo "</ul>";
        }
    } else {
        echo "<p>❌ <strong>No se encontraron integraciones de tipo 'n8n'</strong></p>";
    }
    
    echo "<hr>";
    
    // Consulta directa a la BD
    echo "<h2>🔍 Consulta directa a la BD:</h2>";
    $stmt = $conexion->query("SELECT * FROM integraciones WHERE LOWER(tipo) = 'n8n'");
    $directas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($directas);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p>❌ <strong>Error fatal:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

