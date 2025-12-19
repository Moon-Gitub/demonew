<?php
/**
 * API ENDPOINT - LISTAR CLIENTES
 * Para sincronización con sistema offline
 * 
 * NOTA: Este endpoint permite sincronización sin sesión activa
 * pero requiere ID de cliente para seguridad básica
 */

// Cargar autoload para Dotenv
require_once "../extensiones/vendor/autoload.php";

// Cargar .env si existe
if (file_exists(__DIR__ . '/../.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

// Verificación básica: requerir ID de cliente Moon como parámetro
$id_cliente = isset($_GET['id_cliente']) ? intval($_GET['id_cliente']) : null;

// Si no se proporciona ID, intentar verificar sesión (para compatibilidad)
if (!$id_cliente) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok") {
        http_response_code(401);
        echo json_encode(['error' => 'Se requiere id_cliente como parámetro o sesión activa']);
        exit;
    }
}

require_once "../controladores/clientes.controlador.php";
require_once "../modelos/clientes.modelo.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $clientes = ControladorClientes::ctrMostrarClientes(null, null);
    $resultado = [];
    
    if($clientes && is_array($clientes)) {
        foreach($clientes as $cliente) {
            $resultado[] = [
                'id' => intval($cliente['id']),
                'nombre' => $cliente['nombre'] ?? '',
                'documento' => $cliente['documento'] ?? '',
                'tipo_documento' => intval($cliente['tipo_documento'] ?? 0),
                'condicion_iva' => intval($cliente['condicion_iva'] ?? 0),
                'email' => $cliente['email'] ?? '',
                'telefono' => $cliente['telefono'] ?? '',
                'direccion' => $cliente['direccion'] ?? '',
                'display' => $cliente['id'] . '-' . ($cliente['nombre'] ?? 'Sin nombre')
            ];
        }
    }
    
    echo json_encode($resultado);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
