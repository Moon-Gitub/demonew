<?php
/**
 * Script de prueba para verificar integraciones
 * Acceder desde: testing/test-integraciones.php
 */

// Cargar configuración
require_once "../config.php";
require_once "../modelos/conexion.php";
require_once "../modelos/integraciones.modelo.php";
require_once "../controladores/integraciones.controlador.php";

header('Content-Type: application/json');

try {
    // Probar conexión
    $conexion = Conexion::conectar();
    echo json_encode(['status' => 'ok', 'mensaje' => 'Conexión a BD exitosa'], JSON_PRETTY_PRINT);
    echo "\n\n";
    
    // Verificar si existe la tabla
    $stmt = $conexion->query("SHOW TABLES LIKE 'integraciones'");
    $tablaExiste = $stmt->fetch();
    
    if(!$tablaExiste){
        echo json_encode(['error' => 'La tabla integraciones NO existe. Ejecuta el SQL primero.'], JSON_PRETTY_PRINT);
        exit;
    }
    
    echo json_encode(['status' => 'ok', 'mensaje' => 'Tabla integraciones existe'], JSON_PRETTY_PRINT);
    echo "\n\n";
    
    // Probar obtener todas las integraciones
    $item = null;
    $valor = null;
    $integraciones = ControladorIntegraciones::ctrMostrarIntegraciones($item, $valor);
    
    echo json_encode(['status' => 'ok', 'total' => count($integraciones), 'integraciones' => $integraciones], JSON_PRETTY_PRINT);
    echo "\n\n";
    
    // Probar obtener por ID
    if(count($integraciones) > 0){
        $id = $integraciones[0]['id'];
        $item = "id";
        $valor = $id;
        $integracion = ControladorIntegraciones::ctrMostrarIntegraciones($item, $valor);
        
        echo json_encode(['status' => 'ok', 'id_buscado' => $id, 'integracion' => $integracion], JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], JSON_PRETTY_PRINT);
} catch (Error $e) {
    echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], JSON_PRETTY_PRINT);
}

