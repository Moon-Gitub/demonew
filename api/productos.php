<?php
/**
 * API ENDPOINT - LISTAR PRODUCTOS
 * Para sincronización con sistema offline
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

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $productos = ControladorProductos::ctrMostrarProductos(null, null, "id");
    $resultado = [];
    
    if($productos && is_array($productos)) {
        foreach($productos as $prod) {
            $resultado[] = [
                'id' => $prod['id'],
                'codigo' => $prod['codigo'],
                'descripcion' => $prod['descripcion'],
                'precio_venta' => floatval($prod['precio_venta']),
                'precio_compra' => floatval($prod['precio_compra'] ?? 0),
                'stock' => floatval($prod['stock'] ?? 0),
                'categoria' => $prod['id_categoria'] ?? '',
                'proveedor' => $prod['id_proveedor'] ?? '',
                'tipo_iva' => floatval($prod['tipo_iva'] ?? 0)
            ];
        }
    }
    
    echo json_encode($resultado);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
